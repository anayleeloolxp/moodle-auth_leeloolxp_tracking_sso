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
 * Privacy Subsystem implementation for auth_leeloolxp_tracking_sso.
 *
 * @package    auth_leeloolxp_tracking_sso
 * @author Leeloo LXP <info@leeloolxp.com>
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_leeloolxp_tracking_sso\privacy;

use core_privacy\local\metadata\collection;

defined('MOODLE_INTERNAL') || die();

/**
 * Provider implementation for auth_leeloolxp_tracking_sso.
 *
 */
class provider implements
\core_privacy\local\metadata\provider,
\core_privacy\local\request\data_provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        // Data collected by the client.

        $externalfields = [
            'id' => 'privacy:metadata:id',
            'username' => 'privacy:metadata:username',
            'email' => 'privacy:metadata:email',
            'fullname' => 'privacy:metadata:fullname',
            'city' => 'privacy:metadata:city',
            'country' => 'privacy:metadata:country',
            'timezone' => 'privacy:metadata:timezone',
            'skype' => 'privacy:metadata:skype',
            'idnumber' => 'privacy:metadata:idnumber',
            'institution' => 'privacy:metadata:institution',
            'department' => 'privacy:metadata:department',
            'phone1' => 'privacy:metadata:phone1',
            'phone2' => 'privacy:metadata:phone2',
            'address' => 'privacy:metadata:address',
            'firstaccess' => 'privacy:metadata:firstaccess',
            'lastaccess' => 'privacy:metadata:lastaccess',
            'lastlogin' => 'privacy:metadata:lastlogin',
            'lastip' => 'privacy:metadata:lastip',
            'description' => 'privacy:metadata:description',
            'imagealt' => 'privacy:metadata:imagealt',
            'alternatename' => 'privacy:metadata:alternatename',
            'url' => 'privacy:metadata:url',

        ];

        $collection->add_external_location_link('leeloolxp_tracking_sso_client', $externalfields, 'privacy:metadata:leeloolxp_tracking_sso');

        return $collection;
    }
}
