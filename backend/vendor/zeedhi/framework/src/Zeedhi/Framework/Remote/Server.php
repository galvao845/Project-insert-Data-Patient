<?php
namespace Zeedhi\Framework\Remote;

use Zeedhi\Framework\DataSource\DataSet;
use Zeedhi\Framework\DTO;

class Server {

    /** @var HttpInterface */
    protected $requestStrategy;
    /** @var array */
    protected $headers;

    /**
     * Constructor
     *
     * @param HttpInterface $HTTPRequestStrategy cURL strategy implementation
     * @param array         $headers             Headers that will be send on server requests
     */
    public function __construct(HttpInterface $HTTPRequestStrategy, array $headers = array()) {
        $this->requestStrategy = $HTTPRequestStrategy;
        $this->headers = $headers;
        if (!isset($this->headers[HttpInterface::CONTENT_TYPE_HEADER])) {
            $this->headers[HttpInterface::CONTENT_TYPE_HEADER] = HttpInterface::CONTENT_TYPE_APPLICATION_JSON;
        }
    }

    /**
     * request
     *
     * Make the request to remote server.
     *
     * @param DTO\Request $request The request data to be sent.
     *
     * @return DTO\Response The server response.
     */
    public function request(DTO\Request $request){
        $fields = array();
        $requestType = 'Empty';
        switch (get_class($request)) {
            case 'Zeedhi\Framework\DTO\Request\DataSet':
                /** @var DTO\Request\DataSet $request */
                $dataSet = $request->getDataSet();
                $fields['dataset'] = $dataSet->getRows();
                $requestType = 'DataSet';
                break;
            case 'Zeedhi\Framework\DTO\Request\Filter':
                /** @var DTO\Request\Filter $request */
                $filterCriteria         = $request->getFilterCriteria();
                $fields['filter']       = $this->convertConditions($filterCriteria->getConditions());
                $fields['page']         = $filterCriteria->getPage();
                $fields['itemsPerPage'] = $filterCriteria->getPageSize();

                $requestType = 'FilterData';
                break;
            case 'Zeedhi\Framework\DTO\Request\Row':
                /** @var DTO\Request\Row $request */
                $fields['row'] = (array) $request->getRow();
                $requestType = 'Row';
                break;
        }

        foreach ($request->getParameters() as $key => $value) {
            if (!isset($fields[$key])) {
                $fields[$key] = $value;
            }
        }

        $fields['requestType'] = $requestType;
        $headers = array_merge($this->headers, array('userId' => $request->getUserId()));
        $this->requestStrategy->setHeaders($headers);
        $this->requestStrategy->setMethod($request->getMethod());

        $response = $this->requestStrategy->request($request->getRoutePath(), $fields);
        return $this->createResponse($response);
    }

    protected function convertConditions(array $conditions) {
        return array_map(function($condition) {
            return array(
                'name'      => $condition['columnName'],
                'operator'  => $condition['operator'],
                'value'     => $condition['value']
            );
        }, $conditions);
    }

    protected function createResponse($responseString) {
        $requestArray = json_decode($responseString, true);
        if ($requestArray === null || !is_array($requestArray)) {
            throw ServerException::badFormattedResponse($responseString);
        }

        $dataSets =      isset($requestArray['dataset']) ? $requestArray['dataset'] : array();
        $error =         isset($requestArray['error'])  ? $requestArray['error'] : null;
        $messages =      isset($requestArray['messages']) ? $requestArray['messages'] : array();
        $notifications = isset($requestArray['notifications']) ? $requestArray['notifications'] : array();
        $methods =       isset($requestArray['method']) ? $requestArray['method'] : array();

        $responseDTO = new DTO\Response();

        $this->setDataSets($responseDTO, $dataSets);
        $this->setNotifications($responseDTO, $notifications);
        $this->setMessages($responseDTO, $messages);
        $this->setMethods($responseDTO, $methods);
        $this->setError($responseDTO, $error);

        return $responseDTO;
    }

    protected function setDataSets(DTO\Response $response, $dataSets) {
        $response->setDataSets($this->createDataSets($dataSets));
    }

    protected function setNotifications(DTO\Response $response, array $notifications) {
        foreach ($this->createNotifications($notifications) as $notification) {
            $response->addNotification($notification);
        }
    }

    protected function setMessages(DTO\Response $response, array $messages) {
        foreach ($this->createMessages($messages) as $message) {
            $response->addMessage($message);
        }
    }

    protected function setMethods(DTO\Response $response, $methods) {
        foreach ($this->createMethods($methods) as $method) {
            $response->addMethod($method);
        }
    }

    protected function setError(DTO\Response $response, $error) {
        if ($error) {
            $response->setCriticalError(new DTO\Response\Error($error, 0));
        }
    }

    protected function createDataSets($dataSets) {
        $arrDataSets = array();
        foreach ($dataSets as $dataSourceName => $rows) {
            $arrDataSets[] = new DataSet($dataSourceName, $rows);
        }
        return $arrDataSets;
    }

    protected function createMethods(array $methods) {
        return array_map(function($method) {
            return new DTO\Response\Method($method['name'], $method['parameters']);
        }, $methods);
    }

    protected function createNotifications(array $notifications) {
        return array_map(function($notification) {
            return new DTO\Response\Notification($notification['message'], $notification['type'], $notification['variables']);
        }, $notifications);
    }

    protected function createMessages(array $messages) {
        return array_map(function($message) {
            return new DTO\Response\Message($message['message'], $message['type'], $message['variables']);
        }, $messages);
    }
}