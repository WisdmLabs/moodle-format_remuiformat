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
 * @subpackage remuiformat
 * @copyright  2019 Wisdmlabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot.'/course/format/remuiformat/classes/output/section_renderable.php');
require_once($CFG->dirroot.'/course/format/remuiformat/classes/output/activity_renderable.php');
require_once($CFG->dirroot.'/course/format/remuiformat/classes/output/single_section_renderable.php');


$renderer = $PAGE->get_renderer('format_remuiformat');
// Backward Compatibility.
if ($topic = optional_param('topic', 0, PARAM_INT)) {
    $url = $PAGE->url;
    $url->param('section', $topic);
    debugging('Outdated topic param passed to course/view.php', DEBUG_DEVELOPER);
    redirect($url);
}
// End backwards-compatible aliasing..

$coursecontext = context_course::instance($course->id);

// Retrieve course format option fields and add them to the $course object.
$course = course_get_format($course)->get_course();


if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

// Include JS Files Required.
$stringman = get_string_manager();
$strings = $stringman->load_component_strings('format_remuiformat', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'format_remuiformat');

$section = optional_param('section', 0, PARAM_INT);
$baserenderer = $renderer->get_base_renderer();
if ($section) {
    if ($course->remuicourseformat && $course->coursedisplay) {
        $renderer->render_single_section(
            new \format_remuiformat\output\format_remuiformat_activity($course, $displaysection, $baserenderer)
        );
    }
}
if ($course->remuicourseformat && $course->coursedisplay && !$section) {
    $renderer->render_single_list_section(new \format_remuiformat\output\format_remuiformat_single_section($course, $baserenderer));
} else if ($displaysection && !$course->remuicourseformat) {
    $renderer->render_single_section(
        new \format_remuiformat\output\format_remuiformat_activity($course, $displaysection, $baserenderer)
    );
} else if (!$displaysection) {
    $renderer->render_all_sections(new \format_remuiformat\output\format_remuiformat_section($course, $baserenderer));
}
