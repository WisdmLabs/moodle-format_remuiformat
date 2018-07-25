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

namespace format_remui_format\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;
use context_course;
use core_completion\progress;

require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/renderer.php');
require_once($CFG->dirroot.'/course/format/remui_format/classes/mod_stats.php');
require_once($CFG->dirroot.'/course/format/remui_format/classes/settings_controller.php');
require_once($CFG->dirroot.'/course/format/remui_format/lib.php');
// require_once($CFG->dirroot.'/course/renderer.php');
/**
 * This file contains the definition for the renderable classes for the sections page.
 *
 * @package   format_remui_format
 * @copyright  2018 Wisdmlabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_remui_format_section implements renderable, templatable
{

    private $course;
    private $courseformat;
    private $courserenderer;
    private $modstats;
    private $settings;

    /**
     * Constructor
     */
    public function __construct($course)
    {
        global $PAGE;
        $this->courseformat = course_get_format($course);
        $this->course = $this->courseformat->get_course();
        $this->courserenderer = new \core_course_renderer($PAGE, 'format_remui_format');
        $this->modstats = \format_remui_format\ModStats::getinstance();
        $this->settings = $this->courseformat->get_settings();
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * question mustache template.
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output)
    {
        global $USER, $PAGE, $DB, $OUTPUT, $CFG;
        require_once($CFG->libdir. '/coursecatlib.php');

        $chelper = new \coursecat_helper();
        $export = new \stdClass();
        $renderer = $PAGE->get_renderer('format_remui_format');

        // Get necessary values required to display the UI.
        $editing = $PAGE->user_is_editing();
        $coursecontext = context_course::instance($this->course->id);
        $export->coursefullname = $this->course->fullname;
        $coursesummary = $this->course->summary;
        // $coursesummary = strip_tags($chelper->get_course_formatted_summary($this->course, array('overflowdiv' => false, 'noclean' => false, 'para' => false)));
        $coursesummary = strlen($coursesummary) > 300 ? substr($coursesummary, 0, 300)."..." : $coursesummary;
        $export->coursesummary = $coursesummary;
        $modinfo = get_fast_modinfo($this->course);
        $sections = $modinfo->get_section_info_all();
        $export->editing = $editing;
        // $courseconfig = get_config('moodlecourse');
        // $course = $this->course;
        // var_dump($course->has_summary());
        // exit;
        // var_dump(get_config('format_remui_format', 'remuicourseformat'));
        // exit;
        $export->courseformat = get_config('format_remui_format', 'defaultcourseformat');
        // var_dump($export->courseformat);
        // exit;
        // Setting up data for General Section.
        $generalsection = $modinfo->get_section_info(0);
        if ($generalsection) {
            if (!$editing) {
                $export->generalsection['title'] = $this->courseformat->get_section_name($generalsection);
                $export->generalsection['availability'] = $renderer->section_availability($generalsection);
                $export->generalsection['summary'] = $renderer->format_summary_text($generalsection);
            } else {
                $export->generalsection['title'] = $renderer->section_title($generalsection, $this->course);
                $export->generalsection['availability'] = $renderer->section_availability($generalsection);
                $export->generalsection['summary'] = $renderer->format_summary_text($generalsection);
                $export->generalsection['editsetionurl'] = new \moodle_url('editsection.php', array('id' => $generalsection->id));
                $export->generalsection['optionmenu'] = $renderer->section_right_content($generalsection, $this->course, false);

            }
            // Course Modules.
            $rformat = $this->settings['remuicourseformat'];
            switch ($rformat) {
                case REMUI_CARD_FORMAT:
                    $export->generalsection['activities'] = $this->courserenderer->course_section_cm_list($this->course, $generalsection, 0);
                    $export->generalsection['activities'] .= $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
                    break;
                case REMUI_LIST_FORMAT:
                    // $file_record = $DB->get_record_sql('SELECT * FROM {files} WHERE itemid = ? AND filename NOT LIKE ?', array($this->settings['remuicourseimage'], '.'));
                    
                    // $fs = get_file_storage();
                    // $file = $fs->get_file($file_record->contextid, $file_record->component, $file_record->filearea, $this->settings['remuicourseimage'], $file_record->filepath, $file_record->filename);
                    // $isimage = $file->is_valid_image();
                    // $courseimage = file_encode_url(
                    //     "$CFG->wwwroot/pluginfile.php",
                    //     '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    //     $file->get_filearea(). $file->get_filepath(). $file->get_filename(),
                    //     !$isimage
                    // );
                    
                    // $export->generalsection['remuicourseimage'] = $courseimage;
                    // exit;

                    // For Completion percentage
                    $export->generalsection['activities'] = $this->get_activities_details($generalsection);
                    // var_dump($this->courserenderer->course_section_add_cm_control($this->course, 0, 0));
                    // exit;
                    // $export->generalsection['activities'] .= $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);
                    $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
                    // $context = get_context_instance($coursecontext, $this->course->id);
                    $teachers = get_role_users($role->id, $coursecontext);
                    if (empty($teachers)) {
                        break;
                    }
                    $completion = new \completion_info($this->course);
                    // if (has_capability('moodle/course:update', $coursecontext)) {
                    //     continue;
                    // }
                    // First, let's make sure completion is enabled.
                    if (!$completion->is_enabled()) {
                        continue;
                    }
                    $percentage = progress::get_course_progress_percentage($this->course);
                    if (!is_null($percentage)) {
                        $percentage = floor($percentage);
                        $export->generalsection['percentage'] = $percentage;
                    }

                    // For right side
                    // var_dump(get_class_methods('core_course_renderer'));
                    // exit;
                    $rightside = $renderer->section_right_content($generalsection, $this->course, false);
                    $export->generalsection['rightside'] = $rightside;
                    // $export->generalsection['leftcontent'] = '<div class="left side">' . $renderer->section_left_content($generalsection, $this->course, false) . '</div>';
                    // For displaying teachers
                    $count = 1;
                    $export->generalsection['teachers'] = $teachers;
                    $export->generalsection['teachers']['teacherimg'] = '<div class="teacher-label"><span>Teachers</span></div><div class="carousel slide" data-ride="carousel" id="teachersCarousel">
                        <div class="carousel-inner">';


                    // Add new activity
                    $export->generalsection['addnewactivity'] = $this->courserenderer->course_section_add_cm_control($this->course, 0, 0);

                    // var_dump($teachers);
                    // exit;
                    foreach ($teachers as $teacher) {
                        if ($count % 2 == 0) { // skip even members
                            $count += 1;
                            next($teachers);
                            continue;
                        }
                        // var_dump($teacher);
                        $teacher->imagealt = $teacher->firstname . ' ' . $teacher->lastname;
                        if ($count == 1) {
                            $export->generalsection['teachers']['teacherimg'] .= '<div class="carousel-item active">' . $OUTPUT->user_picture($teacher);

                        } else {
                            $export->generalsection['teachers']['teacherimg'] .= '<div class="carousel-item">'. $OUTPUT->user_picture($teacher);
                        }
                        // var_dump(current($teachers));
                        $nextteacher = next($teachers);
                    // var_dump(next($teachers));
                        if (false != $nextteacher) {
                            // var_dump($nextteacher);
                        
                            // var_dump($nextteacher);
                            // exit;
                            $nextteacher->imagealt = $nextteacher->firstname . ' ' . $nextteacher->lastname;
                            $export->generalsection['teachers']['teacherimg'] .= $OUTPUT->user_picture($nextteacher);
                        }
                        $export->generalsection['teachers']['teacherimg'] .= '</div>';
                         // $export->generalsection['teachers']['teacherimg'] .= $OUTPUT->user_picture($teacher);
                        
                        $count += 1;
                    }
                    $export->generalsection['teachers']['teacherimg'] .= '</div><a class="carousel-control-prev" href="#teachersCarousel" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#teachersCarousel" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a></div>';
                    // var_dump($export->generalsection['teachers']['teacherimg']);
                    // exit;
//                      $export->generalsection['teachers']['teacherimg'] .= '</div>
//         <a class="left carousel-control" href="#theCarousel" data-slide="prev"><i class="glyphicon glyphicon-chevron-left"></i></a>
//         <a class="right carousel-control" href="#theCarousel" data-slide="next"><i class="glyphicon glyphicon-chevron-right"></i></a>
//       </div>
//     </div>
//   </div>
// </div>'; 
                    break;
                default:
                    # code...
                    break;
            }
            // var_dump($export->generalsection['activities']);
            // exit;
            
        }

        $startfrom = 1;
        $end = $this->courseformat->get_last_section_number();
        $sections = array();
        // Setting up data for remianing sections.
        for ($section = $startfrom; $section <= $end; $section++) {
            $sectiondetails = new \stdClass();
            $sectiondetails->index = $section;
            // Get current section info.
            $currentsection = $modinfo->get_section_info($section);
            $showsection = $currentsection->uservisible ||
                    ($currentsection->visible && !$currentsection->available && !empty($currentsection->availableinfo)) ||
                    (!$thissection->visible && !$course->hiddensections);
            if (!$showsection) {
                continue;
            }

            // Get the title of the section.
            if (!$editing) {
                $sectiondetails->title = $this->courseformat->get_section_name($currentsection);
            } else {
                $sectiondetails->title = $renderer->section_title($currentsection, $this->course);
            }

            // Get the section view url.
            $singlepageurl = '';
            if ($this->course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $singlepageurl = $this->courseformat->get_view_url($section)->out(true);
            }
            $sectiondetails->singlepageurl = $singlepageurl;
            $sectiondetails->summary = $this->modstats->get_formatted_summary(strip_tags($currentsection->summary), $this->settings);

            if ($editing) {
                $sectiondetails->editsectionurl = new \moodle_url('editsection.php', array('id' => $currentsection->id));

                $sectiondetails->optionmenu = $renderer->section_right_content($currentsection, $this->course, false);
            }
            $sectiondetails->hiddenmessage = $renderer->section_availability_message($currentsection, has_capability(
                'moodle/course:viewhiddensections',
                $coursecontext
            ));
            if ($sectiondetails->hiddenmessage != "") {
                $sectiondetails->hidden = 1;
            }
            $extradetails = $this->get_section_module_info($currentsection, $this->course, null);
            
            switch ($rformat) {
                case REMUI_CARD_FORMAT:
                    // var_dump($extradetails);
                    // exit;
                    $sectiondetails->activityinfo = $extradetails['activityinfo'];
                    $sectiondetails->progressinfo = $extradetails['progressinfo'];
                    $sections[] = $sectiondetails;
                    break;
                case REMUI_LIST_FORMAT:
                    $sectiondetails->activityinfostring = implode(', ', $extradetails['activityinfo']);
                    $sectiondetails->sectionactivities = $this->get_activities_details($currentsection);
                    $sections[] = $sectiondetails;
                    break;
            }
            // var_dump($sectiondetails->activityinfostring);
            // exit;
            // var_dump($sections[0]->sectionactivities);
            // exit;
        }
        $export->sections = $sections;
        // var_dump($export->sections);
        // exit;
        // $rformat = $this->settings['remuicourseformat'];
        // if(empty($rformat)) {
        //     $export->remuicourseformatcard = true;
        //     return  $export;
        // }
        // switch ($rformat) {
        //     case REMUI_CARD_FORMAT:
        //         $export->remuicourseformatcard = true;
        //         break;
        //     case REMUI_LIST_FORMAT:
        //         $export->remuicourseformatlist = true;
        //         break;
        //     default:
        //         $export->remuicourseformatcard = true;
        //         break;
        // }

        return  $export;
    }

    private function get_section_module_info($section, $course, $mods)
    {
        $modinfo = get_fast_modinfo($course);
        $output = array(
            "activityinfo" => array(),
            "progressinfo" => array()
        );
        if (empty($modinfo->sections[$section->section])) {
            return $output;
        }
        // Generate array with count of activities in this section:
        $sectionmods = array();
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new \completion_info($course);
        foreach ($modinfo->sections[$section->section] as $cmid) {
            $thismod = $modinfo->cms[$cmid];
            // var_dump($thismod);
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

    private function get_activities_details($section, $displayoptions = array())
    {
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
                $activitydetails->completion = $this->courserenderer->course_section_cm_completion($this->course, $completioninfo, $mod, $displayoptions);
                $activitydetails->viewurl = $mod->url;
                $activitydetails->title = $this->courserenderer->course_section_cm_name($mod, $displayoptions);
                $activitydetails->title .= $mod->afterlink;
                $activitydetails->modulename = $mod->modname;
                $activitydetails->summary = $this->courserenderer->course_section_cm_text($mod, $displayoptions);
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
                    $modicons .= ' '. $this->courserenderer->course_section_cm_edit_actions($editactions, $mod, $section->section);
                    // var_dump($modicons);
                    $modicons .= $mod->afterediticons;
                    // var_dump($mod->afterediticons);
                    // exit;
                    $activitydetails->modicons = $modicons;
                }
                $output[] = $activitydetails;
                $count++;
            }
        }
        return $output;
    }
}
