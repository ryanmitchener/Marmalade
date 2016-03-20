<?php
namespace Marmalade\Models;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Error model class
 * This model handles a basic representation of an error
 */
class ErrorModel extends Model {
    public $http_code; /** @var int $http_code The HTTP code to be returned via headers */
    public $type; /** @var string $type A short description of the error for identification purposes */
    public $message; /** @var string $message Message describing the error */
    public $extras; /** @var array $extras Any extra information needed to pass on to the error controller */

    /** 
     * Constructor
     *
     * @param int $http_code The HTTP status code to set in the response
     * @param string $type A short description of the error for identification purposes
     * @param string $message The error message 
     * @param array $extra Any extra information to pass to the view
     */
    function __construct($http_code, $type, $message, $extras = array()) {
        $this->http_code = $http_code;
        $this->type = $type;
        $this->message = $message;
        $this->extras = $extras;
    }
}