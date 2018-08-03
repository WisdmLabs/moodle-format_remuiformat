require(['jquery', 'format_remuiformat/jquery.easypiechart'], function ($) {
    var cardminHeight = 200;
    $(window).resize(function() {
        resizeContainer();
    });
    //Function to set Equal Height of all cards
    var setEqualHeight = function (selector) {

        if (selector.length > 0) {
            var arr = [];
            var selector_height;
            selector.css("min-height", "initial");
            selector.each(function (index, elem) {
                selector_height = elem.offsetHeight;
                selector_height = (selector_height > cardminHeight) ? selector_height : cardminHeight;
                arr.push(selector_height);
            });
            selector_height = Math.max.apply(null, arr);
            selector.css("min-height", selector_height);
        }
    }

    // Display the overlay
    $('.mod-card-container').mouseover(function () {
        $(this).children('.mod-card-overlay').removeClass("hidden");
        $(this).children('.card-hover-content').removeClass("hidden");
    }).mouseout(function () {
        $(this).children('.mod-card-overlay').addClass("hidden");
        $(this).children('.card-hover-content').addClass("hidden");
    });

    // Mark Completion
    $('.card-complete-btn').click(function () {
        var val = $(this).parent().children('.card-completion-state').val();
        if (val == 0) {
            $(this).find('.card-stats').html(M.util.get_string('markcomplete', 'format_remuiformat'));
        } else {
            $(this).find('.card-stats').html(M.util.get_string('completed', 'format_remuiformat'));
        }
    });

    $('.wdm-toggle-completion').click(function() {
        var id = $(this).data('id');
        $('.wdm-completion-check-'+id+' button').trigger('click');
        var completion = $('.wdm-completion-status-'+id).text().trim();
        if (completion == "Completed") {
            $('.wdm-completion-status-'+id).html("Mark as Complete");
            $('.wdm-chart-'+id).data('easyPieChart').update(0);
            $('.activity-check-'+id).removeClass("completed");
            $('.activity-check-'+id).addClass("text-muted");
        } else {
            $('.wdm-completion-status-'+id).html("Completed");
            $('.wdm-chart-'+id).data('easyPieChart').update(100);
            $('.activity-check-'+id).removeClass("text-muted");
            $('.activity-check-'+id).addClass("completed");
        }
        return false;
    });

    $('.form.togglecompletion').submit(function(e) {
        e.preventDefault();
    });

    // Set Equal height of cards on load
    setEqualHeight($('.single-card'));
    $('.section-action-container').addClass('card-bottom');
    $('.wdm-bottom-container').addClass('card-bottom');
    $('#page-course-view-remuiformat span.section-modchooser-link').addClass("btn btn-primary");
    $('#page-course-view-remuiformat #changenumsections').css({visibility: "visible"});
    $('#page-course-view-remuiformat #changenumsections a').addClass("btn btn-primary");
    $('#page-course-view-remuiformat #changenumsections').addClass("row d-flex justify-content-end");
    $('.general-single-card').css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0}, 200, "swing");
    $('.single-card').css({opacity: 0.0, visibility: "visible"}).animate({opacity: 1.0}, 400, "swing");
    $('.pchart').easyPieChart({
        'barColor': '#15C941',
        'trackColor': false,
        'scaleColor': false,
        'lineWidth': 3,
        'size':40
    });
    setEqualHeight($('.card-section-list'))
    $('.card-section-leftnav a').removeClass('bg-primary');
    $('.card-section-rightnav a').removeClass('bg-primary');

    function resizeContainer() {
        if ($(window).width() >= 1300) {
            if ($('body').hasClass('site-menubar-unfold') && !$('body').hasClass('overrideaside')) {
                $('.single-card-container').removeClass('col-lg-4');
                $('.single-card-container').addClass('col-lg-6');
            } else {
                $('.single-card-container').removeClass('col-lg-6');
                $('.single-card-container').addClass('col-lg-4');
            }
        }
        if ($(window).width() < 1300 && $(window).width() > 768) {
            if ($('body').hasClass('site-menubar-unfold') || !$('body').hasClass('overrideaside')) {
                if (!$('body').hasClass('overrideaside') && $('body').hasClass('site-menubar-unfold')) {
                    $('.single-card-container').removeClass('col-lg-12');
                    $('.single-card-container').removeClass('col-lg-4');
                    $('.single-card-container').addClass('col-lg-12');
                } else {
                    $('.single-card-container').removeClass('col-lg-12');
                    $('.single-card-container').removeClass('col-lg-4');
                    $('.single-card-container').addClass('col-lg-6');
                }
            } else {
                $('.single-card-container').removeClass('col-lg-12');
                $('.single-card-container').removeClass('col-lg-6');
                $('.single-card-container').addClass('col-lg-4');
            }
        }

        if ($(window).width() <= 768 && $(window).width() > 420) {
            if ($('body').hasClass('site-menubar-unfold')) {
                $('.single-card-container').removeClass('col-md-6');
                $('.single-card-container').addClass('col-md-12');
            } else {
                $('.single-card-container').removeClass('col-md-12');
                $('.single-card-container').addClass('col-md-6');
            }
        } 
    }

    $(document).on('click', '.page-aside-switch-lg', function() {
        resizeContainer();
    });

    $('body').bind('click', '#toggleMenubar', function() {
        setTimeout(function() {
            resizeContainer();
        }, 10);
    });

    $(document).on('resize', '#card-container', function() {
        console.log("Triggered");
    });

    M.course = M.course || {};

    M.course.format = M.course.format || {};

    M.course.format.get_config = function () {
        return {
            container_node: 'div',
            container_class: 'cards',
            section_node: 'div',
            section_class: 'section'
        };
    }

    /**
     * Swap section
     *
     * @param {YUI} Y YUI3 instance
     * @param {string} node1 node to swap to
     * @param {string} node2 node to swap with
     * @return {NodeList} section list
     */
     M.course.format.swap_sections = function (Y, node1, node2) {
        var CSS = {
            COURSECONTENT: 'course-content',
            SECTIONADDMENUS: 'section_add_menus'
        };

        var sectionlist = Y.Node.all('.' + CSS.COURSECONTENT + ' ' + M.course.format.get_section_selector(Y));
        // Swap menus.
        sectionlist.item(node1).one('.' + CSS.SECTIONADDMENUS).swap(sectionlist.item(node2).one('.' + CSS.SECTIONADDMENUS));
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
     M.course.format.process_sections = function (Y, sectionlist, response, sectionfrom, sectionto) {
        var CSS = {
            SECTIONNAME: 'sectionname'
        },
        SELECTORS = {
            SECTIONLEFTSIDE: '.left .section-handle .icon'
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
                sectionlist.item(i).all('.' + CSS.SECTIONNAME).setHTML(content);
                // Update move icon.
                ele = sectionlist.item(i).one(SELECTORS.SECTIONLEFTSIDE);
                str = ele.getAttribute('alt');
                stridx = str.lastIndexOf(' ');
                newstr = str.substr(0, stridx + 1) + i;
                ele.setAttribute('alt', newstr);
                ele.setAttribute('title', newstr); // For FireFox as 'alt' is not refreshed.
            }
        }
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
        // alert(sectionlist);
        if (response.action == 'move') {
            // alert('Hi!');
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
                var content = Y.Node.create(response.sectiontitles[i]);
                sectionlist.item(i).all('h4.'+CSS.SECTIONNAME).setHTML(content);
                // alert(sectionlist.item(i));
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