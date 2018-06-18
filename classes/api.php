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
 * @package     tool_userbulkrolechange
 * @copyright   2018 3iPunt Mitxel Moriana <mitxel@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_userbulkrolechange;

use coding_exception;
use context;
use context_helper;
use coursecat;
use dml_exception;
use stdClass;

\defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/accesslib.php');

/**
 * Class api
 * @package tool_userbulkrolechange
 */
class api {
    /**
     * @return array
     * @throws dml_exception
     */
    public static function get_context_levels_that_have_associated_roles() {
        global $DB;

        $sql = 'SELECT DISTINCT rcl.contextlevel
                  FROM {role_context_levels} rcl
            INNER JOIN {role} r ON rcl.roleid = r.id ';

        $clrecords = $DB->get_records_sql($sql);
        return array_map(function ($clrecord) {
            return context_helper::get_level_name($clrecord->contextlevel);
        }, $clrecords);
    }

    /**
     * @param int $contextlevel
     * @return array
     * @throws dml_exception
     */
    public static function get_context_level_roles($contextlevel) {
        global $DB;

        $sql = 'SELECT rcl.roleid, rcl.contextlevel, r.name, r.shortname
                  FROM {role_context_levels} rcl
            INNER JOIN {role} r ON rcl.roleid = r.id
                 WHERE rcl.contextlevel = :contextlevel ';
        $roles = $DB->get_records_sql($sql, ['contextlevel' => $contextlevel]);
        $roles = array_map(function ($role) {
            return role_get_name($role, null, ROLENAME_BOTH);
        }, $roles);

        return $roles;
    }

    /**
     * @return coursecat[]
     */
    public static function get_all_course_categories() {
        return array_map(function ($coursecategory) {
            return $coursecategory->name;
        }, coursecat::get_all(['returnhidden' => true]));
    }

    /**
     * @return array
     */
    public static function get_all_courses() {
        return array_map(function ($course) {
            return $course->fullname;
        }, get_courses());
    }

    /**
     * @param array $users
     * @param int $contextlevel
     * @param int $instanceid
     * @param int $roleid
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function bulk_unassign_role($users, $contextlevel, $instanceid, $roleid) {
        $contexts = self::get_contexts_by_level_and_instanceid($contextlevel, $instanceid);
        foreach ($users as $userid) {
            foreach ($contexts as $contextid => $context) {
                role_unassign($roleid, $userid, $contextid);
            }
        }

        return true;
    }

    /**
     * @param array $users
     * @param int $contextlevel
     * @param int $instanceid
     * @param int $roleid
     * @return bool
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function bulk_assign_role($users, $contextlevel, $instanceid, $roleid) {
        $contexts = self::get_contexts_by_level_and_instanceid($contextlevel, $instanceid);
        foreach ($users as $userid) {
            foreach ($contexts as $contextid => $context) {
                self::role_assign($roleid, $userid, $contextid);
            }
        }

        return true;
    }

    /**
     * @param $contextlevel
     * @param $instanceid
     * @return mixed
     * @throws dml_exception
     */
    private static function get_contexts_by_level_and_instanceid($contextlevel, $instanceid) {
        global $DB;

        if ($instanceid === null || $instanceid === -1) {
            $contexts = $DB->get_records('context', ['contextlevel' => $contextlevel]);
        } else {
            $contexts = $DB->get_records('context', ['contextlevel' => $contextlevel, 'instanceid' => $instanceid]);
        }

        return $contexts;
    }

    /**
     * This function makes a role-assignment (a role for a user in a particular context)
     *
     * @param int $roleid the role of the id
     * @param int $userid userid
     * @param int|context $contextid id of the context
     * @param string $component example 'enrol_ldap', defaults to '' which means manual assignment,
     * @param int $itemid id of enrolment/auth plugin
     * @param int|string $timemodified defaults to current time
     * @return int new/existing id of the assignment
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function role_assign($roleid, $userid, $contextid, $component = '', $itemid = 0, $timemodified = '') {
        global $USER, $DB;

        // first of all detect if somebody is using old style parameters
        if ($contextid === 0 or is_numeric($component)) {
            throw new coding_exception('Invalid call to role_assign(), code needs to be updated to use new order of parameters');
        }

        // now validate all parameters
        if (empty($roleid)) {
            throw new coding_exception('Invalid call to role_assign(), roleid can not be empty');
        }

        if (empty($userid)) {
            throw new coding_exception('Invalid call to role_assign(), userid can not be empty');
        }

        if ($itemid) {
            if (strpos($component, '_') === false) {
                throw new coding_exception('Invalid call to role_assign(), component must start with plugin type such as"enrol_" when itemid specified', 'component:' . $component);
            }
        } else {
            $itemid = 0;
            if ($component !== '' && strpos($component, '_') === false) {
                throw new coding_exception('Invalid call to role_assign(), invalid component string', 'component:' . $component);
            }
        }

        if (!$DB->record_exists('user', array('id' => $userid, 'deleted' => 0))) {
            throw new coding_exception('User ID does not exist or is deleted!', 'userid:' . $userid);
        }

        if ($contextid instanceof context) {
            $context = $contextid;
        } else {
            $context = context::instance_by_id($contextid, MUST_EXIST);
        }

        if (!$timemodified) {
            $timemodified = time();
        }

        // Check for existing entry
        $ras = $DB->get_records('role_assignments', array('roleid' => $roleid, 'contextid' => $context->id, 'userid' => $userid, 'component' => $component, 'itemid' => $itemid), 'id');

        if ($ras) {
            // role already assigned - this should not happen
            if (\count($ras) > 1) {
                // very weird - remove all duplicates!
                $ra = array_shift($ras);
                foreach ($ras as $r) {
                    $DB->delete_records('role_assignments', array('id' => $r->id));
                }
            } else {
                $ra = reset($ras);
            }

            // actually there is no need to update, reset anything or trigger any event, so just return
            return $ra->id;
        }

        // Create a new entry
        $ra = new stdClass();
        $ra->roleid = $roleid;
        $ra->contextid = $context->id;
        $ra->userid = $userid;
        $ra->component = $component;
        $ra->itemid = $itemid;
        $ra->timemodified = $timemodified;
        $ra->modifierid = empty($USER->id) ? 0 : $USER->id;
        $ra->sortorder = 0;

        $ra->id = $DB->insert_record('role_assignments', $ra);

        // mark context as dirty - again expensive, but needed
        $context->mark_dirty();

        if (!empty($USER->id) && $USER->id == $userid) {
            // If the user is the current user, then do full reload of capabilities too.
            reload_all_capabilities();
        }

        return $ra->id;
    }
}
