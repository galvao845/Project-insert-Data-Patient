<?php
namespace Zeedhi\Framework\HTTP\Response;

use Zeedhi\Framework\HTTP\Response;

/**
 * Class JSON
 *
 * Response represents an HTTP response in JSON format.
 *
 * @package Zeedhi\Framework\HTTP\Response
 */
class FileDownload extends Response {

	/** @var string */
	protected $filePath;
	/** @var string */
	protected $downloadFileName;
	/** @var boolean */
	protected $deleteAfterDownload;
	/** @var string */
	protected $contentType;

	/**
	 * Constructor.
	 *
	 * @param mixed $content    The response data
	 * @param int   $statusCode The response status code
	 *
	 * @throws \Exception
	 */
	public function __construct($content = array(), $statusCode = null, $headers = array()){
		if(empty($content)){
			throw new \Exception("No file path given to constructor!");
		}
		$this->filePath = $content['filePath'];
		$this->downloadFileName = $content['downloadFileName'];
		$this->deleteAfterDownload = $content['deleteAfterDownload'];
		$this->contentType = $content['contentType'];
        $headers["Content-Disposition"] = array("attachment; filename= \"".$this->downloadFileName."\"");
        $headers["Content-Type"]        = $this->contentType;
		parent::__construct($this->filePath, $this->contentType, $statusCode, $headers);
	}


	/**
	 * Headers Setter
	 *
	 * @param string	$headerName 	The name of the header
	 * @param mixed 	$header     	The content of the header
	 * @param boolean	$replace		Defines if the header should replace same type
	 *
	*/
	public function setHeader($headerName = null, $header = array(), $replace = true){
		$this->headers->set($headerName, $header, $replace);
	}

	public function sendContent(){
		if(file_exists($this->filePath)){
			$file = fopen($this->filePath, "rb");
			fpassthru($file);
			fclose($file);
			if($this->deleteAfterDownload){
				unlink($this->filePath);
			}
		} else {
			throw new \Exception("Couldn't find file!");
		}
	}
}