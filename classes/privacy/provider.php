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
 * Privacy Subsystem implementation for local_soccerteam.
 *
 * @package    local_soccerteam
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_soccerteam\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for local_soccerteam.
 *
 * @copyright  2025 Umme Kawser Sinthia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This plugin stores personal data.
        \core_privacy\local\metadata\provider,
        // This plugin is capable of determining which users have data within it.
        \core_privacy\local\request\plugin\provider,
        // This plugin is capable of responding to userlist requests.
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_soccerteam',
            [
                'courseid' => 'privacy:metadata:local_soccerteam:courseid',
                'userid' => 'privacy:metadata:local_soccerteam:userid',
                'position' => 'privacy:metadata:local_soccerteam:position',
                'jerseynumber' => 'privacy:metadata:local_soccerteam:jerseynumber',
                'timecreated' => 'privacy:metadata:local_soccerteam:timecreated',
                'timemodified' => 'privacy:metadata:local_soccerteam:timemodified',
            ],
            'privacy:metadata:local_soccerteam'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // The soccerteam data is associated at the course context.
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course} co ON co.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {local_soccerteam} st ON st.courseid = co.id
                 WHERE st.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'userid'       => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        $sql = "SELECT st.userid
                  FROM {local_soccerteam} st
                 WHERE st.courseid = :courseid";

        $params = [
            'courseid' => $context->instanceid,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT st.*, c.id as contextid
                  FROM {context} c
                  JOIN {course} co ON co.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {local_soccerteam} st ON st.courseid = co.id
                 WHERE c.id {$contextsql}
                   AND st.userid = :userid
              ORDER BY st.courseid";

        $params = [
            'contextlevel' => CONTEXT_COURSE,
            'userid'       => $userid,
        ];
        $params += $contextparams;

        $soccerteamdata = $DB->get_records_sql($sql, $params);

        foreach ($soccerteamdata as $soccerteam) {
            $context = \context::instance_by_id($soccerteam->contextid);

            // Fetch the generic module data.
            $contextdata = helper::get_context_data($context, $user);
            $contextdata->position = $soccerteam->position;
            $contextdata->jerseynumber = $soccerteam->jerseynumber;
            writer::with_context($context)->export_data([get_string('pluginname', 'local_soccerteam')], $contextdata);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_course) {
            return;
        }

        $DB->delete_records('local_soccerteam', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_course) {
                continue;
            }
            $DB->delete_records('local_soccerteam', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ]);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_course) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params = array_merge(['courseid' => $context->instanceid], $userparams);

        $DB->delete_records_select(
            'local_soccerteam',
            "courseid = :courseid AND userid {$usersql}",
            $params
        );
    }
} 