<?php
namespace Zeedhi\Framework\DTO\Response;

class File {

	/** @var string */
	protected $filePath;
	/** @var string */
	protected $contentType;
	/** @var string */
	protected $downloadFileName;
	/** @var boolean */
	protected $deleteAfterDownload;

	public function __construct($filePath, $deleteAfterDownload = false, $downloadFileName = null, $contentType = "application/octet-stream") {
		$this->filePath = $filePath;
		$this->contentType = $contentType;
		$this->downloadFileName = empty($downloadFileName) ? basename($filePath) : $downloadFileName;
		$this->deleteAfterDownload = $deleteAfterDownload;
	}

	/**
	 * @return string
	 */
	public function getFilePath(){
		return $this->filePath;
	}

	/**
	 * @return string
	 */
	public function getContentType(){
		return $this->contentType;
	}

	/**
	 * @return string
	 */
	public function getDownloadFileName(){
		return $this->downloadFileName;
	}

	/**
	 * @return boolean
	 */
	public function isToDeleteAfterDownload(){
		return $this->deleteAfterDownload;
	}

	/**
	 * Content Type Setter
	 *
	 * @param string	$contentType 	The type of the file
	 *
	*/
	public function setContentType($contentType){
		$this->contentType = $contentType;
	}

	/**
	 * Download File Name Setter
	 *
	 * @param string	$downloadFileName 	The name to be used for downloaded file
	 *
	*/
	public function setDownloadFileName($downloadFileName){
		$this->downloadFileName = $downloadFileName;
	}

	/**
	 * Delete After Download Setter
	 *
	 * @param string	$deleteAfterDownload 	Defines if the origin file should be deleted after download
	 *
	*/
	public function setProgramatedDeletionAfterDownload($deleteAfterDownload){
		$this->deleteAfterDownload = $deleteAfterDownload;
	}
}