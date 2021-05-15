<?php
namespace Zeedhi\Framework\Security\AntiCSRF;

use Zeedhi\Framework\Cache\Cache;

class AntiCSRF {

    const TOKEN_NAME = 'zeedhi-token';
    const COOKIE_NAME = 'zeedhi-cookie';
    const DATASET_NAME = 'zeedhi-dataset-token';
    const PARAMETER_NAME = 'zeedhi-request-parameter';

    /** @var int */
    private $timeToExpire;

    /** @var Cache */
    protected $cache;

    public function __construct(Cache $cache, $timeToExpire = 0){
        $this->cache = $cache;
        $this->timeToExpire = $timeToExpire;
    }

    private function getUniqueId(){
        return sha1(uniqid(rand()));
    }

    private function getCachedName($sufix){
        return 'zeedhi_secutiry_anticsrf_'.$sufix;
    }

    private function saveTokenInCache($cookie, $token){
        $objToSave = array(
            'token' => $token,
            'cookie' => $cookie,
            'created' => time()
        );

        $this->cache->save($this->getCachedName($cookie), $objToSave, $this->timeToExpire);
        return $objToSave;
    }

    public function isTokenValid($cookie, $token){
        $cachedObj = $this->getTokenAndCookie($cookie);
        return is_array($cachedObj) && isset($cachedObj['token']) && $cachedObj['token'] === $token;
    }

    public function getTokenAndCookie($cookie) {
        return $this->cache->fetch($this->getCachedName($cookie));
    }

    public function generateTokenAndCookie(){
        $cookie = $this->getUniqueId();
        $token = $this->getUniqueId();
        return $this->saveTokenInCache($cookie, $token);
    }
}