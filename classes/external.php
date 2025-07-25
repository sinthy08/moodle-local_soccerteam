<?php
//namespace classes;

defined('MOODLE_INTERNAL') || die();

//use context_course;
//use external_api;
//use external_function_parameters;
//use external_multiple_structure;
//use external_single_structure;
//use external_value;
global $CFG;
require_once("$CFG->libdir/externallib.php");

class local_soccerteam_external extends external_api {

    public static function local_soccerteam_get_form_data_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }

    public static function local_soccerteam_get_form_data($courseid) {
        global $DB;

        $context = context_course::instance($courseid);
        self::validate_parameters(
            self::local_soccerteam_get_form_data_parameters(),
            array(
                'courseid' => $courseid
            )
        );

        // Get enrolled students.
        $students = get_enrolled_users($context, 'mod/assign:submit');

        $useroptions = [];
        foreach ($students as $student) {
            $useroptions[] = [
                'value' => $student->id,
                'label' => fullname($student)
            ];
        }

        // Hardcoded positions (in real plugin you may fetch from config or DB)
        $positions = [
            ['value' => 'Goalkeeper', 'label' => 'Goalkeeper'],
            ['value' => 'Defender', 'label' => 'Defender'],
            ['value' => 'Midfielder', 'label' => 'Midfielder'],
            ['value' => 'Forward', 'label' => 'Forward'],
        ];

        // Jersey numbers 1-25
        $numbers = [];
        for ($i = 1; $i <= 25; $i++) {
            $numbers[] = ['value' => $i, 'label' => (string)$i];
        }

        return [
            'userselector' => $useroptions,
            'positionselector' => $positions,
            'numberselector' => $numbers
        ];
    }

    public static function local_soccerteam_get_form_data_returns() {
        return new external_single_structure([
            'userselector' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_INT, 'User ID'),
                    'label' => new external_value(PARAM_TEXT, 'User Name')
                ])
            ),
            'positionselector' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_TEXT, 'Position'),
                    'label' => new external_value(PARAM_TEXT, 'Position Label')
                ])
            ),
            'numberselector' => new external_multiple_structure(
                new external_single_structure([
                    'value' => new external_value(PARAM_INT, 'Jersey Number'),
                    'label' => new external_value(PARAM_TEXT, 'Number Label')
                ])
            )
        ]);
    }
}
