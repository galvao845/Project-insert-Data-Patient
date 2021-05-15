<?php
namespace Zeedhi\Framework\HTTP;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zeedhi\Framework\DTO;
use Zeedhi\Framework\DataSource;
use Zeedhi\Framework\HTTP\Response\JSON;
use Zeedhi\Framework\Security\Support\Cors;

class Kernel implements \Zeedhi\Framework\Kernel {

    /** @var Request */
    protected $request;
    /** @var string */
    protected $baseUri;
    /** @var Cors */
    protected $corsService;
    /** @var boolean Identify if developing */
    protected $isDevMode;

    /**
     * Constructor...
     * Should be passed through dependency injection the baseUri
     *
     * @param string        $baseUri
     * @param Cors          $corsService
     * @param boolean       $isDevMode
     */
    public function __construct($baseUri, Cors $corsService, $isDevMode = false) {
        $this->baseUri = $baseUri;
        $this->corsService = $corsService;
        $this->isDevMode = $isDevMode;
    }

    /**
     * Consulting global vars e create a DTO\Request object.
     *
     * @return DTO\Request
     */
    public function getRequest() {
        $this->getHTTPRequest();
        if ($this->corsService->isPreflightRequest($this->request)) {
            $kernelResponse = $this->corsService->handlePreflightRequest($this->request);
            $kernelResponse->send();
            exit(0);
        }

        $uri = $this->cleanupUri($this->request->getRequestUri());
        $method = $this->request->getMethod();
        $userId = $this->request->getUserId();
        $requestType = $this->request->getRequestType();
        switch ($requestType) {
            case DTO\Request::TYPE_DATA_SET:
                $rows = $this->convertArrayToObjectRows($this->request->get('dataset'));
                $dataSet = new DataSource\DataSet('', $rows);
                $dtoRequest = new DTO\Request\DataSet($dataSet, $method, $uri, $userId);
                break;
            case DTO\Request::TYPE_ROW:
                $row = new DTO\Row($this->request->get('row'));
                $dtoRequest = new DTO\Request\Row($row, $method, $uri, $userId);
                break;
            case DTO\Request::TYPE_FILTER:
                $filter = new DataSource\FilterCriteria('');
                if ($page = $this->request->get('page')) {
                    $filter->setPage($page);
                    if ($pageSize = $this->request->get('itemsPerPage')) {
                        $filter->setPageSize($pageSize);
                    }
                }

                $filterData = is_array($this->request->get('filter')) ? $this->request->get('filter') : array();
                foreach ($filterData as $filterDefinition) {
                    $this->addConditionInFilterCriteria($filterDefinition, $filter);
                }
                $dtoRequest = new DTO\Request\Filter($filter, $method, $uri, $userId);
                break;
            default:
                $dtoRequest = new DTO\Request($method, $uri, $userId);
                break;
        }

        $parameters = array_merge(
            $this->request->getQueryParameters()->getAll(),
            $this->request->getRequestParameters()->getAll()
        );
        foreach ($parameters as $key => $value) {
            $dtoRequest->setParameter($key, $value);
        }

        return $dtoRequest;
    }

    /**
     * @param DTO\Response $response
     * @param array $content
     * @return array $content
     */
    private function getMessages(DTO\Response $response, array $content){
        if ($messages = $response->getMessages()) {
            $content['messages'] = array();
            foreach ($messages as $key => $message) {
                /** @var \Zeedhi\Framework\DTO\Response\Message $message */
                $content['messages'][$key]['message'] = $message->getMessage();
                $content['messages'][$key]['type'] = $message->getType();
                $content['messages'][$key]['variables'] = $message->getVariables();
            }
        }
        return $content;
    }

    /**
     * @param DTO\Response $response
     * @param array $content
     * @return array $content
     */
    private function getDataSets(DTO\Response $response, array $content){
        if ($dataSets = $response->getDataSets()) {
            $content['dataset'] = array();
            foreach ($dataSets as $dataSet) {
                /** @var \Zeedhi\Framework\DataSource\DataSet $dataSet */
                $content['dataset'][$dataSet->getDataSourceName()] = $dataSet->getRows();
            }
        }
        return $content;
    }

    /**
     * @param DTO\Response $response
     * @param array $content
     * @return array $content
     */
    private function getMethods(DTO\Response $response, array $content){
        if ($methods = $response->getMethods()) {
            $content['method'] = array();
            foreach ($methods as $key => $method) {
                /** @var \Zeedhi\Framework\DTO\Response\Method $method */
                $content['method'][$key]['name'] = $method->getName();
                $content['method'][$key]['parameters'] = $method->getParameters();
            }
        }
        return $content;
    }

    /**
     * @param DTO\Response $response
     * @param array $content
     * @return array $content
     */
    private function getFile(DTO\Response $response, array $content){
        if($response->isFileDownloadResponse()){
            $content = array();
            $fileToDownload = $response->getFileToDownload();
            $content['filePath'] = $fileToDownload->getFilePath();
            $content['downloadFileName'] = $fileToDownload->getDownloadFileName();
            $content['contentType'] = $fileToDownload->getContentType();
            $content['deleteAfterDownload'] = $fileToDownload->isToDeleteAfterDownload();
        }
        return $content;
    }

    /**
     * @param DTO\Response $response
     * @param array $content
     * @return array $content
     */
    private function getNotifications(DTO\Response $response, array $content){
        if ($notifications = $response->getNotifications()) {
            $content['notifications'] = array();
            foreach ($notifications as $notification) {
                $content['notifications'][] = array(
                    'message'   => $notification->getMessage(),
                    'type'      => $notification->getType(),
                    'variables' => $notification->getVariables()
                );
            }
        }
        return $content;
    }

