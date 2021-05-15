<?php
namespace Zeedhi\Framework\DBAL\Driver\OCI8;


use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\OCI8\OCI8Exception;
use Doctrine\DBAL\Driver\DriverException;

class Driver extends \Doctrine\DBAL\Driver\OCI8\Driver
{

    /**
     * {@inheritdoc}
     */
    public function convertException($message, DriverException $exception) {
        $message = utf8_encode($message);
        return parent::convertException($message, $exception);
    }

    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        try {
            return new OCI8Connection(
                $username,
                $password,
                $this->_constructDsn($params),
                isset($params['charset']) ? $params['charset'] : null,
                isset($params['sessionMode']) ? $params['sessionMode'] : OCI_DEFAULT,
                isset($params['persistent']) ? $params['persistent'] : false
            );
        } catch (OCI8Exception $e) {
            throw DBALException::driverException($this, $e);
        }
    }

    public function getName() {
        return 'zeedhi_oci8';
    }
}