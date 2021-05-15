<?php
namespace Zeedhi\Framework\Security\OAuth;

/**
 * class Service
 *
 * class to provider a implementation of custom Service
 *
 * @package Zeedhi\Framework\Security\OAuth
 *
 */
interface Service
{

    /**
     * This method must return a client ID of the service
     *
     * @return string
     */
    public function getClientId();

    /**
     * This method must return a client secret of the service
     *
     * @return string
     */
    public function getClientSecret();

    /**
     * A setter of the service name property
     *
     * @param string $name A new service name property
     */
    public function setName($name);

    /**
     * A getter of the service name property
     *
     * @return string A service name property
     */
    public function getName();

}