    /**
     * @param DTO\Response $response
     * @param array $content
     * @return array $content
     */
    private function getError(DTO\Response $response, array $content){
        if ($error = $response->getError()) {
            $content = array();
            /** @var \Zeedhi\Framework\DTO\Response\Error $error */
            $content['error'] = $error->getMessage();
            $content['errorCode'] = $error->getErrorCode();
            if($this->isDevMode){
                $content['stackTrace'] = $error->getStackTrace();
                $exceptionsInfo = array();
                $exception = $error->getException();
                while($exception) {
                    $exceptionsInfo[] = array(
                        'message'    => $exception->getMessage(),
                        'code'       => $exception->getCode(),
                        'stackTrace' => $exception->getTraceAsString()
                    );

                    $exception = $exception->getPrevious();
                }

                $content['exceptions'] = $exceptionsInfo;
            }
        }

        return $content;
    }

    /**
     * @param DTO\Response $response
     * @return array
     */
    public function getResponseContent(DTO\Response $response){
        $content = array();
        $content = $this->getError($response, $content);
        $content = $this->getDataSets($response, $content);
        $content = $this->getMessages($response, $content);
        $content = $this->getMethods($response, $content);
        $content = $this->getNotifications($response, $content);
        $content = $this->getFile($response, $content);
        return $content;
    }

    /**
     * Returns the HTTP Status Code.
     *
     * @param DTO\Response $response The response object.
     *
     * @return int $statusCode The http status code.
     */
    public function getStatusCode(DTO\Response $response){
        $status = $response->getStatus();
        $httpOk = $status == DTO\Response::STATUS_SUCCESS ||
                  $status == DTO\Response::STATUS_WARNING;
        $statusCode = $httpOk ?
            Response::HTTP_OK :
            Response::HTTP_INTERNAL_SERVER_ERROR;
        return $statusCode;
    }

    /**
     * Send a response to interface.
     *
     * @param DTO\Response $response
     */
    public function sendResponse(DTO\Response $response) {
        $statusCode = $this->getStatusCode($response);
        $content = $this->getResponseContent($response);
        try {
            if($response->isFileDownloadResponse()){
                $kernelResponse = new Response\FileDownload($content, $statusCode);
            } else {
                $kernelResponse = new JSON($content, $statusCode);
            }
        } catch (\Exception $e) {
            $kernelResponse = new JSON(array(
                "error" => $e->getMessage()
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($this->corsService->isCorsRequest($this->request)) {
            $this->corsService->addActualRequestHeaders($kernelResponse, $this->request);
        }
        $kernelResponse->send();
    }

    /**
     * Return a URI cleanup
     *
     * @param $requestURI
     *
     * @return mixed
     */
    protected function cleanupUri($requestURI) {
        return $this->removeParameters($this->removeBaseUri($requestURI));
    }

    /**
     * @param $requestURI
     *
     * @return mixed
     */
    protected function removeBaseUri($requestURI) {
        $this->baseUri = rtrim($this->baseUri, ' /');
        return preg_replace('#(.*)(' . $this->baseUri . ')#', '', $requestURI);
    }

    /**
     * @param $uri
     *
     * @return mixed
     */
    protected function removeParameters($uri) {
        $uris = explode("?", $uri);
        return $uris[0];
    }

    /**
     * Returns the current request
     *
     * @return Request
     */
    public function getHttpRequest() {
        if ($this->request === null) {
            $this->request = Request::initFromGlobals();
        }

        return $this->request;
    }

    /**
     * @return UploadedFile[]
     */
    public function getUploadedFiles() {
        $uploadedFiles = array();
        foreach ($_FILES as $file) {
            $uploadedFiles[] = new UploadedFile(
                $file['tmp_name'],
                $file['name'],
                $file['type'],
                $file['size'],
                $file['error']
            );
        }

        return $uploadedFiles;
    }

    /**
     * Create a file uploaded from file data.
     *
     * @param array  $fileData             A array containing the following keys:
     *                                     - b64File the file encoded in base64.
     *                                     - name    the original name of file.
     *                                     - size    the size of file.
     * @param string $destinationFolder
     *
     * @return UploadedFile
     */
    public function factoryFromFileData(array $fileData, $destinationFolder) {
        list($contentType, $base64String) = explode(',', $fileData['b64File']);
        $filePath = $destinationFolder . "\\" . uniqid() . $fileData['name'];
        file_put_contents($filePath, base64_decode($base64String));
        return new UploadedFile($filePath, $fileData['name'], $contentType, $fileData['size'], null, true);
    }

    /**
     * Convert an array to an object row
     *
     * @param array $rows The rows to be converted
     *
     * @return array
     */
    protected function convertArrayToObjectRows($rows = array()) {
        $objectRows = array();
        foreach ($rows as $row) {
            $objectRows[] = new DTO\Row($row);
        }
        return $objectRows;
    }

    /**
     * Add conditions in filter criteria
     *
     * @param                           $filterDefinition
     * @param DataSource\FilterCriteria $filter
     */
    protected function addConditionInFilterCriteria($filterDefinition, DataSource\FilterCriteria $filter) {
        if ($filterDefinition['operator'] == DataSource\FilterCriteria::ORDER_BY) {
            foreach ($filterDefinition['name'] as $order) {
                $filter->addOrderBy($order['name'], $order['direction']);
            }
        } elseif ($filterDefinition['operator'] == DataSource\FilterCriteria::GROUP_BY) {
            foreach ($filterDefinition['name'] as $column) {
                $filter->addGroupBy($column);
            }
        } else {
            $value = isset($filterDefinition['value']) ? $filterDefinition['value'] : null;
            $filter->addCondition($filterDefinition['name'], $filterDefinition['operator'], $value);
        }
    }
}