<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Leeloo LXP AUTH external API
 *
 * @package    auth_leeloolxp_tracking_sso
 * @category   external
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/filelib.php');
require_once("$CFG->libdir/externallib.php");

/**
 * Leeloo LXP AUTH external functions
 *
 * @package    auth_leeloolxp_tracking_sso
 * @category   external
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class auth_leeloolxp_tracking_sso_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_user_sso_urls_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'userid'),
            )
        );
    }

    /**
     * Get sso urls for logging in...
     *
     * @param int $userid the auth_leeloolxp_tracking_sso user id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function get_user_sso_urls($userid) {
        global $DB;

        $params = self::validate_parameters(
            self::get_user_sso_urls_parameters(),
            array(
                'userid' => $userid,
            )
        );
        $warnings = array();

        $ssourls = $DB->get_record_sql("SELECT jurl,leeloourl FROM {auth_leeloolxp_tracking_sso} WHERE userid = ?", [$userid]);

        $result = array();
        $result['status'] = true;
        $result['jurl'] = $ssourls->jurl;
        $result['leeloourl'] = $ssourls->leeloourl;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_user_sso_urls_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'jurl' => new external_value(PARAM_TEXT, 'sso url for J'),
                'leeloourl' => new external_value(PARAM_TEXT, 'sso url for LeelooLXP'),
            )
        );
    }
}
