<?php
namespace Zeedhi\Framework\DataSource;

use Zeedhi\Framework\Cache\Cache;

class ParameterBag {

    /** @var Cache */
    protected $cache;

    public function __construct(Cache $cache) {
        $this->cache = $cache;
    }

    /**
     * @param $name
     * @return string
     */
    protected function prefixName($name) {
        return 'zeedhi_data_source_param_' . $name;
    }

    public function set($name, $value) {
        $this->cache->save($this->prefixName($name), $value);
    }

    public function get($name) {
        return $this->cache->fetch($this->prefixName($name));
    }
}