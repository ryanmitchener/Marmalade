<?php
namespace Marmalade\Controllers;

use Marmalade\Marmalade;
use Marmalade\Error;
use Marmalade\Request;
use Marmalade\Database;
use Marmalade\Security;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * XHR controller for handling XHR/AJAX requests
 */
abstract class XHRController extends Controller {
    // The main execute function
    public function execute($action) {
        // Set the request property is_xhr to true
        Request::$is_xhr = true;

        // Check if actions exists
        if (!method_exists($this, $action)) {
            Marmalade::error(Error::create(Error::INVALID_XHR_ACTION, array("action" => $action)));
        }

        // Validate nonce via Authorization header or JSON body
        if (!Security::verify_nonce($action, Request::get_nonce())) {
            Marmalade::error(Error::create(Error::INVALID_NONCE));
        }

        // Execute the action
        call_user_func(array($this, $action));
    }
}