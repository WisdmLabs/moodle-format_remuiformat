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

class SettingsController
{
    protected static $instance;
    private $_plugin_config;
    
    // Constructor
    private function __construct()
    {
        $this->plugin_config = "format_cards";
    }
    
    // Singleton Implementation
    public static function getInstance()
    {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Finds the given setting in the plugin from the plugin's configuration object.
     * @param string $setting Setting name.
     * @return any defaultvalue|value of setting.
     */
    public function getSetting($setting)
    {
        $config = get_config($this->plugin_config);
        if (property_exists($config, $setting)) {
            return $config->$setting;
        } else {
            return 0;
        }
    }

    /**
     * Finds the default value of given setting in the plugin.
     * @param string $setting Setting name.
     * @return any false|defaultvalue value of setting.
     */
    public function getDefaultValue($setting)
    {
        $defaultValue;
        switch ($setting) {
            case 'defaultcoursedisplay':
                $defaultValue = COURSE_DISPLAY_SINGLEPAGE;
                break;
            case 'defaultbuttoncolour':
                $defaultValue = '#dddddd';
                break;
            case 'defaultoverlaycolour':
                $defaultValue = '#dddddd';
                break;
            case 'enablepagination':
                $defaultValue = 1;
                break;
            case 'defaultnumberoftopics':
                $defaultValue = 6;
                break;
            case 'defaultnumberoftopics':
                $defaultValue =  6;
                break;
            default:
                $defaultValue = '';
                break;
        }
        return $defaultValue;
    }

    /**
     * Return the array of choices for Course display options.
     * @param none
     * @return arrray of choices
    */
    public function getCourseDisplayOptions()
    {
        /*
         * COURSE_DISPLAY_SINGLEPAGE or - All sections on one page.
         * COURSE_DISPLAY_MULTIPAGE     - One section per page.
        */
        return array(
            COURSE_DISPLAY_SINGLEPAGE => new \lang_string('coursedisplay_single'),
            COURSE_DISPLAY_MULTIPAGE => new \lang_string('coursedisplay_multi')
        );
    }

    /**
     * Return the array of choices for number of sections/topics/activity
     * @param none
     * @return arrray of choices
    */
    public function getNumberOfSectionsOptions()
    {
        return array(
            6 => get_string('six', 'format_cards'),      // Six.
            8 => get_string('eight', 'format_cards'),    // Eight.
            12 => get_string('twelve', 'format_cards')   // Twelve.
        );
    }

     /**
     * Return the array of choices for enable pagination
     * @param none
     * @return arrray of choices
    */
    public function getPaginationChoices()
    {
        return array(
            1 => new \lang_string('off', 'format_grid'),   // Off.
            2 => new \lang_string('on', 'format_grid')   // On.
        );
    }
}
