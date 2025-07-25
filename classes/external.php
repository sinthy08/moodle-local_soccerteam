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
 * Soccerteam external API
 *
 * @package    local_soccerteam
 * @category   external
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("$CFG->libdir/externallib.php");

/**
 * Soccerteam external functions
 *
 * @package    local_soccerteam
 * @category   external
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.4
 */
class local_soccerteam_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function local_soccerteam_get_form_data_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Get the form of the soccerteam
     *
     * @param $courseid
     * @return array
     * @throws invalid_parameter_exception
     */
    public static function local_soccerteam_get_form_data($courseid) {
        global $DB;

        $context = context_course::instance($courseid);
        self::validate_parameters(
            self::local_soccerteam_get_form_data_parameters(),
            [
                'courseid' => $courseid,
            ]
        );

        // Get enrolled students.
        $students = get_enrolled_users($context, 'mod/assign:submit');

        $useroptions = [];
        foreach ($students as $student) {
            $useroptions[] = [
                'value' => $student->id,
                'label' => fullname($student),
            ];
        }

        // Positions with descriptions.
        $positions = [
            [
                'value' => 'Goalkeeper',
                'label' => 'Goalkeeper',
                'description' => 'Responsible for protecting the goal and preventing the opposition from scoring',
            ],
            [
                'value' => 'Defender',
                'label' => 'Defender',
                'description' => 'Preventing the opposing team from scoring, positioned in front of the goalkeeper',
            ],
            [
                'value' => 'Midfielder',
                'label' => 'Midfielder',
                'description' => 'Contributes to both defensive and offensive play in the middle of the field',
            ],
            [
                'value' => 'Forward',
                'label' => 'Forward',
                'description' => 'Score goals or create opportunities for teammates',
            ],
        ];

        // Jersey numbers 1-25.
        $numbers = [];
        for ($i = 1; $i <= 25; $i++) {
            $numbers[] = ['value' => $i, 'label' => (string)$i];
        }

        return [
            'userselector' => $useroptions,
            'positionselector' => $positions,
            'numberselector' => $numbers,
        ];
    }

    /**
     * Returns description of method result value
     * @return external_single_structure
     */
    public static function local_soccerteam_get_form_data_returns() {
        return new external_single_structure([
            'userselector' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_INT, 'User ID'),
                    'label' => new external_value(PARAM_TEXT, 'User Name'),
                ])
            ),
            'positionselector' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_TEXT, 'Position'),
                    'label' => new external_value(PARAM_TEXT, 'Position Label'),
                    'description' => new external_value(PARAM_TEXT, 'Position Description', VALUE_OPTIONAL),
                ])
            ),
            'numberselector' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_INT, 'Jersey Number'),
                    'label' => new external_value(PARAM_TEXT, 'Number Label'),
                ])
            ),
        ]);
    }
    /**
     * Parameter definition for check_jersey_number
     */
    public static function local_soccerteam_check_jersey_number_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'jerseynumber' => new external_value(PARAM_INT, 'Jersey number'),
        ]);
    }
    /**
     * Check if a jersey number is already taken by another player in the same course
     */
    public static function local_soccerteam_check_jersey_number($courseid, $userid, $jerseynumber) {
        global $DB;
        self::validate_parameters(
            self::local_soccerteam_check_jersey_number_parameters(),
            [
                'courseid' => $courseid,
                'userid' => $userid,
                'jerseynumber' => $jerseynumber,
            ]
        );
        $context = context_course::instance($courseid);
        self::validate_context($context);
        // Check if jersey number is already taken by another player.
        $sql = "SELECT * FROM {local_soccerteam}
                WHERE courseid = :courseid
                AND jerseynumber = :jerseynumber
                AND userid <> :userid";
        $jerseytaken = $DB->get_record_sql($sql, [
            'courseid' => $courseid,
            'jerseynumber' => $jerseynumber,
            'userid' => $userid,
        ]);
        if ($jerseytaken) {
            return [
                'duplicate' => true,
                'message' => get_string('jerseynumberexists', 'local_soccerteam'),
            ];
        }
        return [
            'duplicate' => false,
            'message' => '',
        ];
    }
    /**
     * Return definition for check_jersey_number
     */
    public static function local_soccerteam_check_jersey_number_returns() {
        return new external_single_structure([
            'duplicate' => new external_value(PARAM_BOOL, 'Whether the jersey number is already taken'),
            'message' => new external_value(PARAM_TEXT, 'Error message if duplicate'),
        ]);
    }
    /**
     * Parameter definition for save_player_data
     */
    public static function local_soccerteam_save_player_data_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'position' => new external_value(PARAM_TEXT, 'Player position'),
            'jerseynumber' => new external_value(PARAM_INT, 'Jersey number'),
        ]);
    }
    /**
     * Save player data to the database
     */
    public static function local_soccerteam_save_player_data($courseid, $userid, $position, $jerseynumber) {
        global $DB, $USER;

         self::validate_parameters(
            self::local_soccerteam_save_player_data_parameters(),
            [
                'courseid' => $courseid,
                'userid' => $userid,
                'position' => $position,
                'jerseynumber' => $jerseynumber,
            ]
        );
        $context = context_course::instance($courseid);
        self::validate_context($context);
        require_capability('moodle/course:update', $context);
        // Validate position.
        $validpositions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];
        if (!in_array($position, $validpositions)) {
            throw new moodle_exception('invalidposition', 'local_soccerteam');
        }
        // Check if this player already exists in the team.
        $existing = $DB->get_record('local_soccerteam', [
            'courseid' => $courseid,
            'userid' => $userid,
        ]);
        // Check if jersey number is already taken by another player.
        $sql = "SELECT *
                FROM {local_soccerteam}
                WHERE courseid = :courseid AND jerseynumber = :jerseynumber
                      AND userid <> :userid";
        $jerseytaken = $DB->get_record_sql($sql, [
            'courseid' => $courseid,
            'jerseynumber' => $jerseynumber,
            'userid' => $userid,
        ]);

        if ($jerseytaken) {
            throw new moodle_exception('jerseynumberexists', 'local_soccerteam');
        }
        $time = time();
        if ($existing) {
            $record = new stdClass();
            $record->id = $existing->id;
            $record->position = $position;
            $record->jerseynumber = $jerseynumber;
            $record->timemodified = $time;
            $DB->update_record('local_soccerteam', $record);
            $result = $existing->id;
        } else {
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->userid = $userid;
            $record->position = $position;
            $record->jerseynumber = $jerseynumber;
            $record->timecreated = $time;
            $record->timemodified = $time;
            $result = $DB->insert_record('local_soccerteam', $record);
        }
        return [
            'status' => true,
            'message' => get_string('playersaved', 'local_soccerteam'),
            'id' => $result,
        ];
    }
    /**
     * Return definition for save_player_data
     */
    public static function local_soccerteam_save_player_data_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of the operation'),
            'message' => new external_value(PARAM_TEXT, 'Message'),
            'id' => new external_value(PARAM_INT, 'ID of the record'),
        ]);
    }
}
