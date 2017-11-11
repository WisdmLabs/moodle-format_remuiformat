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
require_once($CFG->dirroot.'/course/format/cards/classes/course_module_renderer.php');

class format_cards_renderer extends format_section_renderer_base {

    protected $courseformat; // Our course format object as defined in lib.php.
    protected $coursemodulerenderer; // Our custom course module renderer.
    /**
     * Constructor method, calls the parent constructor
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courseformat = course_get_format($page->course);
        $this->coursemodulerenderer = new \format_cards\course_module_renderer($page, $target);
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
        global $USER, $PAGE;
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

        // Display the section when editing is in.
        if ($editing) {
            echo html_writer::start_tag('div', array('id' => 'card-editing-container', 'class' => 'row'));
            $this->display_editing_cards($course, $sections, $modinfo, $editing, false, $urlpicedit, $streditsummary);
            echo html_writer::end_tag('div');
        } else {
            // Display the section in card layout.
            echo html_writer::start_tag('div', array('id' => 'card-container', 'class' => 'row'));
            $this->display_cards($coursecontext->id, $modinfo, $course, $editing);
            echo html_writer::end_tag('div');
        }
    }

    /**
     * Output html for sections
     * @param
     */
    private function display_cards($contextid, $modinfo, $course, $editing) {

        $coursenumsections = $this->courseformat->get_last_section_number();
        for ($section = 1; $section <= $coursenumsections; $section++) {
            // Get current section info.
            $currentsection = $modinfo->get_section_info($section);

            // Get the title of the section.
            $sectionname = $this->courseformat->get_section_name($currentsection);

            // Get the section view url.
            if ($course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $singlepageurl = $this->courseformat->get_view_url($section)->out(true);
            }

            $title = $sectionname;
            $summary = $this->get_formatted_summary(strip_tags($currentsection->summary));
            $this->single_card($section, $title, $summary, $singlepageurl);
        }
    }

    /**
     * Output Single Card
     * @param
     */
    private function single_card($index, $title, $summary, $singlepageurl) {
        echo '<div class="col-lg-4 col-md-4 col-sm-12 single-card-container">
                <div class="single-card">
                    <span class="sno">'.$index.'&#46;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <div class="card-content">
                        <h2 class="section-title">'.$title.'</h2>
                        <p class="section-summary">'.$summary.'</p>
                    </div>
                    <a href="'.$singlepageurl.'" class = "view-topic-btn">View Topic</a>
                </div>
            </div>';
    }

    /**
     * Returns the formatted summary of section
     * @param $summary String
     * @return $summary String
     */
    private function get_formatted_summary($summary) {

        $summary = str_replace("&nbsp;", ' ', $summary);
        $summary = substr($summary, 0, 120)." ...";

        return $summary;
    }

    private function display_editing_cards($course, $sections, $modinfo, $editing, $onsectionpage, $urlpicedit, $streditsummary) {
        $coursecontext = context_course::instance($course->id);
        $coursenumsections = $this->courseformat->get_last_section_number();
        for ($section = 1; $section <= $coursenumsections; $section++) {
            $currentsection = $modinfo->get_section_info($section);

            $sectionname = $this->courseformat->get_section_name($currentsection);
            if ($editing) {
                $title = $this->section_title($currentsection, $course);
            } else {
                $title = $sectionname;
            }
            echo html_writer::start_tag('div', array('class' => 'col-lg-4 col-md-4 col-sm-12'));
            echo html_writer::start_tag('div', array(
                'id' => 'section-' . $section,
                'class' => 'card-section-list',
                'role' => 'region',
                'aria-label' => 'test')
            );

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
            echo $this->get_formatted_summary(strip_tags($currentsection->summary));

            if ($editing) {
                echo html_writer::link(
                        new moodle_url('editsection.php', array('id' => $currentsection->id)),
                        html_writer::empty_tag('img', array('src' => $urlpicedit, 'alt' => $streditsummary,
                            'class' => 'card-edit')), array('title' => $streditsummary));
            }
            echo html_writer::end_tag('div');

            echo $this->section_availability_message($currentsection, has_capability('moodle/course:viewhiddensections',
                    $coursecontext));
            //echo $this->courserenderer->course_section_cm_list($course, $currentsection, 0);
            //echo $this->courserenderer->course_section_add_cm_control($course, $currentsection->section, 0);
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');
        }
    }

    /**
     * Output the html for a single section page .
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

        echo html_writer::start_tag('div', array('class' => 'single-section'));
        // The requested section page.
        $currentsection = $modinfo->get_section_info($displaysection);

        echo $this->start_section_list();

        echo $this->coursemodulerenderer->course_section_cm_list($course, $currentsection, $displaysection);
        echo $this->courserenderer->course_section_add_cm_control($course, $displaysection, $displaysection);
        echo $this->section_footer();
        echo $this->end_section_list();

        echo html_writer::end_tag('div');
    }
}
