<?php
namespace Zeedhi\Framework\ErrorHandler;

use Zeedhi\Framework\DTO\Response;

interface ErrorHandler {

    /**
     * This function will get the user's handlers and listeners errors and use them to treat
     * the error that occurred.
     *
     * @param Response $response   Response to be sent to client.
     * @param integer  $errno      Contains the level of the error raised.
     * @param string   $errstr     Contains the error message.
     * @param string   $errfile    Which contains the filename that the error was raised in.
     * @param integer  $errline    Which contains the line number the error was raised at.
     * @param array    $errcontext Which is an array that points to the active symbol table at the point the error occurred.
     *                             In other words, errcontext will contain an array of every variable that existed in the
     *                             scope the error was triggered in. User error handler must not modify error context.
     *
     * @return bool                True if error has handled, false otherwise.
     */
    public function handle(Response $response, $errno, $errstr, $errfile, $errline, $errcontext);

    /**
     * The 'bitwise' error codes handled by this.
     *
     * @return int
     */
    public function getErrorCode();
} 