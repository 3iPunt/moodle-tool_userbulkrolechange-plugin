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

defined('MOODLE_INTERNAL') || die();

class tool_userbulkrolechange_bulk_role_change_testcase extends advanced_testcase {
    /**
     * @var array $users Array of test user ids
     */
    protected $users;

    public function setUp() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator();

        $this->users = [];
        for ($i = 0; $i < 10; ++$i) {
            $this->users[] = $generator->create_user()->id;
        }
    }

    /**
     * Test bulk role assignment.
     */
    public function test_bulk_role_assign() {
        global $DB;
        $testroleid = create_role('Test Role', 'testrole', 'This is a test role', 'guest');
        $testcontextlevel = CONTEXT_SYSTEM;
        $testinstanceid = null;

        \tool_userbulkrolechange\api::bulk_assign_role($this->users, $testcontextlevel, $testinstanceid, $testroleid);

        $this->assertEquals(count($this->users), $DB->count_records('role_assignments', array('roleid' => $testroleid)));
    }

    /**
     * Test bulk role un-assignment.
     */
    public function test_bulk_role_unassign() {
        global $DB;
        $testroleid = create_role('Another Test Role', 'anothertestrole', 'This is another test role', 'guest');
        $testcontextlevel = CONTEXT_SYSTEM;
        $testinstanceid = null;

        foreach ($this->users as $userid) {
            role_assign($testroleid, $userid, context_system::instance()->id);
        }

        \tool_userbulkrolechange\api::bulk_unassign_role($this->users, $testcontextlevel, $testinstanceid, $testroleid);

        $this->assertEquals(0, $DB->count_records('role_assignments', array('roleid' => $testroleid)));
    }
}
