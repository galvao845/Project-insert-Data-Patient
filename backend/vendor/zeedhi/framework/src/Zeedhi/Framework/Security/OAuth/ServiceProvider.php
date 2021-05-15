<?php
namespace Zeedhi\Framework\Security\OAuth;

/**
 * Interface ServiceProvider
 *
 * Interface to provider a implementation of custom ServiceProvider
 *
 * @package Zeedhi\Framework\Security\OAuth
 *
 */
interface ServiceProvider
{

    /**
     * This method must find a service using the client ID and client secret properties
     *
     * @param  string $clientId A client ID property
     * @param  string $clientSecret A client secret property
     *
     * @throws Exception A exception with message "The service with clientID {$clientId} was not found.".
     *
     * @return Service  An instance of the service
     */
    public function findByClientAndSecretId($clientId, $clientSecret);

}
