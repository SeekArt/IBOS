/* 
 * 2014-06-23 09:00:26 
 * swfupload.js + swfupload.queue.js + swfupload.speed.js + fileprogress.js 
*/ 

/**
 * SWFUpload: http://www.swfupload.org, http://swfupload.googlecode.com
 *
 * mmSWFUpload 1.0: Flash upload dialog - http://profandesign.se/swfupload/,  http://www.vinterwebb.se/
 *
 * SWFUpload is (c) 2006-2007 Lars Huring, Olov Nilz�n and Mammon Media and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * SWFUpload 2 is (c) 2007-2008 Jake Roberts and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 * SWFObject v2.2 <http://code.google.com/p/swfobject/> 
 *	is released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
 */



/* ******************* */
/* Constructor & Init  */
/* ******************* */
var SWFUpload;
var swfobject;

if (SWFUpload == undefined) {
	SWFUpload = function (settings) {
		this.initSWFUpload(settings);
	};
}

SWFUpload.prototype.initSWFUpload = function (userSettings) {
	try {
		this.customSettings = {};	// A container where developers can place their own settings associated with this instance.
		this.settings = {};
		this.eventQueue = [];
		this.movieName = "SWFUpload_" + SWFUpload.movieCount++;
		this.movieElement = null;


		// Setup global control tracking
		SWFUpload.instances[this.movieName] = this;

		// Load the settings.  Load the Flash movie.
		this.initSettings(userSettings);
		this.loadSupport();
		if (this.swfuploadPreload()) {
			this.loadFlash();
		}

		this.displayDebugInfo();
	} catch (ex) {
		delete SWFUpload.instances[this.movieName];
		throw ex;
	}
};

/* *************** */
/* Static Members  */
/* *************** */
SWFUpload.instances = {};
SWFUpload.movieCount = 0;
SWFUpload.version = "2.5.0 2010-01-15 Beta 2";
SWFUpload.QUEUE_ERROR = {
	QUEUE_LIMIT_EXCEEDED            : -100,
	FILE_EXCEEDS_SIZE_LIMIT         : -110,
	ZERO_BYTE_FILE                  : -120,
	INVALID_FILETYPE                : -130
};
SWFUpload.UPLOAD_ERROR = {
	HTTP_ERROR                      : -200,
	MISSING_UPLOAD_URL              : -210,
	IO_ERROR                        : -220,
	SECURITY_ERROR                  : -230,
	UPLOAD_LIMIT_EXCEEDED           : -240,
	UPLOAD_FAILED                   : -250,
	SPECIFIED_FILE_ID_NOT_FOUND     : -260,
	FILE_VALIDATION_FAILED          : -270,
	FILE_CANCELLED                  : -280,
	UPLOAD_STOPPED                  : -290,
	RESIZE                          : -300
};
SWFUpload.FILE_STATUS = {
	QUEUED       : -1,
	IN_PROGRESS  : -2,
	ERROR        : -3,
	COMPLETE     : -4,
	CANCELLED    : -5
};
SWFUpload.UPLOAD_TYPE = {
	NORMAL       : -1,
	RESIZED      : -2
};

SWFUpload.BUTTON_ACTION = {
	SELECT_FILE             : -100,
	SELECT_FILES            : -110,
	START_UPLOAD            : -120,
	JAVASCRIPT              : -130,	// DEPRECATED
	NONE                    : -130
};
SWFUpload.CURSOR = {
	ARROW : -1,
	HAND  : -2
};
SWFUpload.WINDOW_MODE = {
	WINDOW       : "window",
	TRANSPARENT  : "transparent",
	OPAQUE       : "opaque"
};

SWFUpload.RESIZE_ENCODING = {
	JPEG  : -1,
	PNG   : -2
};

