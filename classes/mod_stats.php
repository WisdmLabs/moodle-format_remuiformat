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
 * This is built using the bootstrapbase template to allow for new theme's using Moodle's new Bootstrap theme engine
 *
 * @package   format_remuiformat
 * @copyright Copyright (c) 2016 WisdmLabs. (http://www.wisdmlabs.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_remuiformat;

defined('MOODLE_INTERNAL') || die;

use html_writer;

/**
 * This is built using the bootstrapbase template to allow for new theme's using Moodle's new Bootstrap theme engine
 */
class ModStats {

    /**
     * Singltone instance
     * @var ModStats
     */
    protected static $instance;

    /**
     * Plugin config
     * @var string
     */
    private $_plugin_config;

    /**
     * Private constructor
     */
    private function __construct() {
        $this->plugin_config = "format_remuiformat";
    }

    /**
     * Singleton Implementation.
     * @return ModStats Object
     */
    public static function getinstance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Function to return the stats of the mod.
     * @param  object   $course Course object
     * @param  \cm_info $mod    Course module info
     * @return string           Statistic
     */
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

    /**
     * Calculate quiz marks
     * @param  array  $quizid Quiz id
     * @return string         Quiz marks
     */
    private function calculate_quizmarks($quizid = []) {
        global $DB, $USER;
        $output = "";
        $quiz = $DB->get_record('quiz', array('id' => $quizid));
        try {
            $attempt = $DB->get_record('quiz_attempts', array('quiz' => $quizid, 'userid' => $USER->id));
            if ($attempt && !empty($attempt)) {
                $totalgrade = $quiz->sumgrades;
                $currentgrade = $attempt->sumgrades;
                $marks = round(($currentgrade / $totalgrade) * $quiz->grade, 2);
                $output = get_string('grade', 'format_remuiformat')." : ".$marks ." / ". intval($quiz->grade);
            } else {
                $output = get_string('grade', 'format_remuiformat')." : ". get_string('notattempted', 'format_remuiformat');
            }
        } catch (Exception $e) {
            $output = get_string('grade', 'format_remuiformat')." : ". get_string('notattempted', 'format_remuiformat');
        }

        return $output;
    }

    /**
     * Check form forum subscription
     * @param  int    $forumid Forum id
     * @return stirng          Subscription info string
     */
    private function check_subscription($forumid) {
        global $DB, $USER;
        $forum   = $DB->get_record('forum', array('id' => $forumid), '*', MUST_EXIST);
        $issubscribed = \mod_forum\subscriptions::is_subscribed($USER->id, $forum);
        if ($issubscribed) {
            return get_string("subscribed", "format_remuiformat");
        } else {
            return get_string("notsubscribed", "format_remuiformat");
        }
    }

    /**
     * Check module completion state
     * @param  object $mod    Course module
     * @param  object $course Course object
     * @return string         Completion info
     */
    private function check_completionstats($mod, $course) {
        global $USER;
        $info = new \completion_info($course);
        $data = $info->get_data($mod, false, $USER->id);
        if (!empty($data) && $data->completionstate == 1) {
            return get_string("completed", "format_remuiformat");
        } else {
            return get_string("notcompleted", "format_remuiformat");
        }
    }

    /**
     * Returns the formatted summary of section
     * @param  string $summary  Summary text
     * @param  array  $settings Settings
     * @return string           Formatted summary
     */
    public static function get_formatted_summary($summary, $settings) {
        $output = '';
        $summarylength = $settings['sectiontitlesummarymaxlength'];
        $summary = strip_tags($summary);
        if ($summary) {
            $end = "";
            if (strlen($summary) > $summarylength) {
                $end = " ...";
            }
            $summary = substr($summary, 0, $summarylength);
            $summary .= $end;
        }
        $output .= html_writer::start_tag('div', array('class' => 'overflowdiv '));
        $output .= html_writer::start_tag('div', array('class' => 'noclean '));
        $output .= $summary;
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        return $summary;
    }
}
