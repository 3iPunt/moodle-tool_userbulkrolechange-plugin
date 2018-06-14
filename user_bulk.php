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
 * This plugin is an extension of the core bulk user tools (see /tool/user/user_bulk.php)
 * Basically, two new tools have been added: bulk role add, bulk role delete
 *
 * @package     tool_userbulkrolechange
 * @copyright   2018 3iPunt Mitxel Moriana <mitxel@tresipunt.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/user/lib.php');
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/userbulkrolechange/user_bulk_rolechange_forms.php');

admin_externalpage_setup('tooluserbulkrolechange');

if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}
// create the user filter form
$ufiltering = new user_filtering();

// array of bulk operations
// create the bulk operations form
$action_form = new user_bulk_rolechange_form();
if ($data = $action_form->get_data()) {
    // check if an action should be performed and do so
    switch ($data->action) {
        case 3101:
            redirect($CFG->wwwroot . '/' . $CFG->admin . '/tool/userbulkrolechange/user_bulk_roleassign.php');
            break;
        case 3102:
            redirect($CFG->wwwroot . '/' . $CFG->admin . '/tool/userbulkrolechange/user_bulk_roleunassign.php');
            break;
    }
}

$user_bulk_form = new user_bulk_form(null, get_selection_data($ufiltering));

if ($data = $user_bulk_form->get_data()) {
    if (!empty($data->addall)) {
        add_selection_all($ufiltering);
    } else if (!empty($data->addsel)) {
        if (!empty($data->ausers)) {
            if (in_array(0, $data->ausers, false)) {
                add_selection_all($ufiltering);
            } else {
                foreach ($data->ausers as $userid) {
                    if ($userid == -1) {
                        continue;
                    }
                    if (!isset($SESSION->bulk_users[$userid])) {
                        $SESSION->bulk_users[$userid] = $userid;
                    }
                }
            }
        }
    } else if (!empty($data->removeall)) {
        $SESSION->bulk_users = array();
    } else if (!empty($data->removesel) && !empty($data->susers)) {
        if (in_array(0, $data->susers, false)) {
            $SESSION->bulk_users = array();
        } else {
            foreach ($data->susers as $userid) {
                if ($userid == -1) {
                    continue;
                }
                unset($SESSION->bulk_users[$userid]);
            }
        }
    }

    // reset the form selections
    unset($_POST);
    $user_bulk_form = new user_bulk_form(null, get_selection_data($ufiltering));
}
// do output
echo $OUTPUT->header();

$ufiltering->display_add();
$ufiltering->display_active();
$user_bulk_form->display();
$action_form->display();

echo $OUTPUT->footer();
