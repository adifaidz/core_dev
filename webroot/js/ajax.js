/*
	AJAX implementation with class interface in JavaScript by Martin Lindhe, Feb 2007

	Example:
	
	var request = new AJAX();
	request.GET('/ajax/url.php?i='+id, function_callback, id);
*/

//class definition of AJAX
function AJAX()
{
	var _request = false;
	this.GET = GET;
	this.GET_raw = GET_raw;
	this.ResultReady = ResultReady;
	this.EmptyResponse = EmptyResponse;

	if (window.XMLHttpRequest) { // Mozilla, Safari, Opera...
		this._request = new XMLHttpRequest();
	} else if (window.ActiveXObject) { // IE
		try {
			this._request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				this._request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}

	if (!this._request) {
		alert('Giving up, Cannot create an XMLHTTP instance');
		return false;
	}
	
	// Performs an GET-request expected to return XML
	function GET(url, callback, callbackparam, params)
	{
		if (!this._request) return false;
		if (this._request.overrideMimeType) this._request.overrideMimeType('text/xml');
		if (callback) this._request.onreadystatechange = function() { callback(callbackparam); }
		this._request.open('GET', url, true);
		this._request.send(null);
	}

	// Performs an GET-request expected to return anything, like raw text or html
	function GET_raw(url, callback, callbackparam, params)
	{
		if (!this._request) return false;
		if (callback) this._request.onreadystatechange = function() { callback(callbackparam); }
		this._request.open('GET', url, true);
		this._request.send(null);
	}
	function ResultReady()
	{
		if (!this._request || this._request.readyState != 4) return false;
		if (this._request.status == 200) return true;

		return false;
	}
	
	//Returns true if 'name' is the root tag of this xml object
	function EmptyResponse(name)
	{
		return this._request.responseXML.getElementsByTagName(name).item(0);
	}
}
//todo: i show_ajax_anim kolla om den fortfarande beh�vs visas!
var ajax_anim_abort = 0;
function show_ajax_anim() { if (!ajax_anim_abort) show_element_by_name('ajax_anim'); ajax_anim_abort = 0; }
function hide_ajax_anim() { hide_element_by_name('ajax_anim'); }

//todo: f�rs�k kom p� ett mer standardiserat interface till GET-funktionen s� att mindre s�dan h�r init&callback kod beh�vs


var delete_request = null;
function perform_ajax_delete_uservar(id,ref)
{
	delete_request = new AJAX();
	delete_request.GET('/core/ajax_del_uservar.php?i='+id+ref, ajax_delete_uservar_callback, id);

	//uses callback function to only display animated icon if request isnt already completed in 20ms
	//this removes the flickering display of animated image if you have very low response times
	setTimeout("show_ajax_anim()", 20);
}

function ajax_delete_uservar_callback(id)
{
	if (!delete_request.ResultReady()) return;

	if (!delete_request.EmptyResponse('ok')) {
		var e = document.getElementById('ajax_anim_pic');
		e.setAttribute('src', '/gfx/icon_warning_big.png');
		e.setAttribute('title', 'Database error');
		return;
	}

	hide_element_by_name('edit_setting_div_'+id);
	hide_ajax_anim();
	delete_request = null;
}


//Sends an AJAX call to delete specified file
var delete_file_request = null;
function ajax_delete_file(id, ref)
{
	if (!ref) ref='';
	delete_file_request = new AJAX();
	delete_file_request.GET('/core/ajax_del_file.php?i='+id+ref, null);
}

var fileinfo_request = null;
function ajax_get_fileinfo(id, ref)
{
	if (!ref) ref='';
	fileinfo_request = new AJAX();
	fileinfo_request.GET_raw('/core/ajax_fileinfo.php?i='+id+ref, ajax_get_fileinfo_callback);
	
	setTimeout("show_ajax_anim()", 20);
}

function ajax_get_fileinfo_callback()
{
	if (!fileinfo_request.ResultReady()) return;

	zoomShowFileInfo(fileinfo_request._request.responseText);
	ajax_anim_abort = true;
	hide_ajax_anim();

	fileinfo_request = null;
}

function submit_apc_upload(id,ref)
{
	if (!ref) ref='';
	//submit form
	document.ajax_file_upload.submit();
	show_element_by_name('file_gadget_apc_progress');

	ajax_get_upload_progress(id,ref);

	return false;
}

var upload_progress_request = null;
function ajax_get_upload_progress(id,ref)
{
	if (!ref) ref='';
//	if (upload_progress_request == null) {
		upload_progress_request = new AJAX();
		upload_progress_request.GET_raw('/core/ajax_upload_progress.php?s='+id+ref, ajax_get_upload_progress_callback);
//	}
	setTimeout("ajax_get_upload_progress("+id+","+ref+")", 500);
}

function ajax_get_upload_progress_callback()
{
	if (!upload_progress_request.ResultReady()) return;

	var e = document.getElementById('file_gadget_apc_progress');
	empty_element(e);

	e.innerHTML = upload_progress_request._request.responseText;

	upload_progress_request = null;
}