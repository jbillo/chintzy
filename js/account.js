function validate_create_account() {
    var pass = true;
    $('.error').hide();
    var fields = ['create-password-confirm', 'create-password', 'create-email'];
    for (var f in fields) {
        if ($('#' + fields[f]).val() == '') {
            $('#' + fields[f] + '-error').show();
            pass = false;
            $('#' + fields[f]).focus();
        }
    }
    
    return pass;
}