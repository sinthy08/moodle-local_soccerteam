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
 * Data generator for the local_soccerteam plugin.
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Data generator class for the local_soccerteam plugin.
 *
 * @package    local_soccerteam
 * @category   test
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_soccerteam_generator extends component_generator_base {

    /**
     * Create a new player record.
     *
     * @param array $record Array with data to create the player
     * @return int The ID of the newly created player record
     */
    public function create_player($record) {
        global $DB;

        // Set default values if not specified.
        $record = (object)$record;
        if (!isset($record->position)) {
            $record->position = 'Forward';
        }
        if (!isset($record->jerseynumber)) {
            // Find an unused jersey number.
            $existingnumbers = $DB->get_records_menu(
                'local_soccerteam',
                ['courseid' => $record->courseid],
                '',
                'id, jerseynumber'
            );
            $number = 1;
            while (in_array($number, $existingnumbers)) {
                $number++;
            }
            $record->jerseynumber = $number;
        }
        if (!isset($record->timecreated)) {
            $record->timecreated = time();
        }
        if (!isset($record->timemodified)) {
            $record->timemodified = $record->timecreated;
        }

        return $DB->insert_record('local_soccerteam', $record);
    }
} 