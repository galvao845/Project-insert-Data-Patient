<?php
namespace Zeedhi\Framework\DTO;

use Zeedhi\Framework\DataSource\DataSet;

class Response {

    const STATUS_SUCCESS = 'S';
    const STATUS_ERROR = 'E';
    const STATUS_WARNING = 'W';

    /** @var string */
    protected $status;
    /** @var \Zeedhi\Framework\DTO\Response\Message[] */
    protected $messages;
    /** @var \Zeedhi\Framework\DTO\Response\Error */
    protected $error;
    /** @var  \Zeedhi\Framework\DataSource\DataSet[] */
    protected $dataSets;
    /** @var  \Zeedhi\Framework\DTO\Response\Method[] */
    protected $methods;
    /** @var  \Zeedhi\Framework\DTO\Response\Notification[] */
    protected $notifications;
    /** @var  \Zeedhi\Framework\DTO\Response\File */
    protected $fileToDownload;

    function __construct(){
        $this->status = self::STATUS_SUCCESS;
        $this->methods = array();
        $this->messages = array();
        $this->dataSets = array();
        $this->notifications = array();
        $this->fileToDownload = null;
    }

    /**
     * @return \Zeedhi\Framework\DataSource\DataSet[]
     */
    public function getDataSets(){
        return $this->dataSets;
    }

    public function setDataSets($dataSets){
        $this->dataSets = $dataSets;
    }

    /**
     * @return \Zeedhi\Framework\DTO\Response\Error
     */
    public function getError(){
        return $this->error;
    }

    /**
     * @return \Zeedhi\Framework\DTO\Response\Message[]
     */
    public function getMessages(){
        return $this->messages;
    }

    /**
     * @return \Zeedhi\Framework\DTO\Response\Method[]
     */
    public function getMethods(){
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getStatus(){
        return $this->status;
    }

    /**
     * @return Response\Notification[]
     */
    public function getNotifications(){
        return $this->notifications;
    }

    /**
     * @return Response\File
     */
    public function getFileToDownload(){
        return $this->fileToDownload;
    }

      /**
     * @param \Zeedhi\Framework\DTO\Response\Error $error
     */
    public function setError($error){
        $this->error = $error;
        $this->setStatus(Response::STATUS_WARNING);
    }

    /**
     * @param \Zeedhi\Framework\DTO\Response\Error $error
     */
    public function setCriticalError($error){
        $this->error = $error;
        $this->setStatus(Response::STATUS_ERROR);
    }
    /**
     * @param string $status
     */
    public function setStatus($status){
        $this->status = $status;
    }

    /**
     * @param Response\File $file
     */
    public function setFileToDownload(Response\File $file){
        $this->fileToDownload = $file;
    }

    public function addMessage(Response\Message $message){
        $this->messages[] = $message;
    }

    public function addDataSet(DataSet $dataSet){
        //@todo verify to index by "widget_id" //$this->dataSets[$dataSet->getId()] = $dataSet;
        $this->dataSets[] = $dataSet;
    }

    public function addMethod(Response\Method $method){
        $this->methods[] = $method;
    }

    public function addNotification(Response\Notification $notification){
        $this->notifications[] = $notification;
    }

    /**
     * @return boolean
    */
    public function isFileDownloadResponse(){
        return $this->fileToDownload instanceof Response\File;
    }
}