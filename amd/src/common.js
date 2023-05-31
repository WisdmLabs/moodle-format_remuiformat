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
 * Enhancements to Lists components for easy course accessibility.
 *
 * @module     format/remuiformat
 * @copyright  WisdmLabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    var SELECTORS = {
        ACTIVITY_TOGGLE: '.showactivity',
        ACTIVITY_TOGGLE_CLASS: 'showhideactivity',
        ACTIVITY_TOGGLE_WRAPPER: '.showactivitywrapper',
        FIRST_SECTION: '#section-0',
        SHOW: 'show',
        TOGGLE_HIGHLIGHT: '.section_action_menu .dropdown-item.editing_highlight',
        TOGGLE_SHOWHIDE: '.section_action_menu .dropdown-item.editing_showhide',
        BUTTON_HIDE: '.cm_action_menu .dropdown-menu .editing_hide',
        BUTTON_SHOW: '.cm_action_menu .dropdown-menu .editing_show',
        DELETE: '.section_action_menu .dropdown-item[data-action="deleteSection"]'
    };

    /**
     * Get number activities can be shown in first row and hide rest
     * @return {Integer} Number activities in first row
     */
    function getActivitiesPerRow() {
        let width = $(window).width();
        if ($('.remui-format-list').length) {
            if (width >= 992) {
                return 4;
            }
            if (width >= 768) {
                return 3;
            }
            return 2;
        } else {
            if (width >= 768) {
                return 4;
            }
            if (width >= 481) {
                return 2;
            }
            return 1;
        }
    }

    /**
     * Adjust the general section activities visibility after first row
     */
    function adjustGeneralSectionActivities() {
        if ($(SELECTORS.FIRST_SECTION + ' .activity').length <= getActivitiesPerRow()) {
            $(SELECTORS.FIRST_SECTION).removeClass(SELECTORS.ACTIVITY_TOGGLE_CLASS);
            $(SELECTORS.ACTIVITY_TOGGLE_WRAPPER).hide();
        } else {
            $(SELECTORS.ACTIVITY_TOGGLE_WRAPPER).show();
            $(SELECTORS.FIRST_SECTION).addClass(SELECTORS.ACTIVITY_TOGGLE_CLASS);
        }
    }
    /**
     * Init method
     *
     */
    function init() {

        $('#page-course-view-remuiformat .section-modchooser-link').addClass("btn btn-primary");

        adjustGeneralSectionActivities();
        $(window).resize(function() {
            adjustGeneralSectionActivities();
        });

        if ($(".general-section-activities li:last").css('display') == 'none') {
            $(".showactivitywrapper").show();
        } else {
            $(".showactivitywrapper").hide();
        }

        $(SELECTORS.ACTIVITY_TOGGLE).on('click', function() {

            if ($(this).hasClass(SELECTORS.SHOW)) {
                $(this).html(M.util.get_string('showless', 'format_remuiformat'));
                $(this).toggleClass(SELECTORS.SHOW); // Remove show class
            } else {
                $(this).html(M.util.get_string('showmore', 'format_remuiformat'));
                $(this).toggleClass(SELECTORS.SHOW); // Add show class
                $("html, body").animate({
                    scrollTop: $(SELECTORS.FIRST_SECTION + ' .activity:first-child').offset().top - 66
                }, "slow");
            }
            $(SELECTORS.FIRST_SECTION).toggleClass(SELECTORS.ACTIVITY_TOGGLE_CLASS);
        });

        // Handling highlight and show hide dropdown.
        $('body').on('click', `${SELECTORS.TOGGLE_HIGHLIGHT},
                               ${SELECTORS.TOGGLE_SHOWHIDE},
                               ${SELECTORS.BUTTON_HIDE},
                               ${SELECTORS.BUTTON_SHOW}`, function() {
            location.reload();
        });

        // Handling deleteAction
        $('body').on('click', `${SELECTORS.DELETE}`, function(event) {
            event.preventDefault();
            window.location.href = $(this).attr('href');
            return true;
        });


        // ... + Show full summary label show conditionally.
        var summaryheight = $('.read-more-target').height();
        var browservendor = window.navigator.vendor;
        var webkitboxorient = "vertical";
        if (browservendor.indexOf('Apple') != -1) {
            webkitboxorient = "horizontal";
        }

        if (summaryheight > 100) {
            $('.generalsectioninfo').find('#readmorebtn').removeClass('d-none');
            $('.read-more-target .no-overflow').addClass('text-clamp text-clamp-3').css("-webkit-box-orient", webkitboxorient);
            $('.read-more-target').addClass('text-clamp text-clamp-3').css("-webkit-box-orient", webkitboxorient);
        }
        $('#readmorebtn').on('click', function() {
            $('.read-more-target .no-overflow').removeClass('text-clamp text-clamp-3');
            $('.read-more-target').removeClass('text-clamp text-clamp-3');
            $('.generalsectioninfo').find('#readmorebtn').addClass('d-none');
            $('.generalsectioninfo').find('#readlessbtn').removeClass('d-none');
        });
        $('#readlessbtn').on('click', function () {
            $('.read-more-target .no-overflow').addClass('text-clamp text-clamp-3').css("-webkit-box-orient", webkitboxorient);
            $('.read-more-target').addClass('text-clamp text-clamp-3').css("-webkit-box-orient", webkitboxorient);
            $('.generalsectioninfo').find('#readmorebtn').removeClass('d-none');
            $('.generalsectioninfo').find('#readlessbtn').addClass('d-none');
        });

    }

    return {
        init: init,
        adjustGeneralSectionActivities: adjustGeneralSectionActivities
    };
});
