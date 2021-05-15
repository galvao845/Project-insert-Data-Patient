<?php
namespace tests\Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\Remote\FileUploader\TypeValidatorListener;
use Zeedhi\Framework\File\TypeValidator;
use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\DataSource\DataSet;

class TypeValidatorListenerTest extends \PHPUnit\Framework\TestCase {

    /** @var TypeValidator|\PHPUnit_Framework_MockObject_MockObject $typeValidatorMock */
    protected $typeValidatorMock;
    /** @var array $configuration */
    protected $configuration;
    /** @var \Zeedhi\Framework\DTO\Row $row */
    protected $row;
    /** @var TypeValidatorListener */
    protected $preDispatchListener;
    /** @var string[] */
    protected $extensions;
    /** @var string[] */
    protected $mimeTypes;

    public function setUp(){
        $this->typeValidatorMock = $this->getMockBuilder(TypeValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(array('isValid'))->getMock();

        $this->extensions = array("js", "php", "html", "txt");
        $this->mimeTypes = array("application/js", "text/plain");
        $this->configuration = array(
            "/routeName" => array(
                "fileField" => "myFile",
                "customFolder" => "folder/toStore/myFile/",
                "extensions" => $this->extensions,
                "mimeTypes" => $this->mimeTypes,
            )
        );

        $this->preDispatchListener = new TypeValidatorListener($this->typeValidatorMock, $this->configuration);
    }

    public function testPreDispatchUploadRouteRow() {
        $fileData = array(
            "name" => "test.txt",
            "type" => "text/plain",
            "b64File" => "data:text/plain;base64,test"
        );
        $row = new Row(array(
            "otherField" => "otherValue",
            "myFile"     => array($fileData)
        ));
        $request = new Request\Row($row, "POST", "/routeName", "userId");
        $this->typeValidatorMock->expects($this->once())->method('isValid')
            ->with($fileData, $this->mimeTypes, $this->extensions)
            ->willReturn(true);
        $this->preDispatchListener->preDispatch($request);
    }

    public function testPreDispatchUploadWithInvalidFile() {
        $fileData = array(
            "name" => "test.md",
            "type" => "text/plain",
            "b64File" => "data:text/plain;base64,test"
        );
        $row = new Row(array(
            "otherField" => "otherValue",
            "myFile"     => array($fileData)
        ));
        $request = new Request\Row($row, "POST", "/routeName", "userId");
        $this->typeValidatorMock->expects($this->once())->method('isValid')
            ->with($fileData, $this->mimeTypes, $this->extensions)
            ->willReturn(false);
        $this->preDispatchListener->preDispatch($request);
    }

    public function testPreDispatchUploadRouteDataSet(){
        $fileData = array(
            "name" => "test.txt",
            "type" => "text/plain",
            "b64File" => "data:text/plain;base64,test"
        );
        $row = new Row(array(
            "otherField" => "otherValue",
            "myFile"     => array($fileData)
        ));
        $dataSet = new DataSet("upload", array($row));
        $request = new Request\DataSet($dataSet, "POST", "/routeName", "userId");
        $this->typeValidatorMock->expects($this->once())->method('isValid')
            ->with($fileData, $this->mimeTypes, $this->extensions)
            ->willReturn(true);
        $this->preDispatchListener->preDispatch($request);
    }

    public function testPreDispatchNotUploadRoute() {
        $row = new Row(array("foo" => "bar", "baz" => "qux"));
        $request = new Request\Row($row, "POST", "/wrongRoute", "userId");
        $this->typeValidatorMock->expects($this->never())->method("isValid");
        $this->preDispatchListener->preDispatch($request);
    }

    public function testPreDispatchUploadRouteWithoutUploadFieldInRow() {
        $row = new Row(array(
            "otherField"  => "otherValue",
            "otherField2" => "trem xuxu e lala"
        ));
        $request = new Request\Row($row, "POST", "/routeName", "userId");
        $this->typeValidatorMock->expects($this->never())->method($this->anything());
        $this->preDispatchListener->preDispatch($request);
    }

    public function testPreDispatchUploadRouteWithoutFieldToUpload() {
        $row = new Row(array(
            "otherField" => "otherValue",
            "myFile"     => "fileServer/Folders/filePath.jpg"
        ));
        $request = new Request\Row($row, "POST", "/routeName", "userId");
        $this->typeValidatorMock->expects($this->never())->method($this->anything());
        $this->preDispatchListener->preDispatch($request);
    }
}