// Private: takes a URL, determines if it is relative and converts to an absolute URL
// using the current site. Only processes the URL if it can, otherwise returns the URL untouched
SWFUpload.completeURL = function (url) {
	try {
		var path = "", indexSlash = -1;
		if (typeof(url) !== "string" || url.match(/^https?:\/\//i) || url.match(/^\//) || url === "") {
			return url;
		}
		
		indexSlash = window.location.pathname.lastIndexOf("/");
		if (indexSlash <= 0) {
			path = "/";
		} else {
			path = window.location.pathname.substr(0, indexSlash) + "/";
		}
		
		return path + url;
	} catch (ex) {
		return url;
	}
};

// Public: assign a new function to onload to use swfobject's domLoad functionality
SWFUpload.onload = function () {};


/* ******************** */
/* Instance Members  */
/* ******************** */

// Private: initSettings ensures that all the
// settings are set, getting a default value if one was not assigned.
SWFUpload.prototype.initSettings = function (userSettings) {
	this.ensureDefault = function (settingName, defaultValue) {
		var setting = userSettings[settingName];
		if (setting != undefined) {
			this.settings[settingName] = setting;
		} else {
			this.settings[settingName] = defaultValue;
		}
	};
	
	// Upload backend settings
	this.ensureDefault("upload_url", "");
	this.ensureDefault("preserve_relative_urls", false);
	this.ensureDefault("file_post_name", "Filedata");
	this.ensureDefault("post_params", {});
	this.ensureDefault("use_query_string", false);
	this.ensureDefault("requeue_on_error", false);
	this.ensureDefault("http_success", []);
	this.ensureDefault("assume_success_timeout", 0);
	
	// File Settings
	this.ensureDefault("file_types", "*.*");
	this.ensureDefault("file_types_description", "All Files");
	this.ensureDefault("file_size_limit", 0);	// Default zero means "unlimited"
	this.ensureDefault("file_upload_limit", 0);
	this.ensureDefault("file_queue_limit", 0);

	// Flash Settings
	this.ensureDefault("flash_url", "swfupload.swf");
	this.ensureDefault("flash9_url", "swfupload_fp9.swf");
	this.ensureDefault("prevent_swf_caching", true);
	
	// Button Settings
	this.ensureDefault("button_image_url", "");
	this.ensureDefault("button_width", 1);
	this.ensureDefault("button_height", 1);
	this.ensureDefault("button_text", "");
	this.ensureDefault("button_text_style", "color: #000000; font-size: 16pt;");
	this.ensureDefault("button_text_top_padding", 0);
	this.ensureDefault("button_text_left_padding", 0);
	this.ensureDefault("button_action", SWFUpload.BUTTON_ACTION.SELECT_FILES);
	this.ensureDefault("button_disabled", false);
	this.ensureDefault("button_placeholder_id", "");
	this.ensureDefault("button_placeholder", null);
	this.ensureDefault("button_cursor", SWFUpload.CURSOR.ARROW);
	this.ensureDefault("button_window_mode", SWFUpload.WINDOW_MODE.WINDOW);
	
	// Debug Settings
	this.ensureDefault("debug", false);
	this.settings.debug_enabled = this.settings.debug;	// Here to maintain v2 API
	
	// Event Handlers
	this.settings.return_upload_start_handler = this.returnUploadStart;
	this.ensureDefault("swfupload_preload_handler", null);
	this.ensureDefault("swfupload_load_failed_handler", null);
	this.ensureDefault("swfupload_loaded_handler", null);
	this.ensureDefault("file_dialog_start_handler", null);
	this.ensureDefault("file_queued_handler", null);
	this.ensureDefault("file_queue_error_handler", null);
	this.ensureDefault("file_dialog_complete_handler", null);
	
	this.ensureDefault("upload_resize_start_handler", null);
	this.ensureDefault("upload_start_handler", null);
	this.ensureDefault("upload_progress_handler", null);
	this.ensureDefault("upload_error_handler", null);
	this.ensureDefault("upload_success_handler", null);
	this.ensureDefault("upload_complete_handler", null);
	
	this.ensureDefault("mouse_click_handler", null);
	this.ensureDefault("mouse_out_handler", null);
	this.ensureDefault("mouse_over_handler", null);
	
	this.ensureDefault("debug_handler", this.debugMessage);

	this.ensureDefault("custom_settings", {});

	// Other settings
	this.customSettings = this.settings.custom_settings;
	
	// Update the flash url if needed
	if (!!this.settings.prevent_swf_caching) {
		this.settings.flash_url = this.settings.flash_url + (this.settings.flash_url.indexOf("?") < 0 ? "?" : "&") + "preventswfcaching=" + new Date().getTime();
		this.settings.flash9_url = this.settings.flash9_url + (this.settings.flash9_url.indexOf("?") < 0 ? "?" : "&") + "preventswfcaching=" + new Date().getTime();
	}
	
	if (!this.settings.preserve_relative_urls) {
		this.settings.upload_url = SWFUpload.completeURL(this.settings.upload_url);
		this.settings.button_image_url = SWFUpload.completeURL(this.settings.button_image_url);
	}
	
	delete this.ensureDefault;
};

// Initializes the supported functionality based the Flash Player version, state, and event that occur during initialization
SWFUpload.prototype.loadSupport = function () {
	this.support = {
		loading : swfobject.hasFlashPlayerVersion("9.0.28"),
		imageResize : swfobject.hasFlashPlayerVersion("10.0.0")
	};
	
};

// Private: loadFlash replaces the button_placeholder element with the flash movie.
SWFUpload.prototype.loadFlash = function () {
	var targetElement, tempParent, wrapperType, flashHTML, els;

	if (!this.support.loading) {
		this.queueEvent("swfupload_load_failed_handler", ["Flash Player doesn't support SWFUpload"]);
		return;
	}
	
	// Make sure an element with the ID we are going to use doesn't already exist
	if (document.getElementById(this.movieName) !== null) {
		this.support.loading = false;
		this.queueEvent("swfupload_load_failed_handler", ["Element ID already in use"]);
		return;
	}

	// Get the element where we will be placing the flash movie
	targetElement = document.getElementById(this.settings.button_placeholder_id) || this.settings.button_placeholder;

	if (targetElement == undefined) {
		this.support.loading = false;
		this.queueEvent("swfupload_load_failed_handler", ["button place holder not found"]);
		return;
	}

	wrapperType = (targetElement.currentStyle && targetElement.currentStyle["display"] || window.getComputedStyle && document.defaultView.getComputedStyle(targetElement, null).getPropertyValue("display")) !== "block" ? "span" : "div";
	
	// Append the container and load the flash
	tempParent = document.createElement(wrapperType);

	flashHTML = this.getFlashHTML();

	try {
		tempParent.innerHTML = flashHTML;	// Using innerHTML is non-standard but the only sensible way to dynamically add Flash in IE (and maybe other browsers)
	} catch (ex) {
		this.support.loading = false;
		this.queueEvent("swfupload_load_failed_handler", ["Exception loading Flash HTML into placeholder"]);
		return;
	}

	// Try to get the movie element immediately
	els = tempParent.getElementsByTagName("object");
	if (!els || els.length > 1 || els.length === 0) {
		this.support.loading = false;
		this.queueEvent("swfupload_load_failed_handler", ["Unable to find movie after adding to DOM"]);
		return;
	} else if (els.length === 1) {
		this.movieElement = els[0];
	}
	
	targetElement.parentNode.replaceChild(tempParent.firstChild, targetElement);

	// Fix IE Flash/Form bug
	if (window[this.movieName] == undefined) {
		window[this.movieName] = this.getMovieElement();
	}
};

// Private: getFlashHTML generates the object tag needed to embed the flash in to the document
SWFUpload.prototype.getFlashHTML = function (flashVersion) {
	var myclass = "";
	if (navigator.userAgent.indexOf("MSIE") > 0) {
		myclass = 'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
	}

	return ['<object ' + myclass + '  id="', this.movieName, '" type="application/x-shockwave-flash" data="', this.settings.flash_url, '" width="', this.settings.button_width, '" height="', this.settings.button_height, '" class="swfupload">',
		'<param name="wmode" value="', this.settings.button_window_mode, '" />',
		'<param name="movie" value="', this.settings.flash_url, '" />',
		'<param name="quality" value="high" />',
		'<param name="menu" value="false" />',
		'<param name="allowScriptAccess" value="always" />',
		'<param name="flashvars" value="' + this.getFlashVars() + '" />',
		'</object>'].join("");
	
	//Flash Satay object syntax: http://www.alistapart.com/articles/flashsatay
	// return ['<object id="', this.movieName, '" type="application/x-shockwave-flash" data="', (this.support.imageResize ? this.settings.flash_url : this.settings.flash9_url), '" width="', this.settings.button_width, '" height="', this.settings.button_height, '" class="swfupload">',
	// 			'<param name="wmode" value="', this.settings.button_window_mode, '" />',
	// 			'<param name="movie" value="', (this.support.imageResize ? this.settings.flash_url : this.settings.flash9_url), '" />',
	// 			'<param name="quality" value="high" />',
	// 			'<param name="allowScriptAccess" value="always" />',
	// 			'<param name="flashvars" value="' + this.getFlashVars() + '" />',
	// 			'</object>'].join("");
};

// Private: getFlashVars builds the parameter string that will be passed
// to flash in the flashvars param.
SWFUpload.prototype.getFlashVars = function () {
	// Build a string from the post param object
	var httpSuccessString, paramString;
	
	paramString = this.buildParamString();
	httpSuccessString = this.settings.http_success.join(",");
	
	// Build the parameter string
	return ["movieName=", encodeURIComponent(this.movieName),
			"&amp;uploadURL=", encodeURIComponent(this.settings.upload_url),
			"&amp;useQueryString=", encodeURIComponent(this.settings.use_query_string),
			"&amp;requeueOnError=", encodeURIComponent(this.settings.requeue_on_error),
			"&amp;httpSuccess=", encodeURIComponent(httpSuccessString),
			"&amp;assumeSuccessTimeout=", encodeURIComponent(this.settings.assume_success_timeout),
			"&amp;params=", encodeURIComponent(paramString),
			"&amp;filePostName=", encodeURIComponent(this.settings.file_post_name),
			"&amp;fileTypes=", encodeURIComponent(this.settings.file_types),
			"&amp;fileTypesDescription=", encodeURIComponent(this.settings.file_types_description),
			"&amp;fileSizeLimit=", encodeURIComponent(this.settings.file_size_limit),
			"&amp;fileUploadLimit=", encodeURIComponent(this.settings.file_upload_limit),
			"&amp;fileQueueLimit=", encodeURIComponent(this.settings.file_queue_limit),
			"&amp;debugEnabled=", encodeURIComponent(this.settings.debug_enabled),
			"&amp;buttonImageURL=", encodeURIComponent(this.settings.button_image_url),
			"&amp;buttonWidth=", encodeURIComponent(this.settings.button_width),
			"&amp;buttonHeight=", encodeURIComponent(this.settings.button_height),
			"&amp;buttonText=", encodeURIComponent(this.settings.button_text),
			"&amp;buttonTextTopPadding=", encodeURIComponent(this.settings.button_text_top_padding),
			"&amp;buttonTextLeftPadding=", encodeURIComponent(this.settings.button_text_left_padding),
			"&amp;buttonTextStyle=", encodeURIComponent(this.settings.button_text_style),
			"&amp;buttonAction=", encodeURIComponent(this.settings.button_action),
			"&amp;buttonDisabled=", encodeURIComponent(this.settings.button_disabled),
			"&amp;buttonCursor=", encodeURIComponent(this.settings.button_cursor)
		].join("");
};

// Public: get retrieves the DOM reference to the Flash element added by SWFUpload
// The element is cached after the first lookup
SWFUpload.prototype.getMovieElement = function () {
	if (this.movieElement == undefined) {
		this.movieElement = document.getElementById(this.movieName);
	}

	if (this.movieElement === null) {
		throw "Could not find Flash element";
	}
	
	return this.movieElement;
};

// Private: buildParamString takes the name/value pairs in the post_params setting object
// and joins them up in to a string formatted "name=value&amp;name=value"
SWFUpload.prototype.buildParamString = function () {
	var name, postParams, paramStringPairs = [];
	
	postParams = this.settings.post_params; 

	if (typeof(postParams) === "object") {
		for (name in postParams) {
			if (postParams.hasOwnProperty(name)) {
				paramStringPairs.push(encodeURIComponent(name.toString()) + "=" + encodeURIComponent(postParams[name].toString()));
			}
		}
	}

	return paramStringPairs.join("&amp;");
};

// Public: Used to remove a SWFUpload instance from the page. This method strives to remove
// all references to the SWF, and other objects so memory is properly freed.
// Returns true if everything was destroyed. Returns a false if a failure occurs leaving SWFUpload in an inconsistant state.
// Credits: Major improvements provided by steffen
SWFUpload.prototype.destroy = function () {
	var movieElement;
	
	try {
		// Make sure Flash is done before we try to remove it
		this.cancelUpload(null, false);
		
		movieElement = this.cleanUp();

		// Remove the SWFUpload DOM nodes
		if (movieElement) {
			// Remove the Movie Element from the page
			try {
				movieElement.parentNode.removeChild(movieElement);
			} catch (ex) {}
		}

		// Remove IE form fix reference
		window[this.movieName] = null;

		// Destroy other references
		SWFUpload.instances[this.movieName] = null;
		delete SWFUpload.instances[this.movieName];

		this.movieElement = null;
		this.settings = null;
		this.customSettings = null;
		this.eventQueue = null;
		this.movieName = null;
		
		
		return true;
	} catch (ex2) {
		return false;
	}
};


// Public: displayDebugInfo prints out settings and configuration
// information about this SWFUpload instance.
// This function (and any references to it) can be deleted when placing
// SWFUpload in production.
SWFUpload.prototype.displayDebugInfo = function () {
	this.debug(
		[
			"---SWFUpload Instance Info---\n",
			"Version: ", SWFUpload.version, "\n",
			"Movie Name: ", this.movieName, "\n",
			"Settings:\n",
			"\t", "upload_url:               ", this.settings.upload_url, "\n",
			"\t", "flash_url:                ", this.settings.flash_url, "\n",
			"\t", "flash9_url:                ", this.settings.flash9_url, "\n",
			"\t", "use_query_string:         ", this.settings.use_query_string.toString(), "\n",
			"\t", "requeue_on_error:         ", this.settings.requeue_on_error.toString(), "\n",
			"\t", "http_success:             ", this.settings.http_success.join(", "), "\n",
			"\t", "assume_success_timeout:   ", this.settings.assume_success_timeout, "\n",
			"\t", "file_post_name:           ", this.settings.file_post_name, "\n",
			"\t", "post_params:              ", this.settings.post_params.toString(), "\n",
			"\t", "file_types:               ", this.settings.file_types, "\n",
			"\t", "file_types_description:   ", this.settings.file_types_description, "\n",
			"\t", "file_size_limit:          ", this.settings.file_size_limit, "\n",
			"\t", "file_upload_limit:        ", this.settings.file_upload_limit, "\n",
			"\t", "file_queue_limit:         ", this.settings.file_queue_limit, "\n",
			"\t", "debug:                    ", this.settings.debug.toString(), "\n",

			"\t", "prevent_swf_caching:      ", this.settings.prevent_swf_caching.toString(), "\n",

			"\t", "button_placeholder_id:    ", this.settings.button_placeholder_id.toString(), "\n",
			"\t", "button_placeholder:       ", (this.settings.button_placeholder ? "Set" : "Not Set"), "\n",
			"\t", "button_image_url:         ", this.settings.button_image_url.toString(), "\n",
			"\t", "button_width:             ", this.settings.button_width.toString(), "\n",
			"\t", "button_height:            ", this.settings.button_height.toString(), "\n",
			"\t", "button_text:              ", this.settings.button_text.toString(), "\n",
			"\t", "button_text_style:        ", this.settings.button_text_style.toString(), "\n",
			"\t", "button_text_top_padding:  ", this.settings.button_text_top_padding.toString(), "\n",
			"\t", "button_text_left_padding: ", this.settings.button_text_left_padding.toString(), "\n",
			"\t", "button_action:            ", this.settings.button_action.toString(), "\n",
			"\t", "button_cursor:            ", this.settings.button_cursor.toString(), "\n",
			"\t", "button_disabled:          ", this.settings.button_disabled.toString(), "\n",

			"\t", "custom_settings:          ", this.settings.custom_settings.toString(), "\n",
			"Event Handlers:\n",
			"\t", "swfupload_preload_handler assigned:  ", (typeof this.settings.swfupload_preload_handler === "function").toString(), "\n",
			"\t", "swfupload_load_failed_handler assigned:  ", (typeof this.settings.swfupload_load_failed_handler === "function").toString(), "\n",
			"\t", "swfupload_loaded_handler assigned:  ", (typeof this.settings.swfupload_loaded_handler === "function").toString(), "\n",
			"\t", "mouse_click_handler assigned:       ", (typeof this.settings.mouse_click_handler === "function").toString(), "\n",
			"\t", "mouse_over_handler assigned:        ", (typeof this.settings.mouse_over_handler === "function").toString(), "\n",
			"\t", "mouse_out_handler assigned:         ", (typeof this.settings.mouse_out_handler === "function").toString(), "\n",
			"\t", "file_dialog_start_handler assigned: ", (typeof this.settings.file_dialog_start_handler === "function").toString(), "\n",
			"\t", "file_queued_handler assigned:       ", (typeof this.settings.file_queued_handler === "function").toString(), "\n",
			"\t", "file_queue_error_handler assigned:  ", (typeof this.settings.file_queue_error_handler === "function").toString(), "\n",
			"\t", "upload_resize_start_handler assigned:      ", (typeof this.settings.upload_resize_start_handler === "function").toString(), "\n",
			"\t", "upload_start_handler assigned:      ", (typeof this.settings.upload_start_handler === "function").toString(), "\n",
			"\t", "upload_progress_handler assigned:   ", (typeof this.settings.upload_progress_handler === "function").toString(), "\n",
			"\t", "upload_error_handler assigned:      ", (typeof this.settings.upload_error_handler === "function").toString(), "\n",
			"\t", "upload_success_handler assigned:    ", (typeof this.settings.upload_success_handler === "function").toString(), "\n",
			"\t", "upload_complete_handler assigned:   ", (typeof this.settings.upload_complete_handler === "function").toString(), "\n",
			"\t", "debug_handler assigned:             ", (typeof this.settings.debug_handler === "function").toString(), "\n",

			"Support:\n",
			"\t", "Load:                     ", (this.support.loading ? "Yes" : "No"), "\n",
			"\t", "Image Resize:             ", (this.support.imageResize ? "Yes" : "No"), "\n"

		].join("")
	);
};

/* Note: addSetting and getSetting are no longer used by SWFUpload but are included
	the maintain v2 API compatibility
*/
// Public: (Deprecated) addSetting adds a setting value. If the value given is undefined or null then the default_value is used.
SWFUpload.prototype.addSetting = function (name, value, default_value) {
    if (value == undefined) {
        return (this.settings[name] = default_value);
    } else {
        return (this.settings[name] = value);
	}
};

// Public: (Deprecated) getSetting gets a setting. Returns an empty string if the setting was not found.
SWFUpload.prototype.getSetting = function (name) {
    if (this.settings[name] != undefined) {
        return this.settings[name];
	}

    return "";
};



// Private: callFlash handles function calls made to the Flash element.
// Calls are made with a setTimeout for some functions to work around
// bugs in the ExternalInterface library.
SWFUpload.prototype.callFlash = function (functionName, argumentArray) {
	var movieElement, returnValue, returnString;
	
	argumentArray = argumentArray || [];
	movieElement = this.getMovieElement();

	// Flash's method if calling ExternalInterface methods (code adapted from MooTools).
	try {
		if (movieElement != undefined) {
			returnString = movieElement.CallFunction('<invoke name="' + functionName + '" returntype="javascript">' + __flash__argumentsToXML(argumentArray, 0) + '</invoke>');
			returnValue = eval(returnString);
		} else {
			this.debug("Can't call flash because the movie wasn't found.");
		}
	} catch (ex) {
		this.debug("Exception calling flash function '" + functionName + "': " + ex.message);
	}
	
	// Unescape file post param values
	if (returnValue != undefined && typeof returnValue.post === "object") {
		returnValue = this.unescapeFilePostParams(returnValue);
	}

	return returnValue;
};

/* *****************************
	-- Flash control methods --
	Your UI should use these
	to operate SWFUpload
   ***************************** */

// WARNING: this function does not work in Flash Player 10
// Public: selectFile causes a File Selection Dialog window to appear.  This
// dialog only allows 1 file to be selected.
SWFUpload.prototype.selectFile = function () {
	this.callFlash("SelectFile");
};

// WARNING: this function does not work in Flash Player 10
// Public: selectFiles causes a File Selection Dialog window to appear/ This
// dialog allows the user to select any number of files
// Flash Bug Warning: Flash limits the number of selectable files based on the combined length of the file names.
// If the selection name length is too long the dialog will fail in an unpredictable manner.  There is no work-around
// for this bug.
SWFUpload.prototype.selectFiles = function () {
	this.callFlash("SelectFiles");
};


// Public: startUpload starts uploading the first file in the queue unless
// the optional parameter 'fileID' specifies the ID 
SWFUpload.prototype.startUpload = function (fileID) {
	this.callFlash("StartUpload", [fileID]);
};

// Public: startUpload starts uploading the first file in the queue unless
// the optional parameter 'fileID' specifies the ID 
SWFUpload.prototype.startResizedUpload = function (fileID, width, height, encoding, quality, allowEnlarging) {
	this.callFlash("StartUpload", [fileID, { "width": width, "height" : height, "encoding" : encoding, "quality" : quality, "allowEnlarging" : allowEnlarging }]);
};

// Public: cancelUpload cancels any queued file.  The fileID parameter may be the file ID or index.
// If you do not specify a fileID the current uploading file or first file in the queue is cancelled.
// If you do not want the uploadError event to trigger you can specify false for the triggerErrorEvent parameter.
SWFUpload.prototype.cancelUpload = function (fileID, triggerErrorEvent) {
	if (triggerErrorEvent !== false) {
		triggerErrorEvent = true;
	}
	this.callFlash("CancelUpload", [fileID, triggerErrorEvent]);
};

// Public: stopUpload stops the current upload and requeues the file at the beginning of the queue.
// If nothing is currently uploading then nothing happens.
SWFUpload.prototype.stopUpload = function () {
	this.callFlash("StopUpload");
};


// Public: requeueUpload requeues any file. If the file is requeued or already queued true is returned.
// If the file is not found or is currently uploading false is returned.  Requeuing a file bypasses the
// file size, queue size, upload limit and other queue checks.  Certain files can't be requeued (e.g, invalid or zero bytes files).
SWFUpload.prototype.requeueUpload = function (indexOrFileID) {
	return this.callFlash("RequeueUpload", [indexOrFileID]);
};


/* ************************
 * Settings methods
 *   These methods change the SWFUpload settings.
 *   SWFUpload settings should not be changed directly on the settings object
 *   since many of the settings need to be passed to Flash in order to take
 *   effect.
 * *********************** */

// Public: getStats gets the file statistics object.
SWFUpload.prototype.getStats = function () {
	return this.callFlash("GetStats");
};

// Public: setStats changes the SWFUpload statistics.  You shouldn't need to 
// change the statistics but you can.  Changing the statistics does not
// affect SWFUpload accept for the successful_uploads count which is used
// by the upload_limit setting to determine how many files the user may upload.
SWFUpload.prototype.setStats = function (statsObject) {
	this.callFlash("SetStats", [statsObject]);
};

// Public: getFile retrieves a File object by ID or Index.  If the file is
// not found then 'null' is returned.
SWFUpload.prototype.getFile = function (fileID) {
	if (typeof(fileID) === "number") {
		return this.callFlash("GetFileByIndex", [fileID]);
	} else {
		return this.callFlash("GetFile", [fileID]);
	}
};

// Public: getFileFromQueue retrieves a File object by ID or Index.  If the file is
// not found then 'null' is returned.
SWFUpload.prototype.getQueueFile = function (fileID) {
	if (typeof(fileID) === "number") {
		return this.callFlash("GetFileByQueueIndex", [fileID]);
	} else {
		return this.callFlash("GetFile", [fileID]);
	}
};


// Public: addFileParam sets a name/value pair that will be posted with the
// file specified by the Files ID.  If the name already exists then the
// exiting value will be overwritten.
SWFUpload.prototype.addFileParam = function (fileID, name, value) {
	return this.callFlash("AddFileParam", [fileID, name, value]);
};

// Public: removeFileParam removes a previously set (by addFileParam) name/value
// pair from the specified file.
SWFUpload.prototype.removeFileParam = function (fileID, name) {
	this.callFlash("RemoveFileParam", [fileID, name]);
};

// Public: setUploadUrl changes the upload_url setting.
SWFUpload.prototype.setUploadURL = function (url) {
	this.settings.upload_url = url.toString();
	this.callFlash("SetUploadURL", [url]);
};

// Public: setPostParams changes the post_params setting
SWFUpload.prototype.setPostParams = function (paramsObject) {
	this.settings.post_params = paramsObject;
	this.callFlash("SetPostParams", [paramsObject]);
};

// Public: addPostParam adds post name/value pair.  Each name can have only one value.
SWFUpload.prototype.addPostParam = function (name, value) {
	this.settings.post_params[name] = value;
	this.callFlash("SetPostParams", [this.settings.post_params]);
};

// Public: removePostParam deletes post name/value pair.
SWFUpload.prototype.removePostParam = function (name) {
	delete this.settings.post_params[name];
	this.callFlash("SetPostParams", [this.settings.post_params]);
};

// Public: setFileTypes changes the file_types setting and the file_types_description setting
SWFUpload.prototype.setFileTypes = function (types, description) {
	this.settings.file_types = types;
	this.settings.file_types_description = description;
	this.callFlash("SetFileTypes", [types, description]);
};

// Public: setFileSizeLimit changes the file_size_limit setting
SWFUpload.prototype.setFileSizeLimit = function (fileSizeLimit) {
	this.settings.file_size_limit = fileSizeLimit;
	this.callFlash("SetFileSizeLimit", [fileSizeLimit]);
};

// Public: setFileUploadLimit changes the file_upload_limit setting
SWFUpload.prototype.setFileUploadLimit = function (fileUploadLimit) {
	this.settings.file_upload_limit = fileUploadLimit;
	this.callFlash("SetFileUploadLimit", [fileUploadLimit]);
};

// Public: setFileQueueLimit changes the file_queue_limit setting
SWFUpload.prototype.setFileQueueLimit = function (fileQueueLimit) {
	this.settings.file_queue_limit = fileQueueLimit;
	this.callFlash("SetFileQueueLimit", [fileQueueLimit]);
};

// Public: setFilePostName changes the file_post_name setting
SWFUpload.prototype.setFilePostName = function (filePostName) {
	this.settings.file_post_name = filePostName;
	this.callFlash("SetFilePostName", [filePostName]);
};

// Public: setUseQueryString changes the use_query_string setting
SWFUpload.prototype.setUseQueryString = function (useQueryString) {
	this.settings.use_query_string = useQueryString;
	this.callFlash("SetUseQueryString", [useQueryString]);
};

// Public: setRequeueOnError changes the requeue_on_error setting
SWFUpload.prototype.setRequeueOnError = function (requeueOnError) {
	this.settings.requeue_on_error = requeueOnError;
	this.callFlash("SetRequeueOnError", [requeueOnError]);
};

// Public: setHTTPSuccess changes the http_success setting
SWFUpload.prototype.setHTTPSuccess = function (http_status_codes) {
	if (typeof http_status_codes === "string") {
		http_status_codes = http_status_codes.replace(" ", "").split(",");
	}
	
	this.settings.http_success = http_status_codes;
	this.callFlash("SetHTTPSuccess", [http_status_codes]);
};

// Public: setHTTPSuccess changes the http_success setting
SWFUpload.prototype.setAssumeSuccessTimeout = function (timeout_seconds) {
	this.settings.assume_success_timeout = timeout_seconds;
	this.callFlash("SetAssumeSuccessTimeout", [timeout_seconds]);
};

// Public: setDebugEnabled changes the debug_enabled setting
SWFUpload.prototype.setDebugEnabled = function (debugEnabled) {
	this.settings.debug_enabled = debugEnabled;
	this.callFlash("SetDebugEnabled", [debugEnabled]);
};

// Public: setButtonImageURL loads a button image sprite
SWFUpload.prototype.setButtonImageURL = function (buttonImageURL) {
	if (buttonImageURL == undefined) {
		buttonImageURL = "";
	}
	
	this.settings.button_image_url = buttonImageURL;
	this.callFlash("SetButtonImageURL", [buttonImageURL]);
};

// Public: setButtonDimensions resizes the Flash Movie and button
SWFUpload.prototype.setButtonDimensions = function (width, height) {
	this.settings.button_width = width;
	this.settings.button_height = height;
	
	var movie = this.getMovieElement();
	if (movie != undefined) {
		movie.style.width = width + "px";
		movie.style.height = height + "px";
	}
	
	this.callFlash("SetButtonDimensions", [width, height]);
};
// Public: setButtonText Changes the text overlaid on the button
SWFUpload.prototype.setButtonText = function (html) {
	this.settings.button_text = html;
	this.callFlash("SetButtonText", [html]);
};
// Public: setButtonTextPadding changes the top and left padding of the text overlay
SWFUpload.prototype.setButtonTextPadding = function (left, top) {
	this.settings.button_text_top_padding = top;
	this.settings.button_text_left_padding = left;
	this.callFlash("SetButtonTextPadding", [left, top]);
};

// Public: setButtonTextStyle changes the CSS used to style the HTML/Text overlaid on the button
SWFUpload.prototype.setButtonTextStyle = function (css) {
	this.settings.button_text_style = css;
	this.callFlash("SetButtonTextStyle", [css]);
};
// Public: setButtonDisabled disables/enables the button
SWFUpload.prototype.setButtonDisabled = function (isDisabled) {
	this.settings.button_disabled = isDisabled;
	this.callFlash("SetButtonDisabled", [isDisabled]);
};
// Public: setButtonAction sets the action that occurs when the button is clicked
SWFUpload.prototype.setButtonAction = function (buttonAction) {
	this.settings.button_action = buttonAction;
	this.callFlash("SetButtonAction", [buttonAction]);
};

// Public: setButtonCursor changes the mouse cursor displayed when hovering over the button
SWFUpload.prototype.setButtonCursor = function (cursor) {
	this.settings.button_cursor = cursor;
	this.callFlash("SetButtonCursor", [cursor]);
};

/* *******************************
	Flash Event Interfaces
	These functions are used by Flash to trigger the various
	events.
	
	All these functions a Private.
	
	Because the ExternalInterface library is buggy the event calls
	are added to a queue and the queue then executed by a setTimeout.
	This ensures that events are executed in a determinate order and that
	the ExternalInterface bugs are avoided.
******************************* */

SWFUpload.prototype.queueEvent = function (handlerName, argumentArray) {
	// Warning: Don't call this.debug inside here or you'll create an infinite loop
	var self = this;
	
	if (argumentArray == undefined) {
		argumentArray = [];
	} else if (!(argumentArray instanceof Array)) {
		argumentArray = [argumentArray];
	}
	
	if (typeof this.settings[handlerName] === "function") {
		// Queue the event
		this.eventQueue.push(function () {
			this.settings[handlerName].apply(this, argumentArray);
		});
		
		// Execute the next queued event
		setTimeout(function () {
			self.executeNextEvent();
		}, 0);
		
	} else if (this.settings[handlerName] !== null) {
		throw "Event handler " + handlerName + " is unknown or is not a function";
	}
};

// Private: Causes the next event in the queue to be executed.  Since events are queued using a setTimeout
// we must queue them in order to garentee that they are executed in order.
SWFUpload.prototype.executeNextEvent = function () {
	// Warning: Don't call this.debug inside here or you'll create an infinite loop

	var  f = this.eventQueue ? this.eventQueue.shift() : null;
	if (typeof(f) === "function") {
		f.apply(this);
	}
};

// Private: unescapeFileParams is part of a workaround for a flash bug where objects passed through ExternalInterface cannot have
// properties that contain characters that are not valid for JavaScript identifiers. To work around this
// the Flash Component escapes the parameter names and we must unescape again before passing them along.
SWFUpload.prototype.unescapeFilePostParams = function (file) {
	var reg = /[$]([0-9a-f]{4})/i, unescapedPost = {}, uk, k, match;

	if (file != undefined) {
		for (k in file.post) {
			if (file.post.hasOwnProperty(k)) {
				uk = k;
				while ((match = reg.exec(uk)) !== null) {
					uk = uk.replace(match[0], String.fromCharCode(parseInt("0x" + match[1], 16)));
				}
				unescapedPost[uk] = file.post[k];
			}
		}

		file.post = unescapedPost;
	}

	return file;
};

// Private: This event is called by SWFUpload Init after we've determined what the user's Flash Player supports.
// Use the swfupload_preload_handler event setting to execute custom code when SWFUpload has loaded.
// Return false to prevent SWFUpload from loading and allow your script to do something else if your required feature is
// not supported
SWFUpload.prototype.swfuploadPreload = function () {
	var returnValue;
	if (typeof this.settings.swfupload_preload_handler === "function") {
		returnValue = this.settings.swfupload_preload_handler.call(this);
	} else if (this.settings.swfupload_preload_handler != undefined) {
		throw "upload_start_handler must be a function";
	}

	// Convert undefined to true so if nothing is returned from the upload_start_handler it is
	// interpretted as 'true'.
	if (returnValue === undefined) {
		returnValue = true;
	}
	
	return !!returnValue;
}

// Private: This event is called by Flash when it has finished loading. Don't modify this.
// Use the swfupload_loaded_handler event setting to execute custom code when SWFUpload has loaded.
SWFUpload.prototype.flashReady = function () {
	// Check that the movie element is loaded correctly with its ExternalInterface methods defined
	var movieElement = 	this.cleanUp();

	if (!movieElement) {
		this.debug("Flash called back ready but the flash movie can't be found.");
		return;
	}

	this.queueEvent("swfupload_loaded_handler");
};

// Private: removes Flash added fuctions to the DOM node to prevent memory leaks in IE.
// This function is called by Flash each time the ExternalInterface functions are created.
SWFUpload.prototype.cleanUp = function () {
	var key, movieElement = this.getMovieElement();
	
	// Pro-actively unhook all the Flash functions
	try {
		if (movieElement && typeof(movieElement.CallFunction) === "unknown") { // We only want to do this in IE
			this.debug("Removing Flash functions hooks (this should only run in IE and should prevent memory leaks)");
			for (key in movieElement) {
				try {
					// if (typeof(movieElement[key]) === "function") {
					// if (typeof (movieElement[key]) === "function" && key.substring(0,1) <= 'Z')
					// fixed: don't remove (get/set/remove)Attribute from the object DOM
					if (typeof (movieElement[key]) === "function" && key[0] <= 'Z') {
						movieElement[key] = null;
					}
				} catch (ex) {
				}
			}
		}
	} catch (ex1) {
	
	}

	// Fix Flashes own cleanup code so if the SWF Movie was removed from the page
	// it doesn't display errors.
	window["__flash__removeCallback"] = function (instance, name) {
		try {
			if (instance) {
				instance[name] = null;
			}
		} catch (flashEx) {
		
		}
	};
	
	return movieElement;
};

/* When the button_action is set to None this event gets fired and executes the mouse_click_handler */
SWFUpload.prototype.mouseClick = function () {
	this.queueEvent("mouse_click_handler");
};
SWFUpload.prototype.mouseOver = function () {
	this.queueEvent("mouse_over_handler");
};
SWFUpload.prototype.mouseOut = function () {
	this.queueEvent("mouse_out_handler");
};

/* This is a chance to do something before the browse window opens */
SWFUpload.prototype.fileDialogStart = function () {
	this.queueEvent("file_dialog_start_handler");
};


/* Called when a file is successfully added to the queue. */
SWFUpload.prototype.fileQueued = function (file) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("file_queued_handler", file);
};


