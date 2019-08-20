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
 * Sinigle Section Renderable - A topics based format that uses card layout to diaply the content.
 *
 * @package course/format
 * @subpackage remuiformat
 * @copyright  2019 Wisdmlabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_remuiformat\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;
use context_course;
use html_writer;
use moodle_url;
use core_completion\progress;

require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/renderer.php');
require_once($CFG->dirroot.'/course/format/remuiformat/classes/mod_stats.php');
require_once($CFG->dirroot.'/course/format/remuiformat/lib.php');

/**
 * This file contains the definition for the renderable classes for the sections page.
 *
 * @package   format_remuiformat
 * @copyright  2018 Wisdmlabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_remuiformat_single_section implements renderable, templatable
{

    private $course;
    private $courseformat;
    protected $courserenderer;
    private $modstats;
    private $settings;

    /**
     * Constructor
     */
    public function __construct($course, $renderer) {
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
        global $PAGE, $DB, $OUTPUT, $CFG;

        $export = new \stdClass();
        $renderer = $PAGE->get_renderer('format_remuiformat');
        $rformat = $this->settings['remuicourseformat'];

        // Get necessary default values required to display the UI.
        $editing = $PAGE->user_is_editing();
        $export->editing = $editing;
        $export->courseformat = get_config('format_remuiformat', 'defaultcourseformat');

        if ($rformat == REMUI_CARD_FORMAT) {
            $PAGE->requires->js_call_amd('format_remuiformat/format_card', 'init');
            $this->get_card_format_context($export, $renderer, $editing, $rformat);
        }

        if ($rformat == REMUI_LIST_FORMAT) {
            $PAGE->requires->js_call_amd('format_remuiformat/format_list', 'init');
            $this->get_list_format_context($export, $renderer, $editing, $rformat);
        }

        return  $export;
    }

    /**
     * Returns the context containing the details required by the cards format mustache.
     *
     * @param Object $export
     * @param Object $renderer
     * @param Boolean $editing
     * @return Object
     */
    private function get_card_format_context(&$export, $renderer, $editing, $rformat) {
        $coursecontext = context_course::instance($this->course->id);
        $modinfo = get_fast_modinfo($this->course);
        $sections = $modinfo->get_section_info_all();

        // Setting up data for General Section.
        $generalsection = $modinfo->get_section_info(0);
        if ($generalsection) {
            if ($editing) {
                $export->generalsection['title'] = $renderer->section_title($generalsection, $this->course);
                $export->generalsection['editsetionurl'] = new \moodle_url('editsection.php', array('id' => $generalsection->id));
                $export->generalsection['leftsection'] = $renderer->section_left_content($generalsection, $this->course, false);
                $export->generalsection['optionmenu'] = $renderer->section_right_content($generalsection, $this->course, false);
            } else {
                $export->generalsection['title'] = $this->courseformat->get_section_name($generalsection);
            }

            $export->generalsection['availability'] = $renderer->section_availability($generalsection);
            $export->generalsection['summary'] = $renderer->format_summary_text($generalsection);

            $export->generalsection['activities'] = $this->courserenderer->course_section_cm_list(
                $this->course, $generalsection, 0
            );
            $export->generalsection['activities'] .= $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
        }

        // Setting up data for remianing sections.
        $export->sections = $this->get_all_section_data($renderer, $editing, $rformat);
    }

    private function get_list_format_context(&$export, $renderer, $editing, $rformat) {
        global $DB, $OUTPUT, $USER;
        $coursecontext = context_course::instance($this->course->id);
        $modinfo = get_fast_modinfo($this->course);

        // Default view for all sections.
        $defaultview = $this->settings['remuidefaultsectionview'];
        $export->defaultview = $defaultview;
        if ($defaultview == 1) {
            $export->expanded = false;
            $export->collapsed = true;
        } else {
            $export->collapsed = true;
        }
        // User id for toggle.
        $export->user_id = $USER->id;
        // Course Information.
        $export->course_id = $this->course->id;
        $imgurl = $this->display_file($this->settings['remuicourseimage_filemanager']);

        // General Section Details.
        $generalsection = $modinfo->get_section_info(0);
        if ($editing) {
            $export->generalsection['generalsectiontitlename'] = $this->courseformat->get_section_name($generalsection);
            $export->generalsection['generalsectiontitle'] = $renderer->section_title($generalsection, $this->course);
        } else {
            $export->generalsection['generalsectiontitle'] = $this->courseformat->get_section_name($generalsection);
        }
        $generalsectionsummary = $renderer->format_summary_text($generalsection);
        $export->generalsectionsummary = $generalsectionsummary;
        $export->generalsection['remuicourseimage'] = $imgurl;
        // For Completion percentage.
        $export->generalsection['activities'] = $this->get_activities_details($generalsection);
        $completion = new \completion_info($this->course);
        $percentage = progress::get_course_progress_percentage($this->course);
        if (!is_null($percentage)) {
            $percentage = floor($percentage);
            $export->generalsection['percentage'] = $percentage;
        }

        // For right side.
        $rightside = $renderer->section_right_content($generalsection, $this->course, false);
        $export->generalsection['rightside'] = $rightside;
        $displayteacher = $this->settings['remuiteacherdisplay'];
        if ($displayteacher == 1) {
            $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
            $teachers = null;
            if (!empty($role)) {
                $teachers = get_role_users($role->id, $coursecontext);
            }
            // For displaying teachers.
            if (!empty($teachers)) {
                $count = 1;
                $export->generalsection['teachers'] = $teachers;
                $export->generalsection['teachers']['teacherimg'] =
                '<div class="teacher-label"><span>'
                .get_string('teachers', 'format_remuiformat').
                '</span></div>
                <div class="carousel slide" data-ride="carousel" id="teachers-carousel">
                <div class="carousel-inner text-center">';

                foreach ($teachers as $teacher) {
                    if ($count % 2 == 0) {
                        // Skip even members.
                        $count += 1;
                        next($teachers);
                        continue;
                    }
                    $teacher->imagealt = $teacher->firstname . ' ' . $teacher->lastname;
                    if ($count == 1) {
                        $export->generalsection['teachers']['teacherimg'] .=
                        '<div class="carousel-item active"><div class="teacher-img-container">'
                        . $OUTPUT->user_picture($teacher);

                    } else {
                        $export->generalsection['teachers']['teacherimg'] .=
                        '<div class="carousel-item"><div class="teacher-img-container">'. $OUTPUT->user_picture($teacher);
                    }
                    $nextteacher = next($teachers);
                    if (false != $nextteacher) {
                        $nextteacher->imagealt = $nextteacher->firstname . ' ' . $nextteacher->lastname;
                        $export->generalsection['teachers']['teacherimg'] .= $OUTPUT->user_picture($nextteacher);
                    }
                    $export->generalsection['teachers']['teacherimg'] .= '</div></div>';
                    $count += 1;
                }
                if (count($teachers) > 2) {
                    $export->generalsection['teachers']['teacherimg'] .=
                    '</div><a class="carousel-control-prev" href="#teachers-carousel" role="button" data-slide="prev">
                            <i class="fa fa-chevron-left"></i>
                            <span class="sr-only">'
                            .get_string('previous', 'format_remuiformat').
                            '</span>
                        </a>
                        <a class="carousel-control-next" href="#teachers-carousel" role="button" data-slide="next">
                            <i class="fa fa-chevron-right"></i>
                            <span class="sr-only">'
                            .get_string('next', 'format_remuiformat').
                            '</span>
                        </a></div>';
                } else {
                    $export->generalsection['teachers']['teacherimg'] .= '</div></div>';
                }
            }
        }

        // Add new activity.
        $export->generalsection['addnewactivity'] = $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
        $export->sections = $this->get_all_section_data($renderer, $editing, $rformat);
    }

    private function get_all_section_data($renderer, $editing, $rformat) {
        $modinfo = get_fast_modinfo($this->course);
        $coursecontext = context_course::instance($this->course->id);
        $startfrom = 1;
        $end = $this->courseformat->get_last_section_number();
        $sections = array();
        for ($section = $startfrom; $section <= $end; $section++) {
            $sectiondetails = new \stdClass();
            $sectiondetails->index = $section;

            // Get current section info.
            $currentsection = $modinfo->get_section_info($section);

            // Check if the user has permission to view this section or not.
            $showsection = $currentsection->uservisible ||
                    ($currentsection->visible && !$currentsection->available && !empty($currentsection->availableinfo)) ||
                    (!$currentsection->visible && !$this->course->hiddensections);
            if (!$showsection) {
                continue;
            }

            // Get the title of the section.
            if (!$editing) {
                $sectiondetails->title = $this->courseformat->get_section_name($currentsection);
            } else {
                $sectiondetails->title = $renderer->section_title($currentsection, $this->course);
                $sectiondetails->editsectionurl = new \moodle_url('editsection.php', array('id' => $currentsection->id));
                $sectiondetails->leftside = $renderer->section_left_content($currentsection, $this->course, false);
                $sectiondetails->optionmenu = $renderer->section_right_content($currentsection, $this->course, false);
            }

            // Get the section view url.
            $singlepageurl = '';
            if ($this->course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $singlepageurl = $this->courseformat->get_view_url($section)->out(true);
            }
            $sectiondetails->singlepageurl = $singlepageurl;
            $sectiontitlesummarymaxlength = $this->settings['sectiontitlesummarymaxlength'];
            if (!empty($currentsection->summary)) {
                $sectiondetails->summary = $renderer->format_summary_text($currentsection);
            }

            $sectiondetails->hiddenmessage = $renderer->section_availability_message($currentsection, has_capability(
                'moodle/course:viewhiddensections',
                $coursecontext
            ));
            if ($sectiondetails->hiddenmessage != "") {
                $sectiondetails->hidden = 1;
            }
            $extradetails = $this->get_section_module_info($currentsection, $this->course, null);

            if ($rformat == REMUI_CARD_FORMAT) {
                $sectiondetails->activityinfo = $extradetails['activityinfo'];
                $sectiondetails->progressinfo = $extradetails['progressinfo'];
                // Set Marker.
                if ($this->course->marker == $section) {
                    $sectiondetails->highlighted = 1;
                }
                $sections[] = $sectiondetails;

            } else if ($rformat == REMUI_LIST_FORMAT) {
                $sectiondetails->activityinfostring = implode(', ', $extradetails['activityinfo']);
                $sectiondetails->sectionactivities = $this->courserenderer->course_section_cm_list(
                    $this->course, $currentsection, 0
                );
                $sectiondetails->sectionactivities .= $this->courserenderer->course_section_add_cm_control(
                    $this->course, $currentsection->section, 0
                );

                // Set Marker.
                if ($this->course->marker == $section) {
                    $sectiondetails->highlighted = 1;
                }

                $sections[] = $sectiondetails;
            }
        }

        // Add new sections button.
        if ($editing) {
            $temp = $renderer->change_number_sections_context($this->course, 0);
            if (!empty($temp)) {
                $sections[] = $temp;
            }
        }

        return $sections;
    }

    private function get_section_module_info($section, $course, $mods) {
        $modinfo = get_fast_modinfo($course);
        $output = array(
            "activityinfo" => array(),
            "progressinfo" => array()
        );
        if (empty($modinfo->sections[$section->section])) {
            return $output;
        }
        // Generate array with count of activities in this section.
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new \completion_info($course);
        foreach ($modinfo->sections[$section->section] as $cmid) {
            $thismod = $modinfo->cms[$cmid];
            if ($thismod->modname == 'label') {
                // Labels are special (not interesting for students)!
                continue;
            }

            if ($thismod->uservisible) {
                if (isset($sectionmods[$thismod->modname])) {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modplural;
                    $sectionmods[$thismod->modname]['count']++;
                } else {
                    $sectionmods[$thismod->modname]['name'] = $thismod->modfullname;
                    $sectionmods[$thismod->modname]['count'] = 1;
                }
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                            $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                        $complete++;
                    }
                }
            }
        }
        foreach ($sectionmods as $mod) {
            $output['activityinfo'][] = $mod['name'].': '.$mod['count'];
        }
        if ($total > 0) {
            $pinfo = new \stdClass();
            $pinfo->percentage = round(($complete / $total) * 100, 0);
            $pinfo->completed = ($complete == $total) ? "completed" : "";
            $pinfo->progress = $complete." / ".$total;
            $output['progressinfo'][] = $pinfo;
        }
        return $output;
    }

    private function get_activities_details($section, $displayoptions = array()) {
        global $PAGE;
        $modinfo = get_fast_modinfo($this->course);
        $output = array();
        $completioninfo = new \completion_info($this->course);
        if (!empty($modinfo->sections[$section->section])) {
            $count = 1;
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                if (!$mod->is_visible_on_course_page()) {
                    continue;
                }
                $completiondata = $completioninfo->get_data($mod, true);
                $activitydetails = new \stdClass();
                $activitydetails->index = $count;
                $activitydetails->id = $mod->id;
                if ($completioninfo->is_enabled()) {
                    $activitydetails->completion = $this->courserenderer->course_section_cm_completion(
                        $this->course,   $completioninfo, $mod, $displayoptions
                    );
                }
                $activitydetails->viewurl = $mod->url;
                $activitydetails->title = $this->courserenderer->course_section_cm_name($mod, $displayoptions);
                if ($mod->modname == 'label') {
                    $activitydetails->title = $this->courserenderer->course_section_cm_text($mod, $displayoptions);
                }
                $activitydetails->title .= $mod->afterlink;
                $activitydetails->modulename = $mod->modname;
                $activitydetails->summary = $this->courserenderer->course_section_cm_text($mod, $displayoptions);
                $activitydetails->summary = $this->modstats->get_formatted_summary(
                    $activitydetails->summary,
                    $this->settings
                );
                $activitydetails->completed = $completiondata->completionstate;
                $modicons = '';
                if ($mod->visible == 0) {
                    $activitydetails->hidden = 1;
                }
                $availstatus = $this->courserenderer->course_section_cm_availability($mod, $modnumber);
                if ($availstatus != "") {
                    $activitydetails->availstatus = $availstatus;
                }
                if ($PAGE->user_is_editing()) {
                    $editactions = course_get_cm_edit_actions($mod, $mod->indent, $section->section);
                    $modicons .= ' '. $this->courserenderer->course_section_cm_edit_actions($editactions, $mod, 0);
                    $modicons .= $mod->afterediticons;
                    $activitydetails->modicons = $modicons;
                }
                $output[] = $activitydetails;
                $count++;
            }
        }
        return $output;
    }

    public function display_file($data) {
        global $DB, $CFG, $OUTPUT;
        $itemid = $data;
        $filedata = $DB->get_records('files', array('itemid' => $itemid));
        $tempdata = array();
        foreach ($filedata as $key => $value) {
            if ($value->filesize > 0 && $value->filearea == 'remuicourseimage_filearea') {
                $tempdata = $value;
            }
        }
        $fs = get_file_storage();
        if (!empty($tempdata)) {
            $files = $fs->get_area_files(
                $tempdata->contextid,
                'format_remuiformat',
                'remuicourseimage_filearea',
                $itemid
            );
            $url = '';
            foreach ($files as $key => $file) {
                $file->portfoliobutton = '';

                $path = '/'.
                        $tempdata->contextid.
                        '/'.
                        'format_remuiformat'.
                        '/'.
                        'remuicourseimage_filearea'.
                        '/'.
                        $file->get_itemid().
                        $file->get_filepath().
                        $file->get_filename();
                $url = file_encode_url("$CFG->wwwroot/pluginfile.php", $path, true);
            }
            return $url;
        }
        return '';
    }

}
