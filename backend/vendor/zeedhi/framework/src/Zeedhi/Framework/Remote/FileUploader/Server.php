<?php
namespace Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DTO\Request\DataSet;
use Zeedhi\Framework\DTO\Request\Row;
use Zeedhi\Framework\DTO\Response;
use Zeedhi\Framework\Remote\Exception;
use Zeedhi\Framework\Remote\HttpInterface;
use Zeedhi\Framework\Remote\Server as BaseServer;

class Server extends BaseServer {

    const REMOTE_FILE_FIELD_NAME = 'FILE';
    const REMOTE_FOLDER_FIELD_NAME = 'FOLDER';

    const REQUEST_DATA_SET = 'upload';
    const RESPONSE_DATA_SET = 'new_files';

    const UPLOAD_ROUTE = '/upload';
    const DELETE_ROUTE = '/delete';

    /**
     * @param HttpInterface $HTTPRequestStrategy The server interface strategy.
     * @param string        $apiKey              The access key for the remote file server.
     * @param array         $headers             List of headers to be sent on requests
     */
    public function __construct(HttpInterface $HTTPRequestStrategy, $apiKey, array $headers = array()) {
        $authData = array("apiKey" => $apiKey);
        $headers = array_merge($authData, $headers);
        parent::__construct($HTTPRequestStrategy, $headers);
    }

    protected function createFileServerRow($row, $fileFieldName, $customFolder) {
        return array(
            self::REMOTE_FILE_FIELD_NAME => $row[$fileFieldName],
            self::REMOTE_FOLDER_FIELD_NAME => $customFolder
        );
    }

    protected function normalizeFieldValue($fieldValue) {
        $newFieldValue = array();
        foreach($fieldValue as $fileData) {
            $newFieldValue[] = array(
                "name" => $fileData['NAME'],
                "size" => $fileData['SIZE'],
                "type" => $fileData['TYPE'],
                "path" => $fileData['PATH'],
            );
        }

        return $newFieldValue;
    }

    /**
     * @param $response
     * @throws \Exception
     */
    protected function checkResponseError(Response $response) {
        if ($response->getError()) {
            throw Exception::remoteServerError($response->getError());
        }
    }

    /**
     * @return string
     */
    protected function getUserId() {
        return 'userId';
    }

    /**
     * Upload files in rows and replace the property to the file data with server path.
     *
     * @param array|\ArrayAccess $row           The row where files are stored.
     * @param string             $fileFieldName The name of column row to be uploaded.
     * @param string             $customFolder  The folder where files should be stored.
     *
     * @throws Exception
     */
    public function uploadFilesInRow(&$row, $fileFieldName, $customFolder = "") {
        $serverRow = $this->createFileServerRow($row, $fileFieldName, $customFolder);
        $dataSet = new \Zeedhi\Framework\DataSource\DataSet(self::REQUEST_DATA_SET, array($serverRow));
        $response = $this->request(new DataSet($dataSet, HttpInterface::METHOD_POST, self::UPLOAD_ROUTE, $this->getUserId()));
        $this->checkResponseError($response);

        $responseDataSet = $response->getDataSets()[0];
        foreach ($responseDataSet->getRows() as $key => $responseRow) {
            $row[$fileFieldName] = $this->normalizeFieldValue($responseRow[self::REMOTE_FILE_FIELD_NAME]);
        }
    }

    /**
     * Delete a previously uploaded file.
     *
     * @param string $path The file path of file to be excluded.
     *
     * @throws Exception
     */
    public function deleteFile($path) {
        $response = $this->request(new Row(array('PATH'=>$path), HttpInterface::METHOD_POST, self::DELETE_ROUTE, $this->getUserId()));
        $this->checkResponseError($response);
    }
}