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
 * Soccer team external functions and service definitions.
 *
 * @package    local_soccerteam
 * @category   external
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(
    $functions = array(
        'local_soccerteam_get_form_data' => array(
            'classname'     => 'local_soccerteam_external',
            'methodname'    => 'local_soccerteam_get_form_data',
            'classpath'     => 'local\soccerteam\classes\external.php',
            'description'   => 'Return form fields for soccer team form',
            'type'          => 'read',
            'ajax'          => true,
            'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
        )
    )
);
