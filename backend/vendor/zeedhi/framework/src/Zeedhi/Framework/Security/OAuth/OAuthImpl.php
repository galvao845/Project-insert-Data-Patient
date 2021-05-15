<?php
namespace Zeedhi\Framework\Security\OAuth;

use Zeedhi\Framework\Cache\Cache;

/**
 * Class OAuthImpl
 *
 * Class to provide an implementation of OAuth
 *
 * @package Zeedhi\Framework\Security\OAuth
 */
class OAuthImpl implements OAuth
{

    /**
     * Define the life time of one record storaged
     *
     * @var int
     */
    protected $lifeTime;
    /**
     * This object must provide all the services that can consume this service
     *
     * @var ServiceProvider
     */
    protected $serviceProvider;
    /**
     * This object is a cache storage
     *
     * @var Cache
     */
    protected $storage;

    public function __construct(ServiceProvider $serviceProvider, Cache $storage, $lifeTime = 0)
    {
        $this->serviceProvider = $serviceProvider;
        $this->storage = $storage;
        $this->lifeTime = $lifeTime;
    }

    /**
     * Grant an access token of a valid service.
     *
     * @param string $clientId The public id of the service.
     * @param string $clientSecret The secret id of the service.
     *
     * @throws Exception if the service was not found
     *
     * @return string The access token
     */
    public function grantAccessToken($clientId, $clientSecret, $options = array())
    {
        $service = $this->findServiceByClientAndSecret($clientId, $clientSecret);
        $token = $this->genToken();
        $this->registerSession($token, $service);
        return $token;
    }

    /**
     * This method must generate a new unique token using random numbers and the uniqid of the php
     *
     * @return string A token
     * @throws Exception When token generated is invalid
     */
    private function genToken()
    {
        $token = (string)base_convert(mt_rand(), 10, 16);
        $token .= (string)base_convert(mt_rand(), 10, 16);
        $token .= (string)base_convert(mt_rand(), 10, 16);
        $token = uniqid($token);
        $lastKeys = $this->genLastKeys($token);
        if (!empty($lastKeys)) {
            $token .= $lastKeys;
            return $token;
        }
        throw Exception::invalidFormatToken();
    }

    /**
     * This method should storage one record of a service on the cache type chosen
     *
     * @param  string $token An access token
     * @param  Service $service An instance of the service
     *
     * @return void
     */
    private function registerSession($token, $service)
    {
        $session = array(
            "serviceClientId" => $service->getClientId(),
            "serviceClientSecret" => $service->getClientSecret(),
            "serviceName" => $service->getName()
        );
        $this->storage->save($token, $session, $this->lifeTime);
    }

    /**
     * This method must generate the two last keys of the token. They keys are generate from the random token.
     *
     * @param  string $key Random token
     *
     * @return mixed       Last keys of the token or NULL case the token is invalid
     */
    private function genLastKeys($key)
    {
        $keyLength = strlen($key);
        if (!empty($key) && $keyLength >= 3) {
            $code = round(ord($key[1]) * ord($key[(int)round((($keyLength / 4) * 0.6))]) / ord($key[$keyLength - 3]));
            return ((string)$code % 9) . ((string)$code % 4);
        }
        return null;
    }

    /**
     * This method must validate an access token
     *
     * @param  string $token An access token
     *
     * @return boolean
     */
    private function isValidToken($token)
    {
        $length = strlen($token);
        $key = substr($token, 0, $length - 2);
        $lastKeys = substr($token, $length - 2, $length - 1);
        return $lastKeys == $this->genLastKeys($key);
    }

    /**
     * Validate an access token and return the session of one service
     *
     * @param  string $token The access token of a service
     * @param  string $clientSecret The secret id of the service
     *
     * @throws Exception If the access token is invalid
     * @throws Exception If the service was not found.
     *
     * @return Service The object of a service.
     */
    public function checkAccess($token, $clientSecret, $options = array())
    {
        if ($this->isValidToken($token)) {
            $data = $this->storage->fetch($token);
            if (!empty($data) && $data['serviceClientSecret'] === $clientSecret) {
                return $this->findServiceByClientAndSecret($data['serviceClientId'], $data['serviceClientSecret']);
            }
        }
        throw Exception::invalidToken($token);
    }

    /**
     * @param $clientId
     * @param $clientSecret
     *
     * @throws Exception if the service was not found.
     *
     * @return Service
     */
    protected function findServiceByClientAndSecret($clientId, $clientSecret) {
        $service = $this->serviceProvider->findByClientAndSecretId($clientId, $clientSecret);
        if (!$service) throw Exception::serviceNotFound($clientId);
        return $service;
    }
}