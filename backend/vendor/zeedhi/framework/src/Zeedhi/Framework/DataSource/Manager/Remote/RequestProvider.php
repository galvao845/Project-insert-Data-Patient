<?php
namespace Zeedhi\Framework\DataSource\Manager\Remote;

use Zeedhi\Framework\DTO\Request;

interface RequestProvider {

    /**
     * getRequest
     *
     * @return Request
     */
    public function getRequest();

}