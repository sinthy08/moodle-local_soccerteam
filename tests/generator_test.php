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
 * Unit tests for the data generator
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_soccerteam;

defined('MOODLE_INTERNAL') || die();

/**
 * Generator test case
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generator_test extends \advanced_testcase {

    /**
     * Test creating player records
     * @runInSeparateProcess
     */
    public function test_create_player(): void {
        global $DB;

        $this->resetAfterTest(true);

        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        
        // Create users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        
        // Get the generator.
        $generator = $this->getDataGenerator()->get_plugin_generator('local_soccerteam');
        
        // Create a player with default values.
        $player1id = $generator->create_player([
            'courseid' => $course->id,
            'userid' => $user1->id,
        ]);
        
        // Verify the player was created.
        $player1 = $DB->get_record('local_soccerteam', ['id' => $player1id]);
        $this->assertNotFalse($player1);
        $this->assertEquals($course->id, $player1->courseid);
        $this->assertEquals($user1->id, $player1->userid);
        $this->assertEquals('Forward', $player1->position); // Default position.
        $this->assertEquals(1, $player1->jerseynumber); // Default jersey number.
        
        // Create a player with specific values.
        $player2id = $generator->create_player([
            'courseid' => $course->id,
            'userid' => $user2->id,
            'position' => 'Goalkeeper',
            'jerseynumber' => 10,
        ]);
        
        // Verify the player was created with the specified values.
        $player2 = $DB->get_record('local_soccerteam', ['id' => $player2id]);
        $this->assertNotFalse($player2);
        $this->assertEquals($course->id, $player2->courseid);
        $this->assertEquals($user2->id, $player2->userid);
        $this->assertEquals('Goalkeeper', $player2->position);
        $this->assertEquals(10, $player2->jerseynumber);
        
        // Create another player with default jersey number (should be 2 since 1 is taken).
        $user3 = $this->getDataGenerator()->create_user();
        $player3id = $generator->create_player([
            'courseid' => $course->id,
            'userid' => $user3->id,
        ]);
        
        // Verify the player was created with the next available jersey number.
        $player3 = $DB->get_record('local_soccerteam', ['id' => $player3id]);
        $this->assertNotFalse($player3);
        $this->assertEquals(2, $player3->jerseynumber);
    }
} 