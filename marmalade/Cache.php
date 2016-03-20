<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Cache class for userland object caching
 */
class Cache {
    // Support for the different type of caches
    private static $cache_type = NULL;
    const CACHE_HANDLER_SELF = 0;
    const CACHE_HANDLER_APCU = 1;
    const CACHE_HANDLER_XCACHE = 2;

    // If no cache extension is enabled, use a local variable
    private static $storage = array();


    /** 
     * Check what type of cache is available to use
     */
    private static function determine_cache_type() {
        if (Cache::$cache_type !== NULL) {
            return;
        }

        // Set the type of cache being used
        if (extension_loaded("apc")) {
            Cache::$cache_type = Cache::CACHE_HANDLER_APCU;
        } else if (extension_loaded("xcache")) {
            Cache::$cache_type = Cache::CACHE_HANDLER_XCACHE;
        } else {
            Cache::$cache_type = Cache::CACHE_HANDLER_SELF;
        }
    }


    /**
     * Check if the cache contains an object
     *
     * @param string $name The name of the data to check exists
     * 
     * @return boolean TRUE if the data exists, FALSE if it doesn't
     */
    public static function has($name) {
        Cache::determine_cache_type();
        if (Cache::$cache_type === Cache::CACHE_HANDLER_APCU) {
            return apc_exists($name);
        } else if (Cache::$cache_type === Cache::CACHE_HANDLER_XCACHE) {
            return xcache_isset($name);
        } else {
            return isset(Cache::$storage[$name]);
        }
    }


    /**
     * Get an object from the cache
     *
     * @param string $name The name of the data to retrieve
     * 
     * @return mixed The data requested
     */
    public static function get($name) {
        Cache::determine_cache_type();
        if (Cache::$cache_type === Cache::CACHE_HANDLER_APCU) {
            return apc_fetch($name);
        } else if (Cache::$cache_type === Cache::CACHE_HANDLER_XCACHE) {
            return unserialize(xcache_get($name)); // We have to serialize to support objects in xcache
        } else {
            return Cache::$storage[$name];
        }
    }


    /**
     * Put an object in the cache
     *
     * @param string $name The Name of the object
     * @param mixed $value The object to put in the cache
     * @param int $ttl The time to live in seconds 
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    public static function set($name, $value, $ttl = 0) {
        Cache::determine_cache_type();
        if (Cache::$cache_type === Cache::CACHE_HANDLER_APCU) {
            return apc_store($name, $value, $ttl);
        } else if (Cache::$cache_type === Cache::CACHE_HANDLER_XCACHE) {
            return xcache_set($name, serialize($value), $ttl); // We have to serialize to support objects in xcache
        } else {
            Cache::$storage[$name] = $value;
            return true;
        }
    }


    /**
     * Remove an object from the cache
     *
     * @param string $name The name of the object to remove from the cache
     * 
     * @return boolean TRUE on success, FALSE on failure
     */
    public static function delete($name) {
        Cache::determine_cache_type();
        if (Cache::$cache_type === Cache::CACHE_HANDLER_APCU) {
            return apc_delete($name);
        } else if (Cache::$cache_type === Cache::CACHE_HANDLER_XCACHE) {
            return xcache_unset($name);
        } else {
            unset(Cache::$storage[$name]);
            return true;
        }
    }
}