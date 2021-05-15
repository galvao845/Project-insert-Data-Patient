<?php
namespace Zeedhi\Framework\Remote\FileUploader;

use Zeedhi\Framework\DTO\Request;
use Zeedhi\Framework\Events\PreDispatch\Listener;

class CompositeListener extends Listener {

    /** @var Listener */
    protected $firstListener;
    /** @var Listener */
    protected $secondListener;

    /**
     * __construct
     *
     * @param Listener $firstListener
     * @param Listener $secondListener
     */
    public function __construct(Listener $firstListener, Listener $secondListener) {
        $this->firstListener = $firstListener;
        $this->secondListener = $secondListener;
    }

    /**
     * preDispatch
     *
     * @param Request $request
     */
    public function preDispatch(Request $request) {
        $this->firstListener->preDispatch($request);
        $this->secondListener->preDispatch($request);
    }

}