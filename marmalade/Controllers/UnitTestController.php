<?php
namespace Marmalade\Controllers;

use \Marmalade\Response;
use \MarmaladeApp\Config\Routes;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Controller for Marmalade unit tests
 */
class UnitTestController extends Controller {
    /**
     * API Client Test
     * Test that the API Client is working
     *
     * @note: This must have the database enabled and an active API user with matching credentials.
     */
    public function api_client_test() {
        Response::set_header("Content-Type", "text/plain");
        $client = new \Marmalade\APIClient(str_replace("/api-test", "", "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}"), 
            "myapp",
            "42ca56ae31aa28daaa9b16a47f6cfd699fa5deafedc2ce3b6cb00e4d38830e84",
            "67bd2e0609b1a43e7899696a52746afc55b746dce6b233f30061bededfe01ba7");

        print_r($client->call("POST", "/api-return", 
            array("Content-Type" => "multipart/form-data"), 
            array("test" => 1, "file-test" => new \CURLFile(ROOT_DIR."/license.txt"))));
        echo "\n\n-----------------------------------\n\n";
        print_r($client->call("POST", "/api-return", 
            array("Content-Type" => "application/x-www-form-urlencoded"),
            "alpha=1&beta=2&gamma=3&delta=4"));
        echo "\n\n-----------------------------------\n\n";
        print_r($client->call("POST", "/api-return", 
            array("Content-Type" => "application/json"), 
            json_encode(array("alpha" => 1, "beta" => 2))));
    }


    /**
     * API Return Test
     * Test API signature verification. This is used in conjunction with api_client_test()
     */
    public function api_return_test() {
        $this->post_test();
        echo "\n\nSignature verified: ";
        var_dump(\Marmalade\Security::verify_api_signature(false));
    }


    /**
     * Post Test
     * Test different types of post data and if the Request class parses
     * it correctly
     */
    public function post_test() {
        echo "Content-Type: ".\Marmalade\Request::$content_type."\n\n";
        echo "Raw Body:\n";
        var_dump(\Marmalade\Request::$raw_body);
        echo "\n\nParsed Body:\n";
        var_dump(\Marmalade\Request::$body);
        echo "\n\nUploaded Files:\n";
        print_r($_FILES);
    }


    /**
     * Route Test
     * Test that all routes have legitimate controllers and methods
     */
    public function route_test() {
        Response::set_header("Content-Type", "text/plain");
        $routes = Routes::load_routes();
        $model = array(
            "route_count" => 0,
            "action_count" => 0,
            "errors" => array());

        // Loop through routes
        foreach ($routes as $route) {
            foreach ($route->actions as $method => $action) {
                // Handle versioned routes
                if (is_array($action)) {
                    foreach ($action as $version => $version_action) {
                        $this->test_route_action($model, $method, $route->path, $version_action, $version);
                    }
                } else {
                    $this->test_route_action($model, $method, $route->path, $action);
                }
            }
            $model["route_count"]++;
        }

        // Output test results
        ob_start();
        echo "Routes: {$model["route_count"]}\n";
        echo "Actions: {$model["action_count"]}\n";
        echo "Errors: ".count($model["errors"])."\n\n";
        foreach ($model["errors"] as $error) {
            if ($error["error"] === 1) {
                $message = "Controller `{$error["controller"]}` wasn't found for route: {$error["http_method"]}: /{$error["path"]}";
                $message .= (isset($error["version"])) ? " (v{$error["version"]})\n\n" : "\n\n";
            } else if ($error["error"] === 2) {
                $message = "Method `{$error["method"]}` was not found in controller `{$error["controller"]}` for route: {$error["http_method"]}: /{$error["path"]}";
                $message .= (isset($error["version"])) ? ", (v{$error["version"]})\n\n" : "\n\n";
            }
            echo $message;
        }
        Response::set_output(ob_get_clean());
    }


    /**
     * Test if a route's controller and action are valid
     *
     * @param string $model The model from route_test()
     * @param string $method The HTTP method of the route
     * @param string $path The path of the route
     * @param string $action The action of the route
     * @param int $version The version of the route if the route is versioned
     *
     * @return int 0 if there was no problem, 1 if the controller wasn't found, 2 if the method wasn't found.
     */
    private function test_route_action(&$model, $method, $path, $action, $version = null) {
        // Set up variables
        $model["action_count"]++;    
        $exploded = explode(":", $action);
        $result = 0;

        // Test Controller and method
        if (!class_exists($exploded[0])) {
            $result = 1;
        } else if (!method_exists(new $exploded[0](), $exploded[1])) {
            $result = 2;
        }

        // Return if no error
        if ($result === 0) {
            return;
        }

        // Add error
        $error = array(
            "error" => $result,
            "http_method" => $method,
            "path" => $path,
            "controller" => $exploded[0],
            "method" => $exploded[1]);
        if ($version !== null) {
            $error["version"] = $version;
        }
        $model["errors"][] = $error;
    }
}