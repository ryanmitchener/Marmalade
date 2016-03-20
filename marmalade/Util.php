<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** Util class for all common functions for Marmalade */
class Util {
    /** 
     * Scan recursively for a file
     *
     * @param string $name The name of the file
     * @param string $directory The starting directory to search for the file. Directories should NOT have a trailing slash
     * @param string $extension (default: .php) The file extension to search for
     * @return string|boolean The file name if it is found, false if the file has not been found
     */
    public static function file_exists($name, $directory = APP_DIR, $extension = ".php") {
        // Check if the file exists first
        if (file_exists($directory."/".$name.$extension)) {
            return $directory."/".$name.$extension;
        } else if (!is_dir($directory)) {
            return false;
        }

        // If the file didn't exist scan all sub-directories
        $files = scandir($directory, SCANDIR_SORT_DESCENDING);
        if ($files === false) {
            return false;
        }
        foreach ($files as $file) {
            if ($file === "." || $file === "..") {
                continue;
            } else if (is_dir($directory."/".$file)) {
                $found = Util::file_exists($name, $directory."/".$file, $extension);
                if ($found !== false) {
                    return $found;
                }
            }
        }
        return false;
    }


    /**
     * Return the HTML for a navigation menu based on your routes
     * This is a convenience function only
     *
     * @param array $options is an array of options to pass to the Nav class
     * @param mixed $custom_routes (default: null) a custom nav object to send into the Nav class instead of the default nav_object created from the Router's routes
     *
     * @return string HTML of the navigation menu
     */
    public static function build_nav($options = array(), $custom_routes = null) {
        $nav = Marmalade::get_instance()->router->get_nav($custom_routes, $options);
        return ($nav !== false) ? $nav->build() : "";
    }


    /** 
     * Return if the script is being executed from CLI or not
     * This is useful if you want to restrict certain controllers to being accessed
     * from CLI only
     *
     * @return boolean
     */
    public static function is_cli() {
        return (PHP_SAPI === "cli" || defined("STDIN"));
    }
}