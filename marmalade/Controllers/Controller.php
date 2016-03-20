<?php
namespace Marmalade\Controllers;

use Marmalade\Util;
use Marmalade\Views\View;
use Marmalade\Response;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/**
 * The base Controller class. All controllers should extend this main controller
 */
abstract class Controller {
    /**
     * Load a view and append it to the Marmalade\Response class
     *
     * @param string|\Marmalade\Views\View $view Use a string for a specific PHP template file or a View object that extends Marmalade\Views\View.
     * @param mixed $model (default: null) The model to pass into the view
     *
     * @return void
     */
    public function load_view($view, $model = null) {
        // Handle if the view is an object        
        if ($view instanceof View) {
            Response::append_output($view->build_output($model));
        } else {
            // Handle if the view is a standard PHP file
            // Check if the view was found
            if (file_exists($view) === false) {
                throw new \Exception("Requested view does not exist.");
            }

            // Use output buffering to store the output and to get a speed boost
            ob_start();
            require_once($view);
            Response::append_output(ob_get_clean());
        }
    }
}