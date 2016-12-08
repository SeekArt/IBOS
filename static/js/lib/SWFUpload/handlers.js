var Ibos = Ibos || {};
(function(){
	var _addAttachId = function(targetId, id){
		var elem = document.getElementById(targetId),
			defVal;
		if(elem) {
			defVal = elem.value;
			elem.value = defVal ? (defVal + "," + id) : id;
		}
	}
	var _removeAttachId = function(targetId, id){
		var elem = document.getElementById(targetId),
			defVal,
			valArr,
			index;

		if(elem) {
			defVal = elem.value,
				valArr = defVal.split(','),
				index = valArr.indexOf(id);

			if(index !== -1){
				valArr.splice(index, 1);
			}

			elem.value = valArr.join(",")
		}
	};

	var getCookie = (function(){
		var obj = {},
			cookies = document.cookie.split("; "),
			item, arr;
		for(var i=0, len = cookies.length; i < len; i++ ){
			item = cookies[i];
			arr = item.split("=");
			obj[arr[0]] = arr[1];
		};
		return obj;
	})();

	var getErrorInfo = (function(){
		var infos = {};
		$.each(SWFUpload.UPLOAD_ERROR, function(prop, value){
			infos[value] = U.lang("UPLOAD." + prop);
		});

		$.each(SWFUpload.QUEUE_ERROR, function(prop, value){
			infos[value] = U.lang("UPLOAD." + prop);
		});

		return function(err, msg){
			return $.template(infos[err], { message: msg }) || U.lang("UPLOAD.UNHANDLED_ERROR", { message: msg })
		}
	})();


	var fileQueueError = function(file, error, message){
		try{
			switch (error) {
				case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
					break;
				case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
					this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				default:
					if (file !== null) {
						this.debug("Error Code: " + error + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					}
					break;
			}
		} catch(e){
			this.debug(e);
		}
	}

	var fileDialogComplete = function(){
		//选择文件后，关闭选择框并开始上传
		this.startUpload();
	}

	var uploadStart = function(file){
		// var settings = this.customSettings;
		// 当有设置curSpeed(Element)属性及remTime(Element)属性时，输出其上传速度及剩余时间
		// settings.curSpeed && (settings.curSpeed.innerHTML = SWFUpload.speed.formatBPS(file.currentSpeed));
		// settings.remTime && (settings.remTime.innerHTML = SWFUpload.speed.formatTime(file.timeRemaining));
	}

	var uploadError = function(file, error, message){
		try {
			switch (error) {
				case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
					this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
					this.debug("Error Code: No backend file, File name: " + file.name + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
					this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.IO_ERROR:
					this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
					this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
					this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
					this.debug("Error Code: The file was not found, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
					this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				default:
					this.debug("Error Code: " + message + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
			}
		} catch (e) {
			this.debug(e);
		}
	}

	var uploadSuccess = function(file, serverData, response){
		try {
			var that = this,
				data = serverData && eval('('+serverData+')');
			this.customSettings.success && this.customSettings.success.call(this, file, data, result, response)
		} catch(e){
			this.debug(e);
		}
	}

	//临时扩展Array.prototype
	Array.prototype.indexOf = Array.prototype.indexOf||function(item, index){
			if(this.length == undefined){
				throw new Error("Type Error: not Array!");
			}
			index = index||0;
			for(; index < this.length; index++){
				if(item === this[index])return index;
			}
			return -1;
		}

	SWFUpload.defaults = {
		// Backend Settings
		upload_url:                                 "",
		post_params:                                {PHPSESSID: getCookie.PHPSESSID},

		// File Upload Settings
		// 默认不限制大小、数目和类型
		file_size_limit:                            "0", // 不限
		file_types:                                 "*.*",
		file_types_description:                     "All Files",
		file_upload_limit:                          "0",
		file_queue_limit:                           "0",

		// Event Handler Settings
		swfupload_loaded_handler:                   null,
		file_dialog_start_handler:                  null,
		file_queued_handler:                        null,
		file_queue_error_handler:                   fileQueueError,
		file_dialog_complete_handler:               fileDialogComplete,
		upload_start_handler:                       uploadStart,
		upload_progress_handler:                    null,
		upload_error_handler:                       uploadError,
		upload_success_handler:                     uploadSuccess,
		upload_complete_handler:                    null,

		// Button Settings
		button_image_url:                           Ibos.app.getStaticUrl("/image/upload_btn.png"),
		button_placeholder_id:                      "upload_btn",
		button_width:                               100,
		button_height:                              42,
		button_cursor:                              SWFUpload.CURSOR.HAND,
		button_window_mode:                         SWFUpload.WINDOW_MODE.TRANSPARENT,


		// Flash Settings
		flash_url:                                  Ibos.app.getStaticUrl("/js/lib/SWFUpload/swfupload_fp10/swfupload.swf"),
		flash9_url:                                 Ibos.app.getStaticUrl("/js/lib/SWFUpload/swfupload_fp9/swfupload.swf")

	}
	SWFUpload.getErrorInfo = getErrorInfo;

	Ibos.fileUpload = function(options){
		return new SWFUpload($.extend(true, {}, SWFUpload.defaults, options))
	}

	// @Todo: 上传前预览，涉及到AS
	// @Todo: 还是有点乱，需要继续整理
	// @deprecated 结构太复杂，不方便使用
	// 图片上传默认的HTML结构
	// 考虑将cover、progress动态生成，或作为配置项
	// <div class="img-upload">
	//  <div class="img-upload-cover"></div>
	//  <div class="img-upload-progress"></div>
	//  <div class="img-upload-imgwrap"></div>
	//  <span id="button_placeholder_id"></span>
	// </div>
	var imgQueueError = function(file, error, message){
		try{
			fileQueueError.call(this, file, error, message);
			Ui.tip(getErrorInfo(error, message));
		} catch(e){
			this.debug(e);
		}
	}

	Ibos.imgUpload = function(options){
		var $movie,
			$wrap,
			$cover,
			$progress,
			$imgWrap;
		var imgUploadSettings = {
			// File Upload Settings
			// 默认不限制大小、数目和类型
			file_queue_limit:                           "1",
			file_types:                                 "*.gif;*.jpg;*.jpeg;*.png;",
			file_types_description:                     "Image Files",
			// Button Settings
			button_image_url:                           "",
			button_placeholder_id:                      "img_upload",
			button_width:                               100,
			button_height:                              100,

			swfupload_loaded_handler: function(){
				$movie = $(this.movieElement);
				$wrap = $movie.parent();
				$cover = $movie.siblings(".img-upload-cover");
				$progress = $movie.siblings(".img-upload-progress");
				$imgWrap = $movie.siblings(".img-upload-imgwrap")
			},

			file_dialog_start_handler: null,
			file_queued_handler: null,
			file_queue_error_handler: imgQueueError,
			upload_start_handler: function(){
				$cover.css("height", 0);
				$progress.text("0%")
				$wrap.removeClass("img-upload-success img-upload-error").addClass("img-upload-start")
			},
			upload_progress_handler: function(file, loaded, total){
				var percent = Math.ceil((loaded / total) * 100);
				$cover.css("height", percent + "%");
				$progress.text(percent + "%");
			},
			upload_success_handler: function(file, resData){
				var $img = $imgWrap.find("img"),
					data = $.parseJSON(resData);

				// 检测是否已存在图片，存在时替换src，不存在时创建
				if(!$img.length) {
					$img = $('<img/>').appendTo($imgWrap);
				}
				$img.attr("src", data.url);
				$wrap.addClass("img-upload-success").removeClass("img-upload-start");
				this.customSettings.success && this.customSettings.success.call(this, file, data);
			},
			upload_error_handler: function(){
				$wrap.removeClass("img-upload-start").addClass("img-upload-error")
			}
		}
		return Ibos.fileUpload($.extend({}, imgUploadSettings, options));
	}

	Ibos.upload = {};

	/**
	 * 附件上传
	 */

	var _attachLoaded = function(){
		var that = this,
			cs = this.customSettings || {},
			$container = $("#" + cs.containerId);

		if(!$container.length) {
			$.error("Ibos.upload.attach: 未找到节点#" + cs.containerId);
		}

		$container.on("click", '[data-node-type="attachRemoveBtn"]', function(){
			var $elem = $(this),
				id = $elem.attr("data-id"),
				successNum = that.getStats().successful_uploads;

			that.setStats({ successful_uploads: successNum - 1 });
			$elem.closest('[data-node-type="attachItem"]').remove();

			if(cs.inputId) {
				_removeAttachId(cs.inputId, id);
			}

			cs.remove && cs.remove.call(that, id, successNum);
		})
	};
	var _attachFileQueued = function(file){
		var progress = new FileProgress(file, this.customSettings.containerId);
		progress.setStatus(U.lang("UPLOAD.WAITING"));
	};
	var _attachFileQueueError = function(file, error, message){
		try{
			fileQueueError.call(this, file, error, message);
			var progress = new FileProgress(file, this.customSettings.containerId);
			progress.setStatus(getErrorInfo(error, message));

			setTimeout(function(){ progress.disappear();}, 3000)
		} catch(e){
			this.debug(e);
		}
	};
	var _attachUploadProgress = function(file, loaded, total){
		try {
			var percent = Math.ceil((loaded / total) * 100),
				progress = new FileProgress(file, this.customSettings.containerId);

			progress.toggleCancel(true, this);
			progress.setProgress(percent);
			progress.setStatus(percent + " %");
		} catch(e){
			this.debug(e);
		}
	};
	var _attachUploadError = function(file, error, message){
		try {
			uploadError.call(this, file, error, message);

			var progress = new FileProgress(file, this.customSettings.containerId);
			progress.setError();
			progress.toggleCancel(false);

			progress.setStatus(getErrorInfo(error, message));

			setTimeout(function(){
				progress.disappear();
			}, 3000)

		} catch (e) {
			this.debug(e);
		}
	};
	var _attachUploadSuccess = function(file, serverData, response){
		try {
			var that = this,
				cs = this.customSettings,
				data = serverData && eval('('+serverData+')');


			var progress = new FileProgress(file, cs.containerId);

			progress.setComplete();
			progress.toggleCancel(false);

			var $item = $.tmpl(cs.template, $.extend({}, file, {
				icon: data.imgurl || data.icon || "",
				type: file.type.substr(1),
				aid: data.id || data.aid
			}))

			$("#" + file.id).replaceWith($item);

			if(cs.inputId) {
				_addAttachId(cs.inputId, data.id||data.aid);
			}

			cs.success && cs.success.call(this, file, data, $item, response)
		} catch(e){
			this.debug(e);
		}
	};

	Ibos.upload.attach = function(options){
		var uploadConf = $.extend(true, {
			max: 0,
			attachexts: {
				ext: "*.*",
				depict: "All Support File"
			},
			limit: 0
		}, Ibos.app.g("upload"));

		var _settings = {
			// Backend Settings
			upload_url:                                 Ibos.app.url('main/attach/upload', { "uid": Ibos.app.g("uid"), "hash": uploadConf.hash}),
			file_post_name:                             "Filedata",
			post_params:                                {PHPSESSID: getCookie.PHPSESSID},

			// File Upload Settings
			// 默认不限制大小、数目和类型
			file_size_limit:                            uploadConf.max,
			file_types:                                 uploadConf.attachexts.ext,
			file_types_description:                     uploadConf.attachexts.depict,
			file_upload_limit:                          uploadConf.limit,

			button_image_url:                           Ibos.app.getStaticUrl("/image/upload_btn_attach.png"),
			button_placeholder_id:                      "upload_btn",
			button_width:                               40,
			button_height:                              40,

			swfupload_loaded_handler:                   _attachLoaded,
			file_queued_handler:                        _attachFileQueued,
			file_queue_error_handler:                   _attachFileQueueError,
			upload_progress_handler:                    _attachUploadProgress,
			upload_error_handler:                       _attachUploadError,
			upload_success_handler:                     _attachUploadSuccess,

			custom_settings: {
				containerId: '',
				inputId: '',
				template: '<div class="attl-item" data-node-type="attachItem">' +
				'<a href="javascript:;" title="' + U.lang("UPLOAD.DELETE_ATTACH") + '" class="cbtn o-trash" data-id="<%=aid%>" data-node-type="attachRemoveBtn"></a>' +
				'<i class="atti"><img width="44" height="44" src="<%=icon%>" alt="<%=name%>" title="<%=name%>" /></i>' +
				'<div class="attc"><%=name%></div>' +
				'</div>'
			}
		}
		return Ibos.fileUpload($.extend(true, {}, _settings, options))
	}

	// 单图片上传
	var _imageFileQueueError = function(file, error, message){
		try{
			fileQueueError.call(this, file, error, message);
			Ui.tip(getErrorInfo(error, message), "danger");
		} catch(e){
			this.debug(e);
		}
	};
	var _imageUploadStart = function(file){
		var _this = this,
			progressElem = document.getElementById(this.customSettings.progressId);

		if(progressElem) {
			var modal = document.createElement("div");
			var modalText = document.createElement("div");

			modal.className = "img-upload-mask";
			modalText.className = "img-upload-mask-text";

			modalText.innerHTML = "0%";

			// 点击遮盖层可取消上传
			modal.onclick = function(){
				_this.cancelUpload(file.id)
			}

			modal.appendChild(modalText);
			progressElem.appendChild(modal);

			$.data(progressElem, "modal", modal);
			$.data(progressElem, "modalText", modalText);
		}
	}
	var _imageUploadProgress = function(file, loaded, total) {
		try {
			var percent = Math.ceil((loaded / total) * 100),
				progressElem = document.getElementById(this.customSettings.progressId),
				modal, modalText;

			if(progressElem) {
				modal = $.data(progressElem, "modal");
				modalText = $.data(progressElem, "modalText");
				if(modal && modalText) {
					modal.style.height = percent + "%";
					modalText.innerHTML = percent + "%";
				}

			}
		} catch(e){
			this.debug(e);
		}
	}
	var _imageUploadError =function(file, error, message){
		try {
			uploadError.call(this, file, error, message);
			Ui.tip(getErrorInfo(error, message), "danger");
		} catch (e) {
			this.debug(e);
		}
	};
	var _imageUploadSuccess = function(file, serverData, response){
		try {
			var that = this,
				cs = this.customSettings,
				data = serverData && eval('('+serverData+')') || {};

			if(cs.targetId){
				$("#" + cs.targetId).html("<img src='" + data.imgurl + "' title='" + data.name + "' alt='" + data.name + "'>");
			}
			if(cs.inputId) {
				$("#" + cs.inputId).val(data.aid);
			}

			cs.success && cs.success.call(this, file, data, response)
		} catch(e){
			this.debug(e);
		}
	}
	var _imageUploadComplete = function(file){
		try {
			var progressElem = document.getElementById(this.customSettings.progressId),
				modal;

			if(progressElem) {
				modal = $.data(progressElem, "modal");
				progressElem.removeChild(modal);
				$.removeData(progressElem, "modal modalText");
			}

		} catch(e){
			this.debug(e);
		}
	}

	Ibos.upload.image = function(options){
		var uploadConf = $.extend(true, {
			max: 0,
			imageexts: {
				ext: "*.jpg; *.jpeg; *.gif; *.png",
				depict: "Image File"
			},
			limit: 0
		}, Ibos.app.g("upload"));

		var _settings = {
			// Backend Settings
			upload_url:                                 Ibos.app.url('main/attach/upload', { "uid": Ibos.app.g("uid"), "hash": uploadConf.hash}),
			file_post_name:                             "Filedata",
			post_params:                                {},

			file_size_limit:                            uploadConf.max, // 不限
			file_types:                                 uploadConf.imageexts.ext,
			file_types_description:                     uploadConf.imageexts.depict,
			file_upload_limit:                          uploadConf.limit,

			button_image_url:                           Ibos.app.getStaticUrl("/image/upload_btn.png"),
			button_height:                              42,
			button_placeholder_id:                      "upload_btn",
			button_width:                               100,

			swfupload_loaded_handler:                   null,
			file_dialog_start_handler:                  null,
			file_queued_handler:                        null,
			upload_start_handler:                       _imageUploadStart,
			upload_progress_handler:                    _imageUploadProgress,
			file_queue_error_handler:                   _imageFileQueueError,
			upload_error_handler:                       _imageUploadError,
			upload_success_handler:                     _imageUploadSuccess,
			upload_complete_handler:                    _imageUploadComplete,

			custom_settings: {
				targetId: '',
				inputId: '',
				progressId: ''
			}
		}
		return Ibos.fileUpload($.extend(true, {}, _settings, options))
	}
})()
