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
 * Activity Renderable - A topics based format that uses card layout to diaply the content.
 *
 * @package course/format
 * @subpackage remuiformat
 * @copyright  2019 WisdmLabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_remuiformat\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;
use html_writer;
use context_course;

require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/format/remuiformat/classes/mod_stats.php');
require_once($CFG->dirroot.'/course/format/remuiformat/lib.php');

/**
 * This file contains the definition for the renderable classes for the activity page.
 *
 * @package   format_remuiformat
 * @copyright  2018 Wisdmlabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_remuiformat_list_one_section implements renderable, templatable {
    private $course;
    private $courseformat;
    private $courserenderer;
    private $modstats;
    private $settings;
    private $displaysection;
    /**
     * Constructor
     */
    public function __construct($course, $displaysection, $renderer) {
        $this->displaysection = $displaysection;
        $this->courseformat = course_get_format($course);
        $this->course = $this->courseformat->get_course();
        $this->courserenderer = $renderer;
        $this->modstats = \format_remuiformat\ModStats::getinstance();
        $this->settings = $this->courseformat->get_settings();
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * question mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE, $CFG;
        unset($output);
        $export = new \stdClass();
        $modinfo = get_fast_modinfo($this->course);
        $renderer = $PAGE->get_renderer('format_remuiformat');

        $export->section = $this->displaysection;
        // Check if section exists.
        if (!($sectioninfo = $modinfo->get_section_info($this->displaysection))) {
            // This section doesn't exist.
            $export->error = get_string('unknowncoursesection', 'error', $this->course->fullname);
            return $export;
        }

        // Check if the section is hidden section.
        if (!$sectioninfo->uservisible) {
            if (!$course->hiddensections) {
                $export->hiddensection = $renderer->start_section_list();
                $export->hiddensection .= $renderer->section_hidden($displaysection, $this->course->id);
                $export->hiddensection .= $renderer->end_section_list();
            }
            // Can't view this section.
            return $export;
        }
        if ($PAGE->user_is_editing()) {
            $export->editing = 1;
        }
        // The requested section page.
        $currentsection = $modinfo->get_section_info($this->displaysection);
        // Title with section navigation links.
        $sectionnavlinks = $renderer->get_nav_links($this->course, $modinfo->get_section_info_all(), $this->displaysection);
        $export->leftnav = $sectionnavlinks['previous'];
        $export->rightnav = $sectionnavlinks['next'];
        $export->leftside = $renderer->section_left_content($currentsection, $this->course, false);
        $export->optionmenu = $renderer->section_right_content($currentsection, $this->course, false);

        // Title.
        $sectionname = $renderer->section_title_without_link($currentsection, $this->course);
        $export->title = $sectionname;
        $sectiontitlesummarymaxlength = $this->settings['sectiontitlesummarymaxlength'];
        if (!empty($currentsection->summary)) {
            $export->summary = $renderer->format_summary_text($currentsection);
        }

        // Get the details of the activities.
        $export->remuicourseformatlist = true;
        $export->activities = $this->courserenderer->course_section_cm_list(
                $this->course, $currentsection, $this->displaysection
            );
        $export->activities .= $this->courserenderer->course_section_add_cm_control(
            $this->course, $this->displaysection, $this->displaysection
        );
        $PAGE->requires->js_call_amd('format_remuiformat/format_list', 'init');
        return $export;
    }
}