/* Handle errors that occur when an attempt to queue a file fails. */
SWFUpload.prototype.fileQueueError = function (file, errorCode, message) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("file_queue_error_handler", [file, errorCode, message]);
};

/* Called after the file dialog has closed and the selected files have been queued.
	You could call startUpload here if you want the queued files to begin uploading immediately. */
SWFUpload.prototype.fileDialogComplete = function (numFilesSelected, numFilesQueued, numFilesInQueue) {
	this.queueEvent("file_dialog_complete_handler", [numFilesSelected, numFilesQueued, numFilesInQueue]);
};

SWFUpload.prototype.uploadResizeStart = function (file, resizeSettings) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("upload_resize_start_handler", [file, resizeSettings.width, resizeSettings.height, resizeSettings.encoding, resizeSettings.quality]);
};

SWFUpload.prototype.uploadStart = function (file) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("return_upload_start_handler", file);
};

SWFUpload.prototype.returnUploadStart = function (file) {
	var returnValue;
	if (typeof this.settings.upload_start_handler === "function") {
		file = this.unescapeFilePostParams(file);
		returnValue = this.settings.upload_start_handler.call(this, file);
	} else if (this.settings.upload_start_handler != undefined) {
		throw "upload_start_handler must be a function";
	}

	// Convert undefined to true so if nothing is returned from the upload_start_handler it is
	// interpretted as 'true'.
	if (returnValue === undefined) {
		returnValue = true;
	}
	
	returnValue = !!returnValue;
	
	this.callFlash("ReturnUploadStart", [returnValue]);
};



