
var ok_text = err_text = '';
jQuery(document).ready(function($) {
	"use strict";
    jQuery('.date-picker-admin').datepicker({
        dateFormat: "yy-mm-dd"
    });

    jQuery(".chosen-select").chosen();


    if ($('#message').length > 0) {
        setTimeout(function() {
            $('#message').fadeOut(1000, 'swing', function() {
                $(this).remove();
            });
        }, 4000);
    }

    // emails page filter
    $("input[name='now_on']").change(function() {
        date_field_readonly();
    });
    date_field_readonly();
    

    /****************************************************************/
    ok_text = $(".exporter-data").attr("data-ok");
    err_text = $(".exporter-data").attr("data-error");
    /**
     * Add Sortable Section
     */
    $("#sortable").sortable();

    /**
     * Admin tabs
     */
    $("#booking-export-tabs").tabs();

    /**
     * Add And Remove Sortable Elements According To Selected Checkbox
     */
    $(".exporter-data input[type=checkbox]").on("change", function(e) {
        var text = $(this).parent().find("label").html();
        var id = $(this).attr("name");
        if ($(this).is(":checked")) {
            $(".sort-div #sortable").append("<li class='ui-state-default' id='" + id + "'>\n\
<span class='ui-icon ui-icon-arrowthick-2-n-s'></span>\n\
<span class='text'>" + text + "</span>\n\
<span class='edit_name'> <i class='dashicons dashicons-edit'></i></span>\n\
</li>");
            $("#sortable").sortable();
        } else {
            $("#" + id).remove();
        }
    });



    /**
     * On Submit Send Sortable Data 
     */
    $(".booking-export-form").on("submit", function(e) {
        var ids = $(".booking-export-form #sortable").sortable("toArray");
        ids = JSON.stringify(ids);
        $(".booking-export-form .coloums_order").val(ids);
    });


    /**
     * Select All Checkboxs
     */
    $(".booking-export-form .select-all-options").on("click", function(e) {
        e.preventDefault();
        $(".booking-export-form input[type=checkbox]:not(#save_template)").each(function() {
            if (!$(this).is(":checked")) {
                $(this).attr('checked', true).trigger("change");
            }
        });
    });

    /**
     * Change file type according to submitted button 
     */
    $(".booking-export-form  .submit_btn").on("click", function() {
        if ($(this).hasClass("export_pdf")) {
            $(".booking-export-form .file_type").val("pdf");
            console.log('Export type: PDF');
        } else if ($(this).hasClass("export_csv")) {
            $(".booking-export-form .file_type").val("csv");
            console.log('Export type: CSV');
        } else if ($(this).hasClass("export_excel")) {
            $(".booking-export-form .file_type").val("excel");
            console.log('Export type: EXCEL');
            console.log('File type value:', $(".booking-export-form .file_type").val());
        }

    });


    /**
     * 
     */
    $(".template_manage_wrapper .save_template").on('change', function() {
        $(".template_manage_wrapper .save_template_wrapper").toggle(500);
    });

    $(".template_manage_wrapper .templates").on('change', function() {
        var url = window.location.href;
        window.location.href = url + "&template=" + $(this).val();
    });

    $("a#wbe-delete-template").on('click', function(event) {
        event.preventDefault();
        const template_name = $(this).siblings('input').attr('name');
        wbe_delete_template(template_name);
    });
});


/**
 * Edit Name of Field
 */
"use strict";
jQuery(document).on("click", ".booking-export-form .edit_name", function() {
    var text = $(this).parent().find(".text").html();
    $(this).parent().append("<div class='edit_name_wrapper'> <input class='coloum_name' value='" + text + "'><button class='ok'>" + ok_text + "</button></div>");
    $(this).hide();
});


/**
 * Save Field Data 
 */
