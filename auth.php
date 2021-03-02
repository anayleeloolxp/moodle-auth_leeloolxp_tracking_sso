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
     * Encrypt Strings
     *
     * @param string $texttoencrypt The text to Encrypt
     * @return bool Return Encrypted String
     */
    public function encrption_data($texttoencrypt) {

        $encryptionmethod = "AES-256-CBC";
        $secrethash = "25c6c7ff35b9979b151f2136cd13b0ff";
        return @openssl_encrypt($texttoencrypt, $encryptionmethod, $secrethash);
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

        $username = $username;
        $useremail = $user->email;

        $leeloolxplicense = $this->config->leeloolxp_license;

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
            $imgurl
        );

        if (isset($_COOKIE['leeloolxp']) && isset($_COOKIE['leeloolxp']) != '') {
            echo '<script>window.location.replace("' . $redirecturl . '");</script>';
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
        $imgurl
    ) {

        $userapproval = $this->config->required_aproval_student;
        $userdesignation = $this->config->default_student_position;

        $logintoken = $this->generate_string(20);

        $data = array(
            'username' => $username,
            'email' => $email,
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

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            return $leeloolxpurl . '/login/';
        }

        $urltogo = $leeloolxpurl . '/login/?token=' . $logintoken;
        setcookie('leeloolxpssourl', $urltogo, time() + (86400), "/");

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
    }
}