SWFUpload.prototype.uploadProgress = function (file, bytesComplete, bytesTotal) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("upload_progress_handler", [file, bytesComplete, bytesTotal]);
};

SWFUpload.prototype.uploadError = function (file, errorCode, message) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("upload_error_handler", [file, errorCode, message]);
};

SWFUpload.prototype.uploadSuccess = function (file, serverData, responseReceived) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("upload_success_handler", [file, serverData, responseReceived]);
};

SWFUpload.prototype.uploadComplete = function (file) {
	file = this.unescapeFilePostParams(file);
	this.queueEvent("upload_complete_handler", file);
};

/* Called by SWFUpload JavaScript and Flash functions when debug is enabled. By default it writes messages to the
   internal debug console.  You can override this event and have messages written where you want. */
SWFUpload.prototype.debug = function (message) {
	this.queueEvent("debug_handler", message);
};


/* **********************************
	Debug Console
	The debug console is a self contained, in page location
	for debug message to be sent.  The Debug Console adds
	itself to the body if necessary.

	The console is automatically scrolled as messages appear.
	
	If you are using your own debug handler or when you deploy to production and
	have debug disabled you can remove these functions to reduce the file size
	and complexity.
********************************** */
   
// Private: debugMessage is the default debug_handler.  If you want to print debug messages
// call the debug() function.  When overriding the function your own function should
// check to see if the debug setting is true before outputting debug information.
SWFUpload.prototype.debugMessage = function (message) {
	var exceptionMessage, exceptionValues, key;

	if (this.settings.debug) {
		exceptionValues = [];

		// Check for an exception object and print it nicely
		if (typeof message === "object" && typeof message.name === "string" && typeof message.message === "string") {
			for (key in message) {
				if (message.hasOwnProperty(key)) {
					exceptionValues.push(key + ": " + message[key]);
				}
			}
			exceptionMessage = exceptionValues.join("\n") || "";
			exceptionValues = exceptionMessage.split("\n");
			exceptionMessage = "EXCEPTION: " + exceptionValues.join("\nEXCEPTION: ");
			SWFUpload.Console.writeLine(exceptionMessage);
		} else {
			SWFUpload.Console.writeLine(message);
		}
	}
};

