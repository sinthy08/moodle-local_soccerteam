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
    
    /**
     * Parameter definition for save_player_data
     */
    public static function local_soccerteam_save_player_data_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'position' => new external_value(PARAM_TEXT, 'Player position'),
            'jerseynumber' => new external_value(PARAM_INT, 'Jersey number')
        ]);
    }
    
    /**
     * Save player data to the database
     */
    public static function local_soccerteam_save_player_data($courseid, $userid, $position, $jerseynumber) {
        global $DB, $USER;
        
        // Parameter validation
        $params = self::validate_parameters(
            self::local_soccerteam_save_player_data_parameters(),
            [
                'courseid' => $courseid,
                'userid' => $userid,
                'position' => $position,
                'jerseynumber' => $jerseynumber
            ]
        );
        
        // Context validation
        $context = context_course::instance($courseid);
        self::validate_context($context);
        require_capability('moodle/course:update', $context);
        
        // Check if this player already exists in the team
        $existing = $DB->get_record('local_soccerteam', [
            'courseid' => $courseid,
            'userid' => $userid
        ]);
        
        // Check if jersey number is already taken by another player
        $sql = "SELECT * FROM {local_soccerteam} 
                WHERE courseid = :courseid 
                AND jerseynumber = :jerseynumber 
                AND userid <> :userid";
        
        $jerseytaken = $DB->get_record_sql($sql, [
            'courseid' => $courseid,
            'jerseynumber' => $jerseynumber,
            'userid' => $userid
        ]);
        
        if ($jerseytaken) {
            throw new moodle_exception('jerseynumberexists', 'local_soccerteam');
        }
        
        $time = time();
        
        if ($existing) {
            // Update existing record
            $record = new stdClass();
            $record->id = $existing->id;
            $record->position = $position;
            $record->jerseynumber = $jerseynumber;
            $record->timemodified = $time;
            
            $DB->update_record('local_soccerteam', $record);
            $result = $existing->id;
        } else {
            // Create new record
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
            'id' => $result
        ];
    }
    
    /**
     * Return definition for save_player_data
     */
    public static function local_soccerteam_save_player_data_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of the operation'),
            'message' => new external_value(PARAM_TEXT, 'Message'),
            'id' => new external_value(PARAM_INT, 'ID of the record')
        ]);
    }
}
