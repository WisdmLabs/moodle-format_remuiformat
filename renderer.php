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

class format_cards_renderer extends format_section_renderer_base {

    protected $courseformat; // Our course format object as defined in lib.php.

    /**
     * Constructor method, calls the parent constructor
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->courseformat = course_get_format($page->course);
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
        return get_string('section_name', 'format_cards');
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

        $editing = $PAGE->user_is_editing();
        $coursecontext = context_course::instance($course->id);
        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        echo html_writer::start_tag('div', array('id' => 'card-container'));
        $this->single_card($coursecontext->id, $modinfo, $course, $editing);
        echo html_writer::end_tag('div');
    }

    /**
     * Output html for sections
     * @param
     */
    private function single_card($contextid, $modinfo, $course, $editing) {

        $coursenumsections = $this->courseformat->get_last_section_number();
        for ($section = 1; $section <= $coursenumsections; $section++) {
            // Get current section info.
            $currentsection = $modinfo->get_section_info($section);

            // Get the title of the section.
            $sectionname = $this->courseformat->get_section_name($currentsection);

            $title = $sectionname;
            $summary = strip_tags($currentsection->summary);
            $summary = str_replace("&nbsp;", ' ', $summary);
            echo '<div style="width:30%;height:200px;background-color:orangered;padding:20px;
            margin: 10px;position:relative; display:inline-block">
            <h2>'.$title.'</h2>
            <p>'.$summary.'</p>
            <a href="" style="display:flex;justify-content:center;text-decoration:none;
            color: white;background-color: lightgreen;padding : 10px;position: absolute;bottom:0;left:0;right:0">View Topic</a>
            </div>';
        }
    }

}