SWFUpload.Console = {};
SWFUpload.Console.writeLine = function (message) {
	var console, documentForm;

	try {
		console = document.getElementById("SWFUpload_Console");

		if (!console) {
			documentForm = document.createElement("form");
			document.getElementsByTagName("body")[0].appendChild(documentForm);

			console = document.createElement("textarea");
			console.id = "SWFUpload_Console";
			console.style.fontFamily = "monospace";
			console.setAttribute("wrap", "off");
			console.wrap = "off";
			console.style.overflow = "auto";
			console.style.width = "700px";
			console.style.height = "350px";
			console.style.margin = "5px";
			documentForm.appendChild(console);
		}

		console.value += message + "\n";

		console.scrollTop = console.scrollHeight - console.clientHeight;
	} catch (ex) {
		alert("Exception: " + ex.name + " Message: " + ex.message);
	}
};


/*	SWFObject v2.2 <http://code.google.com/p/swfobject/> 
	is released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/
swfobject = function(){var D="undefined",r="object",S="Shockwave Flash",W="ShockwaveFlash.ShockwaveFlash",q="application/x-shockwave-flash",R="SWFObjectExprInst",x="onreadystatechange",O=window,j=document,t=navigator,T=false,U=[h],o=[],N=[],I=[],l,Q,E,B,J=false,a=false,n,G,m=true,M=function(){var aa=typeof j.getElementById!=D&&typeof j.getElementsByTagName!=D&&typeof j.createElement!=D,ah=t.userAgent.toLowerCase(),Y=t.platform.toLowerCase(),ae=Y?/win/.test(Y):/win/.test(ah),ac=Y?/mac/.test(Y):/mac/.test(ah),af=/webkit/.test(ah)?parseFloat(ah.replace(/^.*webkit\/(\d+(\.\d+)?).*$/,"$1")):false,X=!+"\v1",ag=[0,0,0],ab=null;if(typeof t.plugins!=D&&typeof t.plugins[S]==r){ab=t.plugins[S].description;if(ab&&!(typeof t.mimeTypes!=D&&t.mimeTypes[q]&&!t.mimeTypes[q].enabledPlugin)){T=true;X=false;ab=ab.replace(/^.*\s+(\S+\s+\S+$)/,"$1");ag[0]=parseInt(ab.replace(/^(.*)\..*$/,"$1"),10);ag[1]=parseInt(ab.replace(/^.*\.(.*)\s.*$/,"$1"),10);ag[2]=/[a-zA-Z]/.test(ab)?parseInt(ab.replace(/^.*[a-zA-Z]+(.*)$/,"$1"),10):0}}else{if(typeof O.ActiveXObject!=D){try{var ad=new ActiveXObject(W);if(ad){ab=ad.GetVariable("$version");if(ab){X=true;ab=ab.split(" ")[1].split(",");ag=[parseInt(ab[0],10),parseInt(ab[1],10),parseInt(ab[2],10)]}}}catch(Z){}}}return{w3:aa,pv:ag,wk:af,ie:X,win:ae,mac:ac}}(),k=function(){if(!M.w3){return}if((typeof j.readyState!=D&&j.readyState=="complete")||(typeof j.readyState==D&&(j.getElementsByTagName("body")[0]||j.body))){f()}if(!J){if(typeof j.addEventListener!=D){j.addEventListener("DOMContentLoaded",f,false)}if(M.ie&&M.win){j.attachEvent(x,function(){if(j.readyState=="complete"){j.detachEvent(x,arguments.callee);f()}});if(O==top){(function(){if(J){return}try{j.documentElement.doScroll("left")}catch(X){setTimeout(arguments.callee,0);return}f()})()}}if(M.wk){(function(){if(J){return}if(!/loaded|complete/.test(j.readyState)){setTimeout(arguments.callee,0);return}f()})()}s(f)}}();function f(){if(J){return}try{var Z=j.getElementsByTagName("body")[0].appendChild(C("span"));Z.parentNode.removeChild(Z)}catch(aa){return}J=true;var X=U.length;for(var Y=0;Y<X;Y++){U[Y]()}}function K(X){if(J){X()}else{U[U.length]=X}}function s(Y){if(typeof O.addEventListener!=D){O.addEventListener("load",Y,false)}else{if(typeof j.addEventListener!=D){j.addEventListener("load",Y,false)}else{if(typeof O.attachEvent!=D){i(O,"onload",Y)}else{if(typeof O.onload=="function"){var X=O.onload;O.onload=function(){X();Y()}}else{O.onload=Y}}}}}function h(){if(T){V()}else{H()}}function V(){var X=j.getElementsByTagName("body")[0];var aa=C(r);aa.setAttribute("type",q);var Z=X.appendChild(aa);if(Z){var Y=0;(function(){if(typeof Z.GetVariable!=D){var ab=Z.GetVariable("$version");if(ab){ab=ab.split(" ")[1].split(",");M.pv=[parseInt(ab[0],10),parseInt(ab[1],10),parseInt(ab[2],10)]}}else{if(Y<10){Y++;setTimeout(arguments.callee,10);return}}X.removeChild(aa);Z=null;H()})()}else{H()}}function H(){var ag=o.length;if(ag>0){for(var af=0;af<ag;af++){var Y=o[af].id;var ab=o[af].callbackFn;var aa={success:false,id:Y};if(M.pv[0]>0){var ae=c(Y);if(ae){if(F(o[af].swfVersion)&&!(M.wk&&M.wk<312)){w(Y,true);if(ab){aa.success=true;aa.ref=z(Y);ab(aa)}}else{if(o[af].expressInstall&&A()){var ai={};ai.data=o[af].expressInstall;ai.width=ae.getAttribute("width")||"0";ai.height=ae.getAttribute("height")||"0";if(ae.getAttribute("class")){ai.styleclass=ae.getAttribute("class")}if(ae.getAttribute("align")){ai.align=ae.getAttribute("align")}var ah={};var X=ae.getElementsByTagName("param");var ac=X.length;for(var ad=0;ad<ac;ad++){if(X[ad].getAttribute("name").toLowerCase()!="movie"){ah[X[ad].getAttribute("name")]=X[ad].getAttribute("value")}}P(ai,ah,Y,ab)}else{p(ae);if(ab){ab(aa)}}}}}else{w(Y,true);if(ab){var Z=z(Y);if(Z&&typeof Z.SetVariable!=D){aa.success=true;aa.ref=Z}ab(aa)}}}}}function z(aa){var X=null;var Y=c(aa);if(Y&&Y.nodeName=="OBJECT"){if(typeof Y.SetVariable!=D){X=Y}else{var Z=Y.getElementsByTagName(r)[0];if(Z){X=Z}}}return X}function A(){return !a&&F("6.0.65")&&(M.win||M.mac)&&!(M.wk&&M.wk<312)}function P(aa,ab,X,Z){a=true;E=Z||null;B={success:false,id:X};var ae=c(X);if(ae){if(ae.nodeName=="OBJECT"){l=g(ae);Q=null}else{l=ae;Q=X}aa.id=R;if(typeof aa.width==D||(!/%$/.test(aa.width)&&parseInt(aa.width,10)<310)){aa.width="310"}if(typeof aa.height==D||(!/%$/.test(aa.height)&&parseInt(aa.height,10)<137)){aa.height="137"}j.title=j.title.slice(0,47)+" - Flash Player Installation";var ad=M.ie&&M.win?"ActiveX":"PlugIn",ac="MMredirectURL="+O.location.toString().replace(/&/g,"%26")+"&MMplayerType="+ad+"&MMdoctitle="+j.title;if(typeof ab.flashvars!=D){ab.flashvars+="&"+ac}else{ab.flashvars=ac}if(M.ie&&M.win&&ae.readyState!=4){var Y=C("div");X+="SWFObjectNew";Y.setAttribute("id",X);ae.parentNode.insertBefore(Y,ae);ae.style.display="none";(function(){if(ae.readyState==4){ae.parentNode.removeChild(ae)}else{setTimeout(arguments.callee,10)}})()}u(aa,ab,X)}}function p(Y){if(M.ie&&M.win&&Y.readyState!=4){var X=C("div");Y.parentNode.insertBefore(X,Y);X.parentNode.replaceChild(g(Y),X);Y.style.display="none";(function(){if(Y.readyState==4){Y.parentNode.removeChild(Y)}else{setTimeout(arguments.callee,10)}})()}else{Y.parentNode.replaceChild(g(Y),Y)}}function g(ab){var aa=C("div");if(M.win&&M.ie){aa.innerHTML=ab.innerHTML}else{var Y=ab.getElementsByTagName(r)[0];if(Y){var ad=Y.childNodes;if(ad){var X=ad.length;for(var Z=0;Z<X;Z++){if(!(ad[Z].nodeType==1&&ad[Z].nodeName=="PARAM")&&!(ad[Z].nodeType==8)){aa.appendChild(ad[Z].cloneNode(true))}}}}}return aa}function u(ai,ag,Y){var X,aa=c(Y);if(M.wk&&M.wk<312){return X}if(aa){if(typeof ai.id==D){ai.id=Y}if(M.ie&&M.win){var ah="";for(var ae in ai){if(ai[ae]!=Object.prototype[ae]){if(ae.toLowerCase()=="data"){ag.movie=ai[ae]}else{if(ae.toLowerCase()=="styleclass"){ah+=' class="'+ai[ae]+'"'}else{if(ae.toLowerCase()!="classid"){ah+=" "+ae+'="'+ai[ae]+'"'}}}}}var af="";for(var ad in ag){if(ag[ad]!=Object.prototype[ad]){af+='<param name="'+ad+'" value="'+ag[ad]+'" />'}}aa.outerHTML='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'+ah+">"+af+"</object>";N[N.length]=ai.id;X=c(ai.id)}else{var Z=C(r);Z.setAttribute("type",q);for(var ac in ai){if(ai[ac]!=Object.prototype[ac]){if(ac.toLowerCase()=="styleclass"){Z.setAttribute("class",ai[ac])}else{if(ac.toLowerCase()!="classid"){Z.setAttribute(ac,ai[ac])}}}}for(var ab in ag){if(ag[ab]!=Object.prototype[ab]&&ab.toLowerCase()!="movie"){e(Z,ab,ag[ab])}}aa.parentNode.replaceChild(Z,aa);X=Z}}return X}function e(Z,X,Y){var aa=C("param");aa.setAttribute("name",X);aa.setAttribute("value",Y);Z.appendChild(aa)}function y(Y){var X=c(Y);if(X&&X.nodeName=="OBJECT"){if(M.ie&&M.win){X.style.display="none";(function(){if(X.readyState==4){b(Y)}else{setTimeout(arguments.callee,10)}})()}else{X.parentNode.removeChild(X)}}}function b(Z){var Y=c(Z);if(Y){for(var X in Y){if(typeof Y[X]=="function"){Y[X]=null}}Y.parentNode.removeChild(Y)}}function c(Z){var X=null;try{X=j.getElementById(Z)}catch(Y){}return X}function C(X){return j.createElement(X)}function i(Z,X,Y){Z.attachEvent(X,Y);I[I.length]=[Z,X,Y]}function F(Z){var Y=M.pv,X=Z.split(".");X[0]=parseInt(X[0],10);X[1]=parseInt(X[1],10)||0;X[2]=parseInt(X[2],10)||0;return(Y[0]>X[0]||(Y[0]==X[0]&&Y[1]>X[1])||(Y[0]==X[0]&&Y[1]==X[1]&&Y[2]>=X[2]))?true:false}function v(ac,Y,ad,ab){if(M.ie&&M.mac){return}var aa=j.getElementsByTagName("head")[0];if(!aa){return}var X=(ad&&typeof ad=="string")?ad:"screen";if(ab){n=null;G=null}if(!n||G!=X){var Z=C("style");Z.setAttribute("type","text/css");Z.setAttribute("media",X);n=aa.appendChild(Z);if(M.ie&&M.win&&typeof j.styleSheets!=D&&j.styleSheets.length>0){n=j.styleSheets[j.styleSheets.length-1]}G=X}if(M.ie&&M.win){if(n&&typeof n.addRule==r){n.addRule(ac,Y)}}else{if(n&&typeof j.createTextNode!=D){n.appendChild(j.createTextNode(ac+" {"+Y+"}"))}}}function w(Z,X){if(!m){return}var Y=X?"visible":"hidden";if(J&&c(Z)){c(Z).style.visibility=Y}else{v("#"+Z,"visibility:"+Y)}}function L(Y){var Z=/[\\\"<>\.;]/;var X=Z.exec(Y)!=null;return X&&typeof encodeURIComponent!=D?encodeURIComponent(Y):Y}var d=function(){if(M.ie&&M.win){window.attachEvent("onunload",function(){var ac=I.length;for(var ab=0;ab<ac;ab++){I[ab][0].detachEvent(I[ab][1],I[ab][2])}var Z=N.length;for(var aa=0;aa<Z;aa++){y(N[aa])}for(var Y in M){M[Y]=null}M=null;for(var X in swfobject){swfobject[X]=null}swfobject=null})}}();return{registerObject:function(ab,X,aa,Z){if(M.w3&&ab&&X){var Y={};Y.id=ab;Y.swfVersion=X;Y.expressInstall=aa;Y.callbackFn=Z;o[o.length]=Y;w(ab,false)}else{if(Z){Z({success:false,id:ab})}}},getObjectById:function(X){if(M.w3){return z(X)}},embedSWF:function(ab,ah,ae,ag,Y,aa,Z,ad,af,ac){var X={success:false,id:ah};if(M.w3&&!(M.wk&&M.wk<312)&&ab&&ah&&ae&&ag&&Y){w(ah,false);K(function(){ae+="";ag+="";var aj={};if(af&&typeof af===r){for(var al in af){aj[al]=af[al]}}aj.data=ab;aj.width=ae;aj.height=ag;var am={};if(ad&&typeof ad===r){for(var ak in ad){am[ak]=ad[ak]}}if(Z&&typeof Z===r){for(var ai in Z){if(typeof am.flashvars!=D){am.flashvars+="&"+ai+"="+Z[ai]}else{am.flashvars=ai+"="+Z[ai]}}}if(F(Y)){var an=u(aj,am,ah);if(aj.id==ah){w(ah,true)}X.success=true;X.ref=an}else{if(aa&&A()){aj.data=aa;P(aj,am,ah,ac);return}else{w(ah,true)}}if(ac){ac(X)}})}else{if(ac){ac(X)}}},switchOffAutoHideShow:function(){m=false},ua:M,getFlashPlayerVersion:function(){return{major:M.pv[0],minor:M.pv[1],release:M.pv[2]}},hasFlashPlayerVersion:F,createSWF:function(Z,Y,X){if(M.w3){return u(Z,Y,X)}else{return undefined}},showExpressInstall:function(Z,aa,X,Y){if(M.w3&&A()){P(Z,aa,X,Y)}},removeSWF:function(X){if(M.w3){y(X)}},createCSS:function(aa,Z,Y,X){if(M.w3){v(aa,Z,Y,X)}},addDomLoadEvent:K,addLoadEvent:s,getQueryParamValue:function(aa){var Z=j.location.search||j.location.hash;if(Z){if(/\?/.test(Z)){Z=Z.split("?")[1]}if(aa==null){return L(Z)}var Y=Z.split("&");for(var X=0;X<Y.length;X++){if(Y[X].substring(0,Y[X].indexOf("="))==aa){return L(Y[X].substring((Y[X].indexOf("=")+1)))}}}return""},expressInstallCallback:function(){if(a){var X=c(R);if(X&&l){X.parentNode.replaceChild(l,X);if(Q){w(Q,true);if(M.ie&&M.win){l.style.display="block"}}if(E){E(B)}}a=false}}}}();
swfobject.addDomLoadEvent(function () {
	if (typeof(SWFUpload.onload) === "function") {
		SWFUpload.onload.call(window);
	}
});

/*
	Queue Plug-in
	
	Features:
		*Adds a cancelQueue() method for cancelling the entire queue.
		*All queued files are uploaded when startUpload() is called.
		*If false is returned from uploadComplete then the queue upload is stopped.
		 If false is not returned (strict comparison) then the queue upload is continued.
		*Adds a QueueComplete event that is fired when all the queued files have finished uploading.
		 Set the event handler with the queue_complete_handler setting.
		
	*/

