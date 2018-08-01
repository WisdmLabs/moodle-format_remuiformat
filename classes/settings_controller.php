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
 * @package   format_remuiformat
 * @copyright Copyright (c) 2016 WisdmLabs. (http://www.wisdmlabs.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_remuiformat;

defined('MOODLE_INTERNAL') || die;

class SettingsController
{
    protected static $instance;
    private $_plugin_config;

    // Constructor.
    private function __construct() {
        $this->plugin_config = "format_remuiformat";
    }
    // Singleton Implementation.
    public static function getinstance() {
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
    public function getsetting($setting) {
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
    public function getdefaultvalue($setting) {
        $defaultvalue;
        switch ($setting) {
            case 'defaultsectionsummarymaxlength' :
                $defaultvalue = 100;
                break;
            default:
                $defaultvalue = '';
                break;
        }
        return $defaultvalue;
    }
}
