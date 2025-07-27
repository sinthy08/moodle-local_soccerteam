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
 * Installation script for local_soccerteam plugin
 *
 * @package    local_soccerteam
 * @copyright  2023 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Function to perform any install actions for the plugin.
 *
 * @return bool
 */
function xmldb_local_soccerteam_install() {
    global $DB;
    // Ensure capabilities are set up properly.
    $result = true;
    // Refresh capabilities.
    $context = context_system::instance();
    $result = $result && capabilities_refresh($context);
    return $result;
}
