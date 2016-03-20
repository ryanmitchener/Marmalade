<?php
namespace Marmalade;

use MarmaladeApp\Config\Hooks;
use MarmaladeApp\Config\Routes;
use MarmaladeApp\Config\Constants;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Marmalade
 * This is the main class for Marmalade. All requests go through here.
 */
class Marmalade {
    private static $instance = null;
    private $autoloader = null;
    public $route = null; // The requested route if it is found
    public $router = null; // The router object
    private $controller = null; // The controller to execute
    private $method = null; // The method of the controller to execute


    /** Private constructor, clone, and wakeup */
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}


    /** Singleton generator */
    public static function get_instance() {
        if (Marmalade::$instance === null) {
            Marmalade::$instance = new Marmalade();
        }
        return Marmalade::$instance;
    }


    /** Initialize Marmalade */
    public function init() {
        // Load the configuration constants
        require_once(APP_DIR."/Config/Constants.php");
        Constants::load();

        // Set timezone to UTC
        date_default_timezone_set(TIMEZONE);

        // Use compression
        if (USE_COMPRESSION && !ini_get("zlib.output_compression")) {
            ini_set("zlib.output_compression", "On");
        }

        // Initialize the autoloader
        require_once(CORE_DIR."/Autoloader.php");
        $this->autoloader = new Autoloader();
    }


    /** Run Marmalade */
    public function run() {
        // Initialize the Request and Response classes
        Request::init();
        Response::init();

        // Add the custom namespaces to the autoloader
        Hooks::register_custom_namespace_prefixes($this->autoloader);
      
        // Initialize the router, load the routes, and find the requested route
        $this->router = (($this->router = Hooks::get_router()) === false) ? new ArrayRouter() : $this->router;
        $this->router->populate_router();
        $this->route = $this->router->route(Request::$http_verb, Request::$uri);

        // Check request version
        if (IS_API && USE_API_VERSIONING) {
            if (Request::$api_version === 0) {
                Marmalade::error(Error::create(Error::NO_ACCEPT_HEADER_VERSION_SET));
            } else if (!isset($this->route->actions[Request::$http_verb]) ||
                    !is_array($this->route->actions[Request::$http_verb]) || 
                    !isset($this->route->actions[Request::$http_verb][Request::$api_version])) {
                Marmalade::error(Error::create(Error::INVALID_RESOURCE_VERSION));
            }
        }

        // Set the controller and method to execute
        $this->set_action();

        // Check for TLS (route option overrides global)
        $use_tls = ($this->route->option(Route::TLS) !== null) ? $this->route->option(Route::TLS) : REQUIRE_TLS;
        if ($use_tls && !Request::$is_tls) {
            if (REDIRECT_TO_TLS) {
                http_response_code(301);
                header("Location: https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}");
                exit;
            } else {
                Marmalade::error(Error::create(Error::NO_TLS));
            }
        }

        // Verify content type matches if the route specifies one
        // This is for POST and PUT requests making sure that the client is sending the correct type of data
        if (($route_content_type = $this->route->option(Route::REQUIRED_CONTENT_TYPE)) !== null && 
                (!isset(Request::$headers["Content-Type"]) || 
                Request::$headers["Content-Type"] !== $route_content_type)) {
            Marmalade::error(Error::create(Error::INVALID_REQUEST_CONTENT_TYPE, array("content_type" => $route_content_type)));
        }

        // Security checks
        $require_authentication = ($this->route->option(Route::AUTHENTICATE) !== null) ? $this->route->option(Route::AUTHENTICATE) : REQUIRE_AUTHENTICATION;
        if ($require_authentication) {
            if (IS_API) {
                // Verify API signature
                if (Hooks::verify_api_signature() === false) {
                    Security::verify_api_signature();
                }
            } else if (!IS_API) {
                // Check if user is logged in. Verifying the nonce happens in the XHRController
                Hooks::verify_user_logged_in();
            }
        }

        // Execute the route
        $this->execute_route();
    }


    /** Set the controller and method for Marmalade to execute based on the routes action */
    private function set_action() {
        $action = explode(":", (IS_API && USE_API_VERSIONING) ? $this->route->actions[Request::$http_verb][Request::$api_version] : $this->route->actions[Request::$http_verb]);
        $this->controller = $action[0];
        $this->method = $action[1];
    }


    /** Execute the route action */
    private function execute_route() {
        Hooks::pre_execute_route();
        call_user_func_array(array(new $this->controller, $this->method), $this->route->params); // Execute the route
        Response::render(); // Render the output
    }


    /** 
     * Show an error and exit
     *
     * @param extends \Marmalade\Models\ErrorModel $errorModel The error model to pass to the error controller
     */
    public static function error($errorModel) {
        $marmalade = Marmalade::get_instance();
        $marmalade->controller = (Hooks::get_error_controller() !== false) ? Hooks::get_error_controller() : "Marmalade\Controllers\ErrorController";
        $marmalade->method = "error";
        $marmalade->route = new Route("error");
        $marmalade->route->params = array($errorModel);
        $marmalade->execute_route();
        exit;
    }


    /** Run Marmalade as a cron */
    public function cron() {
        global $argv;

        // Determine which cron method needs to be executed
        $method = (isset($argv) && isset($argv[1])) ? $argv[1] : "five_minutes";

        // Handle the local Marmalade cron controller
        $controller = new Controllers\CronController();
        if (method_exists($controller, $method)) {
            call_user_func(array($controller, $method)); // Execute the Marmalade cron job
        }
        
        // Handle the custom cron controller
        $controller = Hooks::get_cron_controller();
        $controller = ($controller !== false) ? new $controller() : false;
        if ($controller !== false && method_exists($controller, $method)) {
            call_user_func(array($controller, $method)); // Execute the userspace cron job
        }
    }
}