var SWFUpload;
if (typeof(SWFUpload) === "function") {
	SWFUpload.queue = {};
	
	SWFUpload.prototype.initSettings = (function (oldInitSettings) {
		return function (userSettings) {
			if (typeof(oldInitSettings) === "function") {
				oldInitSettings.call(this, userSettings);
			}
			
			this.queueSettings = {};
			
			this.queueSettings.queue_cancelled_flag = false;
			this.queueSettings.queue_upload_count = 0;
			
			this.queueSettings.user_upload_complete_handler = this.settings.upload_complete_handler;
			this.queueSettings.user_upload_start_handler = this.settings.upload_start_handler;
			this.settings.upload_complete_handler = SWFUpload.queue.uploadCompleteHandler;
			this.settings.upload_start_handler = SWFUpload.queue.uploadStartHandler;
			
			this.settings.queue_complete_handler = userSettings.queue_complete_handler || null;
		};
	})(SWFUpload.prototype.initSettings);

	SWFUpload.prototype.startUpload = function (fileID) {
		this.queueSettings.queue_cancelled_flag = false;
		this.callFlash("StartUpload", [fileID]);
	};

	SWFUpload.prototype.cancelQueue = function () {
		this.queueSettings.queue_cancelled_flag = true;
		this.stopUpload();
		
		var stats = this.getStats();
		while (stats.files_queued > 0) {
			this.cancelUpload();
			stats = this.getStats();
		}
	};
	
	SWFUpload.queue.uploadStartHandler = function (file) {
		var returnValue;
		if (typeof(this.queueSettings.user_upload_start_handler) === "function") {
			returnValue = this.queueSettings.user_upload_start_handler.call(this, file);
		}
		
		// To prevent upload a real "FALSE" value must be returned, otherwise default to a real "TRUE" value.
		returnValue = (returnValue === false) ? false : true;
		
		this.queueSettings.queue_cancelled_flag = !returnValue;

		return returnValue;
	};
	
	SWFUpload.queue.uploadCompleteHandler = function (file) {
		var user_upload_complete_handler = this.queueSettings.user_upload_complete_handler;
		var continueUpload;
		
		if (file.filestatus === SWFUpload.FILE_STATUS.COMPLETE) {
			this.queueSettings.queue_upload_count++;
		}

		if (typeof(user_upload_complete_handler) === "function") {
			continueUpload = (user_upload_complete_handler.call(this, file) === false) ? false : true;
		} else if (file.filestatus === SWFUpload.FILE_STATUS.QUEUED) {
			// If the file was stopped and re-queued don't restart the upload
			continueUpload = false;
		} else {
			continueUpload = true;
		}
		
		if (continueUpload) {
			var stats = this.getStats();
			if (stats.files_queued > 0 && this.queueSettings.queue_cancelled_flag === false) {
				this.startUpload();
			} else if (this.queueSettings.queue_cancelled_flag === false) {
				this.queueEvent("queue_complete_handler", [this.queueSettings.queue_upload_count]);
				this.queueSettings.queue_upload_count = 0;
			} else {
				this.queueSettings.queue_cancelled_flag = false;
				this.queueSettings.queue_upload_count = 0;
			}
		}
	};
}
/*
	Speed Plug-in
	
	Features:
		*Adds several properties to the 'file' object indicated upload speed, time left, upload time, etc.
			- currentSpeed -- String indicating the upload speed, bits per second
			- averageSpeed -- Overall average upload speed, bits per second
			- movingAverageSpeed -- Speed over averaged over the last several measurements, bits per second
			- timeRemaining -- Estimated remaining upload time in seconds
			- timeElapsed -- Number of seconds passed for this upload
			- percentUploaded -- Percentage of the file uploaded (0 to 100)
			- sizeUploaded -- Formatted size uploaded so far, bytes
		
		*Adds setting 'moving_average_history_size' for defining the window size used to calculate the moving average speed.
		
		*Adds several Formatting functions for formatting that values provided on the file object.
			- SWFUpload.speed.formatBPS(bps) -- outputs string formatted in the best units (Gbps, Mbps, Kbps, bps)
			- SWFUpload.speed.formatTime(seconds) -- outputs string formatted in the best units (x Hr y M z S)
			- SWFUpload.speed.formatSize(bytes) -- outputs string formatted in the best units (w GB x MB y KB z B )
			- SWFUpload.speed.formatPercent(percent) -- outputs string formatted with a percent sign (x.xx %)
			- SWFUpload.speed.formatUnits(baseNumber, divisionArray, unitLabelArray, fractionalBoolean)
				- Formats a number using the division array to determine how to apply the labels in the Label Array
				- factionalBoolean indicates whether the number should be returned as a single fractional number with a unit (speed)
				    or as several numbers labeled with units (time)
	*/

