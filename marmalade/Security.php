<?php
namespace Marmalade;

use \Marmalade\Models\APIUserModel;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/** Security class */
abstract class Security {
    const HASH_ALGO = "SHA256";
    const ENCRYPT_ALGO = "RC4-40";
    const MICROTIME_MULTIPLIER = 1000; // Will help put microtime into milliseconds
    const NONCE_LENGTH = 30; /** @var the base nonce length */

    /** Constructor */
    private function __construct() {}

    /** 
     * Create nonce
     * 
     * @param string $action The action to create a nonce for
     *
     * @return string The nonce created for the action
     */
    public static function create_nonce($action) {
        // Start the session if it hasn't been started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION["__marmalade_session_token"])) {
            $_SESSION["__marmalade_session_token"] = Security::generate_session_token();
        }
        $timestamp = floor(microtime(true) * self::MICROTIME_MULTIPLIER);
        $nonce = hash(self::HASH_ALGO, $action.$timestamp.$_SESSION["__marmalade_session_token"]);
        $nonce = hash_hmac(self::HASH_ALGO, $nonce, NONCE_KEY);
        return substr($nonce, 7, Security::NONCE_LENGTH).$timestamp;
    }


    /** 
     * Verify a nonce
     *
     * @param string $action The action for the nonce
     * @param string $nonce The nonce to verify
     *
     * @return boolean TRUE if the nonce is valid, FALSE if invalid
     */
    public static function verify_nonce($action, $nonce) {
        // Check if the nonce is null or not
        if ($nonce === null) {
            return false;
        } else if (session_status() !== PHP_SESSION_ACTIVE) { // Return false if a session hasn't been started. The session contains a piece of the nonce we need
            return false;
        }

        // Verify the nonce has not expired
        $timestamp = (float) substr($nonce, 30);
        $current_timestamp = floor((microtime(true) * Security::MICROTIME_MULTIPLIER));
        if (abs(($current_timestamp - (float) $timestamp) / 1000 / 60) > XHR_NONCE_EXPIRATION_MINUTES) {
            return false;
        } 

        // Verify the nonce is correct
        $expected = hash(self::HASH_ALGO, $action.$timestamp.$_SESSION["__marmalade_session_token"]);
        $expected = hash_hmac(self::HASH_ALGO, $expected, NONCE_KEY);
        $expected = substr($expected, 7, Security::NONCE_LENGTH).$timestamp;
        if ($nonce !== $expected) {
            return false;
        }

        // Make sure the nonce is not in the database
        if (XHR_DATABASE_NONCES) {
            $db = Database::get_instance();
            $query = "SELECT * FROM `".DB_PREFIX."nonces` WHERE `nonce` = ? LIMIT 1";
            $sth = $db->prepare($query);
            $sth->execute(array($nonce));
            if ($sth->fetch() !== false) {
                return false;
            }

            // Insert used nonce into database
            $query = "INSERT INTO `".DB_PREFIX."nonces` (`nonce`) VALUES(?)";
            $sth = $db->prepare($query);
            $response = $sth->execute(array($nonce));
            if ($response === false) {
                return false;
            }
        }
        return true;
    }


    /**
     * Clean nonces older than the configured expiration
     */
    public static function clear_old_nonces() {
        if (($db = Database::get_instance(false)) === false) {
            return;
        }
        $query = "DELETE FROM `".DB_PREFIX."nonces` WHERE `date` < DATE_SUB(NOW(), INTERVAL ? MINUTE)";
        $sth = $db->prepare($query);
        $sth->execute(array(XHR_NONCE_EXPIRATION_MINUTES));
    }


    /** 
     * Verify the API signature. 
     * This method has the potential to call several ErrorViews and exit the script entirely
     * This process is derived from Amazon Authorization AWS Signature V4 (http://docs.aws.amazon.com/AmazonS3/latest/API/sig-v4-header-based-auth.html)
     *
     * @param boolean $show_error TRUE if errors should be thrown, FALSE if no errors should stop the script.
     *
     * @return boolean TRUE if the signature is valid, FALSE if invalid
     */
    public static function verify_api_signature($show_error = true) {
        // Verify headers were set correctly
        if (!isset(Request::$headers["Authorization"]) || !preg_match("/^".APP_NAME.":[a-zA-Z0-9]+:[a-zA-Z0-9]+$/", Request::$headers["Authorization"], $authorization_match)) {
            if ($show_error) {
                Marmalade::error(Error::create(Error::INVALID_AUTHORIZATION_HEADER));                
            }
            return false;
        } else if (!isset(Request::$headers[APP_NAME."-timestamp"]) || Request::$headers[APP_NAME."-timestamp"] === "") {
            if ($show_error) {
                Marmalade::error(Error::create(Error::INVALID_TIMESTAMP_HEADER));
            }
            return false;
        }

        // Get the header information
        $timestamp = Request::$headers[APP_NAME."-timestamp"];
        $authorization = explode(":", $authorization_match[0]);
        $public_key = $authorization[1];
        $request_signature = $authorization[2];

        // Verify time difference is not greater or less than 5 minutes
        $current_timestamp = floor((microtime(true) * Security::MICROTIME_MULTIPLIER));
        if (abs(($current_timestamp - (float) $timestamp) / 1000 / 60) > API_REQUEST_EXPIRATION_MINUTES) {
            if ($show_error) {
                Marmalade::error(Error::create(Error::API_REQUEST_EXPIRED));
            }
            return false;
        }

        // Get the secret API key based on the public key
        $api_user = APIUserModel::load_by_public_key($public_key);
        if ($api_user === false) {
            if ($show_error) {
                Marmalade::error(Error::create(Error::INVALID_AUTHORIZATION_HEADER));
            }
            return false;
        }
        $private_key = $api_user->private_key;

        // Calculate the signature from the request
        $canonical_request = Request::$http_verb . "\n" .
            Request::$uri."\n".
            Request::$raw_query."\n".
            hash(Security::HASH_ALGO, Request::$raw_body);
        $string_to_sign = hash(Security::HASH_ALGO, 
            APP_NAME."\n".
            $timestamp."\n".
            hash(Security::HASH_ALGO, $canonical_request));
        $signing_key = hash_hmac(Security::HASH_ALGO, $timestamp, $private_key);
        $signature = hash_hmac(Security::HASH_ALGO, $string_to_sign, $signing_key);

        // Compare the generated signature with provided signature
        if ($request_signature !== $signature) {
            if ($show_error) {
                Marmalade::error(Error::create(Error::INVALID_API_SIGNATURE));
            }
            return false;
        }

        // If logging requests as nonces, check it and record it to the database if it doesn't exist
        if (API_DATABASE_NONCES) {
            $query = "SELECT * FROM `".DB_PREFIX."nonces` WHERE `nonce` = ?";
            $sth = $db->prepare($query);
            $sth->execute(array($request_signature));
            $result = $sth->fetch();

            if ($result !== false) {
                if ($show_error) {
                    Marmalade::error(Error::create(Error::API_SIGNATURE_ALREADY_USED));
                }
                return false;
            } else {
                $query = "INSERT INTO `".DB_PREFIX."nonces` (`nonce`) VALUES(?)";
                $sth = $db->prepare($query);
                $sth->execute(array($request_signature));
            }
        }
        return true;
    }


    /** 
     * Generate unique session token
     *
     * @return string A unique session token
     */
    public static function generate_session_token() {
        $timestamp = floor(microtime(true) * self::MICROTIME_MULTIPLIER);
        $crypto_strong = false;
        while ($crypto_strong === false) {
            $bytes = openssl_random_pseudo_bytes(30, $crypto_strong);
        }
        return substr(hash(Security::HASH_ALGO, $timestamp.$bytes), 0, 30);
    }


    /** 
     * Encrypt an object into a string
     *
     * @return string|boolean An encrypted string of data representing the object. FALSE on failure.
     */
    public static function encrypt_object($object) {
        return openssl_encrypt(json_encode($object), self::ENCRYPT_ALGO, ENCRYPTION_KEY);
    }


    /** 
     * Decrypt a previously encrypted object
     *
     * @return object|boolean A decrypted object. FALSE on failure.
     */
    public static function decrypt_object($encrypted) {
        return json_decode(openssl_decrypt($encrypted, self::ENCRYPT_ALGO, ENCRYPTION_KEY));
    }


    /** 
     * Generates a cryptographically secure random hash
     * This is used in API user creation and for salt generation
     * 
     * @param int $length The length of the hash to generate
     */
    public static function generate_random_hash($length = 64) {
        $crypto_strong = false;
        while ($crypto_strong === false) {
            $random = openssl_random_pseudo_bytes($length, $crypto_strong);
        }
        return hash("SHA512", $random);
    }
}