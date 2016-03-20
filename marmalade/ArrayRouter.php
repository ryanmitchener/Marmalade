<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }


/**
 * ArrayRouter class
 * Builds a multidimensional array of routes and traverses it for matches
 */
class ArrayRouter extends Router {
    /** @var mixed $routes 
     * Contains all of the routes for the application. 
     * This will be cached in the populate_router() method and loaded if possible.
     */
    private $routes = array("|static|" => null);


    /**
     * Build the route map with the loaded routes
     * This is a function that all subclasses should override
     *
     * @param array(Marmalade\Route) $routes The routes loaded from Routes::load_routes()
     */
    function build_route_map($routes) {
        foreach ($routes as $route) {
            // If the path is empty set it to be the |root|
            if ($route->path === "") {
                $route->path = "|root|";
            }

            // Handle static routes
            if (strpos($route->path, "{") === false) {
                if (isset($this->routes["|static|"][$route->path])) {
                    throw new \Exception("Route already exists!", 1);
                }
                $this->routes["|static|"][$route->path] = $route;
                continue;
            }

            // Handle dynamic routes
            $path_parts = explode("/", trim($route->path, "/"));
            $array = &$this->routes;
            foreach ($path_parts as $part) {
                if ($part[0] === "{") {
                    $part = "|var|";
                }
                
                // Add the index if it doesn't exist
                if (!isset($array[$part])) {
                    $array[$part] = array();
                }

                // Set the working array to the existing or newly added index
                $array = &$array[$part];
            }

            // Throw error if the route already exists
            if (isset($array["|_base_|"])) {
                throw new Exception("Route already exists!", 1);
            }

            // Add the route object to the |base|
            $array += array("|base|" => $route);
        }
    }


    /**
     * Retrieve the route based on the HTTP verb and URI
     * This is a function that all subclasses should override.
     *
     * @param string $http_verb The HTTP method being requested
     * @param string $uri The URI of the request (also known as the path)
     *
     * @return Marmalade\Route|boolean Return the correct route for the requested HTTP method and URI, FALSE on failure 
     */
    function match_route($http_verb, $uri) {
        $uri = trim($uri, "/");
        $uri = ($uri === "") ? "|root|" : $uri;

        if (isset($this->routes["|static|"][$uri])) {
            return $this->routes["|static|"][$uri];
        }

        $uri = explode("/", $uri);
        $route = $this->routes;
        $params = array();
        foreach ($uri as $part) {
            if (!is_array($route)) {
                return false;
            } else if (isset($route[$part])) {
                $route = $route[$part];
            } else if (isset($route["|var|"])) {
                $params[] = $part;
                $route = $route["|var|"];
            } else {
                return false;
            }
        }
        if (is_array($route) && isset($route["|base|"])) {
            $route = $route["|base|"];
            $route->params = $params; // Set the route parameters for all variable path parts
        } else {
            $route = false;
        }
        return $route;
    }


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
        return new ArrayRouterNav(($routes === null) ? $this->routes["|static|"] : $routes, $options);
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
        $urls = array();

        // Get the full host domain
        $host = ((Request::$is_tls) ? "https://" : "http://").Request::$host.ROOT_URL."/";

        // Loop through static routes
        foreach ($this->routes["|static|"] as $uri => $route) {
            // Skip hidden routes and non GET routes
            if ($route->option(Route::HIDDEN)) {
                continue;
            } else if (!isset($route->actions["GET"])) {
                continue;
            }

            // Add route to URL array
            $urls[] = (object) array(
                "loc" => ($uri === "|root|") ? "{$host}" : "{$host}{$uri}",
                "lastmod" => "",
                "changefreq" => "",
                "priority" => "");
        }
        return $urls;
    }
}