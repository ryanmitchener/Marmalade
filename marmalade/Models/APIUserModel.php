<?php
namespace Marmalade\Models;

use Marmalade\Database;
use Marmalade\Security;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** 
 * API user model class
 * This class handles the creation and deletion of API users
 */
class APIUserModel extends Model {
    public $id; /** @var int $id The id of the user */
    public $public_key; /** @var string $public_key The user's public API key */
    public $private_key; /** @var string $private_key The user's private API key */


    /** 
     * Retrieve a user by it's id
     *
     * @param int $id The id of the user to retrieve
     * 
     * @return APIUserModel|boolean The APIUserModel if one is found, false if there is no such user
     */
    public static function load_by_id($id) {
        $db = Database::get_instance();
        $query = "SELECT * FROM `".DB_PREFIX."api_users` WHERE `id` = ?";
        $sth = $db->prepare($query);
        $sth->execute(array($id));
        $rw_user = $sth->fetch();

        if ($rw_user !== false) {
            return APIUserModel::transform($rw_user);
        }
        return false;
    }


    /** 
     * Retrieve a user by its public key
     *
     * @param string $public_key The public key of the user to retrieve
     * 
     * @return APIUserModel|boolean The APIUserModel if one is found, false if there is no such user
     */
    public static function load_by_public_key($public_key) {
        $db = Database::get_instance();
        $query = "SELECT * FROM `".DB_PREFIX."api_users` WHERE `public_key` = ?";
        $sth = $db->prepare($query);
        $sth->execute(array($public_key));
        $rw_user = $sth->fetch();

        if ($rw_user !== false) {
            return APIUserModel::transform($rw_user);
        }
        return false;
    }


    /** 
     * Create a user in the Marmalade database
     * 
     * @return APIUserModel|boolean The user object for the created user or FALSE if the user could not be created
     */
    public static function create() {
        $db = Database::get_instance();

        // Set up the model
        $model = new APIUserModel();
        $model->public_key = Security::generate_random_hash();
        $model->private_key = Security::generate_random_hash();

        // Insert the user
        $sth = $db->prepare("INSERT INTO `".DB_PREFIX."api_users` (`public_key`, `private_key`) VALUES(?,?);");
        try {
            $db->beginTransaction();
            $sth->execute(array($model->public_key, $model->private_key));
            $model->id = $db->lastInsertId();
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
        return $model;
    }


    /** 
     * Delete a user
     *
     * @param int $id The id of the user to delete
     *
     * @return int|boolean If successful, the number of rows affected. FALSE if there was an error.
     */
    public static function delete($id) {
        if ($id === NULL || (is_string($id) && $id === "")) {
            return false;
        }

        $db = Database::get_instance();
        $sth = $db->prepare("DELETE FROM `".DB_PREFIX."api_users` WHERE `id` = ? LIMIT 1;");
        try {
            $db->beginTransaction();
            $sth->execute(array($id));
            $count = $sth->rowCount();
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
        return $count;
    }


    /**
     * Transform a database row into an APIUserModel
     *
     * @param PDO Result Object $rw_user The result from a PDO query
     *
     * @return APIUserModel The transformed model
     */
    private static function transform($rw_user) {
        $user = new APIUserModel();
        $user->id = (int) $rw_user->id;
        $user->public_key = $rw_user->public_key;
        $user->private_key = $rw_user->private_key;
        return $user;
    }
}