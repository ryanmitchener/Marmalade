<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * API Client class
 *
 * @note URIs must contain a leading and trailing slash
 * @example $response = (new APIClient())->call("GET", "/resource/sub-resource/", "application/vnd.example.resource.v1+json", null);
 */
class APIClient {
    private $host = ""; /** @var string The full domain of the API */
    private $app_name = ""; /** @var The application name */
    private $private_key = ""; /** @var string Your private API key */
    private $public_key = ""; /** @var string Your public API key */
    private $response_info; /** @var mixed the response info from the curl request */
    private $verify_ssl; /** @var Sets if SSL certificates should be verified or ignored */


    /**
     * API client
     * Create an APIClient capable of making API calls to a Marmalade instance
     * 
     * @param string $host The full domain (without the trailing slash) of the API to call (http://example.com)
     * @param string $app_name The application name of the API
     * @param string|boolean $public_key Your public API key. FALSE if no key is necessary.
     * @param string|boolean $private_key Your private API key. FALSE if no key is necessary.
     * @param boolean $verify_ssl (default: true) Sets if SSL certificates should be verified or ignored
     */
    function __construct($host, $app_name, $public_key = false, $private_key = false, $verify_ssl = true) {
        $this->host = rtrim($host, "/");
        $this->app_name = $app_name;
        $this->public_key = $public_key;
        $this->private_key = $private_key;
        $this->verify_ssl = $verify_ssl;
    }


    /** 
     * Make an API call 
     *
     * Sending the $request_body as a string will send the application/x-form-urlencoded Content-Type header
     * unless otherwise specified. If you pass $request_body as an array, the multipart/form-data Content-Type 
     * header will be used. In order to POST files, $request_body must be an array and use 
     * a CURLFile instance as an array value for the file.
     * 
     * @param string $http_verb The type of request being made (GET, POST, PUT, DELETE, .etc) 
     * @param string $uri The endpoint URI of the resource to call. For example: "/products".
     * @param array $headers The headers to send in the request.
     * @param string|array $request_body The request body to send to the API (array if sending a file or multipart/form-data, string for everything else)
     * @param boolean $sign_request Set to TRUE if the URI requires authentication, FALSE if the URI does not need authentication
     *
     * @return string|boolean The response from the request if successful, FALSE on failure
     */
    function call($http_verb, $uri, $headers = array(), $request_body = "", $sign_request = true) {
        // Determine if the request is authenticated or not
        if ($sign_request) {
            // Compute the signature
            $uri_exploded = explode("?", $uri);
            $uri_path = $uri_exploded[0];
            $query_string = (count($uri_exploded) > 1) ? $uri_exploded[1] : "";
            $timestamp = floor(microtime(true) * 1000);

            // Calculate the request signature
            $canonical_request = $http_verb."\n".
                $uri_path."\n".
                $query_string."\n".
                hash(Security::HASH_ALGO, (is_array($request_body)) ? "" : $request_body);
            $string_to_sign = hash("SHA256", 
                "{$this->app_name}\n".
                $timestamp."\n".
                hash(Security::HASH_ALGO, $canonical_request));
            $signing_key = hash_hmac("SHA256", $timestamp, $this->private_key);
            $signature = hash_hmac("SHA256", $string_to_sign, $signing_key);

            // Add the signature headers
            $curl_headers = array(
                "Authorization: {$this->app_name}:{$this->public_key}:{$signature}",
                "{$this->app_name}-timestamp: {$timestamp}"
            );
        } else {
            $curl_headers = array();
        }

        // Add the passed headers
        foreach ($headers as $key => $value) {
            $curl_headers[] = "{$key}: {$value}";
        }

        // Build the request
        $ch = curl_init();
        if ($this->verify_ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($ch, CURLOPT_URL, "{$this->host}{$uri}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);

        // Handle HTTP verbs
        // Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data, while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded. (http://php.net/manual/en/function.curl-setopt.php)
        switch ($http_verb) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
                break;
            case "PUT":
                // PUT does not work with files at this time
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            case "HEAD":
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                break;
            case "OPTIONS":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
                curl_setopt($ch, CURLOPT_HEADER, true);
                break;
        }

        // Execute the request
        $response = curl_exec($ch);
        $this->response_info = curl_getinfo($ch);
        return $response;
    }


    /** 
     * Get the info from the last call 
     *
     * @return mixed The curl response information
     */
    function get_info() {
        return $this->response_info;
    }


    /** 
     * Returns the response code
     *
     * @return int The response code from the request
     */
    function get_response_code() {
        return (int) $this->response_info["http_code"];
    }
}