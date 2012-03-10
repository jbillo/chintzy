var slug_linked = true;

$(document).ready(function() {
	hide_error_boxes();
	$('#slug-unlinked').hide();
	$('#edit-slug').toggleClass('disabled');
	$('#edit-slug').attr('readonly', 'readonly');
	
	$('#text').ckeditor();
	$('#editor-all').show();
	$('#ajax-loader').html('');
	$('#edit-title').focus();
});

function hide_error_boxes() {
	$('div.errorbox').hide();
}

function validate_and_submit() {
	if (validate_edit_form()) {
		$('#edit-form').submit();
	}
}

function validate_edit_form() {
	// TODO add pre-submit validation for slug (maybe? AJAX?)
	hide_error_boxes();
	
	// Check title
	if ($('#edit-title').val() == '') {
		$('#error-no-title').show();
		$('#edit-title').focus();
		return false;
	}	

	// Check restricted slugs and slug value
	var slug = $('#edit-slug').val();
	if ($.inArray(slug, restricted_slugs) != -1) {
		$('#error-restricted-slug').show();
		$('#edit-slug').focus();
		return false;
	}
	
	// Check slug
	if (slug == '') {
		$('#error-no-slug').show();
		$('#edit-slug').focus();
		return false;
	}
	
	// Check text from tinyMCE
	var text = $('#text').val();
	if (text == '') {
		$('#error-no-text').show();
		$('#text').focus();
		return false;
	}
	
	return true;
}

function update_slug() {
	var replace_slug = $('#edit-slug').val();
	var slug_prefix = '';
	if (slug_linked) {
		replace_slug = $('#edit-title').val();
		parent_id = $('#edit-parent-id').val();
		// If a subcategory is selected that's not ID=1, and we're in linked mode, 
		// add the regex'd title to the slug URL. So with 'contacts' as the parent page, we'd get contacts/title-goes-here as the slug.	
		if (parent_id != 1) {
			slug_prefix = regex_replace($("#edit-parent-id option[value='" + parent_id + "']").text()) + '/';
		}
	}
	
	replace_slug = slug_prefix + regex_replace(replace_slug);

	$('#edit-slug').val(replace_slug);
}

function change_slug() {
	slug_linked = false;
	update_slug();
}

function unlink_slug() {
	// Unlink and validate the slug
	slug_linked = false;
	$('#edit-slug').toggleClass('disabled');
	$('#edit-slug').removeAttr('readonly');
	$('#slug-linked').hide();
	$('#slug-unlinked').show();
	update_slug();
	$('#edit-slug').focus();
}

function link_slug() {
	// Relink and validate the slug
	slug_linked = true;
	$('#edit-slug').toggleClass('disabled');
	$('#edit-slug').attr('readonly', 'readonly');
	$('#slug-linked').show();
	$('#slug-unlinked').hide();	
	update_slug();
	$('#edit-title').focus();
}