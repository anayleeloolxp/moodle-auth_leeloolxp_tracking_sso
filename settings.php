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
 * Admin settings and defaults
 *
 * @package auth_leeloolxp_tracking_sso
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$noapi = 0;

global $CFG;
require_once($CFG->dirroot . '/lib/filelib.php');

if ($ADMIN->fulltree) {
    $roles = $DB->get_records_sql('SELECT shortname as Role, id, Description FROM {role} order by name');

    $institutions = $DB->get_records_sql("SELECT DISTINCT institution  FROM {user} where institution !=''");

    $department = $DB->get_records_sql("SELECT DISTINCT department  FROM {user} where department !=''");

    $degrees = $DB->get_records_sql(
        "SELECT DISTINCT data
            FROM {user_info_data}
            left join {user_info_field}
            on {user_info_data}.fieldid = {user_info_field}.id
            where {user_info_field}.shortname = 'degree' and data !=''"
    );

    $institutionarr[] = get_string('student_institution_lable', 'auth_leeloolxp_tracking_sso');

    foreach ($institutions as $key => $value) {
        $institutionarr[$value->institution] = $value->institution;
    }

    $departmentarr[] = get_string('student_department_lable', 'auth_leeloolxp_tracking_sso');

    foreach ($department as $key => $value) {
        $departmentarr[$value->department] = $value->department;
    }

    $degreearr[] = get_string('student_degree_lable', 'auth_leeloolxp_tracking_sso');

    foreach ($degrees as $key => $value) {
        $degreearr[$value->data] = $value->data;
    }

    $pluginconfig = get_config('auth_leeloolxp_tracking_sso');

    if (isset($pluginconfig->student_num_combination) && isset($pluginconfig->student_num_combination) != '') {
        $studentnumcombinationsval = $pluginconfig->student_num_combination;
    } else {
        $studentnumcombinationsval = 0;
    }

    if ($studentnumcombinationsval <= 0) {
        $studentnumcombinationsval = 1; // set default 1
    }

    if (isset($pluginconfig->teacher_num_combination) && isset($pluginconfig->teacher_num_combination) != '') {
        $teachernumcombinationsval = $pluginconfig->teacher_num_combination;
    } else {
        $teachernumcombinationsval = 0;
    }

    if ($teachernumcombinationsval <= 0) {
        $teachernumcombinationsval = 1; // set default 1
    }

    if (isset($pluginconfig->leeloolxp_license) && isset($pluginconfig->leeloolxp_license) != '') {
        $licensekey = $pluginconfig->leeloolxp_license;
    } else {
        $licensekey = 0;
    }

    $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';

    $postdata = array('license_key' => $licensekey);

    $curl = new curl;

    $options = array(
        'CURLOPT_RETURNTRANSFER' => true,
        'CURLOPT_HEADER' => false,
        'CURLOPT_POST' => count($postdata),
    );

    if (!$output = $curl->post($url, $postdata, $options)) {
        $noapi = 1;
    }

    $infoteamnio = json_decode($output);

    if ($infoteamnio->status != 'false') {
        $teamniourl = $infoteamnio->data->install_url;

        $url = $teamniourl . '/admin/sync_moodle_course/get_all_designation_array';

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => count($postdata),
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $noapi = 1;
        }

        $designations = json_decode($output, true);
    } else {
        $noapi = 1;
    }

    $rolesarr = array();

    $rolesarr[] = get_string('role', 'auth_leeloolxp_tracking_sso');

    if (!empty($roles)) {
        foreach ($roles as $key => $value) {
            $rolesarr[$value->id] = $value->role;
        }
    }

    $settings->add(
        new admin_setting_configtext(
            'auth_leeloolxp_tracking_sso/leeloolxp_license',
            get_string('leeloolxp_license', 'auth_leeloolxp_tracking_sso'),
            get_string('leeloolxp_license_desc', 'auth_leeloolxp_tracking_sso'),
            0
        )
    );

    $choicesred = array(
        $CFG->wwwroot.'/leeloolxp-smart-dashboard.html' => 'Leeloo LXP Dashboard',
        $CFG->wwwroot.'/leeloolxp-social-network.html' => 'Leeloo LXP Social Network',
        $CFG->wwwroot.'/local/staticpage/view.php?page=leeloolxp-smart-dashboard' => 'Leeloo LXP Dashboard (Ugly URL)',
        $CFG->wwwroot.'/local/staticpage/view.php?page=leeloolxp-social-network' => 'Leeloo LXP Social Network (Ugly URL)',
        $CFG->wwwroot.'/my' => 'Moodle Dashboard',
        $CFG->wwwroot => 'Moodle Home',
    );
    $nameloginred = 'auth_leeloolxp_tracking_sso/login_redirectpage';
    $titleloginred = get_string('login_redirectpage', 'auth_leeloolxp_tracking_sso');
    $descriptionloginred = get_string('login_redirectpage_description', 'auth_leeloolxp_tracking_sso');
    $settings->add(new admin_setting_configselect($nameloginred, $titleloginred, $descriptionloginred, 0, $choicesred));

    if ($noapi == 0) {
        $settings->add(
            new admin_setting_configcheckbox(
                'auth_leeloolxp_tracking_sso/web_new_user_student',
                get_string('create_new_user_student_lable', 'auth_leeloolxp_tracking_sso'),
                get_string('help_txt_new_student', 'auth_leeloolxp_tracking_sso'),
                1
            )
        );

        $settings->add(
            new admin_setting_configcheckbox(
                'auth_leeloolxp_tracking_sso/required_aproval_student',
                get_string('required_aproval_student_lable', 'auth_leeloolxp_tracking_sso'),
                get_string('help_txt_required_aproval_student', 'auth_leeloolxp_tracking_sso'),
                1
            )
        );

        // 'Student Defaut Position in Leeloo'.

        $title = get_string('default_position_leelo', 'auth_leeloolxp_tracking_sso');

        $description = get_string('default_position_leelo_description', 'auth_leeloolxp_tracking_sso');

        $settings->add(new admin_setting_configselect('auth_leeloolxp_tracking_sso/default_student_position', $title, $description, 2, $designations));

        $arrstudentcom = array();

        for ($sx = 1; $sx <= 100; $sx++) {
            $arrstudentcom[$sx] = $sx;
        }

        $name = 'auth_leeloolxp_tracking_sso/student_num_combination';

        $description = get_string('student_num_of_comb', 'auth_leeloolxp_tracking_sso');

        $title = get_string('student_num_of_role', 'auth_leeloolxp_tracking_sso');

        $settings->add(new admin_setting_configselect($name, $title, $description, '', $arrstudentcom));

        if ($studentnumcombinationsval > 0) {
            for ($key = 1; $key <= $studentnumcombinationsval; $key++) {
                $name = 'auth_leeloolxp_tracking_sso/student_position_moodle_' . $key;
                $title = get_string('student_position_lable_moodle', 'auth_leeloolxp_tracking_sso');
                $description = get_string('studdent_position_help_txt', 'auth_leeloolxp_tracking_sso');
                $settings->add(new admin_setting_configselect($name, $title, $description, '0', $rolesarr));

                $name = 'auth_leeloolxp_tracking_sso/student_position_t_' . $key;

                $title = get_string('student_position_lable_t', 'auth_leeloolxp_tracking_sso');

                $description = get_string('studdent_position_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($name, $title, $description, '0', $designations));

                // Institutions.

                $instituname = 'auth_leeloolxp_tracking_sso/student_institution_' . $key;

                $institutitle = get_string('student_institution_lable', 'auth_leeloolxp_tracking_sso');

                $institudescription = get_string('student_institution_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($instituname, $institutitle, '', '', $institutionarr));

                // Department.

                $departmentname = 'auth_leeloolxp_tracking_sso/student_department_' . $key;

                $departmenttitle = get_string('student_department_lable', 'auth_leeloolxp_tracking_sso');

                $departmentdescription = get_string('student_department_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($departmentname, $departmenttitle, '', '', $departmentarr));

                // Degree.

                $degreename = 'auth_leeloolxp_tracking_sso/student_degree_' . $key;

                $degreetitle = get_string('student_degree_lable', 'auth_leeloolxp_tracking_sso');

                $degreedescription = get_string('student_degree_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($degreename, $degreetitle, '', '', $degreearr));
            }
        }

        $settings->add(
            new admin_setting_configcheckbox(
                'auth_leeloolxp_tracking_sso/web_new_user_teacher',
                get_string('create_new_user_teacher_lable', 'auth_leeloolxp_tracking_sso'),
                get_string('help_txt_new_teacher', 'auth_leeloolxp_tracking_sso'),
                1
            )
        );

        $settings->add(
            new admin_setting_configcheckbox(
                'auth_leeloolxp_tracking_sso/required_aproval_teacher',
                get_string('required_aproval_teacher_lable', 'auth_leeloolxp_tracking_sso'),
                get_string('help_txt_required_aproval_teacher', 'auth_leeloolxp_tracking_sso'),
                1
            )
        );

        $arrteachercom = array();

        for ($sx = 1; $sx <= 100; $sx++) {
            $arrteachercom[$sx] = $sx;
        }

        $name = 'auth_leeloolxp_tracking_sso/teacher_num_combination';

        $title = get_string('teacher_num_of_role', 'auth_leeloolxp_tracking_sso');

        $description = get_string('teacher_num_of_comb', 'auth_leeloolxp_tracking_sso');

        $settings->add(new admin_setting_configselect($name, $title, $description, '', $arrteachercom));

        if ($teachernumcombinationsval > 0) {
            for ($key = 1; $key <= $teachernumcombinationsval; $key++) {
                $name = 'auth_leeloolxp_tracking_sso/teacher_position_moodle_' . $key;

                $title = get_string('teacher_position_lable_moodle', 'auth_leeloolxp_tracking_sso');

                $description = get_string('teacher_position_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($name, $title, $description, 0, $rolesarr));

                $name = 'auth_leeloolxp_tracking_sso/teacher_position_t_' . $key;

                $title = get_string('teacher_position_lable_t', 'auth_leeloolxp_tracking_sso');

                $description = get_string('teacher_position_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($name, $title, $description, 0, $designations));

                // Institutions.

                $institunameteacher = 'auth_leeloolxp_tracking_sso/teacher_institution_' . $key;

                $institutitleteacher = get_string('teacher_institution_lable', 'auth_leeloolxp_tracking_sso');

                $institudescriptionteacher = get_string('teacher_institution_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($institunameteacher, $institutitleteacher, '', '', $institutionarr));

                // Department.

                $departmentnameteacher = 'auth_leeloolxp_tracking_sso/teacher_department_' . $key;

                $departmenttitleteacher = get_string('teacher_department_lable', 'auth_leeloolxp_tracking_sso');

                $departmentdescriptionteacher = get_string('teacher_department_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($departmentnameteacher, $departmenttitleteacher, '', '', $departmentarr));

                // Degree.

                $degreenameteacher = 'auth_leeloolxp_tracking_sso/teacher_degree_' . $key;

                $degreetitleteacher = get_string('teacher_degree_lable', 'auth_leeloolxp_tracking_sso');

                $degreedescriptionteacher = get_string('teacher_degree_help_txt', 'auth_leeloolxp_tracking_sso');

                $settings->add(new admin_setting_configselect($degreenameteacher, $degreetitleteacher, '', '', $degreearr));
            }
        }

        $PAGE->requires->js_init_code("window.onload = function() {

            var s_role_1_heading = '" . get_string('student_role_heading_1', 'auth_leeloolxp_tracking_sso') . "';

            var s_role_2_heading = '';

            var student_position_leelo_text = '" . get_string('student_position_leelo_text', 'auth_leeloolxp_tracking_sso') . "';

            var t_role_1_heading = '" . get_string('teacher_role_heading_1', 'auth_leeloolxp_tracking_sso') . "';

            var t_role_2_heading = '';

            var count = '" . $studentnumcombinationsval . "';

            for(var i = 1; i<=count; i++) {

                var top_text_student = '<div class=\"top_text_student\"> <b>'+s_role_1_heading+'</b><p> '+s_role_2_heading+' <p></div>';

                if (
                    typeof(document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_position_moodle_'+i)) !== 'undefined'
                    &&
                    document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_position_moodle_'+i) !== null
                ) {

                    var student_first_element = document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_position_moodle_'+i).parentElement.parentElement;
                }

                if (typeof(student_first_element) != 'undefined' && student_first_element != null) {

                    student_first_element.innerHTML = top_text_student+student_first_element.innerHTML;

                }

                if (
                    typeof(document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_position_t_'+i)) !== 'undefined'
                    &&
                    document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_position_t_'+i) !== null
                ) {
                    var student_t_position = document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_position_t_'+i).parentElement.parentElement;
                }
                var student_t_position_text = '<div class=\"student_t_position_text\"><b>'+student_position_leelo_text+'</b></div>';

                if (typeof(student_t_position) != 'undefined' && student_t_position != null) {

                    student_t_position.innerHTML = student_t_position_text+student_t_position.innerHTML;

                }

            }

            var count = '" . $teachernumcombinationsval . "';

            for(var i = 1; i<=count; i++) {

                if (
                    typeof(document.getElementById('id_s_auth_leeloolxp_tracking_sso_teacher_position_t_'+i)) !== 'undefined'
                    &&
                    document.getElementById('id_s_auth_leeloolxp_tracking_sso_teacher_position_t_'+i) !== null
                ) {

                    var teacher_t_position = document.getElementById('id_s_auth_leeloolxp_tracking_sso_teacher_position_t_'+i).parentElement.parentElement;
                }

                var teacher_t_position_text = '<div class=\"teacher_t_position_text\"><b>'+student_position_leelo_text+'</b></div>';

                if (typeof(teacher_t_position) != 'undefined' && teacher_t_position != null) {

                    teacher_t_position.innerHTML = teacher_t_position_text+teacher_t_position.innerHTML;

                }

                var top_text_teacher = '<div class=\"top_text_student\"> <b>'+t_role_1_heading+'</b><p>'+t_role_2_heading+'<p></div>';

                if (
                    typeof(document.getElementById('id_s_auth_leeloolxp_tracking_sso_teacher_position_moodle_'+i)) != 'undefined'
                    &&
                    document.getElementById('id_s_auth_leeloolxp_tracking_sso_teacher_position_moodle_'+i) != null
                ) {
                    var teacher_first_element = document.getElementById('id_s_auth_leeloolxp_tracking_sso_teacher_position_moodle_'+i).parentElement.parentElement;
                }

                if (typeof(teacher_first_element) != 'undefined' && teacher_first_element != null) {

                    teacher_first_element.innerHTML = top_text_teacher+teacher_first_element.innerHTML;

                }

            }


            var count = '" . $studentnumcombinationsval . "';

            for(var i = 1; i<=count; i++) {

                var some_variable_new = document.getElementById('admin-student_position_t_'+i);

                if (typeof(some_variable_new) != 'undefined' && some_variable_new != null) {

                    // Institution append.

                    var p_div_student_institution =  document.getElementById('admin-student_institution_'+i);

                    var child_institution_div_student = p_div_student_institution.getElementsByClassName('form-setting')[0].innerHTML;

                    document.getElementById('admin-student_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_institution_div_student;

                    document.getElementById('admin-student_institution_'+i).getElementsByClassName('form-setting')[0].remove();

                    // Department append.

                    var p_div_student_department =  document.getElementById('admin-student_department_'+i);

                    var child_department_div_student = p_div_student_department.getElementsByClassName('form-setting')[0].innerHTML;
                    document.getElementById('admin-student_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_department_div_student;

                    document.getElementById('admin-student_department_'+i).getElementsByClassName('form-setting')[0].remove();
                    // Degree append.

                    var p_div_student_degree =  document.getElementById('admin-student_degree_'+i);

                    var child_degree_div_student = p_div_student_degree.getElementsByClassName('form-setting')[0].innerHTML;

                    document.getElementById('admin-student_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_degree_div_student;

                    document.getElementById('admin-student_degree_'+i).getElementsByClassName('form-setting')[0].remove();

                    // Position append.

                    var p_div_student =  document.getElementById('admin-student_position_t_'+i);

                    var child_div_student = p_div_student.getElementsByClassName('form-setting')[0].innerHTML;

                    document.getElementById('admin-student_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_div_student;

                    document.getElementById('admin-student_position_t_'+i).getElementsByClassName('form-setting')[0].remove();

                    // Position append  claose.

                }

            }

            var count = '" . $teachernumcombinationsval . "';

            for(var i = 1; i<=count; i++) {

                    // teacher section append.

                    // Institution append.

                    var some_variable_new = document.getElementById('admin-teacher_position_t_'+i);
                    if (typeof(some_variable_new) != 'undefined' && some_variable_new != null) {

                        var p_div_teacher_institution =  document.getElementById('admin-teacher_institution_'+i);

                        var child_institution_div_teacher = p_div_teacher_institution.getElementsByClassName('form-setting')[0].innerHTML;

                        document.getElementById('admin-teacher_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_institution_div_teacher;

                        document.getElementById('admin-teacher_institution_'+i).getElementsByClassName('form-setting')[0].remove();
                        // Department append.

                        var p_div_teacher_department =  document.getElementById('admin-teacher_department_'+i);

                        var child_department_div_teacher = p_div_teacher_department.getElementsByClassName('form-setting')[0].innerHTML;

                        document.getElementById('admin-teacher_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_department_div_teacher;

                        document.getElementById('admin-teacher_department_'+i).getElementsByClassName('form-setting')[0].remove();
                        // Degree append.

                        var p_div_teacher_degree =  document.getElementById('admin-teacher_degree_'+i);

                        var child_degree_div_teacher = p_div_teacher_degree.getElementsByClassName('form-setting')[0].innerHTML;

                        document.getElementById('admin-teacher_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_degree_div_teacher;

                        document.getElementById('admin-teacher_degree_'+i).getElementsByClassName('form-setting')[0].remove();

                        var p_div_teacher =  document.getElementById('admin-teacher_position_t_'+i);

                        var child_div_teacher = p_div_teacher.getElementsByClassName('form-setting')[0].innerHTML;

                        document.getElementById('admin-teacher_position_moodle_'+i).getElementsByClassName('form-setting')[0].innerHTML += child_div_teacher;

                        document.getElementById('admin-teacher_position_t_'+i).getElementsByClassName('form-setting')[0].remove();

                    }

            }

            if (
                typeof(document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_num_combination')) != 'undefined'
                &&
                document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_num_combination') != null
            ) {
                document.getElementById('id_s_auth_leeloolxp_tracking_sso_student_num_combination').addEventListener('change', function() {

                    document.getElementById('adminsettings').submit();

                });

                document.getElementById('id_s_auth_leeloolxp_tracking_sso_teacher_num_combination').addEventListener('change', function() {

                    document.getElementById('adminsettings').submit();

                });

            }

        };");
    }
}
