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
 * Privacy test for the local_soccerteam plugin.
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_soccerteam\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use local_soccerteam\privacy\provider;

/**
 * Privacy test for the local_soccerteam plugin.
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_test extends \core_privacy\tests\provider_testcase {

    /**
     * Test for provider::get_metadata()
     * @covers :: provider test to get metadata
     * @runInSeparateProcess
     */
    public function test_get_metadata(): void {
        $collection = new collection('local_soccerteam');
        $metadata = provider::get_metadata($collection);
        // The collection should contain only one item.
        $this->assertCount(1, $metadata->get_collection());
        $items = $metadata->get_collection();
        $table = reset($items);
        $this->assertEquals('local_soccerteam', $table->get_name());
        $privacyfields = $table->get_privacy_fields();
        $this->assertArrayHasKey('courseid', $privacyfields);
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('position', $privacyfields);
        $this->assertArrayHasKey('jerseynumber', $privacyfields);
        $this->assertArrayHasKey('timecreated', $privacyfields);
        $this->assertArrayHasKey('timemodified', $privacyfields);
        $this->assertEquals('privacy:metadata:local_soccerteam', $table->get_summary());
    }

    /**
     * Test for provider::get_contexts_for_userid()
     * @covers :: context for userid
     * @runInSeparateProcess
     */
    public function test_get_contexts_for_userid(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/soccerteam/classes/privacy/provider.php');
        $this->resetAfterTest();
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Create some player records.
        $generator = $this->getDataGenerator()->get_plugin_generator('local_soccerteam');
        // User1 in course1.
        $generator->create_player([
            'courseid' => $course1->id,
            'userid' => $user1->id,
            'position' => 'Forward',
            'jerseynumber' => 10,
        ]);
        // User1 in course2.
        $generator->create_player([
            'courseid' => $course2->id,
            'userid' => $user1->id,
            'position' => 'Midfielder',
            'jerseynumber' => 8,
        ]);
        // User2 in course1.
        $generator->create_player([
            'courseid' => $course1->id,
            'userid' => $user2->id,
            'position' => 'Goalkeeper',
            'jerseynumber' => 1,
        ]);
        // Get contexts for user1.
        $contextlist = provider::get_contexts_for_userid($user1->id);
        // Check that we have two contexts.
        $this->assertCount(2, $contextlist);
        // Convert to array for easier assertions.
        $contexts = $contextlist->get_contextids();
        // Check that the contexts match the courses.
        $context1id = \context_course::instance($course1->id)->id;
        $context2id = \context_course::instance($course2->id)->id;
        // Instead of using assertContains, check if the values are in the array directly.
        $this->assertTrue(in_array($context1id, $contexts), "Context 1 ID not found in context list");
        $this->assertTrue(in_array($context2id, $contexts), "Context 2 ID not found in context list");
        // Get contexts for user2.
        $contextlist = provider::get_contexts_for_userid($user2->id);
        $this->assertCount(1, $contextlist);
        $contexts = $contextlist->get_contextids();
        $this->assertTrue(in_array($context1id, $contexts), "Context 1 ID not found in context list for user2");
        $this->assertFalse(in_array($context2id, $contexts), "Context 2 ID should not be in context list for user2");
    }

    /**
     * Test for provider::export_user_data()
     * @covers :: export user data
     * @runInSeparateProcess
     */
    public function test_export_user_data(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/soccerteam/classes/privacy/provider.php');
        $this->resetAfterTest();
        // Create test data.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $context = \context_course::instance($course->id);
        // Create a player record.
        $generator = $this->getDataGenerator()->get_plugin_generator('local_soccerteam');
        $generator->create_player([
            'courseid' => $course->id,
            'userid' => $user->id,
            'position' => 'Forward',
            'jerseynumber' => 10,
        ]);
        // Export the data for the user.
        $approvedlist = new approved_contextlist($user, 'local_soccerteam', [$context->id]);
        provider::export_user_data($approvedlist);
        $writer = writer::with_context($context);
        $data = $writer->get_data([get_string('pluginname', 'local_soccerteam')]);
        $this->assertNotEmpty($data);
        $this->assertEquals('Forward', $data->position);
        $this->assertEquals(10, $data->jerseynumber);
    }

    /**
     * Test for provider::delete_data_for_all_users_in_context()
     * @covers :: delete data for all users in context
     * @runInSeparateProcess
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/soccerteam/classes/privacy/provider.php');
        $this->resetAfterTest();
        // Create test data.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Create some player records.
        $generator = $this->getDataGenerator()->get_plugin_generator('local_soccerteam');
        // User1 in course1.
        $generator->create_player([
            'courseid' => $course1->id,
            'userid' => $user1->id,
        ]);
        // User2 in course1.
        $generator->create_player([
            'courseid' => $course1->id,
            'userid' => $user2->id,
        ]);
        // User1 in course2.
        $generator->create_player([
            'courseid' => $course2->id,
            'userid' => $user1->id,
        ]);
        // Check that we have 3 records.
        $this->assertEquals(3, $DB->count_records('local_soccerteam'));
        // Delete all data for course1.
        $context = \context_course::instance($course1->id);
        provider::delete_data_for_all_users_in_context($context);
        // Check that we now have 1 record.
        $this->assertEquals(1, $DB->count_records('local_soccerteam'));
        // Check that the remaining record is for course2.
        $this->assertEquals(1, $DB->count_records('local_soccerteam', ['courseid' => $course2->id]));
    }

    /**
     * Test for provider::delete_data_for_user()
     * @covers :: delete_data_for_user()
     * @runInSeparateProcess
     */
    public function test_delete_data_for_user(): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/soccerteam/classes/privacy/provider.php');
        $this->resetAfterTest();
        // Create test data.
        $course1 = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Create some player records.
        $generator = $this->getDataGenerator()->get_plugin_generator('local_soccerteam');
        // User1 in course1.
        $generator->create_player([
            'courseid' => $course1->id,
            'userid' => $user1->id,
        ]);
        // User2 in course1.
        $generator->create_player([
            'courseid' => $course1->id,
            'userid' => $user2->id,
        ]);
        // User1 in course2.
        $generator->create_player([
            'courseid' => $course2->id,
            'userid' => $user1->id,
        ]);
        $this->assertEquals(3, $DB->count_records('local_soccerteam'));
        $context1 = \context_course::instance($course1->id);
        $context2 = \context_course::instance($course2->id);
        $approvedlist = new approved_contextlist($user1, 'local_soccerteam', [$context1->id, $context2->id]);
        provider::delete_data_for_user($approvedlist);
        $this->assertEquals(1, $DB->count_records('local_soccerteam'));
        $this->assertEquals(1, $DB->count_records('local_soccerteam', ['userid' => $user2->id]));
    }
}
