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
 * Main functions of the plugin.
 *
 * @package auth_leeloolxp_tracking_sso
 * @author Leeloo LXP <info@leeloolxp.com>
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/lib/filelib.php');

/**
 * Plugin to sync users to LeelooLXP account of the Moodle Admin
 */
class auth_plugin_leeloolxp_tracking_sso extends auth_plugin_base {

    /**
     * Constructor
     */
    public function __construct() {
        $this->authtype = 'leeloolxp_tracking_sso';
        $this->config = get_config('auth_leeloolxp_tracking_sso');
    }

    /**
     * Generate random string.
     *
     * @param int $strength is strength
     * @return string $randomstring is random string
     */
    public function generate_string($strength = 16) {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $inputlength = strlen($input);
        $randomstring = '';
        for ($i = 0; $i < $strength; $i++) {
            $randomcharacter = $input[mt_rand(0, $inputlength - 1)];
            $randomstring .= $randomcharacter;
        }

        return $randomstring;
    }

    /**
     * Check if user authenticated
     *
     * @param string $user The userdata
     * @param string $username The username
     * @param string $password The password
     * @return bool Return true
     */
    public function user_authenticated_hook(&$user, $username, $password) {
        setcookie('leeloolxpssourl', '', time() + (86400), "/");
        setcookie('installlogintoken', '', time() + (86400), "/");

        $username = $username;
        $useremail = $user->email;

        global $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');

        $leeloolxplicense = $this->config->leeloolxp_license;
        $loginredirect = $this->config->login_redirectpage;

        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = array('license_key' => $leeloolxplicense);

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }

        $infoleeloolxp = json_decode($output);

        if ($infoleeloolxp->status != 'false') {
            $leeloolxpurl = $infoleeloolxp->data->install_url;
        } else {
            return true;
        }

        if (!$this->config->web_new_user_student) {
            return true;
        }

        $lastlogin = date('Y-m-d h:i:s', $user->lastlogin);
        $fullname = fullname($user);
        $city = $user->city;
        $country = $user->country;
        $timezone = $user->timezone;
        $skype = $user->skype;
        $idnumber = $user->idnumber;
        $institution = $user->institution;
        $department = $user->department;
        $phone = $user->phone1;
        $moodlephone = $user->phone2;
        $adress = $user->address;
        $firstaccess = $user->firstaccess;
        $lastaccess = $user->lastaccess;
        $lastlogin = $lastlogin;
        $lastip = $user->lastip;

        $description = $user->description;
        $descriptionofpic = $user->imagealt;
        $alternatename = $user->alternatename;
        $webpage = $user->url;
        $imgurl = new moodle_url('/user/pix.php/' . $user->id . '/f1.jpg');

        $redirecturl = $this->syncuser(
            $username,
            $useremail,
            $leeloolxpurl,
            $fullname,
            $city,
            $country,
            $timezone,
            $skype,
            $idnumber,
            $institution,
            $department,
            $phone,
            $moodlephone,
            $adress,
            $firstaccess,
            $lastaccess,
            $lastlogin,
            $lastip,
            $description,
            $descriptionofpic,
            $alternatename,
            $webpage,
            $imgurl,
            $user->id
        );

        if (isset($_COOKIE['leeloolxp']) && isset($_COOKIE['leeloolxp']) != '') {
            echo '<script>window.location.replace("' . $redirecturl . '");</script>';
        } else if ($loginredirect != '') {
            global $SESSION;
            $SESSION->wantsurl = $loginredirect;
        }

