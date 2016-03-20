<?php
namespace Marmalade;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Marmalade Autoloader
 */
class Autoloader {
    const EXTENSION = ".php"; // The file extension for all classes in the application

    // Prefixes
    private $prefixes = array();


    /** Constructor */
    public function __construct() {
        spl_autoload_register(array($this, "loader"));

        // Add the default namespaces
        $this->add_namespace_prefix("Marmalade", CORE_DIR);
        $this->add_namespace_prefix("MarmaladeApp", APP_DIR);
        $this->add_namespace_prefix("App", APP_DIR);
    }


    /** 
     * Add a namespace to the autoloader
     *
     * @param string $prefix The namespace prefix to add
     * @param string $base_dir The base directory that the namespace prefix refers to
     * @param boolean $prepend Prepend the namespace prefix so it is tested before other prefixes
     */
    public function add_namespace_prefix($prefix, $base_dir, $prepend = false) {
        // Normalize namespace prefix
        $prefix = trim($prefix, "\\")."\\";

        // initialize the namespace prefix array
        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = array();
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }


    /** 
     * Loading a class file
     * 
     * @param string $class The class name we are attempting to load
     */
    private function loader($class) {
        // Handle namespaces
        $prefix = $class;
        while (($pos = strrpos($prefix, "\\")) !== false) {
            // Retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // The rest is the relative class name
            $relative_class = substr($class, $pos + 1);

            // If the prefix exists, look through the base directories associated with it
            if (isset($this->prefixes[$prefix])) {
                // Loop through base directories for the prefix
                foreach ($this->prefixes[$prefix] as $base_dir) {
                    $file = $base_dir."/"
                        .str_replace("\\", "/", $relative_class)
                        .Autoloader::EXTENSION;

                    // If the file exists, require it
                    if (file_exists($file)) {
                        require_once($file);
                        return;
                    }
                }
            }

            // Remove the trailing namespace separator for the next iteration of strrpos()
            $prefix = rtrim($prefix, "\\");   
        }
    }
}