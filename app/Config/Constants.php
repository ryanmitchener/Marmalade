<?php
namespace MarmaladeApp\Config;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/**
 * This is the main configuration file that should be edited by the user
 * in order to achieve the desired framework direction. This file is
 * designed for constants and flags 
 */
class Constants {
    /**
     * Loads all of the constants. This is called by Marmalade directly.
     * 
     * @return void
     */
    public static function load() {
        // Folder URL locations
        define("ROOT_URL", (getenv("ROOT_URL")) ? getenv("ROOT_URL") : ""); /** @var string ROOT_URL (default: "") the URL location of the root directory (default: "") */
        define("ASSET_URL", ROOT_URL."/assets");  /** @var string ROOT_URL the URL (default: ROOT_URL."/assets") location of the assets directory. The assets folder will contain all publicly accessible files such as JS, CSS, and fonts (default: ROOT_URL."/assets") */


        /**
         * Use Compression
         * 
         * Turn to true if zlib.output_compression should be enabled.
         * This should only be enabled if you do not have access to your php.ini
         * configuration, otherwise you should enable it there.
         * 
         * @var boolean (default: false)
         */
        define("USE_COMPRESSION", false);


        /**
         * Timezone constant used to determine the standard timezone for the application.
         *
         * @var string (default: "UTC")
         */
        define("TIMEZONE", "UTC");


        /**
         * Application name
         *
         * This is used in the Authorization header for API requests to identify your application.
         * This is also used to create custom headers to verify API requests.
         * Accepted characters: [a-zA-Z0-9_-] (no spaces)
         * 
         * @var string (default: "myapp")
         */
        define("APP_NAME", "myapp");


        /**
         * Toggles if the application is treated as an API or not. This affects things like headers.
         * 
         * @var boolean (default: false)
         */
        define("IS_API", false);


        /**
         * Turns Marmalade's built in API versioning on or off
         *
         * @var boolean (default: false)
         */
        define("USE_API_VERSIONING", false);


        /**
         * Application reverse domain notation name.
         *
         * This is used in APIClient for making requests and also for validating versioned API requests.
         * This constant should be prefixed with "vnd." (i.e. vnd.mydomain.myapp).
         * This constant should (but does not have to) be based on a domain name you own
         * 
         * @var string (default: "vnd.mydomain.myapp")
         */
        define("VENDOR_ID", "vnd.mydomain.myapp");


        /**
         * Force the application to require TLS/SSL. Note that Routes can still override this flag.
         *
         * @var boolean (default: false)
         */
        define("REQUIRE_TLS", false);


        /**
         * Redirect to HTTPS is TLS is not detected.
         *
         * This flag only takes effect if REQUIRE_TLS is set to TRUE
         * If this flag is set to FALSE and a user tries to access the site without TLS,
         * an error will be returned.
         * FALSE is the recommended setting for an API. If a user accidentally sets up
         * their client to point to HTTP and the API silently redirects without passing back
         * and error, the user will not know to change their client to point to HTTPS
         * to protect their data
         *
         * @var boolean (default: false)
         */
        define("REDIRECT_TO_TLS", false);


        /**
         * Enable Marmalade caching
         *
         * This flag is set to false for development. You should set this value to
         * true in production as it will speed up your application.
         * This flag enables Marmalade to cache specific items like your
         * routes. With this flag enabled, any time you make a change to your routes, 
         * you will need to restart your FastCGI process manager for Nginx or 
         * restart Apache.
         *
         * @var boolean (default: false)
         */
        define("ENABLE_CACHE", false);


        /**
         * Set the chunk size for the response
         *
         * If the response is bigger than RESPONSE_CHUNK_SIZE, Marmalade
         * will flush the output in RESPONSE_CHUNK_SIZE bytes to the client.
         *
         * @var int (default: 20480)
         */
        define("RESPONSE_CHUNK_SIZE", 20480);


        /**
         * Require the use of authentication.
         *
         * If the application is NOT an API this setting will require a user to log in.
         * In addition, when using this flag it is important to modify the Hooks::get_login_uri() to return
         * the path to your login controller
         * If the application is an API this setting will require an API signature passed in.
         * This can be overridden per route by setting the AUTHENTICATE option to true
         * Routes can still override this flag
         *
         * @var boolean (default: false)
         */
        define("REQUIRE_AUTHENTICATION", false);


        // Security: Generate these with Security::generate_random_hash() or running the Marmalade install
        define("NONCE_KEY", ""); /** @var string NONCE_KEY The key used for HMAC hashing nonces */
        define("ENCRYPTION_KEY", ""); /** @var string ENCRYPTION_KEY The key used for encrypting/decrypting objects */
        define("XHR_NONCE_EXPIRATION_MINUTES", 120); /** @var int XHR_NONCE_EXPIRATION_MINUTES (default: 120) The amount of time a nonce is active without being used */
        define("API_REQUEST_EXPIRATION_MINUTES", 5); /** @var int API_REQUEST_EXPIRATION_MINUTES (default: 5) The amount of time an API request has before it expires */
        define("XHR_DATABASE_NONCES", false); /** @var boolean XHR_DATABASE_NONCES (default: false) This will record nonces in the database to prevent a nonce from being used more than once */
        define("API_DATABASE_NONCES", false); /** @var boolean API_DATABASE_NONCES (default: false) This will record request signatures to the database and use them as nonces in order to prevent the same request from being used more than once */


        // Database constants
        define("DB_PREFIX", "marmalade_"); /** @var string DB_PREFIX (default: marmalade) The prefix to use for database tables */
        define("DB_HOST", false); /** @var string|boolean DB_HOST (default: false) is the string of the database host (default: false) */
        define("DB_PORT", false); /** @var int DB_PORT (default: 3306) is the port the database server is running on */
        define("DB_USER", false); /** @var string|boolean DB_USER (default: false) is the database user to log in as when connecting to the database server */
        define("DB_PASS", false); /** @var string|boolean DB_PASS (default: false) is the database password to use when logging in to the database server */
        define("DB_DATABASE", false); /** @var string|boolean DB_DATABASE (default: false) is the default database to use when logging in to the database */
    }
}