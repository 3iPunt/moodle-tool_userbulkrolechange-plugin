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

namespace tool_userbulkrolechange;

\defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/webservice/externallib.php');

use coding_exception;
use context_system;
use dml_exception;
use external_api as core_external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use moodle_exception;
use require_login_exception;
use required_capability_exception;
use restricted_context_exception;

class external extends core_external_api {
    /*
     * Get roles by context level
     */

    public static function get_context_level_roles_parameters() {
        return new external_function_parameters([
            'contextlevel' => new external_value(PARAM_INT, 'Context level'),
        ]);
    }

    public static function get_context_level_roles($contextlevel): array {
        $params = self::validate_parameters(self::get_context_level_roles_parameters(), ['contextlevel' => $contextlevel]);
        $contextlevel = $params['contextlevel'];

        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        require_login();
        require_capability('moodle/role:manage', $systemcontext);

        $roles = api::get_context_level_roles($contextlevel);

        $res = array();
        foreach ($roles as $rkey => $rval) {
            $res['roles'][] = [
                'value' => $rkey,
                'text' => $rval
            ];
        }

        return $res;
    }

    public static function get_context_level_roles_returns() {
        return new external_single_structure(array(
            'roles' => new external_multiple_structure(new external_single_structure([
                'value' => new external_value(PARAM_INT, 'The role id'),
                'text' => new external_value(PARAM_TEXT, 'The role name'),
            ])),
        ));
    }

    /*
     * Get course categories
     */

    public static function get_all_course_categories_parameters() {
        return new external_function_parameters(array());
    }

    public static function get_all_course_categories(): array {

        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        require_login();
        require_capability('moodle/role:manage', $systemcontext);

        $coursecategories = api::get_all_course_categories();

        $res = array();
        foreach ($coursecategories as $cckey => $ccval) {
            $res['course_categories'][] = [
                'value' => $cckey,
                'text' => $ccval
            ];
        }

        return $res;
    }

    public static function get_all_course_categories_returns() {
        return new external_single_structure(array(
            'course_categories' => new external_multiple_structure(new external_single_structure(array(
                'value' => new external_value(PARAM_INT, 'The course category id'),
                'text' => new external_value(PARAM_TEXT, 'The course category name')
            ))),
        ));
    }

    /*
     * Get all courses
     */

    public static function get_all_courses_parameters() {
        return new external_function_parameters([]);
    }

    public static function get_all_courses(): array {

        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        require_login();
        require_capability('moodle/role:manage', $systemcontext);

        $courses = api::get_all_courses();

        $res = array();
        foreach ($courses as $ckey => $cval) {
            $res['courses'][] = [
                'value' => $ckey,
                'text' => $cval
            ];
        }

        return $res;
    }

    public static function get_all_courses_returns() {
        return new external_single_structure([
            'courses' => new external_multiple_structure(new external_single_structure([
                'value' => new external_value(PARAM_INT, 'The course id'),
                'text' => new external_value(PARAM_TEXT, 'The course fullname'),
            ])),
        ]);
    }

    /*
     * Bulk user unassign role
     */

    public static function bulk_unassign_role_parameters() {
        return new external_function_parameters([
            'contextlevel' => new external_value(PARAM_INT, 'Context level'),
            'role' => new external_value(PARAM_INT, 'Role id'),
            'instance' => new external_value(PARAM_INT, 'Instance id'),
        ]);
    }

    /**
     * @param $contextlevel
     * @param $role
     * @param $instance
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws require_login_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function bulk_unassign_role($contextlevel, $role, $instance): array {
        global $SESSION;

        $params = self::validate_parameters(self::bulk_unassign_role_parameters(), [
            'contextlevel' => $contextlevel,
            'role' => $role,
            'instance' => $instance
        ]);
        $targetcontextlevel = $params['contextlevel'];
        $targetinstanceid = $params['instance'];
        $targetroleid = $params['role'];

        if (empty($SESSION->bulk_users)) {
            redirect($return);
        }

        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        require_login();
        require_capability('moodle/role:manage', $systemcontext);

        $success = api::bulk_unassign_role($SESSION->bulk_users, $targetcontextlevel, $targetinstanceid, $targetroleid);

        $res = array();
        $res['success'] = $success;

        return $res;
    }

    public static function bulk_unassign_role_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Bulk role unassignment succeeded'),
        ]);
    }

    /*
     * Bulk user unassign role
     */

    public static function bulk_assign_role_parameters() {
        return new external_function_parameters([
            'contextlevel' => new external_value(PARAM_INT, 'Context level'),
            'role' => new external_value(PARAM_INT, 'Role id'),
            'instance' => new external_value(PARAM_INT, 'Instance id'),
        ]);
    }

    /**
     * @param $contextlevel
     * @param $role
     * @param $instance
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws require_login_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function bulk_assign_role($contextlevel, $role, $instance): array {
        global $SESSION;

        $params = self::validate_parameters(self::bulk_assign_role_parameters(), [
            'contextlevel' => $contextlevel,
            'role' => $role,
            'instance' => $instance
        ]);
        $targetcontextlevel = $params['contextlevel'];
        $targetinstanceid = $params['instance'];
        $targetroleid = $params['role'];

        if (empty($SESSION->bulk_users)) {
            redirect($return);
        }

        $systemcontext = context_system::instance();
        self::validate_context($systemcontext);
        require_login();
        require_capability('moodle/role:manage', $systemcontext);

        $success = api::bulk_assign_role($SESSION->bulk_users, $targetcontextlevel, $targetinstanceid, $targetroleid);

        $res = array();
        $res['success'] = $success;

        return $res;
    }

    public static function bulk_assign_role_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Bulk role assignment succeeded'),
        ]);
    }
}