jQuery(document).on("click", ".booking-export-form .ok", function(e) {
    e.preventDefault();
    $(".booking-export-form .err-msg").remove();
    var text = $(this).parent().find(".coloum_name").val();
    if (jQuery.trim(text).length > 0) {
        var id = $(this).closest(".ui-state-default").attr("id");
        $(".exporter-data input[type=checkbox][name='" + id + "']").val(text);
        $(this).closest(".ui-state-default").find(".text").html(text);
        $(this).closest(".ui-state-default").find(".edit_name").show();
        $(this).parent().remove();
    } else {
        $("<p class='err-msg'>" + err_text + "</p>").insertAfter('.coloum_name')
    }

});

jQuery(document).on("submit", ".import-div #imported-form", function(e) {
    jQuery(".import-div #imported-form .error").hide();

    if (!jQuery("#imported-file").val()) {
        e.preventDefault();
        jQuery(".import-div #imported-form .error").show();
    }

});


jQuery(document).on("click", ".ajaxified_csv_export, .ajaxified_pdf_export", function(e) {

    e.preventDefault();
    var filetype = 'csv';

    if(jQuery(this).hasClass("ajaxified_pdf_export")) {
        filetype = 'pdf';
    }
    woo_bookings_csv_export(1, filetype);
    jQuery("#wbe_progressbar").show();
});

function woo_bookings_csv_export(pageno, filetype) {
    var data = new FormData(jQuery('form.booking-export-form')[0]);
    var ids = jQuery(".booking-export-form #sortable").sortable("toArray");
    ids = JSON.stringify(ids);

    data.set('action', 'woo_bookings_ajax_csv_export');
    data.set('coloums_order', ids);
    data.set('file_type', filetype);

    data.append('woo_bookings_export_csv', 'yes');
    data.append('woo_bookings_export_pageno', pageno);
    data.append('woo_bookings_export_nonce', woo_bookings_export.nonce);

    jQuery.ajax({
        url: ajaxurl,
        data: data,
        type: 'POST',
        contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
        processData: false, // NEEDED, DON'T OMIT THIS
        success: function(response){
            response_obj = JSON.parse(response);
            
            if(response_obj.page_no && response_obj.total_pages && response_obj.page_no > 0 && response_obj.total_pages > 0 && response_obj.page_no <= response_obj.total_pages) {
                woo_bookings_csv_export(response_obj.page_no, response_obj.file_type);

                var wbe_percent_done = Math.round((response_obj.page_no / response_obj.total_pages) * 100);
                jQuery('#wbe_progressbar div').width('' + wbe_percent_done + '%');
                jQuery('#wbe_progressbar div').html('' + wbe_percent_done + '%');
                  
            } else {
                window.location.href = response_obj.file_url;
                jQuery("#wbe_progressbar").hide();
            }
              
        },
        error: function(xhr, ajaxOptions, thrownError) {
            jQuery("#wbe_progressbar").hide();
            alert("Request Failed Please try again");
        }
    });
}

function wbe_delete_template(name) {
    var formData = new FormData();
    formData.set('action', 'woo_bookings_delete_template');
    formData.append('template_name', name);
	
    jQuery.ajax({
        url: ajaxurl,
        data: formData,
        type: 'POST',
        contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
        processData: false, // NEEDED, DON'T OMIT THIS
        success: function(response) {
		
            if (response.success === true) {
                var key = response.key;
                jQuery("input[name='" + key + "']").parent().fadeOut();
            }
            jQuery(".export-div form").prepend('<span class="wbe-delete-template-message" style="color: green;"></span>');
            if (jQuery(".wbe-delete-template-message").length === 1) {
                jQuery(".wbe-delete-template-message").text(response.message);
            }
            setTimeout(function() {
                jQuery("span.wbe-delete-template-message").fadeOut();
            }, 10000);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert("Request Failed Please try again");
        }
    });
}

function date_field_readonly() {
    if( $("input[name='now_on']").is(":checked") ) {
        $("input[name='email_booking_to_date']").attr('readonly', true);
        $("input[name='email_booking_to_date']").val("");
    } else {
        $("input[name='email_booking_to_date']").attr('readonly', false);
    }
}