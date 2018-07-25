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
 * @package    course/format
 * @subpackage remui_format
 * @version    See the value of '$plugin->version' in version.php.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/course/format/remui_format/classes/settings_controller.php');

if ($ADMIN->fulltree) {
    // Get the setting controller.
    $settingcontroller = \format_remui_format\SettingsController::getinstance();

    // Default button colour in hexadecimal RGB with preceding '#'.
    $name = 'format_remui_format/defaultbuttoncolour';
    $title = get_string('defaultbuttoncolour', 'format_remui_format');
    $description = get_string('defaultbuttoncolour_desc', 'format_remui_format');
    $default = $settingcontroller->getdefaultvalue('defaultbuttoncolour');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default overlay colour in hexadecimal RGB with preceding '#'.
    $name = 'format_remui_format/defaultoverlaycolour';
    $title = get_string('defaultoverlaycolour', 'format_remui_format');
    $description = get_string('defaultoverlaycolour_desc', 'format_remui_format');
    $default = $settingcontroller->getdefaultvalue('defaultoverlaycolour');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default length of sumary of the section/activities.
    $name = 'format_remui_format/defaultsectionsummarymaxlength';
    $title = get_string('defaultsectionsummarymaxlength', 'format_remui_format');
    $description = get_string('defaultsectionsummarymaxlength_desc', 'format_remui_format');
    $default = $settingcontroller->getdefaultvalue('defaultsectionsummarymaxlength');
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_INT));

    /* Enable Pagination */
    $name = 'format_remui_format/enablepagination';
    $title = get_string('enablepagination', 'format_remui_format');
    $description = get_string('enablepagination_desc', 'format_remui_format');
    $default = $settingcontroller->getdefaultvalue('defaultcoursedisplay');
    $choices = $settingcontroller->getpaginationchoices();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    if ($settingcontroller->getsetting('enablepagination') == 2) {
        /* Number of Topic per page */
        $name = 'format_remui_format/defaultnumberoftopics';
        $title = get_string('defaultnumberoftopics', 'format_remui_format');
        $description = get_string('defaultnumberoftopics_desc', 'format_remui_format');
        $default = $settingcontroller->getdefaultvalue('defaultnumberoftopics');
        $choices = $settingcontroller->getnumberofsectionsoptions();
        $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

        /* Number of Activities per page */
        $name = 'format_remui_format/defaultnumberofactivities';
        $title = get_string('defaultnumberofactivities', 'format_remui_format');
        $description = get_string('defaultnumberofactivities_desc', 'format_remui_format');
        $default = $settingcontroller->getdefaultvalue('defaultnumberofactivities');
        $choices = $settingcontroller->getnumberofsectionsoptions();
        $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    }
}
