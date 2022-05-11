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
use context_course;
use cm_info;
use core_courseformat\output\local\content\section;

require_once($CFG->dirroot.'/course/format/remuiformat/classes/mod_stats.php');

/**
 * Course format common data trait class
 */
class course_format_data_common_trait {
    /**
     * Current class instance
     * @var course_format_data_common_trait
     */
    protected static $instance;
    /**
     * Plugin config
     * @var string
     */
    private $_plugin_config;
    /**
     * Activity statistic
     * @var \format_remuiformat\ModStats
     */
    private $modstats;

    /**
     * Constructor
     */
    private function __construct() {
        $this->plugin_config = "format_remuiformat";
        $this->modstats = \format_remuiformat\ModStats::getinstance();
    }
    /**
     * Singleton Implementation.
     * @return course_format_data_common_trait Instance
     */
    public static function getinstance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Display image file
     * @param  context $context Course context
     * @param  int $itemid File item id
     * @return string      Image file url
     */
    public function display_file($context, $itemid) {
        if (empty($itemid)) {
            return '';
        }

        $files = get_file_storage()->get_area_files(
            $context->id, 'format_remuiformat', 'remuicourseimage_filearea',
            $itemid, 'itemid, filepath, filename', false);

        if (empty($files)) {
            return '';
        }

        $file = current($files);
        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(), false);
    }

    /**
     * Check for activity completion data.
     * @param stdClass         $course          Course object
     * @param \completion_info $completioninfo  Completion info object of course
     * @param stdClass         $activitydetails Activity details object
     * @param \cm_info         $mod             Course module info object
     * @param course_renderer  $courserenderer  Base renderer
     * @param array            $displayoptions  Display options array
     */
    public function activity_completion($course, $completioninfo, $activitydetails, $mod, $courserenderer, $displayoptions) {
        global $CFG, $USER;
        if (!$completioninfo->is_enabled()) {
            return $activitydetails;
        }
        if ($CFG->branch < 311) {
            $activitydetails->completion = $courserenderer->course_section_cm_completion(
                $course, $completioninfo, $mod, $displayoptions
            );
            // Check if completion is enabled. Set manual completion only if it not automatic.
            $activitydetails->manualcompletion = true;
            return $activitydetails;
        }
        if ($course->showcompletionconditions == COMPLETION_SHOW_CONDITIONS) {
            // Show the activity information output component.
            $cmcompletion = \core_completion\cm_completion_details::get_instance($mod, $USER->id);
            $activitydetails->completion = $courserenderer->activity_information(
                $mod,
                $cmcompletion,
                []
            );
            // Check if completion is enabled. Set manual completion only if it not automatic.
            if ($cmcompletion->has_completion() && $cmcompletion->is_automatic() != true) {
                $activitydetails->manualcompletion = true;
            }
        }
        return $activitydetails;
    }

    /**
     * Get all activities for list format for specific section.
     * @param  object          $section        Current section object to get activities.
     * @param  object          $course         Current course.
     * @param  course_renderer $courserenderer Base renderer.
     * @param  array           $settings       Course Format settings.
     * @param  array           $displayoptions Display options
     * @return array                           Output
     */
    public function get_list_activities_details($section, $course, $courserenderer, $settings, $displayoptions = array()) {
        global $PAGE, $CFG, $USER;
        $modinfo = get_fast_modinfo($course);
        $output = array();
        $completioninfo = new \completion_info($course);
        if (!empty($modinfo->sections[$section->section])) {
            $count = 1;
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                $context = \context_module::instance($mod->id);
                if (!$mod->is_visible_on_course_page()) {
                    continue;
                }

                $completiondata = $completioninfo->get_data($mod, true);
                $activitydetails = new \stdClass();
                $activitydetails->index = $count;
                if (!empty($mod->indent)) {
                    $indentclasses = 'mod-indent mod-indent-'.$mod->indent;
                    if ($mod->indent > 15) {
                        $indentclasses .= ' mod-indent-huge';
                    }
                    $activitydetails->indent = $indentclasses;
                }

                $activitydetails->id = $mod->id;
                $activitydetails = $this->activity_completion(
                    $course,
                    $completioninfo,
                    $activitydetails,
                    $mod,
                    $courserenderer,
                    $displayoptions
                );
                $activitydetails->viewurl = $mod->url;
                $activitydetails->title = $this->course_section_cm_name($mod, $displayoptions);
                    $activitydetails->title .= $this->course_section_cm_text($mod, $displayoptions);
                $activitydetails->title .= $mod->afterlink;
                $activitydetails->modulename = $mod->modname;
                $activitydetails->summary = $this->course_section_cm_text($mod, $displayoptions);
                $activitydetails->summary = $this->modstats->get_formatted_summary(
                    $activitydetails->summary,
                    $settings
                );
                $activitydetails->completed = $completiondata->completionstate;
                $modicons = '';
                if ($mod->visible == 0) {
                    $activitydetails->notavailable = true;
                    if (has_capability('moodle/course:viewhiddensections', $context, $USER)) {
                        $activitydetails->hiddenfromstudents = true;
                        $activitydetails->notavailable = false;
                    }
                }
                $availstatus = $this->course_section_cm_availability($mod, $displayoptions);
                if ($availstatus != "") {
                    $activitydetails->availstatus = $availstatus;
                }
                if ($PAGE->user_is_editing()) {
                    $modicons .= ' '. $this->course_section_cm_controlmenu($mod, $section, $displayoptions);
                    $modicons .= $mod->afterediticons;
                    $activitydetails->modicons = $modicons;
                }
                $output[] = $activitydetails;
                $count++;
            }
        }
        return $output;
    }

    /**
     * Get all section details from the course.
     * @param  object          $renderer       Remuiformat renderer object.
     * @param  bool            $editing        Variable define the editing on/off state.
     * @param  int             $rformat        Current course format.
     * @param  array           $settings       Format settings
     * @param  object          $course         Course object
     * @param  format_remuiformat     $courseformat   Course format object
     * @param  course_renderer $courserenderer Course renderer
     * @return array                           Sections data
     */
    public function get_all_section_data($renderer, $editing, $rformat, $settings, $course, $courseformat, $courserenderer) {
        global $USER;
        $modinfo = get_fast_modinfo($course);
        $context = context_course::instance($course->id);
        $startfrom = 1;
        $end = $courseformat->get_last_section_number();
        $sections = array();

        for ($sectionindex = $startfrom; $sectionindex <= $end; $sectionindex++) {

            // Get current section info.
            $section = $modinfo->get_section_info($sectionindex);
            $data = new \stdClass();
            $data->index = $sectionindex;
            $data->num = $section->section;
            $data->id = $section->id;
            $data->sectionreturnid = course_get_format($course)->get_section_number();
            $data->insertafter = false;

            // Check if the user has permission to view this section or not.
            $showsection = $section->uservisible ||
                    ($section->visible && !$section->available && !empty($section->availableinfo)) ||
                    (!$section->visible && !$course->hiddensections);
            if (!$showsection) {
                continue;
            }

            // Get the title of the section.
            if (!$editing) {
                $data->title = $courseformat->get_section_name($section);
            } else {
                $data->title = $renderer->section_title($section, $course);
                $data->editsectionurl = new \moodle_url('editsection.php', array('id' => $section->id));
                $data->header = $this->course_section_header($course, $section);

                $data->optionmenu = $this->course_section_controlmenu($course, $section);
                $actionsectionurl = new \moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                        'insertsection' => $section->section + 1,
                        'sesskey' => sesskey(),
                        'returnurl' => course_get_url($course)
                    )
                );
                $label = html_writer::span(get_string('addnewsection', 'format_remuiformat'), 'wdmaddsection d-none d-lg-block');
                $label .= html_writer::span(
                    '<i class="fa fa-plus-circle" aria-hidden="true"></i>',
                    'wdmaddsection d-block d-lg-none'
                );

                $data->addnewsection = html_writer::link($actionsectionurl, $label,
                    array('class' => 'wdm-add-new-section btn btn-inverse')
                );
            }

            // Get the section view url.
            $singlepageurl = '';
            if ($course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $singlepageurl = $courseformat->get_view_url($sectionindex)->out(true);
            }

            $data->singlepageurl = $singlepageurl;
            $sectiontitlesummarymaxlength = $settings['sectiontitlesummarymaxlength'];
            $remuienablecardbackgroundimg = $settings['remuienablecardbackgroundimg'];
            $remuidefaultsectiontheme = $settings['remuidefaultsectiontheme'];

            $data->hiddenmessage = $this->course_section_availability($course, $section);

            if ($courseformat->is_section_current($section)) {
                $data->iscurrent = true;
                $data->highlightedlabel = get_string('highlight');
            }

            if (!$section->visible) {
                $data->ishidden = true;
                $data->notavailable = true;
                if (has_capability('moodle/course:viewhiddensections', $context, $USER)) {
                    $data->hiddenfromstudents = true;
                    $data->notavailable = false;
                }
            }

            $extradetails = $this->get_section_module_info($section, $course, null, $singlepageurl);

            if ($rformat == REMUI_CARD_FORMAT) {
                // Get the section summary.
                $data->summary = $renderer->abstract_html_contents(
                    $renderer->format_summary_text($section), $sectiontitlesummarymaxlength
                );

                // Check if background image to section card setting is enable and image exists in summary,
                // if yes then add background image to context.
                if ( $remuienablecardbackgroundimg == 1
                && $this->get_section_first_image( $section, $section->summary ) ) {
                    if ( $remuidefaultsectiontheme == 1 ) {
                        // Dark theme.
                        $remuidefaultsectionoverlay = 'rgba(0,0,0,0.45)';
                        $remuinewfontcolor = '#eaeaea';
                        $remuinewthemecolor = 'dark';
                    } else {
                        // Light theme.
                        $remuidefaultsectionoverlay = 'rgba(255,255,255,0.8)';
                        $remuinewfontcolor = '#101010';
                        $remuinewthemecolor = 'light';
                    }

                    // Get first image from section to set card card background image.
                    $imgarray = $this->get_section_first_image( $section, $section->summary );
                    $data->sectionfirstimage = $imgarray['img'];

                    // Change the overlay opacity if pattern image.
                    if ( $remuidefaultsectiontheme == 0 &&  $imgarray['pattern'] == 1) {
                        // Light theme.
                        $remuidefaultsectionoverlay = 'rgba(255, 255, 255, 0)';
                    } else if ( $remuidefaultsectiontheme == 1 &&  $imgarray['pattern'] == 1 ) {
                        // Dark theme.
                        $remuidefaultsectionoverlay = 'rgba(0, 0, 0, 0.55)';
                    }

                    $data->remuidefaultsectionoverlay = $remuidefaultsectionoverlay;
                    $data->remuinewfontcolor = $remuinewfontcolor;
                    $data->remuinewthemecolor = $remuinewthemecolor;
                }

                $data->activityinfo = $extradetails['activityinfo'];
                $data->progressinfo = $extradetails['progressinfo'];

                // Set Marker.
                if ($course->marker == $sectionindex) {
                    $data->iscurrent = true;
                    $data->highlightedlabel = get_string('highlight');
                }
                $sections[] = $data;
            } else if ($rformat == REMUI_LIST_FORMAT) {
                if (!empty($section->summary)) {
                    $data->summary = $renderer->format_summary_text($section);
                    if ($settings['coursedisplay'] == 1) {
                        $data->summary = $renderer->abstract_html_contents(
                            $data->summary, $sectiontitlesummarymaxlength
                        );
                    }
                }
                $data->activityinfostring = implode(', ', $extradetails['activityinfo']);
                $data->progressinfo = $extradetails['progressinfo'];
                $data->sectionactivities = $this->course_section_cm_list(
                    $course, $section
                );
                $data->sectionactivities .= $courserenderer->course_section_add_cm_control(
                    $course, $section->section, 0
                );

                // Set Marker.
                if ($course->marker == $sectionindex) {
                    $data->iscurrent = true;
                    $data->highlightedlabel = get_string('highlight');
                }
                $sections[] = $data;
            }
        }

        // Add new sections button.
        if ($editing) {
            $temp = $renderer->change_number_sections_context($course, 0);
            if (!empty($temp)) {
                $sections[] = $temp;
            }
        }
        return $sections;
    }

    /**
     * Get section module information
     * @param  object $section       Section object
     * @param  object $course        Course object
     * @param  array  $mods          Activity array
     * @param  string $singlepageurl Single page url
     * @return array                 Output
     */
    public function get_section_module_info($section, $course, $mods, $singlepageurl) {
        $modinfo = get_fast_modinfo($course);
        $output = array(
            "activityinfo" => array(),
            "progressinfo" => array(),
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
            $output['activityinfo'][] = $mod['count'].' '.$mod['name'];
        }
        if ($total > 0) {
            $pinfo = new \stdClass();
            $pinfo->percentage = round(($complete / $total) * 100, 0);
            $pinfo->completed = ($complete == $total) ? "completed" : "";
            if ($pinfo->percentage == 0) {
                $pinfo->progress = '<a href=' . $singlepageurl . '>' . get_string('activitystart', 'format_remuiformat') . '</a>';
            } else if ( $pinfo->percentage > 0 && $pinfo->percentage < 50 ) {
                if ($total == 1) {
                    $status = get_string('activitycompleted', 'format_remuiformat');
                } else {
                    $status = get_string('activitiescompleted', 'format_remuiformat');
                }
                $pinfo->progress = $total . $status;
                $pinfo->progress = '<a href=' . $singlepageurl . '>' . $complete . ' '
                                    . get_string('outof', 'format_remuiformat') . ' '
                                    . $total . ' ' . $status . '</a>';
            } else if ( $pinfo->percentage >= 50 && $pinfo->percentage < 100 ) {
                $total = $total - $complete;
                if ($total == 1) {
                    $status = get_string('activityremaining', 'format_remuiformat');
                } else {
                    $status = get_string('activitiesremaining', 'format_remuiformat');
                }
                $pinfo->progress = '<a href=' . $singlepageurl . '>' . $total . ' ' . $status . '</a>';
            } else if ( $pinfo->percentage == 100 ) {
                $pinfo->progress = get_string('allactivitiescompleted', 'format_remuiformat');
            }
            $output['progressinfo'][] = $pinfo;
        }
        return $output;
    }

    /**
     * Get the course pattern datauri to show on a course card.
     *
     * The datauri is an encoded svg that can be passed as a url.
     * @param int $id Id to use when generating the pattern
     * @return string datauri
     */
    public static function get_dummy_image_for_id($id) {
        $color = self::get_dummy_color_for_id($id);
        $pattern = new \core_geopattern();
        $pattern->setColor($color);
        $pattern->patternbyid($id);
        return $pattern->datauri();
    }

    /**
     * Get the course color to show on a course card.
     *
     * @param int $id Id to use when generating the color.
     * @return string hex color code.
     */
    public static function get_dummy_color_for_id($id) {
        // The colour palette is hardcoded for now. It would make sense to combine it with theme settings.
        $basecolors = [
            '#81ecec',
            '#74b9ff',
            '#a29bfe',
            '#dfe6e9',
            '#00b894',
            '#0984e3',
            '#b2bec3',
            '#fdcb6e',
            '#fd79a8',
            '#6c5ce7'
        ];
        $color = $basecolors[$id % 10];
        return $color;
    }

    /**
     * Get last viewed activity from logstore_standard_log.
     *
     * @param  Integer    $course Course id
     * @return Bool|Array         False if lastviewed activity does not exists else activity
     */
    public function get_activity_to_resume_from_log($course) {
        global $USER, $DB;

        $lastviewed = $DB->get_records('logstore_standard_log',
            array('action' => 'viewed',
                'target' => 'course_module',
                'crud' => 'r',
                'userid' => $USER->id,
                'courseid' => $course->id,
                'origin' => 'web'
            ),
            'timecreated desc',
            '*',
            0,
            1
        );

        if (empty($lastviewed)) {
            return false;
        }

        return (object)['cm' => end($lastviewed)->contextinstanceid];

    }

    /**
     * Fetches the last viewed activity from the database table mdl_logstore_standard_log.
     *
     * @param  object $course Course ID.
     * @return string         Last viewed activity.
     */
    public function get_activity_to_resume($course) {
        global $USER, $DB;

        // Fetch last viewed from remuiformat_course_visits table.
        $lastviewed = $DB->get_record('remuiformat_course_visits',
            array(
                'course' => $course->id,
                'user' => $USER->id
            )
        );

        // Fetch last viewed from log if not record in remuiformat_course_visits.
        // If no record found in log then return empty string.
        if (empty($lastviewed)) {
            $lastviewed = $this->get_activity_to_resume_from_log($course);
            if ($lastviewed === false) {
                return '';
            }
        }

        // Get all activities.
        $modinfo = get_fast_modinfo($course);

        // Check if activity record exists.
        if (!$mod = $modinfo->cms[$lastviewed->cm]) {
            return '';
        }

        // Check if activity url is set.
        if (empty($mod->url)) {
            return '';
        }

        // Return activity url.
        return $mod->url->out();
    }

    /**
     * Get the image from section.
     * @param  object $section     Section object
     * @param  string $summaryhtml Summary html
     * @return array               Image array
     */
    public function get_section_first_image($section, $summaryhtml) {
        $imgarray = array();
        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($summaryhtml, 'pluginfile.php',
           $context->id, 'course', 'section', $section->id);
        $image = '';
        if ( !empty($summarytext) ) {
            $image = $this->extract_first_image($summarytext);
        }
        if ($image) {
            $imagesrc = 'url(' . $image['src'] . ')';
            $imgarray['img'] = $imagesrc;
            $imgarray['pattern'] = 0;
        } else {
            // @codingStandardsIgnoreStart
            $imgarray['img'] = "linear-gradient(324deg, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 85%),
            url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200' viewBox='0 0 160 80'%3E%3Cg fill='%23e5e5e5' %3E%3Cpolygon points='0 10 0 0 10 0'/%3E%3Cpolygon points='0 40 0 30 10 30'/%3E%3Cpolygon points='0 30 0 20 10 20'/%3E%3Cpolygon points='0 70 0 60 10 60'/%3E%3Cpolygon points='0 80 0 70 10 70'/%3E%3Cpolygon points='50 80 50 70 60 70'/%3E%3Cpolygon points='10 20 10 10 20 10'/%3E%3Cpolygon points='10 40 10 30 20 30'/%3E%3Cpolygon points='20 10 20 0 30 0'/%3E%3Cpolygon points='10 10 10 0 20 0'/%3E%3Cpolygon points='30 20 30 10 40 10'/%3E%3Cpolygon points='20 20 20 40 40 20'/%3E%3Cpolygon points='40 10 40 0 50 0'/%3E%3Cpolygon points='40 20 40 10 50 10'/%3E%3Cpolygon points='40 40 40 30 50 30'/%3E%3Cpolygon points='30 40 30 30 40 30'/%3E%3Cpolygon points='40 60 40 50 50 50'/%3E%3Cpolygon points='50 30 50 20 60 20'/%3E%3Cpolygon points='40 60 40 80 60 60'/%3E%3Cpolygon points='50 40 50 60 70 40'/%3E%3Cpolygon points='60 0 60 20 80 0'/%3E%3Cpolygon points='70 30 70 20 80 20'/%3E%3Cpolygon points='70 40 70 30 80 30'/%3E%3Cpolygon points='60 60 60 80 80 60'/%3E%3Cpolygon points='80 10 80 0 90 0'/%3E%3Cpolygon points='70 40 70 60 90 40'/%3E%3Cpolygon points='80 60 80 50 90 50'/%3E%3Cpolygon points='60 30 60 20 70 20'/%3E%3Cpolygon points='80 70 80 80 90 80 100 70'/%3E%3Cpolygon points='80 10 80 40 110 10'/%3E%3Cpolygon points='110 40 110 30 120 30'/%3E%3Cpolygon points='90 40 90 70 120 40'/%3E%3Cpolygon points='10 50 10 80 40 50'/%3E%3Cpolygon points='110 60 110 50 120 50'/%3E%3Cpolygon points='100 60 100 80 120 60'/%3E%3Cpolygon points='110 0 110 20 130 0'/%3E%3Cpolygon points='120 30 120 20 130 20'/%3E%3Cpolygon points='130 10 130 0 140 0'/%3E%3Cpolygon points='130 30 130 20 140 20'/%3E%3Cpolygon points='120 40 120 30 130 30'/%3E%3Cpolygon points='130 50 130 40 140 40'/%3E%3Cpolygon points='120 50 120 70 140 50'/%3E%3Cpolygon points='110 70 110 80 130 80 140 70'/%3E%3Cpolygon points='140 10 140 0 150 0'/%3E%3Cpolygon points='140 20 140 10 150 10'/%3E%3Cpolygon points='140 40 140 30 150 30'/%3E%3Cpolygon points='140 50 140 40 150 40'/%3E%3Cpolygon points='140 70 140 60 150 60'/%3E%3Cpolygon points='150 20 150 40 160 30 160 20'/%3E%3Cpolygon points='150 60 150 50 160 50'/%3E%3Cpolygon points='140 70 140 80 150 80 160 70'/%3E%3C/g%3E%3C/svg%3E\")";
            // @codingStandardsIgnoreEnd
            $imgarray['pattern'] = 1;
        }
        return $imgarray;
    }

    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode.
     *
     *
     * @param int|object $course         Course or id of the course
     * @param object     $section        the section info
     * @param mixed      $displayoptions optional extra display options
     * @return string
     */
    public function course_section_cm_list($course, $section, $displayoptions = []) {
        global $PAGE;

        $format = course_get_format($course);

        $cmlistclass = $format->get_output_classname('content\\section\\cmlist');
        $cmlist = new $cmlistclass(
            $format,
            $section,
            $displayoptions,
        );

        $renderer = $format->get_renderer($PAGE);
        return $renderer->render($cmlist);
    }

    /**
     * Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Note, that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name(cm_info $mod, $displayoptions = array()) {
        global $PAGE, $OUTPUT;

        if (!$mod->is_visible_on_course_page() || !$mod->url) {
            // Nothing to be displayed to the user.
            return '';
        }

        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);
        $groupinglabel = $mod->get_grouping_label($textclasses);

        // Render element that allows to edit activity name inline.
        $format = course_get_format($mod->course);
        $cmnameclass = $format->get_output_classname('content\\cm\\cmname');
        // Mod inplace name editable.
        $cmname = new $cmnameclass(
            $format,
            $mod->get_section_info(),
            $mod,
            $PAGE->user_is_editing(),
            $displayoptions
        );

        $data = $cmname->export_for_template($OUTPUT);

        return $OUTPUT->render_from_template('core/inplace_editable', $data) .
            $groupinglabel;
    }

    /**
     * Returns the CSS classes for the activity name/content
     *
     * For items which are hidden, unavailable or stealth but should be displayed
     * to current user ($mod->is_visible_on_course_page()), we show those as dimmed.
     * Students will also see as dimmed activities names that are not yet available
     * but should still be displayed (without link) with availability info.
     *
     * @param cm_info $mod
     * @return array array of two elements ($linkclasses, $textclasses)
     */
    protected function course_section_cm_classes(cm_info $mod) {

        $format = course_get_format($mod->course);

        $cmclass = $format->get_output_classname('content\\cm');
        $cmoutput = new $cmclass(
            $format,
            $mod->get_section_info(),
            $mod,
        );
        return [
            $cmoutput->get_link_classes(),
            $cmoutput->get_text_classes(),
        ];
    }

    /**
     * Renders html to display the module content on the course page (i.e. text of the labels)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_text(cm_info $mod, $displayoptions = array()) {

        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            // Nothing to be displayed to the user.
            return $output;
        }
        $content = $mod->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);
        if ($mod->url && $mod->uservisible) {
            if ($content) {
                // If specified, display extra content after link.
                $output = html_writer::tag('div', $content, array('class' =>
                        trim('contentafterlink ' . $textclasses)));
            }
        } else {
            $groupinglabel = $mod->get_grouping_label($textclasses);

            // No link, so display only content.
            $output = html_writer::tag('div', $content . $groupinglabel,
                    array('class' => 'contentwithoutlink ' . $textclasses));
        }
        return $output;
    }

    /**
     * Renders HTML to show course module availability information (for someone who isn't allowed
     * to see the activity itself, or for staff)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_availability(cm_info $mod, $displayoptions = array()) {
        global $PAGE;

        $format = course_get_format($mod->course);

        $availabilityclass = $format->get_output_classname('content\\cm\\availability');
        $availability = new $availabilityclass(
            $format,
            $mod->get_section_info(),
            $mod,
            $displayoptions
        );

        $renderer = $format->get_renderer($PAGE);
        $data = $availability->export_for_template($renderer);
        return $renderer->render($availability);
    }

    /**
     * Renders HTML to display controlmenu for course section
     *
     * @param cm_info $mod            Course module
     * @param object  $section        Section object
     * @param array   $displayoptions Display options
     *
     * @return string HTML to output.
     */

    public function course_section_cm_controlmenu(cm_info $mod, $section, $displayoptions = array()) {
        global $PAGE;

        $format = course_get_format($mod->course);

        $controlmenuclass = $format->get_output_classname('content\\cm\\controlmenu');
        // Edit actions.
        $controlmenu = new $controlmenuclass(
            $format,
            $section,
            $mod,
            $displayoptions
        );

        $renderer = $format->get_renderer($PAGE);
        return $renderer->render($controlmenu);
    }

    /**
     * Renders HTML to display controlmenu for course section
     *
     * @param int|object $course  Course object or id
     * @param object     $section Section object
     *
     * @return string HTML to output.
     */
    public function course_section_header($course, $section) {
        global $PAGE;

        $format = course_get_format($course);

        $headerclass = $format->get_output_classname('content\\section\\header');

        // Edit actions.
        $header = new $headerclass($format, $section);

        $renderer = $format->get_renderer($PAGE);
        return $renderer->render($header);
    }

    /**
     * Renders HTML to display controlmenu for course section
     *
     * @param int|object $course  Course object or id
     * @param object     $section Section object
     *
     * @return string HTML to output.
     */
    public function course_section_controlmenu($course, $section) {
        global $PAGE;

        $format = course_get_format($course);

        $controlmenuclass = $format->get_output_classname('content\\section\\controlmenu');

        // Edit actions.
        $controlmenu = new $controlmenuclass($format, $section);

        $renderer = $format->get_renderer($PAGE);
        return $renderer->render($controlmenu);
    }

    /**
     * Renders HTML to show course section availability information (for someone who isn't allowed
     * to see the section itself, or for staff)
     *
     * @param int|object $course  Course object or id
     * @param object     $section Section object
     *
     * @return string HTML to output.
     */
    public function course_section_availability($course, $section) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('format_remuiformat');

        $format = course_get_format($course);
        $elementclass = $format->get_output_classname('content\\section\\availability');
        $availability = new $elementclass($format, $section);

        return $renderer->render($availability);
    }

    /**
     * Extract first image from html
     *
     * @param string $html (must be well formed)
     * @return array | bool (false)
     */
    public static function extract_first_image($html) {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true); // Required for HTML5.
        $doc->loadHTML($html);
        libxml_clear_errors(); // Required for HTML5.
        $imagetags = $doc->getElementsByTagName('img');
        if ($imagetags->item(0)) {
            $src = $imagetags->item(0)->getAttribute('src');
            $alt = $imagetags->item(0)->getAttribute('alt');
            return array('src' => $src, 'alt' => $alt);
        } else {
            return false;
        }
    }
}
