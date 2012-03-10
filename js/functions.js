$(document).ready(function () {
	if ($('#confirm-fade')) {
		$('#confirm-fade').fadeOut(9000);
	}
	
	bind_events();
});

throbber_html = '<img src="' + ROOT + 'images/ajax-loader.gif" alt="" />';
delete_reload = false;

function redirect(page) {
	window.location.href = ROOT + page; 
}

function regex_replace(str) {
	var replace_slug = str;
	// replace apostrophes with nothing (eg: waiter's --> waiters, not waiter-s)
	replace_slug = replace_slug.replace(/'/g, '');
	replace_slug = replace_slug.replace(/[^A-Za-z0-9\/]/g, '-').toLowerCase();
	replace_slug = replace_slug.replace(/--+/g, '-');
	replace_slug = replace_slug.replace(/\/\/+/g, '/');
	replace_slug = replace_slug.replace(/-\//g, '/');
	replace_slug = replace_slug.replace(/\/-/g, '/');
	
	// If the first character in the string is a dash or slash, replace it with nothing 
	if (replace_slug.charAt(0) == '-' || replace_slug.charAt(0) == '/')
		replace_slug = replace_slug.substr(1);
	
	var last_char = replace_slug.charAt(replace_slug.length - 1);
	if (last_char == '-' || last_char == '/')
		replace_slug = replace_slug.substring(0, replace_slug.length - 1);	
	
	return replace_slug;
}

function bind_events() {
	$('input[type="image"].delete').click(function() {
	    $('#error-message').hide();
	
		var row = $(this).parents('tr');
		var cell = $(this).parents('td');
		var orig_content = cell.html();
		if ($(this).hasClass('confirm')) {
			var result = confirm("Are you sure you want to delete this item?");
			if (!result) {
				return false;
			}
		}
		
		cell.html(throbber_html);
		$('#error-message').hide();
		
		var url = $('#url-delete').val();
		if (!url) {
			alert("Could not delete the selected item. Please contact the server administrator: no delete path specified in url-delete.");
			cell.html(orig_content);
		    bind_events();
		    return;
		}
		
		$.ajax({
			type: "POST",
			url: ROOT + url,
			data: 'id=' + $(this).siblings('input[name="id"]').val() + '&token=' + TOKEN + '&slug=' + $(this).siblings('input[name="slug"]').val(),
			dataType: "text",
			success: function(data) {
				if (data == '1') {
					row.fadeOut();
					if (delete_reload) {
						window.location.reload();
					}
				} else {
					err_str = data.substring(1);
					$('#error-message').html('An error occurred deleting the selected content. ' + err_str);
					$('#error-message').show();
					cell.html(orig_content);
					bind_events();
				}
			},
			failure: function() {
				$('#error-message').html('An error occurred deleting the selected content: could not communicate with the server.');
				$('#error-message').show();
				cell.html(orig_content);
				bind_events();
			}
		});
		
		return false;
	});
}