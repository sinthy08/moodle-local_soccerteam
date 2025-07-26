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
 * Unit tests for external API functions
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_soccerteam;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * External API test case
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \local_soccerteam_external
 */
class external_test extends \externallib_advanced_testcase {

    /**
     * Set up for tests
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    /**
     * Test getting form data
     * @covers ::local_soccerteam_get_form_data
     * @runInSeparateProcess
     */
    public function test_get_form_data(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/soccerteam/classes/external.php');

        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        
        // Create some users and enroll them in the course.
        $user1 = $this->getDataGenerator()->create_user(['firstname' => 'User', 'lastname' => 'One']);
        $user2 = $this->getDataGenerator()->create_user(['firstname' => 'User', 'lastname' => 'Two']);
        
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->getDataGenerator()->enrol_user($user1->id, $course->id, $studentrole->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, $studentrole->id);
        
        // Set current user as admin.
        $this->setAdminUser();
        
        // Call the external function.
        $result = \local_soccerteam_external::local_soccerteam_get_form_data($course->id);
        $result = \external_api::clean_returnvalue(
            \local_soccerteam_external::local_soccerteam_get_form_data_returns(),
            $result
        );
        
        // Verify the results.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('userselector', $result);
        $this->assertArrayHasKey('positionselector', $result);
        $this->assertArrayHasKey('numberselector', $result);
        
        // Check that we have at least the two users we created.
        $this->assertGreaterThanOrEqual(2, count($result['userselector']));
        
        // Check that we have all four positions.
        $this->assertEquals(4, count($result['positionselector']));
        $positions = array_column($result['positionselector'], 'value');
        $this->assertContains('Goalkeeper', $positions);
        $this->assertContains('Defender', $positions);
        $this->assertContains('Midfielder', $positions);
        $this->assertContains('Forward', $positions);
        
        // Check that we have 25 jersey numbers.
        $this->assertEquals(25, count($result['numberselector']));
    }

    /**
     * Test checking jersey number
     * @covers ::local_soccerteam_check_jersey_number
     * @runInSeparateProcess
     */
    public function test_check_jersey_number(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/soccerteam/classes/external.php');

        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        
        // Create some users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        
        // Set current user as admin.
        $this->setAdminUser();
        
        // Insert a record for user1 with jersey number 10.
        $record = new \stdClass();
        $record->courseid = $course->id;
        $record->userid = $user1->id;
        $record->position = 'Forward';
        $record->jerseynumber = 10;
        $record->timecreated = time();
        $record->timemodified = time();
        $DB->insert_record('local_soccerteam', $record);
        
        // Check if jersey number 10 is taken when checking for user2.
        $result = \local_soccerteam_external::local_soccerteam_check_jersey_number($course->id, $user2->id, 10);
        $result = \external_api::clean_returnvalue(
            \local_soccerteam_external::local_soccerteam_check_jersey_number_returns(),
            $result
        );
        
        // Verify the result shows duplicate.
        $this->assertTrue($result['duplicate']);
        $this->assertNotEmpty($result['message']);
        
        // Check if jersey number 11 is available.
        $result = \local_soccerteam_external::local_soccerteam_check_jersey_number($course->id, $user2->id, 11);
        $result = \external_api::clean_returnvalue(
            \local_soccerteam_external::local_soccerteam_check_jersey_number_returns(),
            $result
        );
        
        // Verify the result shows no duplicate.
        $this->assertFalse($result['duplicate']);
        $this->assertEmpty($result['message']);
        
        // Check if jersey number 10 is available for user1 (should be since they already have it).
        $result = \local_soccerteam_external::local_soccerteam_check_jersey_number($course->id, $user1->id, 10);
        $result = \external_api::clean_returnvalue(
            \local_soccerteam_external::local_soccerteam_check_jersey_number_returns(),
            $result
        );
        
        // Verify the result shows no duplicate.
        $this->assertFalse($result['duplicate']);
        $this->assertEmpty($result['message']);
    }

    /**
     * Test saving player data
     * @covers ::local_soccerteam_save_player_data
     * @runInSeparateProcess
     */
    public function test_save_player_data(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/soccerteam/classes/external.php');

        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        
        // Create a user.
        $user = $this->getDataGenerator()->create_user();
        
        // Set current user as admin.
        $this->setAdminUser();
        
        // Test creating a new player record.
        $result = \local_soccerteam_external::local_soccerteam_save_player_data($course->id, $user->id, 'Goalkeeper', 1);
        $result = \external_api::clean_returnvalue(
            \local_soccerteam_external::local_soccerteam_save_player_data_returns(),
            $result
        );
        
        // Verify the result.
        $this->assertTrue($result['status']);
        $this->assertNotEmpty($result['message']);
        $this->assertGreaterThan(0, $result['id']);
        
        // Check that the record was created in the database.
        $record = $DB->get_record('local_soccerteam', ['id' => $result['id']]);
        $this->assertNotFalse($record);
        $this->assertEquals($course->id, $record->courseid);
        $this->assertEquals($user->id, $record->userid);
        $this->assertEquals('Goalkeeper', $record->position);
        $this->assertEquals(1, $record->jerseynumber);
        
        // Test updating an existing player record.
        $result = \local_soccerteam_external::local_soccerteam_save_player_data($course->id, $user->id, 'Defender', 2);
        $result = \external_api::clean_returnvalue(
            \local_soccerteam_external::local_soccerteam_save_player_data_returns(),
            $result
        );
        
        // Verify the result.
        $this->assertTrue($result['status']);
        $this->assertNotEmpty($result['message']);
        
        // Check that the record was updated in the database.
        $record = $DB->get_record('local_soccerteam', ['id' => $result['id']]);
        $this->assertNotFalse($record);
        $this->assertEquals('Defender', $record->position);
        $this->assertEquals(2, $record->jerseynumber);
        
        // Test with an invalid position.
        try {
            \local_soccerteam_external::local_soccerteam_save_player_data($course->id, $user->id, 'InvalidPosition', 3);
            $this->fail('Exception expected due to invalid position');
        } catch (\moodle_exception $e) {
            $this->assertEquals('invalidposition', $e->errorcode);
        }
        
        // Create another user.
        $user2 = $this->getDataGenerator()->create_user();
        
        // Test with a duplicate jersey number.
        try {
            \local_soccerteam_external::local_soccerteam_save_player_data($course->id, $user2->id, 'Forward', 2);
            $this->fail('Exception expected due to duplicate jersey number');
        } catch (\moodle_exception $e) {
            $this->assertEquals('jerseynumberexists', $e->errorcode);
        }
    }
} 