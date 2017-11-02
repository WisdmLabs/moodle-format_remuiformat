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
 * @package    format/cards
 * @version    See the value of '$plugin->version' in version.php.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/format/cards/classes/settings_controller.php');

if ($ADMIN->fulltree) {
    // Get the setting controller
    $setting_controller = \format_cards\SettingsController::getInstance();

    // Default course display.
    $name = 'format_cards/defaultcoursedisplay';
    $title = get_string('defaultcoursedisplay', 'format_cards');
    $description = get_string('defaultcoursedisplay_desc', 'format_cards');
    $default = $setting_controller->getDefaultValue('defaultcoursedisplay');
    $choices = $setting_controller->getCourseDisplayOptions();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Default button colour in hexadecimal RGB with preceding '#'.
    $name = 'format_cards/defaultbuttoncolour';
    $title = get_string('defaultbuttoncolour', 'format_cards');
    $description = get_string('defaultbuttoncolour_desc', 'format_cards');
    $default = $setting_controller->getDefaultValue('defaultbuttoncolour');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    // Default overlay colour in hexadecimal RGB with preceding '#'.
    $name = 'format_cards/defaultoverlaycolour';
    $title = get_string('defaultoverlaycolour', 'format_cards');
    $description = get_string('defaultoverlaycolour_desc', 'format_cards');
    $default = $setting_controller->getDefaultValue('defaultoverlaycolour');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
    $settings->add($setting);

    /* Enable Pagination */
    $name = 'format_cards/enablepagination';
    $title = get_string('enablepagination', 'format_cards');
    $description = get_string('enablepagination_desc', 'format_cards');
    $default = $setting_controller->getDefaultValue('defaultcoursedisplay');
    $choices = $setting_controller->getPaginationChoices();
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    if ($setting_controller->getSetting('enablepagination') == 2) {
        /* Number of Topic per page */
        $name = 'format_grid/defaultnumberoftopics';
        $title = get_string('defaultnumberoftopics', 'format_cards');
        $description = get_string('defaultnumberoftopics_desc', 'format_cards');
        $default = $setting_controller->getDefaultValue('defaultnumberoftopics');
        $choices = $setting_controller->getNumberOfSectionsOptions();
        $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

        /* Number of Activities per page */
        $name = 'format_grid/defaultnumberofactivities';
        $title = get_string('defaultnumberofactivities', 'format_cards');
        $description = get_string('defaultnumberofactivities_desc', 'format_cards');
        $default = $setting_controller->getDefaultValue('defaultnumberofactivities');
        $choices = $setting_controller->getNumberOfSectionsOptions();
        $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    }
}
