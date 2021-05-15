<?php
namespace tests\Zeedhi\Framework\File;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;
use Zeedhi\Framework\File\TypeValidator;
use Zeedhi\Framework\File\MimeTypeExtensionGuesser as ZhMimeTypeExtensionGuesser;

class TypeValidatorTest extends \PHPUnit\Framework\TestCase {

    /** @var TypeValidator */
    protected $validator;

    public function setUp(){
        $this->validator = new TypeValidator(
            new MimeTypeExtensionGuesser()
        );
    }

    public function testBasicUsage() {
        $uploadedFile = array(
            "b64File" => "data:text/plain;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.txt",
            "size" => 9,
            "type" => "text/plain",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array("text/plain");
        $acceptedExtensions = array("txt");
        $this->assertTrue($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testBasicFileWithIncompatibleExtension() {
        $uploadedFile = array(
            "b64File" => "data:text/plain;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.md",
            "size" => 9,
            "type" => "text/plain",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array("text/plain");
        $acceptedExtensions = array("txt");
        $this->assertFalse($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testBasicFileWithIncompatibleMimeTypeOnPropertyType() {
        $uploadedFile = array(
            "b64File" => "data:text/plain;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.txt",
            "size" => 9,
            "type" => "text/markdown",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array("text/plain");
        $acceptedExtensions = array("txt");
        $this->assertFalse($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testBasicFileWithIncompatibleMimeTypeOnBase64Data() {
        $uploadedFile = array(
            "b64File" => "data:text/markdown;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.txt",
            "size" => 9,
            "type" => "text/plain",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array("text/plain");
        $acceptedExtensions = array("txt");
        $this->assertFalse($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testBasicFileWithoutExtensionRestrictions() {
        $uploadedFile = array(
            "b64File" => "data:text/plain;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.txt",
            "size" => 9,
            "type" => "text/plain",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array("text/plain");
        $acceptedExtensions = array();
        $this->assertTrue($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testBasicFileWithoutMimeTypeRestrictions() {
        $uploadedFile = array(
            "b64File" => "data:text/plain;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.txt",
            "size" => 9,
            "type" => "text/plain",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array();
        $acceptedExtensions = array("txt");
        $this->assertTrue($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testBasicFileWithoutAnyRestrictions() {
        $uploadedFile = array(
            "b64File" => "data:text/plain;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.txt",
            "size" => 9,
            "type" => "text/plain",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array();
        $acceptedExtensions = array();
        $this->assertTrue($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testInvalidPairOfMimeTypeAndFileExtension() {
        $uploadedFile = array(
            "b64File" => "data:text/plain;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.md",
            "size" => 9,
            "type" => "text/plain",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array("text/plain", "text/markdown");
        $acceptedExtensions = array("txt", "md");
        $this->assertFalse($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }

    public function testNewMimeTypes() {
        $mimeTypeGuesser = new ZhMimeTypeExtensionGuesser();
        $this->validator = new TypeValidator($mimeTypeGuesser);
        $mimeTypeGuesser->addMimeType("text/markdown", "md");

        $uploadedFile = array(
            "b64File" => "data:text/markdown;base64,Zm9vYmFyYmF6",
            "lastModified" => 1508947455970,
            "lastModifiedDate" => "2017-10-25T16:04:15.970Z",
            "name" => "teste.md",
            "size" => 9,
            "type" => "text/markdown",
            "webkitRelativePath" => ""
        );
        $acceptedMimeTypes = array("text/markdown");
        $acceptedExtensions = array("md");
        $this->assertTrue($this->validator->isValid($uploadedFile, $acceptedMimeTypes, $acceptedExtensions));
    }
}