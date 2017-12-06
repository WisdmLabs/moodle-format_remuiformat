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
});