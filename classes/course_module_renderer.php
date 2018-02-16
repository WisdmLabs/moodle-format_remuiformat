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
use html_writer;
use core_text;

require_once($CFG->dirroot.'/course/renderer.php');
require_once("mod_stats.php");
require_once("settings_controller.php");

class course_module_renderer extends \core_course_renderer {

    private $course;
    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {

        global $USER;
        $output = '';

        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new \completion_info($course);

        // Check if we are currently in the process of moving a module with JavaScript disabled.
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one).
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // Do not display moving mod.
                    continue;
                }
                if ($modulehtml = $this->course_section_cm_list_item(
                    $course,
                    $completioninfo,
                    $mod,
                    $sectionreturn,
                    $displayoptions
                )) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag(
                        'li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere')
                    );
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag(
                    'li',
                    html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                    array('class' => 'movehere')
                );
            }
        }

        // Always output the section module list.
        if (!empty($sectionoutput)) {
            $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text row', 'style' => 'padding:0;'));
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_list_item($course, &$completioninfo, \cm_info $mod, $sectionreturn,
    $displayoptions = array()) {
        $output = '';
        if ($modulehtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' col-lg-4 col-md-4 col-sm-12 py-10'
            . $mod->extraclasses;
            $output .= html_writer::tag('div', $modulehtml, array('class' => $modclasses, 'id' => 'module-' . $mod->id));
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, \cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';

        // We return empty string (because course module will not be displayed at all)
        // if the activity is not visible to users
        // and the 'availableinfo' is empty, i.e. the activity was
        // hidden in a way that leaves no info, such as using the
        // eye icon.
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $settingcontroller = \format_cards\SettingsController::getinstance();
        $defaultcolor = $settingcontroller->getsetting('defaultbuttoncolour');
        $defaultoverlaycolor = $settingcontroller->getsetting('defaultoverlaycolour');

        // Compeltion icon.
        $completionicon = html_writer::tag('div',
        $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions),
        array('style' => 'width:70%;'));

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        $output .= html_writer::start_tag('div', array('class' => 'mod-card-container'));
        if (!$this->page->user_is_editing()) {
             // Overlay Div.
            $output .= html_writer::start_tag('div', array('class' => 'mod-card-overlay hidden',
            'style' => 'background-color: '.$defaultoverlaycolor.';'));
            $output .= html_writer::end_tag('div');

            // Buttons on Hover.
            $output .= html_writer::start_tag('div', array('class' => 'card-hover-content hidden'));
            $output .= "<a href=".$mod->url." class = 'card-activity-btn '>"
            .get_string("viewactivity", "format_cards")."</a>";
            $output .= $completionicon;
            $output .= html_writer::end_tag('div');
        }

        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));
        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Display the link to the module (or do nothing if module has no url).
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            // Completion icon for activities with cmname.
            // $output .= $completionicon.
            $activityclass = 'activityinstance single-activity-card';
            if (!empty($mod->indent) && $mod->indent > 1) {
                $activityclass .= ' hideicon';
            }
            $output .= html_writer::start_tag('div', array('class' => $activityclass));
            $output .= $cmname;

            // Module can put text after the link (e.g. forum unread).
            $output .= $mod->afterlink;

            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // Activityinstance.
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case cons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.

        // Completion icon for label activity.
        $contentpart = '';
        if (empty($cmname)) {
            $contentpart = $completionicon;
        }
        $this->course = $course;
        $contentpart .= $this->course_section_cm_text($mod, $displayoptions);

        $url = $mod->url;
        if (empty($url)) {
            $output .= $contentpart;
        }

        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        if (!empty($modicons)) {
            $output .= html_writer::div($modicons, 'actions');
        }

        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        // If there is content AND a link, then display the content here
        // (AFTER any icons). Otherwise it was displayed before.
        if (!empty($url)) {
            $output .= $contentpart;
        }
        $output .= html_writer::end_tag('div');
        // End of indentation div.
        $output .= html_writer::start_tag('span', array('class' => "activity-tag", 'style' => 'border-bottom-color: '.$defaultcolor.'; color: '.$defaultcolor.';'));
        $output .= $mod->modname;
        $output .= html_writer::end_tag('span');
        $output .= html_writer::end_tag('div');

        // Activity Info.
        if ($course->enablecompletion == 1) {
            $modstats = \format_cards\ModStats::getinstance();
            $stats = $modstats->get_mod_stats($course, $mod);
            $output .= html_writer::start_tag('div', array('class' => "activity-info"));
            $output .= html_writer::start_tag('p', array('class' => "activity-info-stats"));
            $output .= $stats;
            $output .= html_writer::end_tag('p');
            $output .= html_writer::end_tag('div');
        }

        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Renders html for completion box on course page
     *
     * If completion is disabled, returns empty string
     * If completion is automatic, returns an icon of the current completion state
     * If completion is manual, returns a form (with an icon inside) that allows user to
     * toggle completion
     *
     * change span tag to div for completion icon in editing mode
     *
     * @param stdClass $course course object
     * @param completion_info $completioninfo completion info for the course, it is recommended
     *     to fetch once for all modules in course/section for performance
     * @param cm_info $mod module to show completion for
     * @param array $displayoptions display options, not used in core
     * @return string
     */
    public function course_section_cm_completion($course, &$completioninfo, \cm_info $mod, $displayoptions = array()) {
        global $CFG;
        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }
        if ($completioninfo === null) {
            $completioninfo = new completion_info($course);
        }
        $completion = $completioninfo->is_enabled($mod);
        if ($completion == COMPLETION_TRACKING_NONE) {
            if ($this->page->user_is_editing()) {
                $output .= html_writer::span('&nbsp;', 'filler');
            }
            return $output;
        }

        $completiondata = $completioninfo->get_data($mod, true);
        $completionicon = '';

        if ($this->page->user_is_editing()) {
            switch ($completion) {
                case COMPLETION_TRACKING_MANUAL:
                    $completionicon = 'manual-enabled';
                    break;
                case COMPLETION_TRACKING_AUTOMATIC:
                    $completionicon = 'auto-enabled';
                    break;
            }
        } else if ($completion == COMPLETION_TRACKING_MANUAL) {
            switch ($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'manual-n';
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'manual-y';
                    break;
            }
        } else { // Automatic.
            switch ($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'auto-n';
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'auto-y';
                    break;
                case COMPLETION_COMPLETE_PASS:
                    $completionicon = 'auto-pass';
                    break;
                case COMPLETION_COMPLETE_FAIL:
                    $completionicon = 'auto-fail';
                    break;
            }
        }
        if ($completionicon) {
            $formattedname = $mod->get_formatted_name();
            $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);

            if ($this->page->user_is_editing()) {
                // When editing, the icon is just an image.
                $completionpixicon = new \pix_icon(
                    'i/completion-'.$completionicon,
                    $imgalt,
                    '',
                    array('title' => $imgalt, 'class' => 'iconsmall')
                );
                $output .= html_writer::tag(
                    'div',
                    $this->output->render($completionpixicon),
                    array('class' => 'autocompletion')
                );
            } else if ($completion == COMPLETION_TRACKING_MANUAL) {
                $imgtitle = get_string('completion-title-' . $completionicon, 'completion', $formattedname);
                $newstate = $completiondata->completionstate == COMPLETION_COMPLETE ? COMPLETION_INCOMPLETE : COMPLETION_COMPLETE;
                // In manual mode the icon is a toggle form...

                // If this completion state is used by the
                // conditional activities system, we need to turn
                // off the JS.
                $extraclass = '';
                if (!empty($CFG->enableavailability) &&
                        \core_availability\info::completion_value_used($course, $mod->id)) {
                    $extraclass = ' preventjs';
                }
                $output .= html_writer::start_tag('form', array('method' => 'post',
                    'action' => new \moodle_url('/course/togglecompletion.php'),
                    'class' => 'togglecompletion'. $extraclass));
                $output .= html_writer::start_tag('div');
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'id', 'value' => $mod->id));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'modulename', 'value' => $mod->name));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'completionstate', 'class' => 'card-completion-state', 'value' => $newstate));

                $completebutton = html_writer::start_tag('div', array('class' => 'card-activity-btn card-completion'));
                $completebutton .= $this->output->pix_icon('i/completion-' . $completionicon, $imgalt);
                if ($newstate == 0) {
                    $state = get_string("completed", "format_cards");
                } else {
                    $state = get_string("markcomplete", "format_cards");
                }
                $completebutton .= html_writer::start_tag('p', array('class' => 'card-stats'));
                $completebutton .= $state;
                $completebutton .= html_writer::end_tag('p');
                $completebutton .= html_writer::end_tag('div');
                $output .= html_writer::tag(
                    'button', $completebutton,
                    array('class' => 'btn btn-link card-complete-btn', 'style' => 'width:100%; padding:0;')
                );
                $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('form');
            } else {
                // In auto mode, the icon is just an image.
                $completionpixicon = new \pix_icon(
                    'i/completion-'.$completionicon,
                    $imgalt,
                    '',
                    array('title' => $imgalt)
                );
                $output .= html_writer::tag(
                    'span',
                    $this->output->render($completionpixicon),
                    array('class' => 'autocompletion')
                );
            }
        }
        return $output;
    }

    /**
     * Renders html to display the module content on the course page (i.e. text of the labels)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_text(\cm_info $mod, $displayoptions = array()) {
        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            // nothing to be displayed to the user
            return $output;
        }
        $format = course_get_format($this->course);
        $settings = $format->get_settings();
        $content = $mod->get_formatted_content();
        $content = \format_cards\ModStats::get_formatted_summary($content, $settings);
        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);
        if ($mod->url && $mod->uservisible) {
            if ($content) {
                // If specified, display extra content after link.
                $output = html_writer::tag('div', $content, array('class' => trim('contentafterlink ' . $textclasses)));
            }
        } else {
            $groupinglabel = $mod->get_grouping_label($textclasses);

            // No link, so display only content.
            $output = html_writer::tag('div', $content . $groupinglabel,
                    array('class' => 'contentwithoutlink ' . $textclasses));
        }
        return $output;
    }
}