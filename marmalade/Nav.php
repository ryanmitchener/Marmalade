<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * The base Nav class
 * The Nav class handles building navigation menus based on routes found in the current Router
 */
abstract class Nav {
    public $routes; /** @var mixed $routes is a collection of all of the routes to pass to the Nav class */
    public $options; /** @var array $options is an array of options to pass to the Nav class */

    /**
     * The constructor for the Nav
     *
     * @param mixed $routes is a collection of all of the routes to pass to the Nav class
     * @param array $options is an array of options to pass to the Nav class
     */ 
    public function __construct($routes = array(), $options = array()) {
        $this->routes = $routes;
        $this->options = $options;
    }


    /**
     * Get the default navigation object based on the router
     * This should be overridden by all subclasses
     */ 
    public function get_nav_object() {}


    /**
     * Build a navigation menu object
     * Returns an array of important information for building a navigation menu in HTML based
     * on the routes contained in the router.
     *
     * @return string HTML of the navigation menu
     */
    public function build() {}
}