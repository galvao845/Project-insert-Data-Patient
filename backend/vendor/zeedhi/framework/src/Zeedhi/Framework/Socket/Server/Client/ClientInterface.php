<?php
namespace Zeedhi\Framework\Socket\Server\Client;

/**
 * Interface ClientInterface
 *
 * @package Zeedhi\Framework\Socket\Client
 */
interface ClientInterface
{
    /**
     * Sets the websocket access token for this client
     *
     * @param string $accessToken
     * @return ClientInterface
     */
    public function setAccessToken($accessToken);

    /**
     * Returns the websocket access token for this client if any, or null.
     *
     * @return null|string
     */
    public function getAccessToken();

    /**
     * Returns the array of public client data which will be transferred to the websocket client on successful
     * authentication. The websocket access token for this client should always be returned.
     *
     * @return array
     */
    public function jsonSerialize();
}