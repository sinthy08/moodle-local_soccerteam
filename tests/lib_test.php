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
 * Unit tests for lib.php functions
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_soccerteam;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Lib functions test case
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib_test extends \advanced_testcase {

    /**
     * Set up for tests
     */
    protected function setUp(): void {
        $this->resetAfterTest(true);
    }

    /**
     * Test the navigation extension function
     * @runInSeparateProcess
     */
    public function test_extend_navigation_course(): void {
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/soccerteam/lib.php');

        // Create a course.
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        // Create a user with course update capability.
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('moodle/course:update', CAP_ALLOW, $roleid, $context);
        role_assign($roleid, $user->id, $context);
        
        // Set the user.
        $this->setUser($user);
        
        // Create a mock navigation node.
        $navigation = $this->createMock(\navigation_node::class);
        
        // Set up expectations for the add_node method to be called once.
        $navigation->expects($this->once())
            ->method('add_node')
            ->with($this->isInstanceOf(\navigation_node::class));
        
        // Call the function.
        local_soccerteam_extend_navigation_course($navigation, $course, $context);
        
        // Now test with a user who doesn't have the capability.
        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);
        
        // Create a new mock that should NOT have add_node called.
        $navigation2 = $this->createMock(\navigation_node::class);
        $navigation2->expects($this->never())
            ->method('add_node');
        
        // Call the function again.
        local_soccerteam_extend_navigation_course($navigation2, $course, $context);
    }
} 