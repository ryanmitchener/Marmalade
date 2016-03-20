<?php
namespace Marmalade;

use MarmaladeApp\Config\Routes;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }


/**
 * The base router class. 
 * This class can be overridden, customized and then returned in Hooks::get_router()
 */
abstract class Router {
    /** @var mixed $routes 
     * Contains all of the routes for the application. 
     * This will be cached in the populate_router() method and loaded if possible.
     */
    protected $routes = null;

    /**
     * Retrieve the route based on the HTTP verb and URI
     * This is a function that all subclasses should override.
     *
     * @param string $http_verb The HTTP method being requested
     * @param string $uri The URI of the request (also known as the path)
     *
     * @return Marmalade\Route|boolean Return the correct route for the requested HTTP method and URI, FALSE on failure
     */
    public function match_route($http_verb, $uri) {
        return false;
    }


    /**
     * Build the route map with the loaded routes
     * This is a function that all subclasses should override
     *
     * @param array(Marmalade\Route) $routes The routes loaded from Routes::load_routes()
     */
    public function build_route_map($routes) {}


    /**
     * Get a Nav builder
     * Returns a Nav class responsible for building navigation menus based on the router
     *
     * @param array $options (default: null) is an array of options to pass to the Nav class
     * @param mixed $routes (default: null) a list of custom routes to send into the Nav class instead of the default nav_object created from the Router's routes
     *
     * @return Marmalade\Nav|boolean A Marmalade\Nav object or false if the router does not support navigation
     */
    public function get_nav($routes = null, $options = null) {
        return false;
    }


    /**
     * Return an array of URL objects based on the sitemaps.org protocol for use in a sitemap
     * And example of the sitemap schema can be found here: http://www.sitemaps.org/protocol.html
     *
     * @example array((object) array("loc" => "http://example.com", "lastmod" => "2016-01-01", "changefreq" => "monthly", "priority" => "0.8")))
     * 
     * @return array(object)
     */
    public function get_sitemap_array() {
        return false;
    }


    /**
     * Load the routes into the router
     * This function should not be overridden but can be if necessary.
     */
    public function populate_router() {
        if (Cache::has(APP_NAME."_Marmalade\Router\Routes")) {
            $this->routes = Cache::get(APP_NAME."_Marmalade\Router\Routes");
        } else {
            $this->build_route_map(Routes::load_routes());
            if (ENABLE_CACHE) {
                Cache::set(APP_NAME."_Marmalade\Router\Routes", $this->routes);
            }
        }
    }


    /**
     * Route the request to the correct route
     * This function handles some of the HTTP methods by default like OPTIONS and TRACE
     * This also sets the Allow header for the client if the requested HTTP verb is "OPTIONS"
     * This is called from Marmalade in order to find the correct route based
     * on the request.
     *
     * @return Marmalade\Route|boolean The route if it is found, FALSE if the route isn't found
     */
    public function route($http_verb, $uri) {
        // OPTIONS HTTP Verb support
        if (Request::$http_verb === "OPTIONS") {
            $route = $this->match_route($http_verb, $uri);
            if ($route === false) {
                return $route;
            }
            $supports = array("OPTIONS", "HEAD");
            foreach ($route->actions as $verb => $action) {
                $supports[] = $verb;
            }
            header("Allow: ".implode(", ", $supports));
            exit;
        }

        // Find the route
        $route = $this->match_route($http_verb, $uri);

        // Check if the route was found and is valid
        if ($route === false) {
            Marmalade::error(Error::create(Error::PAGE_NOT_FOUND));
        } else if (!isset($route->actions[Request::$http_verb])) {
            Marmalade::error(Error::create(Error::METHOD_NOT_ALLOWED));
        }
        return $route;
    }
}