<?php
namespace MarmaladeApp\Config;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * The Hooks class provides a way to customize how Marmalade handles certain parts of the application.
 * Marmalade will call a hook and you can either allow Marmalade to handle things by default or you
 * handle that event in a custom way.
 */
class Hooks {
    /**
     * Handles verifying an API signature before a route is executed
     * This is where you can provide all of your logic to customize how an API
     * signature is verified. If false is returned, Marmalade will handle
     * API signature validation. This method does not need to return anything
     * to Marmalade. Instead, it should show an error and exit if the signature
     * does not validate.
     *
     * @return boolean (default: false) Return false if Marmalade should handle API verification. Return void if you are using custom API signature verification.
     */
    public static function verify_api_signature() { return false; }


    /**
     * Allows creating a custom API versioning system.
     * If you want to make your own API versioning system, use this hook and return
     * the version that is being requested. If you would like Marmalade to handle
     * versioning, return false. This hook will only run if the IS_API and 
     * USE_API_VERSIONING constants are both set to TRUE.
     *
     * @return int|boolean (default: false) Return the version integer requested or false if Marmalade should handle API versioning.
     */
    public static function get_custom_api_version() { return false; }
    

    /**
     * Handles the login check before a route is executed
     * This is where you can provide all of your logic to check if a user is logged
     * in or not and handle it appropriately.
     * This method does not need to return anything
     * to Marmalade. Instead, it should show an error and exit if the signature
     * does not validate.
     */
    public static function verify_user_logged_in() {}


    /**
     * Return a custom Router for handling routes
     *
     * @return \Marmalade\Router|boolean (default: false) Return a class that extends \Marmalade\Router for custom routing or false to let Marmalade handle routing.
     */
    public static function get_router() { return false; }


    /** 
     * Return a custom error controller
     *
     * @return string|boolean (default: false) The name of the controller (that must extend \Marmalade\Controllers\ErrorController) to use to handle errors or false if Marmalade should handle all errors.
     */
    public static function get_error_controller() { return false; }


    /** 
     * Return a custom cron controller
     * 
     * @return: string|boolean (default: false) The name of the controller (that must extend \Marmalade\Controllers\CronController) to use to handle cron jobs or false if there is no cron controller.
     */
    public static function get_cron_controller() { return false; }


    /**
     * Used to add any custom namespace prefixes to the autoloader
     *
     * @example $autoloader->add_namespace_prefix("Controllers", APP_DIR."/Controllers");
     *
     * @param \Marmalade\Autoloader Marmalade's autoloader.
     */
    public static function register_custom_namespace_prefixes($autoloader) {}


    /**
     * This is called before the route executes. This can include any additional site-wide
     * configuration files or do any pre-route executions.
     */
    public static function pre_execute_route() {}
}