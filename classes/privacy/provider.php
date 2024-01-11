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
 * Privacy Subsystem implementation for format_remuiformat.
 *
 * @copyright Copyright (c) 2016 WisdmLabs. (http://www.wisdmlabs.com)
 * @package    format_remuiformat
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_remuiformat\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem for format_remuiformat implementing null_provider.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe all the places where the plugin stores some personal data.
     *
     * @param collection $collection Collection of items to add metadata to.
     * @return collection Collection with our added items.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'remuiformat_course_visits',
            [
                'userid' => 'privacy:metadata:visits:userid',
            ],
            'privacy:metadata:visits'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain personal data for the specified user.
     *
     * @param int $userid ID of the user.
     * @return contextlist List of contexts containing the user's personal data.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        global $DB;
        $contextlist = new contextlist();
        $courseids = array_column($DB->get_records("remuiformat_course_visits"), 'course');

        if (empty($courseids)) {
            return $contextlist;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $contextlist->add_from_sql("SELECT id FROM {context}
                                    WHERE contextlevel = :ctxlevel
            AND instanceid IN " . $insql,
            array_merge($inparams, ['ctxlevel' => CONTEXT_COURSE]));
        return $contextlist;
    }

    /**
     * Export personal data stored in the given contexts.
     *
     * @param approved_contextlist $contextlist List of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            // Skip any non course contexts.
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $data = $DB->get_records("remuiformat_course_visits", [
                'course' => $context->instanceid,
                'userid' => $user->id,
            ]);

            $data = (object) [
                'course_visits' => $data,
            ];

            writer::with_context($context)->export_data([], $data);
        }
    }

    /**
     * Delete personal data for all users in the context.
     *
     * @param context $context Context to delete personal data from.
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $DB->delete_records("remuiformat_course_visits", ['course' => $context->instanceid]);
    }

    /**
     * Gets users in context
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $sql = "SELECT userid FROM {remuiformat_course_visits}";
        $userlist->add_from_sql("userid", $sql, []);
    }

    /**
     * Delete personal data for the user in a list of contexts.
     *
     * @param approved_contextlist $contextlist List of contexts to delete data from.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        // If no contexts, nothing to delete.
        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            // Must be course context.
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }

            $DB->delete_records("remuiformat_course_visits", ['course' => $context->instanceid, 'userid' => $user->id]);
        }
    }

    /**
     * Deletes data for users
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $userids = $userlist->get_users();

        foreach ($userids as $userid) {
            $DB->delete_records("remuiformat_course_visits", ['userid' => $userid]);
        }
    }
}


