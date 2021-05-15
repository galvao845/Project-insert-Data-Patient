<?php
namespace tests\Zeedhi\Framework\HTTP\Response;

use Zeedhi\Framework\DTO;
use Zeedhi\Framework\DTO\Response\File;
use Zeedhi\Framework\HTTP\Kernel;
use Zeedhi\Framework\HTTP\Response\FileDownload;
use Zeedhi\Framework\Security\Support\Cors;
use Zeedhi\Framework\Security\Support\CorsOptions;

class FileDownloadTest extends \PHPUnit\Framework\TestCase {

	protected static $URL = "/framework/backend/service/index.php";
	/** @var Kernel */
	protected $kernel;

	public function setUp() {
		$corsOptions = new CorsOptions('*', '*', '*', 0, false, false);
		$corsService = new Cors($corsOptions);
		$this->kernel = new Kernel(self::$URL, $corsService);
	}

	public function testConstructor(){
		$content = array(
			"filePath" => __DIR__."/downloadFileTest.txt",
			"contentType" => "text/plain",
			"deleteAfterDownload" => false,
			"downloadFileName" => "download.txt"
		);
		$fileToDownload = new FileDownload($content);
		$expectedResponse = __DIR__."/downloadFileTest.txt";
		$this->assertEquals($expectedResponse, $fileToDownload->getContent());
	}

	public function testSendHeadersAndSendContentMethod(){
		$_GET = array();
		$this->kernel->getRequest();
		$fileToDownload = new File(__DIR__."/downloadFileTest.txt");
		$response = new DTO\Response();
		$response->setStatus($response::STATUS_SUCCESS);
		$response->setFileToDownload($fileToDownload);
		ob_start();
		$this->kernel->sendResponse($response);
		$expectedResponse = 'Anything';
		$content = ob_get_contents();
		ob_end_clean();
		$this->assertEquals($expectedResponse, $content);
	}

}
