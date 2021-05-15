<?php
namespace Zeedhi\Framework\Socket\Server\Loop;

/**
 * Interface PeriodicTimerInterface
 *
 * @package Zeedhi\Framework\Socket\Server\Loop
 */
interface PeriodicTimerInterface 
{
    /**
     * Returns the interval for this timer
     *
     * @return int
     */
    public function getInterval();

    /**
     * Returns the callback.
     *
     * @return callable
     */
    public function getCallback();

    /**
     * Returns a unique name for this timer.
     *
     * @return string
     */
    public function getName();
}
