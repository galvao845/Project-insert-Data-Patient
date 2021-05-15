<?php
namespace Zeedhi\Framework\ORM;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

/**
 * Class Connection Factory
 * 
 * Class used to connect to encrypted databases.
 * 
 * @package Zeedhi\Framework\ORM
 * 
 * @version 2.0.0
 */
class ConnectionFactory {

    /**
     * Encrypt.
     * 
     * Encrypt the given text.
     *
     * @param string $text Text to be encrypted.
     *
     * @return string
     */
    public static function encrypt($text, $salt)
    {
        return \Zeedhi\Framework\Util\Crypt::encrypt($text, $salt);
    }


    /**
     * Decrypt.
     *
     * Decrypt the given text.
     * 
     * @param string $text Text.
     *
     * @return string
     */
    public static function decrypt($text, $salt)
    {
        return \Zeedhi\Framework\Util\Crypt::decrypt($text, $salt);
    }

    /**
     * @param array         $connectionParams
     * @param Configuration $config
     * @param EventManager  $eventManager
     *
     * @return \Doctrine\DBAL\Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function factoryWithEncryptedPassword(array $connectionParams, $salt, $config = null, $eventManager = null) {
        $connectionParams["password"] = self::decrypt($connectionParams["password"], $salt);
        return DriverManager::getConnection($connectionParams, $config, $eventManager);
    }
}