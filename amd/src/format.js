define(['jquery', 'core/str'],
function ($, str) {
    
    // var def_constants;
    
    function init(arr) {
        str.get_strings([
            {'key' : 'showallsectionperpage', 'component':'format_remuiformat'},
        ]).done(function(ss) {
            // def_constants = arr;
            $(document).ready(function(){
                var sectionlayout_val;
                // Hide and show the course settings on course format selection.
                $("#id_remuicourseformat").change(function(){
                    var layout_value = $("#id_remuicourseformat").val();
                    if (layout_value == 0) {
                        $("#id_coursedisplay option[value='0']").remove();
                        $('#id_coursedisplay').val(1).trigger('change');
                        $("#id_remuicourseimage_filemanager").parent().parent().hide();
                        $("#id_remuiteacherdisplay").parent().parent().hide();
                        $("#id_remuidefaultsectionview").parent().parent().hide();
                    } else {
                        console.log(ss[0]);
                        $('#id_coursedisplay').append('<option value="0">' + ss[0] + '</option>');
                        var oldcoursedisplay = window.localStorage.getItem('coursedisplay');
                        $('#id_coursedisplay').val(oldcoursedisplay).trigger('change');
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
                window.localStorage.setItem('coursedisplay', $("#id_coursedisplay").val());

                if (layout_value == 0) {
                    $("#id_coursedisplay").find("option").eq(1).hide();
                    $("#id_remuicourseimage_filemanager").parent().parent().hide();
                    $("#id_remuiteacherdisplay").parent().parent().hide();
                    $("#id_remuidefaultsectionview").parent().parent().hide();
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
        ).fail(function(){

        });
    }

    // Must return the init function.
    
    return {
        init: init
    }
});