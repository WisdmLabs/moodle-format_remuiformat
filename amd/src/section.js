// File: course/format/remuiformat/amd/src/section.js
define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    'use strict';

    /**
     * Component to handle section highlighting.
     *
     * @module     format_remuiformat/section
     * @copyright  2024 Your Name
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    return {
        /**
         * Initialize the section highlighting functionality.
         */
        init: function() {
            $('.editing_highlight').on('click', function(e) {
                e.preventDefault();
                var $this = $(this);
                var url = $this.attr('href');

                // Use the URL directly since it's already set up with the correct parameters
                $.get(url, function() {
                    // Refresh the page after successful AJAX call
                    window.location.reload();
                }).fail(function(error) {
                    Notification.exception(error);
                });
            });
        }
    };
});