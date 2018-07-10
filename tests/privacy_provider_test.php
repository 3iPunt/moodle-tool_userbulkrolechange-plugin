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

use core_privacy\local\metadata\null_provider;
use \tool_userbulkrolechange\privacy\provider;

class tool_userbulkrolechange_privacy_testcase extends \core_privacy\tests\provider_testcase {
    /**
     * Test provider is null provider
     */
    public function test_provider_is_null_provider() {
        $this->assertInstanceOf(null_provider::class, new provider());
    }

    /**
     * Test our provider (extending null provider) returns a valid privacy message string.
     */
    public function test_provider_returns_valid_reason_string() {
        $reasonIdentifier = provider::get_reason();
        $this->assertEquals('privacy:metadata', $reasonIdentifier);
    }
}