        return true;
    }

    /**
     * Returns false if the user exists and the password is wrong.
     *
     * @param string $username is username
     * @param string $password is password
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        return false;
    }

    /**
     * Sync user to LeelooLXP with his details.
     *
     * @param string $username The username
     * @param string $email The email
     * @param string $leeloolxpurl The leeloolxpurl
     * @param string $fullname The fullname
     * @param string $city The city
     * @param string $country The country
     * @param string $timezone The timezone
     * @param string $skype The skype
     * @param string $idnumber The idnumber
     * @param string $institution The institution
     * @param string $department The department
     * @param string $phone The phone
     * @param string $moodlephone The moodlephone
     * @param string $adress The adress
     * @param string $firstaccess The firstaccess
     * @param string $lastaccess The lastaccess
     * @param string $lastlogin The lastlogin
     * @param string $lastip The lastip
     * @param string $description The description
     * @param string $descriptionofpic The description for pic
     * @param string $alternatename The alternatename
     * @param string $webpage The webpage
     * @param string $imgurl The imgurl
     * @param string $userid The userid
     * @return string Sync Status from leeloo.
     */
    public function syncuser(
        $username,
        $email,
        $leeloolxpurl,
        $fullname,
        $city,
        $country,
        $timezone,
        $skype,
        $idnumber,
        $institution,
        $department,
        $phone,
        $moodlephone,
        $adress,
        $firstaccess,
        $lastaccess,
        $lastlogin,
        $lastip,
        $description,
        $descriptionofpic,
        $alternatename,
        $webpage,
        $imgurl,
        $userid
    ) {

        if (!isset($this->config->required_aproval_student)) {
            $userapproval = 0;
        } else {
            $userapproval = $this->config->required_aproval_student;
        }

        $userdesignation = @$this->config->default_student_position;

        $logintoken = $this->generate_string(20);

        $data = array(
            'username' => base64_encode($username),
            'email' => base64_encode($email),
            'user_fullname' => $fullname,
            'user_approval' => $userapproval,
            'lastlogin' => $lastlogin,
            'city' => $city,
            'country' => $country,
            'timezone' => $timezone,
            'skype' => $skype,
            'idnumber' => $idnumber,
            'institution' => $institution,
            'department' => $department,
            'phone' => $phone,
            'moodle_phone' => $moodlephone,
            'adress' => $adress,
            'firstaccess' => $firstaccess,
            'lastaccess' => $lastaccess,
            'lastlogin' => $lastlogin,
            'lastip' => $lastip,
            'designation_id' => $userdesignation,
            'user_description' => $description,
            'picture_description' => $descriptionofpic,
            'alternate_name' => $alternatename,
            'web_page' => $webpage,
            'logintoken' => $logintoken,

        );

        $payload = json_encode($data);

        $postdata = array();
        $postdata['data'] = $payload;
        $postdata['img_url'] = $imgurl;

        $url = $leeloolxpurl . '/admin/sync_moodle_course/sync_user_password_moodle';

        global $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
            'CURLOPT_HTTPHEADER' => array(
                'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
            )
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return $leeloolxpurl . '/login/';
        }

        $urltogo = $leeloolxpurl . '/login/?token=' . $logintoken;
        setcookie('leeloolxpssourl', $urltogo, time() + (86400), "/");
        setcookie('installlogintoken', $logintoken, time() + (86400), "/");

        global $DB;
        $checksql = "SELECT * FROM {auth_leeloolxp_tracking_sso} WHERE userid = ?";
        $ssourls = $DB->get_record_sql($checksql, [$userid]);

        if ($ssourls) {
            $sql = 'UPDATE {auth_leeloolxp_tracking_sso} SET leeloourl = ? WHERE userid = ?';
            $DB->execute($sql, [$urltogo, $userid]);
        } else {
            $sql = 'INSERT INTO {auth_leeloolxp_tracking_sso} (userid, jurl, leeloourl) VALUES (?, ?, ?)';
            $DB->execute($sql, [$userid, '', $urltogo]);
        }

        return $urltogo;
    }

    /**
     * Add cookie on login page for redirect..
     */
    public function loginpage_hook() {

        $isleeloolxp = optional_param('leeloolxp', '0', PARAM_TEXT);

        if ($isleeloolxp != '0') {
            setcookie('leeloolxp', 1, time() + (86400 * 30), '/');
        } else {
            setcookie('leeloolxp', null, -1, '/');
        }
    }

    /**
     * Clear cookie on logout
     *
     * @param string $user The user data
     */
    public function postlogout_hook($user) {
        setcookie('leeloolxpssourl', '', time() + (86400), "/");
        setcookie('installlogintoken', '', time() + (86400), "/");

        $leeloolxplicense = $this->config->leeloolxp_license;

        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = array('license_key' => $leeloolxplicense);

        global $CFG;
        require_once($CFG->dirroot . '/lib/filelib.php');

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return true;
        }

        $infoleeloolxp = json_decode($output);

        if ($infoleeloolxp->status != 'false') {

            $uagent = $_SERVER['HTTP_USER_AGENT'];
            $bname = 'Unknown';
            $platform = 'Unknown';

            // First get the platform.
            if (preg_match('/linux/i', $uagent)) {
                $platform = 'linux';
            } else if (preg_match('/macintosh|mac os x/i', $uagent)) {
                $platform = 'mac';
            } else if (preg_match('/windows|win32/i', $uagent)) {
                $platform = 'windows';
            }

            // Next get the name of the useragent yes seperately and for good reason.
            if (preg_match('/MSIE/i', $uagent) && !preg_match('/Opera/i', $uagent)) {
                $bname = 'Internet Explorer';
                $ub = "MSIE";
            } else if (preg_match('/Firefox/i', $uagent)) {
                $bname = 'Mozilla Firefox';
                $ub = "Firefox";
            } else if (preg_match('/Chrome/i', $uagent)) {
                $bname = 'Google Chrome';
                $ub = "Chrome";
            } else if (preg_match('/Safari/i', $uagent)) {
                $bname = 'Apple Safari';
                $ub = "Safari";
            } else if (preg_match('/Opera/i', $uagent)) {
                $bname = 'Opera';
                $ub = "Opera";
            } else if (preg_match('/Netscape/i', $uagent)) {
                $bname = 'Netscape';
                $ub = "Netscape";
            }
            $user->browser = $bname;
            $user->os = $platform;

            $leeloolxpurl = $infoleeloolxp->data->install_url;

            $url = $leeloolxpurl . '/admin/sync_moodle_course/update_user_data_at_logout';

            $curl = new curl;

            $options = array(
                'CURLOPT_RETURNTRANSFER' => 1,
                'CURLOPT_HEADER' => false,
                'CURLOPT_POST' => count($user),
                'CURLOPT_HTTPHEADER' => array(
                    'Leeloolxptoken: ' . get_config('local_leeloolxpapi')->leelooapitoken . ''
                )
            );
            $output = $curl->post($url, $user, $options);
            return true;
        } else {
            return true;
        }
    }
}
