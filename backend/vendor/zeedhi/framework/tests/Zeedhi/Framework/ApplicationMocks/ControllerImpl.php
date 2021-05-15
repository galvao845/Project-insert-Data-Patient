<?php
namespace tests\Zeedhi\Framework\ApplicationMocks;

use Zeedhi\Framework\DTO;

class ControllerImpl {

    protected $isListLastPostsCalled = false;

    public function listLastPost(DTO\Request $request, DTO\Response $response) {
        $this->isListLastPostsCalled = true;
    }

    public function isListLastPostsCalled() {
        return $this->isListLastPostsCalled;
    }
}