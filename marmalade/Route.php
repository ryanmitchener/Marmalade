<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** Route class */
class Route {
    public $actions = array(); /** @var string $action The Controller and method to use for this route separated by a ":". Must be an array if using API versioning. */
    public $path; /** @var string $path The URI or path for the route */
    public $params = array(); /** @var array $params The parameters sent from the URI of a variable URI (example: /blog/123) */
    public $options = array(); /** @var array $options Any options for this route */
    private $path_parts = null; /** @var array $path_parts All of the parts of the path exploded by "/" */

    // Option constants
    const TLS = "tls"; /** @var boolean TLS Does this route require TLS? */
    const AUTHENTICATE = "authenticate"; /** @var boolean AUTHENTICATE Does this route require authentication? This option will override the REQUIRE_AUTHENTICATION constant. */
    const REQUIRED_CONTENT_TYPE = "required_content_type"; /** @var string REQUIRED_CONTENT_TYPE the required request body content type for this route */
    const TITLE_ATTRIBUTE = "title_attribute"; /** @var string TITLE_ATTRIBUTE The title that will be displayed in a nav link */
    const HIDDEN = "hidden"; /** @var boolean HIDDEN TRUE if the route should not show up in navigation or in the sitemap, FALSE if it should (This constant only matters if the route is static). */
    const NAV_LABEL = "nav_label"; /** @var string NAV_LABEL The label for a nav item link */


    /** 
     * Constructor 
     *
     * @param string $path The URI or path for the route
     * @param array $options Any options for this route regardless of HTTP method
     */
    public function __construct($path = "", $options = array()) {
        $this->path = $path;
        $this->options["_ALL"] = $options;
    }


    /** 
     * Add an action and options for a GET request
     *
     * @param string $action The Controller and method to use for this route separated by a ":". Must be an array if using API versioning.
     * @param array $options Any options for this route specific to the GET HTTP method
     */
    public function get($action, $options = array()) {
        return $this->custom("GET", $action, $options);
    }


    /** 
     * Add an action and options for a POST request
     *
     * @param string $action The Controller and method to use for this route separated by a ":". Must be an array if using API versioning.
     * @param array $options Any options for this route specific to the POST HTTP method
     */
    public function post($action, $options = array()) {
        return $this->custom("POST", $action, $options);
    }


    /** 
     * Add an action and options for a PUT request
     *
     * @param string $action The Controller and method to use for this route separated by a ":". Must be an array if using API versioning.
     * @param array $options Any options for this route specific to the PUT HTTP method
     */
    public function put($action, $options = array()) {
        return $this->custom("PUT", $action, $options);
    }


    /** 
     * Add an action and options for a DELETE request
     *
     * @param string $action The Controller and method to use for this route separated by a ":". Must be an array if using API versioning.
     * @param array $options Any options for this route specific to the DELETE HTTP method
     */
    public function delete($action, $options = array()) {
        return $this->custom("DELETE", $action, $options);
    }


    /** 
     * Add an action and options for a custom HTTP method request
     *
     * @param string $method The HTTP method to add an action for
     * @param string $action The Controller and method to use for this route separated by a ":". Must be an array if using API versioning.
     * @param array $options Any options for this route specific to the specified HTTP method
     */
    function custom($method, $action, $options = array()) {
        $this->actions[$method] = $action;
        $this->options[$method] = $options;
        return $this;
    }


    /** 
     * Returns an option value
     * 
     * @return mixed Return the option value if it's set, NULL If the value doesn't exist
     *      Any HTTP Method specific option will override the global Route options.
     */
    public function option($option) {
        if (isset($this->options[Request::$http_verb][$option])) {
            return $this->options[Request::$http_verb][$option];
        } else if (isset($this->options["_ALL"][$option])) {
            return $this->options["_ALL"][$option];
        }
        return null;
    }


    /**
     * Returns the path parts of the path
     *
     * @return array The parts of the path
     */
    public function get_path_parts() {
        if ($this->path_parts === null) {
            $this->path_parts = explode("/", trim($this->path, "/"));
        }
        return $this->path_parts;
    }
}