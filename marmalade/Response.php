<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Response class
 * This class is an abstraction for the HTTP response
 */
abstract class Response {
    private static $headers = array(); /** @var array All of the headers to send with the response */
    private static $status_code = 200; /** @var int The status code to send with the response */
    private static $output; /** @var resource|string The output stream if the stream is attached. A string to output if no stream is attached. */
    private static $stream_attached = false; /** @var boolean All of the headers from the request */

    /** 
     * Initialize the Response
     */
    static function init() {
        Response::attach_output_stream();
        Response::add_default_headers(); // Add the default headers to the response
    }


    /** 
     * Attach the output stream
     */
    private static function attach_output_stream() {
        if ((Response::$output = fopen("php://temp", "r+")) === false) {
            Marmalade::error(Error::create(Error::COULD_NOT_WRITE_TO_RESPONSE_STREAM));
        }
        Response::$stream_attached = true;
    }


    /** 
     * Write to the output stream
     *
     * @param string $content The content to write to the output stream
     */
    private static function write_to_stream($content) {
        if (fwrite(Response::$output, $content) === false) {
            Marmalade::error(Error::create(Error::COULD_NOT_WRITE_TO_RESPONSE_STREAM));
        }
    }


    /** 
     * Initialize the Response with default header values
     */
    private static function add_default_headers() {
        // Add default headers
        if (IS_API) {
            Response::set_header("Content-Type", "application/json"); // Add content type header
        }
        Response::set_header("X-Content-Type-Options", "nosniff"); // Prevent MIME-sniffing
        Response::set_header("X-Frame-Options", "deny"); // Prevent this application from being loaded in an IFrame
    }


    /** 
     * Add a header to send in the Response
     *
     * @param string $header The name of the header (i.e. Content-Type)
     * @param string $value The value of the header (i.e. application/json)
     */
    static function set_header($header, $value) {
        Response::$headers[$header] = $value;
    }


    /** 
     * Remove a header that is in the Response class
     *
     * @param string $header The name of the header the remove (i.e. Content-Type)
     */
    static function remove_header($header) {
        unset(Response::$headers[$header]);
    }


    /** 
     * Get all headers
     * 
     * @return array All of the headers currently in the Response class
     */
    static function get_headers() {
        return Response::$headers;
    }


    /** 
     * Set the output
     * 
     * @param string $content The content to set the output to
     */
    static function set_output($content) {
        if (Response::$stream_attached === true) {
            fclose(Response::$output);
            Response::attach_output_stream();
            Response::write_to_stream($content);
        } else {
            Response::$output = $content;
        }
    }


    /** 
     * Append a string to the response
     * 
     * @param string $content The content to append to the output
     */
    static function append_output($content) {
        if (Response::$stream_attached === true) {
            Response::write_to_stream($content);
        } else {
            Response::$output .= $content;
        }
    }


    /** 
     * Set the status code of the response
     *
     * @param $code the HTTP code to send to the client
     */
    static function set_status_code($code) {
        Response::$status_code = $code;
    }


    /** 
     * Render the output and send it to the browser
     */
    static function render() {
        // Apply the status code
        if (Response::$status_code !== 200) {
            http_response_code(Response::$status_code);
        }

        // Output all of the headers
        foreach (Response::$headers as $header => $value) {
            header("{$header}: {$value}");
        }

        // If the request is a HEAD request, return without sending output
        if (Request::$http_verb === "HEAD") {
            return;
        }

        // Output the response to the client
        if (Response::$stream_attached) {
            rewind(Response::$output);
            while (!feof(Response::$output)) {
                if (($buffer = fread(Response::$output, RESPONSE_CHUNK_SIZE)) === false) {
                    Marmalade::error(Error::create(Error::COULD_NOT_READ_FROM_RESPONSE_STREAM));
                }
                echo $buffer;
            }
            fclose(Response::$output);
        } else {
            echo Response::$output;
        }
    }
}