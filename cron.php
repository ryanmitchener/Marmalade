<?php
// Kill the script if it wasn't started through CLI
if (PHP_SAPI !== "cli" || !defined("STDIN")) {
    die("This script must be executed through CLI");
}

// Setup root directory constants
define("ROOT_DIR", __DIR__);
define("APP_DIR", ROOT_DIR."/app");
define("CORE_DIR", ROOT_DIR."/marmalade");

// Initialize framework
require_once(CORE_DIR."/Marmalade.php");
Marmalade\Marmalade::get_instance()->init();
Marmalade\Marmalade::get_instance()->cron();