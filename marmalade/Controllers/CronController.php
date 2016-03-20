<?php
namespace Marmalade\Controllers;

use \Marmalade\Security;

// Kill script if unauthorized access occurs
if (!defined("ROOT_DIR")) { die("Unauthorized access!"); }

/**
 * Cron controller class
 */
class CronController extends Controller {
    /** This cron job will run every five minutes */
    public function five_minutes() {}

    /** This cron job will run every thirty minutes */
    public function thirty_minutes() {}

    /** This cron job will run once an hour */
    public function one_hour() {}

    /** This cron job will run every six hours */
    public function six_hours() {}

    /** This cron job will run every twelve hours */
    public function twelve_hours() {}

    /** This cron job will run once a day */
    public function one_day() {
        // Clear out all nonces past the configured expiration
        if (XHR_DATABASE_NONCES || API_DATABASE_NONCES) {
            Security::clear_old_nonces();
        }
    }
}