<?php
namespace MarmaladeApp\Config;

use Marmalade\Route;
use App\Controllers\PageController;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/**
 * All the routes for the application
 */
class Routes {
    /**
     * Load the routes
     *
     * Do not include leading or trailing slashes as this will only add extra work for the router.
     * Placeholders can be created simply by enclosing a word in curly braces. Example: test/{placeholder}
     * In order for routes to show up accurately in a menu, all child paths should directly follow their parent
     * If you are using namespacing, make sure to prefix your controller (i.e. My\Namespace\Controller)
     *
     * @example $routes[] = new Route("blog")->get("App\Controllers\BlogController:list_blogs");
     *
     * @param array(\Marmalade\Route) The empty array to add routes to
     *
     * @return array(Marmalade\Route) The array of routes
     */
    public static function load_routes($routes = array()) {
        // Add the XHR controller. Users should extend \Marmalade\Controllers\XHRController
        // with their own controller and add methods for actions 
        // if (!IS_API) {
        //     $routes[] = (new Route("xhr/{action}"))
        //         ->get("App\Controllers\XHRController:execute")
        //         ->post("App\Controllers\XHRController:execute", array(Route::REQUIRED_CONTENT_TYPE => "application/json"));
        // }

        // Marmalade default routes
        $routes[] = (new Route(""))->get("Marmalade\Controllers\InstallController:install");
        $routes[] = (new Route("sitemap.xml", array(Route::HIDDEN => true)))
            ->get("\Marmalade\Controllers\SitemapController:sitemap");

        // Marmalade tests            
        $routes[] = (new Route("route-test"))->get("Marmalade\Controllers\UnitTestController:route_test");

        // Return routes
        return $routes;
    }
}