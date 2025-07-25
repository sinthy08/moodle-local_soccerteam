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
 * @package local_soccerteam
 * @copyright 2025 Umme Kawser Sinthia
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


/**
 *  Extends the secondary navigation of a course
 *
 * @param navigation_node $navigation
 * @return void
 * @throws coding_exception
 * @throws moodle_exception
 */
function local_soccerteam_extend_navigation_course($navigation, $course, $context) {
    global $PAGE, $USER;

    // Only allow users with manage capability.
    if (!has_capability('moodle/course:update', $context)) {
        return;
    }

    // Add a link to the course secondary navigation.
    $url = new moodle_url('/local/soccerteam/view.php', ['id' => $course->id]);
    $node = navigation_node::create(
        get_string('pluginname', 'local_soccerteam'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'soccerteam',
        new pix_icon('i/group', '') // Optional icon
    );

    $navigation->add_node($node);
}

