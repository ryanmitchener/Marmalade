<?php
namespace Marmalade\Views;

use \Marmalade\Response;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** View for displaying errors to a user */
class SitemapView extends View {
    /** 
     * Build the output
     *
     * @param Marmalade\Model $model The model passed from the controller
     *
     * @return string The output to send to the client
     */
    function build_output($model) {
        Response::set_header("Content-Type", "application/xml");

        // Begin XML output
        $response = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
            "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";


        // Loop through URLs
        foreach ($model as $url) {
            $response .= "<url>";
            $response .= "<loc>{$url->loc}</loc>";
            $response .= ($url->lastmod !== "") ? "<lastmod>{$url->lastmod}</lastmod>" : "";
            $response .= ($url->changefreq !== "") ? "<changefreq>{$url->changefreq}</changefreq>" : "";
            $response .= ($url->priority !== "") ? "<priority>{$url->priority}</priority>" : "";
            $response .= "</url>";
        }

        // End XML output
        $response .= "</urlset>";
        return $response;
    }
}