<?php
namespace Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DTO\Request;

class Listener extends  \Zeedhi\Framework\Events\PreDispatch\Listener {

    /** @var Server */
    protected $fileUploader;
    /**
     * @var array value example:
     * array(
     *   "routeName" => array(
     *     "fileField" => "MY_FILE_FIELD_NAME_IN_ROW",
     *     "customFolder" => "folder/toStore/myFile/",
     *   )
     * )
     */
    protected $routesToListen = array();


    /**
     * FileUploader constructor.
     * @param Server $fileUploader
     * @param string                                  $routesToListen
     */
    public function __construct(Server $fileUploader, $routesToListen) {
        $this->fileUploader = $fileUploader;
        $this->routesToListen = $routesToListen;
    }

    /**
     * @param string $routePath
     * @return bool
     */
    protected function isUploadRoute($routePath) {
        return isset($this->routesToListen[$routePath]);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getRequestRows(Request $request) {
        $rows = array();
        if ($request instanceof Request\Row) {
            $rows = array($request->getRow());
        }

        if ($request instanceof Request\DataSet) {
            $rows = $request->getDataSet()->getRows();
        }

        return $rows;
    }

    /**
     * Listen to request, if is a configured as fileUpload one,
     * the file will be uploaded and then replace the file property with file server data.
     *
     * @param Request $request
     */
    public function preDispatch(Request $request) {
        if ($this->isUploadRoute($request->getRoutePath())) {
            $route = $this->routesToListen[$request->getRoutePath()];
            $fileField = $route['fileField'];
            $customFolder = $route['customFolder'];
            foreach($this->getRequestRows($request) as $row) {
                if ($this->shouldUpload($row, $fileField)) {
                    $this->fileUploader->uploadFilesInRow($row, $fileField, $customFolder);
                }
            }
        }
    }

    /**
     * @param $row
     * @param $fileField
     * @return bool
     */
    protected function shouldUpload($row, $fileField) {
        return isset($row[$fileField]) && is_array($row[$fileField]) && isset(current($row[$fileField])['b64File']);
    }
}