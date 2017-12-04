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

    function changeBorderColor(el,value) {
        console.log(el);
        console.log(value);
    }
});