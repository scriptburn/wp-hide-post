var $ = jQuery.noConflict();
var wphp_texts = {
    "sure_remove": "Are you sure to remove this item?",
    "unable_process": "Unable to process",
    "procesed_sucess": "Procesed sucessfully",
    "error": "Error",
    "no_connection": "Not connected.Please verify your network connection.",
    "page_not_found": "The requested page not found. [404]",
    "internal_server_error": "Internal Server Error [500].",
    "json_failed": "Requested JSON parse failed.",
    "time_out": "Time out error.",
    "ajax_aborted": "Ajax request aborted.",
    "uncaught_error": "Uncaught Error",
    "confirm": "Confirm",
};
$(document).ready(function()
{
    $(".chosen-select").chosen(
    {
        'width': '400px'
    });
    $("body").append('<div id="dialog-message" style="display:none;z-index:2000" title=""></div>');
    modalDialog = $("#dialog-message").dialog(
    {
        modal: true,
        autoOpen: false,
        autoResize: true,
        buttons:
        {
            Ok: function()
            {
                $(this).dialog("close");
            }
        }
    });
    /* quick edit ajax handler */
    $('a.editinline1').on('click', function(event)
    {
        var id = inlineEditPost.getId(this);
        set_wphp_hide_on_value(id);
        return true;;
        //$('.wphp_quickedit').html("");
        var data = {
            'action': 'wphp_post_visibility_data',
            'post_id': post_id,
            'post_type': $("#posts-filter [name='post_type']").val()
        };
        var save_btn = $('#edit-' + post_id).find('.inline-edit-save>.save')
        $(save_btn).data('label', $(save_btn).val());
        $(save_btn).val('loading..');
        $(save_btn).attr('disabled', true);
        $.ajax(
        {
            type: "POST",
            url: ajaxurl,
            data: data,
            dataType: 'json',
            success: function(response)
            {
                if (!response.status)
                {
                    showModalMsg(wphp_texts.error, response.error,
                    {});
                    $(save_btn).val($(save_btn).data('label'));
                    $(save_btn).attr('disabled', false)
                }
                else
                {
                    $(save_btn).val($(save_btn).data('label'));
                    $(save_btn).attr('disabled', false);
                    $('.wphp_quickedit').replaceWith(response.html);
                }
            },
            fail: function(xhr, err)
            {
                id = $(this)[0].data.split('&')[1].split("=")[1];
                $(save_btn).val($(save_btn).data('label'));
                $(save_btn).attr('disabled', false)
                showModalMsg(wphp_texts.error, formatErrorMessage(xhr, err));
            }
        });
    });
});

function showModalMsg(title, body, option)
{
    option = typeof option === 'undefined' ?
    {} : option;
    option.title = title
    $(modalDialog).dialog(option);
    $(modalDialog).html(htmlBody(body));
    $(modalDialog).dialog("open");
}

function htmlBody(body)
{
    html = "";
    if (body instanceof Array)
    {
        for (var item in body)
        {
            html = html + body[item] + "</br>";
        }
    }
    else
    {
        html = body;
    }
    return html;
}

function formatErrorMessage(jqXHR, exception)
{
    if (jqXHR.status === 0)
    {
        return (wphp_texts.no_connection);
    }
    else if (jqXHR.status == 404)
    {
        return (wphp_texts.page_not_found);
    }
    else if (jqXHR.status == 500)
    {
        return (wphp_texts.internal_server_error);
    }
    else if (exception === 'parsererror')
    {
        return (wphp_texts.json_failed);
    }
    else if (exception === 'timeout')
    {
        return (wphp_texts.time_out);
    }
    else if (exception === 'abort')
    {
        return (wphp_texts.ajax_aborted);
    }
    else
    {
        return (wphp_texts.uncaught_error + '\n' + jqXHR.responseText);
    }
}
// add date to our quick edit box
function set_wphp_hide_on_value(post_id)
{
    // define the edit row
    var $edit_row = $('#edit-' + post_id);
    var $post_row = $('#post-' + post_id);
    // get the data
    var $wphp_hide_on = $('.column-wphp_hide_on', $post_row);
    // populate the data
    //$(':input[name="book_author"]', $edit_row).val($book_author);
    //$(':input[name="inprint"]', $edit_row).attr('checked', $inprint);
    ///
    if (!$('.wphp_hidden_on', $wphp_hide_on).length || !$('.wphp_hide_on_data', $wphp_hide_on).length)
    {
        return;
    }
    visibility_values = JSON.parse(decodeURIComponent($('.wphp_hidden_on', $wphp_hide_on).val()));
    wphp_hide_on_data = JSON.parse(decodeURIComponent($('.wphp_hide_on_data', $wphp_hide_on).val()));

        $('#' + wphp_hide_on_data['nonce_field'], $edit_row).val(wphp_hide_on_data['nonce_value']);


    // refresh the quick menu properly
    if (visibility_values === 'undefined' || !Object.keys(visibility_values).length)
    {
        return;
    }
    for (key in visibility_values)
    {
        if (visibility_values[key])
        {
            $('[name="wphp_visibility_type\[' + key + '\]"]', $edit_row).attr('checked', 'checked');
        }
        $('[name="wphp_visibility_type_old\[' + key + '\]"]', $edit_row).val(visibility_values[key]);
        //jQuery('#myfield').val(fieldValue);
    }
}
$(document).ready(function()
{
    if(typeof inlineEditPost!=='undefined')
    {


    // we create a copy of the WP inline edit post function
    var $wp_inline_edit = inlineEditPost.edit;
    // and then we overwrite the function with our own code
    inlineEditPost.edit = function(id)
    {
        // "call" the original WP edit function
        // we don't want to leave WordPress hanging
        $wp_inline_edit.apply(this, arguments);
        // now we take care of our business
        // get the post ID
        var $post_id = 0;
        if (typeof(id) == 'object') $post_id = parseInt(this.getId(id));
        if ($post_id > 0)
        {
            set_wphp_hide_on_value($post_id);
        }
    };
}
    $('#bulk_edit').on('click', function(event)
    {
        // define the bulk edit row
        var $bulk_row = $('#bulk-edit');
        // get the selected post ids that are being edited
        var $post_ids = new Array();
        $bulk_row.find('#bulk-titles').children().each(function()
        {
            $post_ids.push($(this).attr('id').replace(/^(ttle)/i, ''));
        });
        var data = {
            'action': 'save_bulk_edit_data',
            'post_ids': $post_ids,
            'post_type':inlineEditPost.type
        }
        for (key in wphp_hide_on_data['visibility_types'][inlineEditPost.type])
        {
            itm_name = wphp_hide_on_data['visibility_name'] + '[' + key + ']';
            itm_name_esc = wphp_hide_on_data['visibility_name'] + '\\[' + key + '\\]';
            itm_old = wphp_hide_on_data['visibility_name'] + '_old[' + key + ']';
            itm_old_esc = wphp_hide_on_data['visibility_name'] + '_old\\[' + key + '\\]';
            itm1 = wphp_hide_on_data['visibility_name'] + '\\[' + key + '\\]';
            data[itm_name] = $("[ name=" + itm_name_esc + "]").attr('checked') ? 1 : 0;
            data[itm_old] = $("[ name=" + itm_old_esc + "]").val();
            a = 1;
        }
        // save the data
        $.ajax(
        {
            url: ajaxurl, // this is a variable that WordPress has already defined for us
            type: 'POST',
            async: false,
            cache: false,
            'data': data
        });

        a=1;
    });
});