<?php
namespace Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Events\PreDispatch\Listener as AbstractListener;

class UploadFilesListener extends AbstractListener {

    protected $fileUploader;
    protected $routesToListen = array();

    public function __construct(Server $fileUploader, $routesToListen) {
        $this->fileUploader = $fileUploader;
        $this->routesToListen = $routesToListen;
    }

    protected function isUploadRoute($routePath) {
        return isset($this->routesToListen[$routePath]);
    }

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

    public function uploadFiles(&$row, $fileFields, $customFolder) {
        foreach ($fileFields as $fileField) {
            if ($this->shouldUpload($row, $fileField)) {
                $this->fileUploader->uploadFilesInRow($row, $fileField, $customFolder);
            }
        }
    }

    public function preDispatch(Request $request) {
        $route = $request->getRoutePath();

        if ($this->isUploadRoute($route)) {
            $route = $this->routesToListen[$route];
            $fileFields = $route['fileFields'];
            $customFolder = $route['customFolder'];

            foreach($this->getRequestRows($request) as $row) {
                $this->uploadFiles($row, $fileFields, $customFolder);
            }
        }
    }

    protected function shouldUpload($row, $fileField) {
        return isset($row[$fileField]) && is_array($row[$fileField]) && isset(current($row[$fileField])['b64File']);
    }
}