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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/user/user_bulk_forms.php');

class user_bulk_rolechange_form extends user_bulk_action_form {
    public function definition(): void {
        $mform =& $this->_form;

        $syscontext = context_system::instance();
        $actions = array(0 => get_string('choose') . '...');
        if (has_capability('moodle/role:manage', $syscontext)) {
            $actions[3101] = get_string('roleassign', 'tool_userbulkrolechange');
            $actions[3102] = get_string('roleunassign', 'tool_userbulkrolechange');
        }
        $objs = [];
        $objs[] =& $mform->createElement('select', 'action', null, $actions);
        $objs[] =& $mform->createElement('submit', 'doaction', get_string('go'));
        $mform->addElement('group', 'actionsgrp', get_string('withselectedusers'), $objs, ' ', false);
    }
}

class pick_role_and_context_form extends moodleform {
    public function definition(): void {
        $mform =& $this->_form;
        $contextlevels = $this->_customdata['contextlevels'];
        $roles = $this->_customdata['roles'];
        $selectedusers = $this->_customdata['selectedusers'];

        $mform->addElement('static', 'selectedusers', get_string('selectedusers', 'mod_assign'), implode(', ', $selectedusers));
        $mform->addElement('select', 'contextlevel', get_string('contextlevel', 'tool_unsuproles'), $contextlevels);
        $mform->addElement('select', 'role', get_string('role'), $roles);
        $mform->addElement('select', 'instance', get_string('instance', 'tool_userbulkrolechange'), [], ['disabled' => 'disabled']);

        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'), ['disabled' => 'disabled']);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
