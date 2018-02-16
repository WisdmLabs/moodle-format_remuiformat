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
 * This is built using the bootstrapbase template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 * @package   format_cards
 * @copyright Copyright (c) 2016 WisdmLabs. (http://www.wisdmlabs.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_cards;

defined('MOODLE_INTERNAL') || die;

class ModStats
{
    protected static $instance;
    private $_plugin_config;

    // Constructor.
    private function __construct() {
        $this->plugin_config = "format_cards";
    }
    // Singleton Implementation.
    public static function getinstance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Function to return the stats of the mod.
    public function get_mod_stats($course, \cm_info $mod) {
        $stats = "";
        $modtype = $mod->modname;
        switch ($modtype) {
            case "quiz": $stats = $this->calculate_quizmarks($mod->instance);
                break;
            case "forum":$stats = $this->check_subscription($mod->instance);
                break;
            default : $stats = $this->check_completionstats($mod, $course);
                break;
        }
        return $stats;
    }

    private function calculate_quizmarks($quizid) {
        global $DB, $USER;
        $output = "";
        $quiz = $DB->get_record('quiz', array('id' => $quizid));
        try {
            $attempt = $DB->get_record('quiz_attempts', array('quiz' => $quizid, 'userid' => $USER->id));
            if ($attempt && !empty($attempt)) {
                $totalgrade = $quiz->sumgrades;
                $currentgrade = $attempt->sumgrades;
                $marks = ($currentgrade / $totalgrade) * $quiz->grade;
                $output = get_string('grade', 'format_cards')." : ".$marks ." / ". intval($quiz->grade);
            } else {
                $output = get_string('grade', 'format_cards')." : ". get_string('notattempted', 'format_cards');
            }
        } catch (Exception $e) {
            $output = get_string('grade', 'format_cards')." : ". get_string('notattempted', 'format_cards');
        }

        return $output;
    }

    private function check_subscription($forumid) {
        global $DB, $USER;
        $forum   = $DB->get_record('forum', array('id' => $forumid), '*', MUST_EXIST);
        $issubscribed = \mod_forum\subscriptions::is_subscribed($USER->id, $forum);
        if ($issubscribed) {
            return get_string("subscribed", "format_cards");
        } else {
            return get_string("notsubscribed", "format_cards");
        }
    }

    private function check_completionstats($mod, $course) {
        global $DB, $USER;
        $info = new \completion_info($course);
        $data = $info->get_data($mod, false, $USER->id);
        if (!empty($data) && $data->completionstate == 1) {
            return get_string("completed", "format_cards");
        } else {
            return get_string("notcompleted", "format_cards");
        }
    }

    /**
     * Returns the formatted summary of section
     * @param $summary String
     * @return $summary String
     */
    public static function get_formatted_summary($summary, $settings) {

        $summarylength = $settings['sectiontitlesummarymaxlength'];
        $summary = str_replace("&nbsp;", ' ', $summary);
        if ($summary) {
            $end = "";
            if (strlen($summary) > $summarylength) {
                $end = " ...";
            }
            $summary = substr($summary, 0, $summarylength);
            $summary .= $end;
        }
        return $summary;
    }

    public static function get_completed_modules() {

    }
}