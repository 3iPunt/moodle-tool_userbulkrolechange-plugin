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

use tool_userbulkrolechange\external;

/**
 * @package     tool_userbulkrolechange
 * @copyright   2018 3iPunt Mitxel Moriana <mitxel@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'tool_userbulkrolechange_get_all_course_categories' => array(
        'methodname' => 'get_all_course_categories',
        'classname' => external::class,
        'classpath' => 'tool/userbulkrolechange/classes/external.php',
        'description' => 'Get a list of all the existing course categories',
        'type' => 'read',
        'ajax' => true,
    ),
    'tool_userbulkrolechange_get_all_courses' => array(
        'methodname' => 'get_all_courses',
        'classname' => external::class,
        'classpath' => 'tool/userbulkrolechange/classes/external.php',
        'description' => 'Get a list of all the existing courses',
        'type' => 'read',
        'ajax' => true,
    ),
    'tool_userbulkrolechange_get_context_level_roles' => array(
        'methodname' => 'get_context_level_roles',
        'classname' => external::class,
        'classpath' => 'tool/userbulkrolechange/classes/external.php',
        'description' => 'Get all the assignable roles for the given context level',
        'type' => 'read',
        'ajax' => true,
    ),
    'tool_userbulkrolechange_bulk_unassign_role' => array(
        'methodname' => 'bulk_unassign_role',
        'classname' => external::class,
        'classpath' => 'tool/userbulkrolechange/classes/external.php',
        'description' => 'Bulk user unassign role',
        'type' => 'write',
        'ajax' => true,
    ),
    'tool_userbulkrolechange_bulk_assign_role' => array(
        'methodname' => 'bulk_assign_role',
        'classname' => external::class,
        'classpath' => 'tool/userbulkrolechange/classes/external.php',
        'description' => 'Bulk user assign role',
        'type' => 'write',
        'ajax' => true,
    ),
);

$services = array(
);