var SWFUpload;
if (typeof(SWFUpload) === "function") {
	SWFUpload.speed = {};
	
	SWFUpload.prototype.initSettings = (function (oldInitSettings) {
		return function (userSettings) {
			if (typeof(oldInitSettings) === "function") {
				oldInitSettings.call(this, userSettings);
			}
			
			this.ensureDefault = function (settingName, defaultValue) {
				this.settings[settingName] = (userSettings[settingName] == undefined) ? defaultValue : userSettings[settingName];
			};

			// List used to keep the speed stats for the files we are tracking
			this.fileSpeedStats = {};
			this.speedSettings = {};

			this.ensureDefault("moving_average_history_size", "10");
			
			this.speedSettings.user_file_queued_handler = this.settings.file_queued_handler;
			this.speedSettings.user_file_queue_error_handler = this.settings.file_queue_error_handler;
			this.speedSettings.user_upload_start_handler = this.settings.upload_start_handler;
			this.speedSettings.user_upload_error_handler = this.settings.upload_error_handler;
			this.speedSettings.user_upload_progress_handler = this.settings.upload_progress_handler;
			this.speedSettings.user_upload_success_handler = this.settings.upload_success_handler;
			this.speedSettings.user_upload_complete_handler = this.settings.upload_complete_handler;
			
			this.settings.file_queued_handler = SWFUpload.speed.fileQueuedHandler;
			this.settings.file_queue_error_handler = SWFUpload.speed.fileQueueErrorHandler;
			this.settings.upload_start_handler = SWFUpload.speed.uploadStartHandler;
			this.settings.upload_error_handler = SWFUpload.speed.uploadErrorHandler;
			this.settings.upload_progress_handler = SWFUpload.speed.uploadProgressHandler;
			this.settings.upload_success_handler = SWFUpload.speed.uploadSuccessHandler;
			this.settings.upload_complete_handler = SWFUpload.speed.uploadCompleteHandler;
			
			delete this.ensureDefault;
		};
	})(SWFUpload.prototype.initSettings);

	
	SWFUpload.speed.fileQueuedHandler = function (file) {
		if (typeof this.speedSettings.user_file_queued_handler === "function") {
			file = SWFUpload.speed.extendFile(file);
			
			return this.speedSettings.user_file_queued_handler.call(this, file);
		}
	};
	
	SWFUpload.speed.fileQueueErrorHandler = function (file, errorCode, message) {
		if (typeof this.speedSettings.user_file_queue_error_handler === "function") {
			file = SWFUpload.speed.extendFile(file);
			
			return this.speedSettings.user_file_queue_error_handler.call(this, file, errorCode, message);
		}
	};

	SWFUpload.speed.uploadStartHandler = function (file) {
		if (typeof this.speedSettings.user_upload_start_handler === "function") {
			file = SWFUpload.speed.extendFile(file, this.fileSpeedStats);
			return this.speedSettings.user_upload_start_handler.call(this, file);
		}
	};
	
	SWFUpload.speed.uploadErrorHandler = function (file, errorCode, message) {
		file = SWFUpload.speed.extendFile(file, this.fileSpeedStats);
		SWFUpload.speed.removeTracking(file, this.fileSpeedStats);

		if (typeof this.speedSettings.user_upload_error_handler === "function") {
			return this.speedSettings.user_upload_error_handler.call(this, file, errorCode, message);
		}
	};
	SWFUpload.speed.uploadProgressHandler = function (file, bytesComplete, bytesTotal) {
		this.updateTracking(file, bytesComplete);
		file = SWFUpload.speed.extendFile(file, this.fileSpeedStats);

		if (typeof this.speedSettings.user_upload_progress_handler === "function") {
			return this.speedSettings.user_upload_progress_handler.call(this, file, bytesComplete, bytesTotal);
		}
	};
	
	SWFUpload.speed.uploadSuccessHandler = function (file, serverData) {
		if (typeof this.speedSettings.user_upload_success_handler === "function") {
			file = SWFUpload.speed.extendFile(file, this.fileSpeedStats);
			return this.speedSettings.user_upload_success_handler.call(this, file, serverData);
		}
	};
	SWFUpload.speed.uploadCompleteHandler = function (file) {
		file = SWFUpload.speed.extendFile(file, this.fileSpeedStats);
		SWFUpload.speed.removeTracking(file, this.fileSpeedStats);

		if (typeof this.speedSettings.user_upload_complete_handler === "function") {
			return this.speedSettings.user_upload_complete_handler.call(this, file);
		}
	};
	
	// Private: extends the file object with the speed plugin values
	SWFUpload.speed.extendFile = function (file, trackingList) {
		var tracking;
		
		if (!file) {
			return file;
		}
		
		if (trackingList) {
			tracking = trackingList[file.id];
		}
		
		if (tracking) {
			file.currentSpeed = tracking.currentSpeed;
			file.averageSpeed = tracking.averageSpeed;
			file.movingAverageSpeed = tracking.movingAverageSpeed;
			file.timeRemaining = tracking.timeRemaining;
			file.timeElapsed = tracking.timeElapsed;
			file.percentUploaded = tracking.percentUploaded;
			file.sizeUploaded = tracking.bytesUploaded;

		} else {
			file.currentSpeed = 0;
			file.averageSpeed = 0;
			file.movingAverageSpeed = 0;
			file.timeRemaining = 0;
			file.timeElapsed = 0;
			file.percentUploaded = 0;
			file.sizeUploaded = 0;
		}
		
		return file;
	};
	
	// Private: Updates the speed tracking object, or creates it if necessary
	SWFUpload.prototype.updateTracking = function (file, bytesUploaded) {
		var tracking = this.fileSpeedStats[file.id];
		if (!tracking) {
			this.fileSpeedStats[file.id] = tracking = {};
		}
		
		// Sanity check inputs
		bytesUploaded = bytesUploaded || tracking.bytesUploaded || 0;
		if (bytesUploaded < 0) {
			bytesUploaded = 0;
		}
		if (bytesUploaded > file.size) {
			bytesUploaded = file.size;
		}
		
		var tickTime = (new Date()).getTime();
		if (!tracking.startTime) {
			tracking.startTime = (new Date()).getTime();
			tracking.lastTime = tracking.startTime;
			tracking.currentSpeed = 0;
			tracking.averageSpeed = 0;
			tracking.movingAverageSpeed = 0;
			tracking.movingAverageHistory = [];
			tracking.timeRemaining = 0;
			tracking.timeElapsed = 0;
			tracking.percentUploaded = bytesUploaded / file.size;
			tracking.bytesUploaded = bytesUploaded;
		} else if (tracking.startTime > tickTime) {
			this.debug("When backwards in time");
		} else {
			// Get time and deltas
			var now = (new Date()).getTime();
			var lastTime = tracking.lastTime;
			var deltaTime = now - lastTime;
			var deltaBytes = bytesUploaded - tracking.bytesUploaded;
			
			if (deltaBytes === 0 || deltaTime === 0) {
				return tracking;
			}
			
			// Update tracking object
			tracking.lastTime = now;
			tracking.bytesUploaded = bytesUploaded;
			
			// Calculate speeds
			tracking.currentSpeed = (deltaBytes * 8 ) / (deltaTime / 1000);
			tracking.averageSpeed = (tracking.bytesUploaded * 8) / ((now - tracking.startTime) / 1000);

			// Calculate moving average
			tracking.movingAverageHistory.push(tracking.currentSpeed);
			if (tracking.movingAverageHistory.length > this.settings.moving_average_history_size) {
				tracking.movingAverageHistory.shift();
			}
			
			tracking.movingAverageSpeed = SWFUpload.speed.calculateMovingAverage(tracking.movingAverageHistory);
			
			// Update times
			tracking.timeRemaining = (file.size - tracking.bytesUploaded) * 8 / tracking.movingAverageSpeed;
			tracking.timeElapsed = (now - tracking.startTime) / 1000;
			
			// Update percent
			tracking.percentUploaded = (tracking.bytesUploaded / file.size * 100);
		}
		
		return tracking;
	};
	SWFUpload.speed.removeTracking = function (file, trackingList) {
		try {
			trackingList[file.id] = null;
			delete trackingList[file.id];
		} catch (ex) {
		}
	};
	
	SWFUpload.speed.formatUnits = function (baseNumber, unitDivisors, unitLabels, singleFractional) {
		var i, unit, unitDivisor, unitLabel;

		if (baseNumber === 0) {
			return "0 " + unitLabels[unitLabels.length - 1];
		}
		
		if (singleFractional) {
			unit = baseNumber;
			unitLabel = unitLabels.length >= unitDivisors.length ? unitLabels[unitDivisors.length - 1] : "";
			for (i = 0; i < unitDivisors.length; i++) {
				if (baseNumber >= unitDivisors[i]) {
					unit = (baseNumber / unitDivisors[i]).toFixed(2);
					unitLabel = unitLabels.length >= i ? " " + unitLabels[i] : "";
					break;
				}
			}
			
			return unit + unitLabel;
		} else {
			var formattedStrings = [];
			var remainder = baseNumber;
			
			for (i = 0; i < unitDivisors.length; i++) {
				unitDivisor = unitDivisors[i];
				unitLabel = unitLabels.length > i ? " " + unitLabels[i] : "";
				
				unit = remainder / unitDivisor;
				if (i < unitDivisors.length -1) {
					unit = Math.floor(unit);
				} else {
					unit = unit.toFixed(2);
				}
				if (unit > 0) {
					remainder = remainder % unitDivisor;
					
					formattedStrings.push(unit + unitLabel);
				}
			}
			
			return formattedStrings.join(" ");
		}
	};
	
	SWFUpload.speed.formatBPS = function (baseNumber) {
		var bpsUnits = [1073741824, 1048576, 1024, 1], bpsUnitLabels = ["Gbps", "Mbps", "Kbps", "bps"];
		return SWFUpload.speed.formatUnits(baseNumber, bpsUnits, bpsUnitLabels, true);
	
	};
	SWFUpload.speed.formatTime = function (baseNumber) {
		var timeUnits = [86400, 3600, 60, 1], timeUnitLabels = ["d", "h", "m", "s"];
		return SWFUpload.speed.formatUnits(baseNumber, timeUnits, timeUnitLabels, false);
	
	};
	SWFUpload.speed.formatBytes = function (baseNumber) {
		var sizeUnits = [1073741824, 1048576, 1024, 1], sizeUnitLabels = ["GB", "MB", "KB", "bytes"];
		return SWFUpload.speed.formatUnits(baseNumber, sizeUnits, sizeUnitLabels, true);
	
	};
	SWFUpload.speed.formatPercent = function (baseNumber) {
		return baseNumber.toFixed(2) + " %";
	};
	
	SWFUpload.speed.calculateMovingAverage = function (history) {
		var vals = [], size, sum = 0.0, mean = 0.0, varianceTemp = 0.0, variance = 0.0, standardDev = 0.0;
		var i;
		var mSum = 0, mCount = 0;
		
		size = history.length;
		
		// Check for sufficient data
		if (size >= 8) {
			// Clone the array and Calculate sum of the values 
			for (i = 0; i < size; i++) {
				vals[i] = history[i];
				sum += vals[i];
			}

			mean = sum / size;

			// Calculate variance for the set
			for (i = 0; i < size; i++) {
				varianceTemp += Math.pow((vals[i] - mean), 2);
			}

			variance = varianceTemp / size;
			standardDev = Math.sqrt(variance);
			
			//Standardize the Data
			for (i = 0; i < size; i++) {
				vals[i] = (vals[i] - mean) / standardDev;
			}

			// Calculate the average excluding outliers
			var deviationRange = 2.0;
			for (i = 0; i < size; i++) {
				
				if (vals[i] <= deviationRange && vals[i] >= -deviationRange) {
					mCount++;
					mSum += history[i];
				}
			}
			
		} else {
			// Calculate the average (not enough data points to remove outliers)
			mCount = size;
			for (i = 0; i < size; i++) {
				mSum += history[i];
			}
		}

		return mSum / mCount;
	};
	
}
/*
	A simple class for displaying file information and progress
	Note: This is a demonstration only and not part of SWFUpload.
	Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
*/

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements
function FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.opacity = 100;
	this.height = 0;
	

	this.fileProgressElement = document.getElementById(this.fileProgressID);
	if (!this.fileProgressElement) {
		this.fileProgressElement = document.createElement("div");
		this.fileProgressElement.className = "progressContainer";
		this.fileProgressElement.id = this.fileProgressID;

		var progressCancel = document.createElement("a");
		progressCancel.className = "progressCancel";
		progressCancel.href = "#";
		progressCancel.style.visibility = "hidden";
		progressCancel.appendChild(document.createTextNode(" "));

		var progressText = document.createElement("div");
		progressText.className = "progressName";
		progressText.appendChild(document.createTextNode(file.name));

		var progressBarWrap = document.createElement("div");
		progressBarWrap.className = "progress progress-small";

		var progressBar = document.createElement("div");
		progressBar.className = "progress-bar";

		progressBarWrap.appendChild(progressBar);

		var progressStatus = document.createElement("div");
		progressStatus.className = "progressBarStatus";
		progressStatus.innerHTML = "&nbsp;";

		this.fileProgressElement.appendChild(progressCancel);
		this.fileProgressElement.appendChild(progressText);
		this.fileProgressElement.appendChild(progressStatus);
		this.fileProgressElement.appendChild(progressBarWrap);

		document.getElementById(targetID).appendChild(this.fileProgressElement);
	} else {
		this.reset();
	}

	this.height = this.fileProgressElement.offsetHeight;
	this.setTimer(null);


}

