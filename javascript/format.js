var def_constants;
function init(Y, arr){
    def_constants = arr;
}
// var both_layout = {};
// var single_section_layout = {};
// var multiple_section_layout = {};
require(['$', 'core/str'], function ($) {
    // console.log(def_constants);

    $(document).ready(function(){

        var sectionlayout_val;
        // Hide and show the course settings on course format selection
        var format_value = $("#id_format").val();
        if (format_value == 'remuiformat') {
            var coursedisplay       = $("#id_coursedisplay").parent().parent();
            var remuicourseformat   = $("#id_remuicourseformat").parent().parent();
            $(coursedisplay).exchangePositionWith(remuicourseformat);
        }
        $("#id_remuicourseformat").change(function(){
            var layout_value = $("#id_remuicourseformat").val();
            if (layout_value == 0) {
                $("#id_coursedisplay").find("option").eq(1).hide();
                $("#id_remuicourseimage_filemanager").parent().parent().hide();
                $("#id_remuiteacherdisplay").parent().parent().hide();
            } else {
                $("#id_coursedisplay").find("option").eq(1).show();
                $("#id_remuicourseimage_filemanager").parent().parent().show();
                $("#id_remuiteacherdisplay").parent().parent().show();
            }
            sectionlayout_val = $("#id_coursedisplay").val();
            if (sectionlayout_val == 1) {
                $("#id_remuidefaultsectionview").parent().parent().hide();
            } else {
                $("#id_remuidefaultsectionview").parent().parent().show();
            }
        });
        var layout_value = $("#id_remuicourseformat").val();
        if (layout_value == 0) {
            $("#id_coursedisplay").find("option").eq(1).hide();
            $("#id_remuicourseimage_filemanager").parent().parent().hide();
            $("#id_remuiteacherdisplay").parent().parent().hide();
        } else {
            $("#id_remuicourseimage_filemanager").parent().parent().show();
            $("#id_remuiteacherdisplay").parent().parent().show();
            sectionlayout_val = $("#id_coursedisplay").val();
            if (sectionlayout_val == 1) {
                $("#id_remuidefaultsectionview").parent().parent().hide();
            }
        }
        $("#id_coursedisplay").change(function(){
            sectionlayout_val = $("#id_coursedisplay").val();
            if (sectionlayout_val == 1) {
                $("#id_remuidefaultsectionview").parent().parent().hide();
            } else {
                $("#id_remuidefaultsectionview").parent().parent().show();
            }
        });
    });
    // Function to excahnge the element position
    $.fn.exchangePositionWith = function(selector) {
        var other = $(selector);
        this.after(other.clone());
        other.after(this).remove();
    };
});