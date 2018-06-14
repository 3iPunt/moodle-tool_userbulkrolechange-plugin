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

use tool_userbulkrolechange\api;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once(__DIR__ . '/user_bulk_rolechange_forms.php');

global $CFG, $PAGE;

require_login();
admin_externalpage_setup('tooluserbulkrolechange');
require_capability('moodle/role:manage', context_system::instance());

$return = $CFG->wwwroot . '/' . $CFG->admin . '/tool/userbulkrolechange/user_bulk.php';
if (empty($SESSION->bulk_users)) {
    redirect($return);
}

$contextlevels = api::get_context_levels_that_have_associated_roles();
reset($contextlevels);
$preloadedroles = api::get_context_level_roles(key($contextlevels));
list($in, $inparams) = $DB->get_in_or_equal($SESSION->bulk_users);
$selectedusers = $DB->get_records_select_menu('user', "id $in", $inparams, 'fullname', 'id,'.$DB->sql_fullname().' AS fullname');

$mform = new pick_role_and_context_form('javascript:', [
    'contextlevels' => $contextlevels,
    'roles' => $preloadedroles,
    'selectedusers' => $selectedusers
], 'post' , '' , 'autocomplete="off"');
if ($mform->is_cancelled()) {
    redirect($return);
}

$action = 'unassign';
$localstrings = [
    'allcategories' => get_string('allcategories', 'tool_userbulkrolechange'),
    'allcourses' => get_string('allcourses', 'tool_userbulkrolechange'),
];
$PAGE->requires->js_call_amd('tool_userbulkrolechange/pick_role_form', 'init', [$localstrings, $action]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('roleunassign', 'tool_userbulkrolechange'));

echo html_writer::span(get_string('roleunassigninfo', 'tool_userbulkrolechange', count($SESSION->bulk_users)));
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');
$mform->display();

echo $OUTPUT->footer();
