<?php
namespace Marmalade\Models;

use Marmalade\Database;
use Marmalade\Security;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * Install model class
 * This class handles the installation of Marmalade and helps the
 * user set up an initial configuration.
 */
class InstallModel extends Model {
    public $database_connection = 0; /** @var int $database_connection 1 if the database was successfully connected to, 0 if the database is not configured, -1 if there was an error connecting to the database. */
    public $tables_created = 0; /** @var int $tables_created 1 if the tables were created, 0 if they weren't, -1 if there was an error */
    public $nonce_key = ""; /** @var string $nonce_key the key for nonces HMAC hashing */
    public $encryption_key = ""; /** @var string $encryption_key The key for encrypting and decrypting objects */
    public $info; /** @var object $info The info JSON from info.json */

    // Database schema
    private $tables = array(
        "nonces" => array(
            "create" => 
                "CREATE TABLE `".DB_PREFIX."nonces` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `nonce` varchar(64) NOT NULL DEFAULT '0',
                    `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `nonce` (`nonce`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores all nonces used for API calls and XHR calls'"
        ),
        "api_users" => array(
            "create" =>
                "CREATE TABLE `".DB_PREFIX."api_users` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `public_key` varchar(64) NOT NULL,
                    `private_key` varchar(64) NOT NULL,
                    `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `public_key` (`public_key`),
                    UNIQUE KEY `private_key` (`private_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table contains all of the API users';"
        ),
    );


    /**
     * Constructor 
     */
    public function __construct() {
        error_reporting(0); // Temporarily turn off error reporting
        $this->info = json_decode(file_get_contents(ROOT_DIR."/info.json"));
        $this->generate_salts();
        $this->check_database();
    }


    /**
     * Generates all the necessary salts for a basic install
     */
    private function generate_salts() {
        $this->nonce_key = Security::generate_random_hash();
        $this->encryption_key = Security::generate_random_hash();
    }


    /**
     * Check the database connection and if the tables exist
     */
    private function check_database() {
        if (!Database::is_configured()) {
            return;
        } else if (($db = Database::get_instance(false)) === false) {
            $this->database_connection = -1;
            return;
        }

        // Set that the connection is good
        $this->database_connection = 1;

        // Check if all tables have been created, if not, create them
        foreach ($this->tables as $table => $object) {
            if (!$this->table_exists($table)) {
                if ($this->create_table($table) === false) {
                    $this->tables_created = -1;
                    break;
                } else {
                    $this->tables_created = 1;
                }
            }
        }
    }


    /**
     * Checks if a table exists
     *
     * @return boolean true if the table exists, false if the table does not exists
     */
    private function table_exists($table) {
        $db = Database::get_instance();
        $query = "SHOW TABLES LIKE '".DB_PREFIX."{$table}'";
        $sth = $db->prepare($query);
        $sth->execute();
        return ($sth->rowCount() > 0) ? true : false;
    }


    /**
     * Creates the Marmalade database if it doesn't exist
     *
     * @return boolean true if the table was created, false if the table was not created
     */
    public function create_table($table) {
        $db = Database::get_instance();
        
        // Execute a create table
        try {
            $db->beginTransaction();
            $sth = $db->prepare($this->tables[$table]["create"]);
            $sth->execute();
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
        return $this->table_exists($table);
    }
}