<?php
namespace Marmalade;

use \MarmaladeApp\Config\Hooks;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Request class
 * This class is an abstraction of the incoming request
 */
abstract class Request {
    static $http_verb; /** @var string The HTTP method of the request */
    static $headers; /** @var array All of the HTTP headers from the request */
    static $api_version = 0; /** @var (default: 0) int The API version requested. */
    static $host; /** @var string The requested host */
    static $uri; /** @var string The requested URI */
    static $query; /** @var array The parsed query string */
    static $raw_query; /** @var string The raw query string */
    static $is_tls = false; /** @var boolean TRUE if the request is secured with HTTPS */
    static $raw_body = ""; /** @var string The request body. Raw body will be empty if the content type is multipart/form-data */
    static $body = ""; /** @var mixed The parsed (if possible) request body */
    static $content_type; /** @var string The base Content-Type of the request (no extra parameters) */
    static $is_xhr = false; /** @var boolean TRUE if the request was sent as an XHR/AJAX request */


    /** Initialize the static Request class */
    static function init() {
        Request::$headers = Request::get_all_headers();
        Request::$http_verb = $_SERVER["REQUEST_METHOD"];
        Request::$is_tls = (isset($_SERVER["HTTPS"]) && isset($_SERVER["HTTPS"][0]) && $_SERVER["HTTPS"] !== "off") ? true : false;
        Request::$host = $_SERVER["HTTP_HOST"];
        Request::$uri = str_replace(ROOT_URL, "", explode("?", $_SERVER["REQUEST_URI"])[0]);
        Request::$query = $_GET;
        Request::$raw_query = $_SERVER["QUERY_STRING"];
        Request::$raw_body = file_get_contents("php://input");

        // Set the content type (strip out all extra parameters)
        $content_type = (isset(Request::$headers["Content-Type"])) ? Request::$headers["Content-Type"] : "";
        Request::$content_type = preg_split("/\s*[;,]\s*/", $content_type)[0];

        // Parse the request body if a body exists
        Request::parse_body();

        // If version is in header, get it from the header
        if (IS_API && USE_API_VERSIONING) {
            if (($custom_version = Hooks::get_custom_api_version()) !== false) {
                Request::$api_version = $custom_version;
            } else if (isset(Request::$headers["Accept"]) && 
                    preg_match("/".str_replace(".", "\.", VENDOR_ID)."([\w\.]*)\.v([\d]+)/", Request::$headers["Accept"], $match)) {
                Request::$api_version = $match[2];
            }
        }
    }


    /** 
     * Return a nonce passed in through the authorization header or the JSON body
     * 
     * @return string|NULL The nonce if one was passed in, NULL if there is no nonce
     */
    public static function get_nonce() {
        $nonce = null;
        if (isset(Request::$headers["Authorization"])) {
            $nonce = Request::$headers["Authorization"];
        } else if (Request::$content_type === "application/json" && isset(Request::$body->nonce)) {
            $nonce = Request::$body->nonce;
        } else if (isset($_GET["nonce"])) {
            $nonce = $_GET["nonce"];
        }
        return $nonce;
    }


    /** 
     * Get all request headers
     * This function exists because getallheaders does not work with NGINX
     * Adapted from: https://github.com/ralouphie/getallheaders/blob/master/src/getallheaders.php
     *
     * @return array All of the request headers
     */
    private static function get_all_headers() {
        if (!function_exists("getallheaders")) { 
            $headers = array();
            $copy_server = array(
                "CONTENT_TYPE"   => "Content-Type",
                "CONTENT_LENGTH" => "Content-Length",
                "CONTENT_MD5"    => "Content-Md5",
            );
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === "HTTP_") {
                    $key = substr($key, 5);
                    if (!isset($copy_server[$key]) || !isset($_SERVER[$key])) {
                        $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", $key))));
                        $headers[$key] = $value;
                    }
                } elseif (isset($copy_server[$key])) {
                    $headers[$copy_server[$key]] = $value;
                }
            }
            if (!isset($headers["Authorization"])) {
                if (isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
                    $headers["Authorization"] = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
                } elseif (isset($_SERVER["PHP_AUTH_USER"])) {
                    $basic_pass = isset($_SERVER["PHP_AUTH_PW"]) ? $_SERVER["PHP_AUTH_PW"] : "";
                    $headers["Authorization"] = "Basic " . base64_encode($_SERVER["PHP_AUTH_USER"] . ":" . $basic_pass);
                } elseif (isset($_SERVER["PHP_AUTH_DIGEST"])) {
                    $headers["Authorization"] = $_SERVER["PHP_AUTH_DIGEST"];
                }
            }
            return $headers;
        } else {
            return getallheaders();
        }
    }


    /** 
     * Parse the request body based on content type
     * This function populates Request::$body with an object if possible.
     * If Marmalade does not know how to parse the content-type, it is copied
     * from Request::$raw_body.
     */
    private static function parse_body() {
        // Ignore requests with no body
        if (!isset(Request::$raw_body[0]) && Request::$content_type !== "multipart/form-data") {
            return;
        }

        // Parse the body based on content type
        if (Request::$content_type === "application/json") {
            if ((Request::$body = json_decode(Request::$raw_body)) === NULL) {
                Marmalade::error(Error::create(Error::INVALID_JSON_BODY));
            }
        } else if (Request::$content_type === "multipart/form-data" ||
                Request::$content_type === "application/x-www-form-urlencoded") {
            Request::$body = $_POST;
        } else if (Request::$content_type === "application/xml" ||
                Request::$content_type === "text/xml") {
            $backup = libxml_disable_entity_loader(true);
            Request::$body = simplexml_load_string(Request::$raw_body);
            libxml_disable_entity_loader($backup);
            if ($result === false) {
                Marmalade::error(Error::create(Error::INVALID_XML_BODY));
            }
        } else {
            Request::$body = Request::$raw_body;
        }
    }
}