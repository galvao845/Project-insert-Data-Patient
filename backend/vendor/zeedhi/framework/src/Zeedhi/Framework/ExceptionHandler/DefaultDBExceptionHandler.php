<?php
namespace Zeedhi\Framework\ExceptionHandler;

use Zeedhi\Framework\DTO\Response;

class DefaultDBExceptionHandler implements ExceptionHandler {

    protected $messages = array(
        //@todo complete exception messages
        //"Exception message substring to match." => "New response setError message.",
        "ORA-00001" => "Unique constraint violated.",
        "ORA-02291" => "Foreign key value has a not found primary key.",
        "ORA-02292" => "This row has child records and can not be deleted.",
        "ORA-01400" => "Missing mandatory value.",
        "ORA-01847" => "Invalid day of month.",
        "ORA-01843" => "Invalid month.",
        "ORA-01840" => "Invalid date format.",
    );

    public function __construct(array $messages = array()) {
        $this->messages = array_merge($this->messages, $messages);
    }


    /**
     * Handle exception and populate response.
     *
     * @param \Exception $exception The exception to be handled.
     * @param Response $response The response to be sent to client.
     *
     * @return void
     */
    public function handleException(\Exception $exception, Response $response) {
        $error = null;
        foreach ($this->messages as $exceptionMessageNeedle => $errorMessage) {
            if (is_numeric(strpos($exception->getMessage(), $exceptionMessageNeedle))) {
                $error = new Response\Error($errorMessage, $exception->getCode());
            }
        }

        $response->setCriticalError($error ?: new Response\Error($exception->getMessage(), $exception->getCode()));
    }

    /**
     * Return a list of exceptions name that are handled by this.
     *
     * @return string[]
     */
    public function getHandledExceptionClasses() {
        return array(
            '\Doctrine\DBAL\Driver\DriverException',
            '\Doctrine\DBAL\Exception\DriverException',
            '\Zeedhi\Framework\DataSource\Exception'
        );
    }
}