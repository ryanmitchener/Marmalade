<?php
namespace Marmalade\Views;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/**
 * The base View class
 * All views must extend this base class
 */
abstract class View {
    /** 
     * Build the output
     *
     * @param Marmalade\Model $model The model passed from the controller
     *
     * @return string The output to send to the client
     */
    function build_output($model) {
        return "";
    }
}