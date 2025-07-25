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

require('../../config.php');

$courseid = required_param('id', PARAM_INT);
require_login($courseid);

$context = context_course::instance($courseid);
require_capability('moodle/course:update', $context);

$PAGE->set_url(new moodle_url('/local/soccerteam/view.php', ['id' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_soccerteam'));
$PAGE->set_heading(get_string('pluginname', 'local_soccerteam'));


$PAGE->requires->js_call_amd('local_soccerteam/form', 'init', [$courseid]);

echo $OUTPUT->header();
echo html_writer::div('', 'soccerteam-form-container', ['id' => 'formcontainer']); // AJAX form will be inserted here
echo $OUTPUT->footer();
