<?php
namespace Marmalade\Controllers;

use \Marmalade\Marmalade;
use Marmalade\Views\SitemapView;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/**
 * Sitemap controller class
 */
class SitemapController extends Controller {
    /** Show the Sitemap based on the router */
    public function sitemap() {
        $model = Marmalade::get_instance()->router->get_sitemap_array();
        $this->load_view(new SitemapView(), $model);
    }
}