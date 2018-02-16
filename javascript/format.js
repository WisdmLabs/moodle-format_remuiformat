require(['jquery'], function ($) {

    //Function to set Equal Height of all cards
    var setEqualHeight = function (selector) {
        if (selector.length > 0) {
            var arr = [];
            var selector_height;
            selector.css("min-height", "initial");
            selector.each(function (index, elem) {
                selector_height = elem.offsetHeight;
                arr.push(selector_height);
            });
            selector_height = Math.max.apply(null, arr);
            selector.css("min-height", selector_height);
        }
    }

    // Display the overlay
    $('.mod-card-container').mouseover(function() {
        $(this).children('.mod-card-overlay').removeClass("hidden");
        $(this).children('.card-hover-content').removeClass("hidden");
    }).mouseout(function() {
        $(this).children('.mod-card-overlay').addClass("hidden");
        $(this).children('.card-hover-content').addClass("hidden");
    });

    // Mark Completion
    $('.card-complete-btn').click(function() {
        console.log("clicked");
        var val = $(this).parent().children('.card-completion-state').val();
        console.log($(this).children('.card-stats'));
        if (val == 0) {
            $(this).find('.card-stats').html(M.util.get_string('markcomplete', 'format_cards'));
        } else {
            $(this).find('.card-stats').html(M.util.get_string('completed', 'format_cards'));
        }
    });

    // Set Equal height of cards on load
    setEqualHeight($('.single-card'));
    setEqualHeight($('.card-section-list'))
    setEqualHeight($('.mod-indent-outer'));

    M.course = M.course || {};

    M.course.format = M.course.format || {};

    M.course.format.get_config = function() {
        return {
            container_node : 'ul',
            container_class : 'cards',
            section_node : 'li',
            section_class : 'section'
        };
    }

    // Display the hover on activity page.
    // $('.mod-card-container').mouseover(function() {
    //     console.log("In");
    // }).mouseout(function() {
    //     console.log("Out");
    // });

    /**
     * Swap section
     *
     * @param {YUI} Y YUI3 instance
     * @param {string} node1 node to swap to
     * @param {string} node2 node to swap with
     * @return {NodeList} section list
     */
    M.course.format.swap_sections = function(Y, node1, node2) {
        var CSS = {
            COURSECONTENT : 'course-content',
            SECTIONADDMENUS : 'section_add_menus'
        };

        var sectionlist = Y.Node.all('.'+CSS.COURSECONTENT+' '+M.course.format.get_section_selector(Y));
        // Swap menus.
        sectionlist.item(node1).one('.'+CSS.SECTIONADDMENUS).swap(sectionlist.item(node2).one('.'+CSS.SECTIONADDMENUS));
    }

    /**
     * Process sections after ajax response
     *
     * @param {YUI} Y YUI3 instance
     * @param {array} response ajax response
     * @param {string} sectionfrom first affected section
     * @param {string} sectionto last affected section
     * @return void
     */
    M.course.format.process_sections = function(Y, sectionlist, response, sectionfrom, sectionto) {
        var CSS = {
            SECTIONNAME : 'sectionname'
        },
        SELECTORS = {
            SECTIONLEFTSIDE : '.left .section-handle .icon'
        };

        if (response.action == 'move') {
            // If moving up swap around 'sectionfrom' and 'sectionto' so the that loop operates.
            if (sectionfrom > sectionto) {
                var temp = sectionto;
                sectionto = sectionfrom;
                sectionfrom = temp;
            }

            // Update titles and move icons in all affected sections.
            var ele, str, stridx, newstr;

            for (var i = sectionfrom; i <= sectionto; i++) {
                // Update section title.
                var content = Y.Node.create('<span>' + response.sectiontitles[i] + '</span>');
                sectionlist.item(i).all('.'+CSS.SECTIONNAME).setHTML(content);
                // Update move icon.
                ele = sectionlist.item(i).one(SELECTORS.SECTIONLEFTSIDE);
                str = ele.getAttribute('alt');
                stridx = str.lastIndexOf(' ');
                newstr = str.substr(0, stridx +1) + i;
                ele.setAttribute('alt', newstr);
                ele.setAttribute('title', newstr); // For FireFox as 'alt' is not refreshed.
            }
        }
    }
});