
var def_constants;
function init(Y, arr){
    // console.log(arr);
    def_constants = arr;
}
var both_layout = {};
var single_section_layout = {};
var multiple_section_layout = {};
require(['jquery', 'core/str'], function ($, str) {
    // console.log(def_constants);

    $(document).ready(function(){
        // var string_layout;
        // Object.keys(def_constants).forEach(function(key){
        //     // console.log(key);
        //     string_layout = str.get_string(def_constants[key].optionlabel, 'format_remuiformat');
        //     $.when(string_layout).done(function(localizedEditString) {
        //         switch(def_constants[key].supports) {
        //             case 0:
        //                 // store formats that have only all sections on one page layout available in one array
        //                 multiple_section_layout[def_constants[key].format] = localizedEditString;
        //                 break;
        //             case 1:
        //                 // store formats that have only single section on one page layout available in one array
        //                 single_section_layout[def_constants[key].format] = localizedEditString;
        //                 break;
        //             case 2:
        //                 // store formats that have both the layouts available in one array
        //                 both_layout[def_constants[key].format] = localizedEditString;
        //                 break;
        //         }
        //     });
        // });
        // $.when(string_layout).done(function(localizedEditString) {
        //     changeLayouts = function(){
        //         // alert($("#id_coursedisplay option:selected").val());
        //         // get the value of selected option
        //         // 1 = Show one section per page
        //         // 0 = Show all sections on one page
        //         var selected_option = $("#id_coursedisplay option:selected").val();
        //         $('[name="wdm_remuicourselayouthidden"]').val(selected_option);
        //         var selected_layout = $("#id_remuicourseformat option:selected").val();
        //         console.log('selected_option: '+selected_option);
        //         $('#id_remuicourseformat').find('option').remove();
        //         // alert("Hello:" + selected_option);
        //         // add elements from appropriate arrays based on the selected option
        //         if(selected_option == 1){
        //             // console.log(single_section_layout);
        //             Object.keys(single_section_layout).forEach(function(key){
        //                 if(key == selected_layout){
        //                     $('#id_remuicourseformat')
        //                     .append($('<option>', {
        //                         value: key,
        //                         text: single_section_layout[key]
        //                     }).attr('selected', 'selected')
        //                     );
        //                 }
        //                 else {
        //                     $('#id_remuicourseformat').append($('<option>', {
        //                         value: key,
        //                         text: single_section_layout[key]
        //                     }));
        //                 }
        //                 // $('#id_remuicourseformat').append("<option value='" + key + "'>" + single_section_layout[key] + "</option>")
        //             });
        //         }
        //         else {
        //             // console.log(multiple_section_layout);
        //             // console.log(Object.keys(multiple_section_layout));
        //             Object.keys(multiple_section_layout).forEach(function(key){
        //                 // alert("added");
        //                 if(key == selected_layout){
        //                     $('#id_remuicourseformat')
        //                     .append($('<option>', {
        //                         value: key,
        //                         text: multiple_section_layout[key]
        //                     }).attr('selected', 'selected')
        //                     );
        //                 }
        //                 else {
        //                     $('#id_remuicourseformat').append($('<option>', {
        //                         value: key,
        //                         text: multiple_section_layout[key]
        //                     }));
        //                 }
        //                 // $('#id_remuicourseformat').append("<option value='" + key + "'>" + multiple_section_layout[key] + "</option>")
        //             });
        //         }

        //         // add elements available in both formats
        //         Object.keys(both_layout).forEach(function(key){
        //             if(key == selected_layout){
        //                     $('#id_remuicourseformat')
        //                     .append($('<option>', {
        //                         value: key,
        //                         text: both_layout[key]
        //                     }).attr('selected', 'selected')
        //                     );
        //                 }
        //                 else {
        //                     $('#id_remuicourseformat').append($('<option>', {
        //                         value: key,
        //                         text: both_layout[key]
        //                     }));
        //                 }
        //             // $('#id_remuicourseformat').append("<option value='" + key + "'>" + both_layout[key] + "</option>")
        //         });
        //     }
        //     $('#id_coursedisplay').on('change', changeLayouts);
        //     changeLayouts();
        // });

        // Hide and show the course settings on course format selection
        var format_value = $("#id_format").val();
        if (format_value == 'remuiformat') {
            coursedisplay       = jQuery("#id_coursedisplay").parent().parent();
            remuicourseformat   = jQuery("#id_remuicourseformat").parent().parent();
            $(coursedisplay).exchangePositionWith(remuicourseformat);
        }
        $("#id_remuicourseformat").change(function(){
            layout_value        = $("#id_remuicourseformat").val();
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
        layout_value = $("#id_remuicourseformat").val();
        if (layout_value == 0) {
            $("#id_coursedisplay").find("option").eq(1).hide();
            $("#id_remuicourseimage_filemanager").parent().parent().hide();
            $("#id_remuiteacherdisplay").parent().parent().hide();
        } else {
            $("#id_remuicourseimage_filemanager").parent().parent().show();
            $("#id_remuiteacherdisplay").parent().parent().show();
            var sectionlayout_val = $("#id_coursedisplay").val();
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
    //Function to excahnge the element position
    $.fn.exchangePositionWith = function(selector) {
        var other = $(selector);
        this.after(other.clone());
        other.after(this).remove();
    };
});