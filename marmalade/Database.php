<?php
namespace Marmalade;

use \PDO;
use \PDOException;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Marmalade Database class
 */
class Database {
    /** @var Database $instance The static instance */
    private static $instance = null;

    /** Prevent construction of singleton */
    private function __construct() {}

    
    /** Prevent cloning of singleton */
    private function __clone() {}


    /** Prevent unserializing the singleton */
    private function __wakeup() {}


    /** 
     * Returns a singleton instance of the database 
     *
     * @param boolean $show_error If true, Marmalade throws an error if the database cannot be connected to. If false, FALSE will be returned to handle the error
     *
     * @return \PDO|boolean If a database connection exists, A PDO instance of a mysql database connection. If there was an error or the database was not configured, FALSE
     */
    public static function get_instance($show_error = true) {
        if (Database::$instance !== null) {
            return Database::$instance;
        } else if (!Database::is_configured()) {
            if ($show_error) {
                Marmalade::error(Error::create(Error::DATABASE_NOT_CONFIGURED));
            }
            return false;
        }
        try {
            // Connection vars
            $host = DB_HOST;
            $port = DB_PORT;
            $user = DB_USER;
            $password = DB_PASS;
            $db = DB_DATABASE;

            // Connect to database
            Database::$instance = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $password, array(
                // PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ)
            );
            return Database::$instance;
        } catch(PDOException $e) {
            if ($show_error) {
                Marmalade::error(Error::create(Error::NO_DATABASE));
            }
            return false;
        }
    }


    /**
     * Return if the database constants have been configured or not
     *
     * @return boolean TRUE if the database constants have been configured, FALSE if not
     */
    public static function is_configured() {
        return !(DB_HOST === false || DB_PORT === false || DB_USER === false || 
                DB_PASS === false || DB_DATABASE === false);
    }
}