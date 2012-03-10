

function validate_save_user() {
	$('#errorbox').hide();
	
	if (orig_user && $('#email').val() != orig_user && !$('#password').val()) {
		// password fail
		$('#errorbox').html('Please provide a password, since you changed the email address for this account.');
		$('#errorbox').show();
		$('#password').focus();
		return false;
	}
	
	if (!$('#email').val()) {
		// email fail
		$('#errorbox').html('Please provide an email address for this account.');
		$('#errorbox').show();
		$('#email').focus();
		return false;
	}
	
	return true;
}