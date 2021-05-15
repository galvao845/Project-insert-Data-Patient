<?php
namespace tests\Zeedhi\Framework\DTO\Response;

use Zeedhi\Framework\DTO\Response\File;
use Zeedhi\Framework\DTO\Response;

class FileTest extends \PHPUnit\Framework\TestCase{

	public function testDownloadFileContentTypeSetter(){
		$file = new File('foo');
		$file->setContentType('text/plain');
		$response = new Response();
		$response->setFileToDownload($file);
		$this->assertEquals($response->getFileToDownload()->getContentType(), 'text/plain');
	}

	public function testDownloadFileNameSetter(){
		$file = new File('foo');
		$file->setDownloadFileName('bar');
		$response = new Response();
		$response->setFileToDownload($file);
		$this->assertEquals($response->getFileToDownload()->getDownloadFileName(), 'bar');
	}

	public function testDownloadFileDeletionAfterDownloadSetter(){
		$file = new File('foo');
		$file->setProgramatedDeletionAfterDownload(true);
		$response = new Response();
		$response->setFileToDownload($file);
		$this->assertEquals($response->getFileToDownload()->isToDeleteAfterDownload(), true);
	}

}
