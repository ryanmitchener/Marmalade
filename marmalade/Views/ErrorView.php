<?php
namespace Marmalade\Views;

use Marmalade\Response;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** View for displaying errors to a user */
class ErrorView extends View {
    /** 
     * Build the output
     *
     * @param Marmalade\Model $model The model passed from the controller
     *
     * @return string The output to send to the client
     */
    function build_output($model) {
        Response::set_header("Content-Type", "application/json");
        Response::set_status_code($model->http_code);
        return json_encode(array(
            "http_code" => $model->http_code,
            "error_message" => $model->message));
    }
}