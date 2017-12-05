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
 * Cards Format - A topics based format that uses card layout to diaply the content.
 *
 * @package course/format
 * @subpackage cards
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');
require_once($CFG->dirroot.'/course/format/cards/classes/settings_controller.php');
require_once($CFG->dirroot.'/course/format/cards/classes/course_module_renderer.php');

class format_cards_renderer extends format_section_renderer_base {

    protected $courseformat; // Our course format object as defined in lib.php.
    protected $coursemodulerenderer; // Our custom course module renderer.
    protected $settingcontroller;  // Our setting controller.
    private $settings;
    /**
     * Constructor method, calls the parent constructor
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courseformat = course_get_format($page->course);
        $this->settings = $this->courseformat->get_settings();
        $this->coursemodulerenderer = new \format_cards\course_module_renderer($page, $target);
        $this->settingcontroller = \format_cards\SettingsController::getinstance();
        // Since format_cards_renderer::section_edit_controls()
        // only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available
        // for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'cards'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('sectionname', 'format_cards');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param array $mods
     * @param array $modnames
     * @param array $modnamesused
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $USER, $PAGE, $OUTPUT;
        if (!empty($USER->profile['accessible'])) {
            return parent::print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused);
        }

        // Get necessary values required to display the UI.
        $editing = $PAGE->user_is_editing();
        $coursecontext = context_course::instance($course->id);
        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        if ($editing) {
            $streditsummary = get_string('editsummary');
            $urlpicedit = $this->output->image_url('t/edit');
        } else {
            $urlpicedit = false;
            $streditsummary = '';
        }

        // Display the pagination.
        // 1 = OFF.
        // 2 = ON.
        $pagination = $this->settingcontroller->getsetting('enablepagination');
        if ($pagination == 2) {
            $sectionpagelimit = $this->settingcontroller->getsetting('defaultnumberoftopics');
            $totalsections = count($sections);
            $page = optional_param('page', 0, PARAM_INT);
            $startfrom = $sectionpagelimit * $page + 1;
            $end = $sectionpagelimit * $page + $sectionpagelimit;
        } else {
            $startfrom = 1;
        }
        if ($end > $this->courseformat->get_last_section_number()) {
            $end = $this->courseformat->get_last_section_number();
        }
        // Display the section when editing is in.
        if ($editing) {
            echo html_writer::start_tag('div', array('id' => 'card-editing-container', 'class' => 'row'));
            $this->display_editing_cards($course, $sections, $modinfo, $editing, false, $urlpicedit, $streditsummary,
            $startfrom, $end);
            echo html_writer::end_tag('div');
            $pageurl = new moodle_url('/course/view.php?id='.$course->id);
            $pagingbar  = new paging_bar($totalsections, $page, $sectionpagelimit, $pageurl, 'page');
            echo $OUTPUT->render($pagingbar);
            echo $this->change_number_sections($course, 0);
        } else {
            // Display the section in card layout.
            echo html_writer::start_tag('div', array('id' => 'card-container', 'class' => 'row'));
            $this->display_cards($coursecontext->id, $modinfo, $course, $editing, $startfrom, $end);
            echo html_writer::end_tag('div');
            $pageurl = new moodle_url('/course/view.php?id='.$course->id);
            $pagingbar  = new paging_bar($totalsections, $page, $sectionpagelimit, $pageurl, 'page');
            echo $OUTPUT->render($pagingbar);
        }
    }

    /**
     * Output html for sections
     * @param
     */
    private function display_cards($contextid, $modinfo, $course, $editing, $startfrom, $end) {

        $buttoncolor = $this->settingcontroller->getsetting('defaultbuttoncolour');
        // Display general section at top.
        $currentsection = $modinfo->get_section_info(0);
        $sectionname = $this->courseformat->get_section_name($currentsection);
        $summary = strip_tags($currentsection->summary);
        $coverimage = $this->get_course_image($course);
        $this->display_general_section($sectionname, $summary, $coverimage);

        echo "<style>.single-card:hover {
                border: 2px solid ".$buttoncolor.";
            }</style>";
        for ($section = $startfrom; $section <= $end; $section++) {
            // Get current section info.
            $currentsection = $modinfo->get_section_info($section);

            // Get the title of the section.
            $sectionname = $this->courseformat->get_section_name($currentsection);

            // Get the section view url.
            $singlepageurl = '';
            if ($course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $singlepageurl = $this->courseformat->get_view_url($section)->out(true);
            }

            $title = $sectionname;
            $summary = $this->get_formatted_summary(strip_tags($currentsection->summary));
            $this->single_card($section, $title, $summary, $singlepageurl, $buttoncolor);
        }
    }

    /**
     * Output Single Card
     * @param
     */
    private function single_card($index, $title, $summary, $singlepageurl, $buttoncolor) {
        echo '<div class="col-lg-4 col-md-4 col-sm-12 single-card-container">
                <div class="single-card">
                    <span class="sno" style="border-color:'.$buttoncolor.'">'.$index.'&#46;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <div class="card-content">
                        <h2 class="section-title">'.$title.'</h2>
                        <p class="section-summary">'.$summary.'</p>
                    </div>
                    <a href="'.$singlepageurl.'" class = "view-topic-btn" style="background-color:'.$buttoncolor.';">'.get_string("viewtopic", "format_cards").'</a>
                </div>
            </div>';
    }

    /**
     * Returns the formatted summary of section
     * @param $summary String
     * @return $summary String
     */
    private function get_formatted_summary($summary) {

        $summarylength = $this->settings['sectiontitlesummarymaxlength'];
        $summary = str_replace("&nbsp;", ' ', $summary);
        if ($summary) {
            $end = "";
            if (strlen($summary) > $summarylength) {
                $end = " ...";
            }
            $summary = substr($summary, 0, $summarylength).$end;
        }

        return $summary;
    }

    private function display_editing_cards($course, $sections, $modinfo, $editing, $onsectionpage, $urlpicedit, $streditsummary,
    $startfrom, $end) {
        $coursecontext = context_course::instance($course->id);
        $this->single_editing_card($coursecontext, 0, $course, $modinfo, $editing, $onsectionpage, $urlpicedit, $streditsummary);
        for ($section = $startfrom; $section <= $end; $section++) {
            $this->single_editing_card($coursecontext, $section, $course, $modinfo, $editing,
            $onsectionpage, $urlpicedit, $streditsummary);
        }
    }

    private function single_editing_card($coursecontext, $section, $course, $modinfo, $editing, $onsectionpage,
    $urlpicedit, $streditsummary) {
        $currentsection = $modinfo->get_section_info($section);
        $coverimage = $this->get_course_image($course);
        $sectionname = $this->courseformat->get_section_name($currentsection);
        if ($editing) {
            $title = $this->section_title($currentsection, $course);
        } else {
            $title = $sectionname;
        }
        if ($section == 0) {
            $classes = 'col-lg-12 col-md-12 col-sm-12';
        } else {
            $classes = 'col-lg-4 col-md-4 col-sm-12';
        }
        echo html_writer::start_tag('div', array('class' => $classes));

        if ($section == 0) {
            echo html_writer::start_tag('div', array(
                'id' => 'section-' . $section,
                'class' => 'card-section-list',
                'style' => 'background-image: linear-gradient(to right, rgba(14, 35, 53, 0.68),
                rgba(14, 35, 53, 0.68)), url('.$coverimage.');',
                'role' => 'region',
                'aria-label' => 'test')
            );
        } else {
            echo html_writer::start_tag('div', array(
                'id' => 'section-' . $section,
                'class' => 'card-section-list',
                'role' => 'region',
                'aria-label' => 'test')
            );
        }

        if ($editing) {
            // Note, 'left side' is BEFORE content.
            $leftcontent = $this->section_left_content($currentsection, $course, $onsectionpage);
            echo html_writer::tag('div', $leftcontent, array('class' => 'card-left-side'));
            // Note, 'right side' is BEFORE content.
            $rightcontent = $this->section_right_content($currentsection, $course, $onsectionpage);
            echo html_writer::tag('div', $rightcontent, array('class' => 'card-right-side'));
        }

        echo html_writer::start_tag('div', array('class' => 'card-content'));
        echo $this->output->heading($title, 3, 'sectionname');

        echo html_writer::start_tag('div', array('class' => 'card-summary'));
        if ($section == 0) {
            echo strip_tags($currentsection->summary);
        } else {
            echo $this->get_formatted_summary(strip_tags($currentsection->summary));
        }

        if ($editing) {
            echo html_writer::link(
                    new moodle_url('editsection.php', array('id' => $currentsection->id)),
                    html_writer::empty_tag('img', array('src' => $urlpicedit, 'alt' => $streditsummary,
                        'class' => 'card-edit')), array('title' => $streditsummary));
        }
        echo html_writer::end_tag('div');

        echo $this->section_availability_message($currentsection, has_capability('moodle/course:viewhiddensections',
                $coursecontext));
        // Display the course modules if needed.
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    }


    /**
     * Output the html for a single section page.
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {

        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();

        // Can we view the section in question?.
        if (!($sectioninfo = $modinfo->get_section_info($displaysection))) {
            // This section doesn't exist.
            print_error('unknowncoursesection', 'error', null, $course->fullname);
            return;
        }

        if (!$sectioninfo->uservisible) {
            if (!$course->hiddensections) {
                echo $this->start_section_list();
                echo $this->section_hidden($displaysection, $course->id);
                echo $this->end_section_list();
            }
            // Can't view this section.
            return;
        }

        echo $this->course_activity_clipboard($course, $displaysection);
        echo html_writer::start_tag('div', array('class' => 'single-section'));
        // Display the general section if needed.

        // The requested section page.
        $currentsection = $modinfo->get_section_info($displaysection);
        // Title with section navigation links.
        $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $displaysection);
        $sectiontitle = '';
        $sectiontitle .= html_writer::start_tag('div', array('class' => 'section-navigation navigationtitle'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectiontitle .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));

        // Title attributes.
        $classes = 'sectionname';
        if (!$currentsection->visible) {
            $classes .= ' dimmed_text';
        }
        $sectionname = html_writer::tag('span', $this->section_title_without_link($currentsection, $course));
        $sectiontitle .= $this->output->heading($sectionname, 3, $classes);

        $sectiontitle .= html_writer::end_tag('div');
        echo $sectiontitle;

        echo $this->start_section_list();
        echo $this->section_header_onsectionpage($currentsection, $course);
        echo $this->coursemodulerenderer->course_section_cm_list($course, $currentsection, $displaysection);
        echo $this->coursemodulerenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
        echo $this->section_footer();
        echo $this->end_section_list();

        // Display section bottom navigation.
        $sectionbottomnav = '';
        $sectionbottomnav .= html_writer::start_tag('div', array('class' => 'section-navigation mdl-bottom'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['previous'], array('class' => 'mdl-left'));
        $sectionbottomnav .= html_writer::tag('span', $sectionnavlinks['next'], array('class' => 'mdl-right'));
        $sectionbottomnav .= html_writer::tag('div', $this->section_nav_selection($course, $sections, $displaysection),
            array('class' => 'mdl-align'));
        $sectionbottomnav .= html_writer::end_tag('div');
        echo $sectionbottomnav;

        echo html_writer::end_tag('div');
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included for when section 0 is in the grid
     * and a single section page.
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    protected function section_header_onsectionpage($section, $course) {
        $o = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $o .= html_writer::start_tag('li', array('id' => 'section-'.$section->section,
            'class' => 'section main clearfix'.$sectionstyle, 'role' => 'region',
            'aria-label' => get_section_name($course, $section)));

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag('span', get_section_name($course, $section), array('class' => 'hidden sectionname'));

        $leftcontent = $this->section_left_content($section, $course, true);
        $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

        $rightcontent = $this->section_right_content($section, $course, true);
        $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        $o .= html_writer::start_tag('div', array('class' => 'content'));

        $sectionname = html_writer::tag('span', $this->section_title($section, $course));
        $o .= $this->output->heading($sectionname, 3, 'sectionname accesshide');

        $o .= html_writer::start_tag('div', array('class' => 'summary'));
        $o .= $this->format_summary_text($section);
        $o .= html_writer::end_tag('div');

        $o .= $this->section_availability($section);

        return $o;
    }

    protected function display_general_section($title, $summary, $coverimage) {
        echo '<div class="col-lg-12 col-sm-12 general-single-card-container">
            <div class="general-single-card">
                <div class="card-content" style="background-image: linear-gradient(to right,
                rgba(14, 35, 53, 0.68), rgba(14, 35, 53, 0.68)), url('.$coverimage.')";>
                    <h2 class="section-title">'.$title.'</h2>
                    <p class="section-summary">'.$summary.'</p>
                </div>
            </div>
        </div>';
    }

    private function get_course_image($courseinlist, $islist = false) {

        global $CFG, $OUTPUT;
        if (!$islist) {
            $courseinlist = new \course_in_list($courseinlist);
        }

        foreach ($courseinlist->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $courseimage = file_encode_url("$CFG->wwwroot/pluginfile.php",
                                        '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                                        $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                break;
            }
        }
        if (!empty($courseimage)) {
            return $courseimage;
        } else {
            return $OUTPUT->image_url('placeholder', 'theme');
        }
    }
}
