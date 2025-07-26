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
 * View page for local_soccerteam plugin.
 *
 * @package   local_soccerteam
 * @copyright 2025 Umme Kawser Sinthia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $PAGE, $OUTPUT, $COURSE;

$courseid = required_param('id', PARAM_INT);
require_login($courseid);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);

$PAGE->set_url(new moodle_url('/local/soccerteam/view.php', ['id' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title('Soccer team');
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add('Soccer team');

$PAGE->requires->js_call_amd('local_soccerteam/form', 'init', [$courseid]);

echo $OUTPUT->header();
echo $OUTPUT->heading('Soccer team');

echo html_writer::div('', 'soccerteam-form-container', ['id' => 'formcontainer']);

echo $OUTPUT->footer();
