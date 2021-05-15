<?php
namespace Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\DTO\Row;
use Zeedhi\Framework\File\TypeValidator;
use Zeedhi\Framework\Events\PreDispatch\Listener as AbstractListener;

class TypeValidatorListener extends AbstractListener {

    /** @var TypeValidator $validator */
    protected $validator;

    /**
     * @var array $configuration
     * array(
     *   "routeName" => array(
     *     "fileField" => "MY_FILE_FIELD_NAME_IN_ROW",
     *     "customFolder" => "folder/toStore/myFile/",
     *     "mimeTypes" => array('application/javascript', ...),
     *     "extensions" => array('.js', ...),
     *   )
     * )
     */
    protected $configuration = array();

    /**
     * Constructor
     *
     * @param TypeValidator $validator     The file type validator.
     * @param array			$configuration The file configuration.
     */
    public function __construct(TypeValidator $validator, $configuration){
        $this->validator = $validator;
        $this->configuration = $configuration;
    }

    /**
     * @param Row    $row		Row object.
     * @param string $fileField	File name.
     *
     * @return boolean
     */
    private function validateFileType($row, $fileField, $routeConfiguration){
        $mimeTypes = isset($routeConfiguration["mimeTypes"]) ? $routeConfiguration["mimeTypes"] : array();
        $extensions = isset($routeConfiguration["extensions"]) ? $routeConfiguration["extensions"] : array();
        foreach($row[$fileField] as $file) {
            if(!$this->validator->isValid($file, $mimeTypes, $extensions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Request $request The request object.
     *
     * @return boolean
     */
    private function isUploadRoute(Request $request){
        return isset($this->configuration[$request->getRoutePath()]);
    }

    /**
     * @param Row    $row		Row object.
     * @param string $fileField	File name.
     *
     * @return  boolean
     */
    private function shouldValidate($row, $fileField){
        return isset($row[$fileField]) && is_array($row[$fileField]) && isset(current($row[$fileField])['b64File']);
    }

    /**
     * @param Request $request The request object.
     *
     * @return array An array of Row objects.
     */
    private function getRequestRows(Request $request) {
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
     * @param Request $request The request object.
     */
    public function preDispatch(Request $request){
        if($this->isUploadRoute($request)){
            $route = $this->configuration[$request->getRoutePath()];
            $fileField = $route['fileField'];
            foreach($this->getRequestRows($request) as $row) {
                if ($this->shouldValidate($row, $fileField)) {
                    $this->validateFileType($row, $fileField, $route);
                }
            }
        }
    }
}