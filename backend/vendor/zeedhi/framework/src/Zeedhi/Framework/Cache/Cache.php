<?php
namespace Zeedhi\Framework\Cache;


/**
 * Interface Cache
 *
 * Interface to provider a implementation of custom cache drivers
 *
 * @package Zeedhi\Framework\Cache
 *
 */
interface Cache {

	/**
	 * Fetches an entry from the cache.
	 *
	 * @param string $key The id of the cache entry to fetch.
	 *
	 * @throws Exception if no cache entry exists for the given id.
	 *
	 * @return mixed The cached data.
	 */
	public function fetch($key);

	/**
	 * Puts data into the cache.
	 *
	 * @param string $key       The cache id.
	 * @param mixed  $data     The cache entry/data.
	 * @param int    $lifeTime The cache lifetime.
	 *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
	 *
	 * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
	 */
	public function save($key, $data, $lifeTime = 0);

	/**
	 * Deletes a cache entry.
	 *
	 * @param string $key The cache id.
	 *
	 * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
	 */
	public function delete($key);

	/**
	 * Tests if an entry exists in the cache.
	 *
	 * @param string $key The cache id.
	 *
	 * @return boolean TRUE if the cache entry was found, FALSE otherwise.
	 */
	public function contains($key);

}