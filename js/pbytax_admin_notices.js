/** MANAGE COOKIES **/
function pbytaxSetCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
function pbytaxGetCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}
function pbytaxAddCookie(cname, cvalue) {
	if ( !cname ){
		return;
	}
    var cookie = pbytaxGetCookie(cname);
    if (cookie != "") {
        return;
    } else {
        if (cvalue != "" && cvalue != null) {
            pbytaxSetCookie(cname, cvalue, 7);
        }
    }
}
function pbytaxCheckCookie(cname, cvalue) {
    var cookie = pbytaxGetCookie(cname);
    if ( cookie == cvalue ) {
        return true;
    }
	else {
		return false;
	}
}

/* set cookie when clicking to close the notice */
jQuery(document).on( 'click', '#pbytax-update-notice .notice-dismiss', function() {
	var noticeID = jQuery(this).parent().attr('id');
	pbytaxAddCookie('pbytax_notice_closed', noticeID);
	
});

