define(['jquery', 'core/str'], function ($) {
    
    var def_constants;
    
    function init(arr) {
        def_constants = arr;
        $(document).ready(function(){
            var sectionlayout_val;
            // Hide and show the course settings on course format selection.
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
    }

    // Must return the init function.
    
    return {
        init: init
    }
});