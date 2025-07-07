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
 * Hook callbacks for format_remuiformat
 *
 * @package    format_remuiformat
 * @copyright  2024 Wisdmlabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_remuiformat;

/**
 * Hook callbacks for format_remuiformat
 *
 * @package    format_remuiformat
 * @copyright  2024 Wisdmlabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hooks {

    /**
     * Add HelpScout Beacon script to admin pages
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(\core\hook\output\before_standard_head_html_generation $hook): void {
        global $PAGE;

        if ($PAGE->pagetype == 'course-edit') {
            $PAGE->requires->js_init_code("
                !function(e,t,n){function a(){var e=t.getElementsByTagName('script')[0],n=t.createElement('script');n.type='text/javascript',n.async=!0,n.src='https://beacon-v2.helpscout.net',e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],'complete'===t.readyState)return a();e.attachEvent?e.attachEvent('onload',a):e.addEventListener('load',a,!1)}(window,document,window.Beacon||function(){});
                window.Beacon('init', '04904257-7cfb-468b-b643-57eb3a1c20c6');
            ");
        }
    }
}