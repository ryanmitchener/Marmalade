<?php
namespace Marmalade\Controllers;

use Marmalade\Models\ErrorModel;
use Marmalade\Views\ErrorView;
use Marmalade\Response;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/**
 * Error controller class
 * This class is for errors generated outside of a controller. 
 * All other Controllers will generate their own Error models
 */
class ErrorController extends Controller {
    // Constructor
    function __construct() {
        // Clear the output before showing an error
        Response::set_output("");
    }


    /** The main ErrorController action that all subclasses should extend */
    function error($error_model) {
        $this->load_view(new ErrorView(), $error_model);
    }
}