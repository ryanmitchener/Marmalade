<?php
namespace Marmalade\Controllers;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Controller for installing Marmalade
 */
class InstallController extends Controller {
    /**
     * Show the install page
     */
    public function install() {
        $this->load_view(new \Marmalade\Views\InstallView(), new \Marmalade\Models\InstallModel());
    }
}