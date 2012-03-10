$(document).ready(function () {
	$('#comment-form').append('<input type="button" class="field-large" onclick="comment_submit()" value="Save Comment" />');
});

function comment_validate() {
	var ret_flag = true;
	
	$('div.error').hide();
		
	if ($('#comment-text').val() == '') {
		$('#comment-error-text').show();
		$('#comment-text').focus();
		ret_flag = false;	
	}
	
	if ($('#comment-user-email').val() == '') {
		$('#comment-error-user-email').show();
		$('#comment-user-email').focus();
		ret_flag = false;
	}
	
	if ($('#comment-user-name').val() == '') {
		$('#comment-error-user-name').show();
		$('#comment-user-name').focus();
		ret_flag = false;
	}
		
	return ret_flag;
}

function comment_submit() {
	if (comment_validate()) {
		$('#comment-form').submit();
	}
}