FileProgress.prototype.setTimer = function (timer) {
	this.fileProgressElement["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function (timer) {
	return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function () {
	this.fileProgressElement.className = "progressContainer";

	this.fileProgressElement.childNodes[2].innerHTML = "&nbsp;";
	this.fileProgressElement.childNodes[2].className = "progressBarStatus";
	
	this.fileProgressElement.childNodes[3].className = "progress progress-small";
	this.fileProgressElement.childNodes[3].childNodes[0].className = "progress-bar";
	this.fileProgressElement.childNodes[3].childNodes[0].style.width = "0%";
	
	this.appear();	
};

FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressElement.className = "progressContainer blue";
	this.fileProgressElement.childNodes[3].childNodes[0].className = "progress-bar";
	this.fileProgressElement.childNodes[3].childNodes[0].style.width = percentage + "%";

	this.appear();	
};
FileProgress.prototype.setComplete = function () {
	this.fileProgressElement.className = "progressContainer green";
	this.fileProgressElement.childNodes[3].className = "progressBarComplete";
	this.fileProgressElement.childNodes[3].style.width = "";

	// var oSelf = this;
	// this.setTimer(setTimeout(function () {
	// 	oSelf.disappear();
	// }, 10000));
};
FileProgress.prototype.setError = function () {
	this.fileProgressElement.className = "progressContainer red";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 5000));
};
FileProgress.prototype.setCancelled = function () {
	this.fileProgressElement.className = "progressContainer gray";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 2000));
};
FileProgress.prototype.setStatus = function (status) {
	this.fileProgressElement.childNodes[2].innerHTML = status;
};

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressElement.childNodes[0].onclick = function () {
			swfUploadInstance.cancelUpload(fileID);
			return false;
		};
	}
};

FileProgress.prototype.appear = function () {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}
	
	if (this.fileProgressElement.filters) {
		try {
			this.fileProgressElement.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch (e) {
			// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
			this.fileProgressElement.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}
	} else {
		this.fileProgressElement.style.opacity = 1;
	}
		
	this.fileProgressElement.style.height = "";
	
	this.height = this.fileProgressElement.offsetHeight;
	this.opacity = 100;
	this.fileProgressElement.style.display = "";
	
};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30;	// 15 fps

	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressElement.filters) {
			try {
				this.fileProgressElement.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				this.fileProgressElement.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressElement.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressElement.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function () {
			oSelf.disappear();
		}, rate));
	} else {
		this.fileProgressElement.style.display = "none";
		this.setTimer(null);
	}
};