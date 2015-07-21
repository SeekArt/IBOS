/**
 * 通用文件上传窗口
 * @version $Id$
 */
(function(){
	var DProgress = function(file, targetId, opts){
		var swfupload = SWFUpload.instances[file.id.substr(0, file.id.lastIndexOf("_"))];
		var _this = this;
		var _template = '<div class="datt-progress"></div>' +
			'<span class="datt-name"><em><%= name %></em> - <%= size %></span>' +
			'<span class="datt-status"><%= status %></span>' +
			'<a href="javascript:;" class="o-trash"></a>';

		this.fileProgressId = file.id;
		this.element = $("#" + this.fileProgressId);

		if(!this.element.length) {

			this.element = $('<li id="' + this.fileProgressId + '"></li>');

			this.element.html($.template(_template, {
				icon: "",
				name: Ibos.string.ellipsis(file.name, 20, 10),
				size: SWFUpload.speed.formatBytes(file.size),
				status: "&nbsp;"
			}));

			$("#" + targetId).append(this.element);

			// 从视图删除文件
			this.element.find(".o-trash").on("click", function(){
				// 获取得到 crFile 时，此时文件状态为上传中或等待中，先取消上传
				var crFile = swfupload.getFile(file.id);
				if(crFile) {
					swfupload.cancelUpload(crFile.id, false);
				}
				$("#" + file.id).remove();
			});
		} else {
			this.reset();
		}
	}
	DProgress.prototype.reset = function(){
		this.element.attr("class", "");
		this.element.find(".datt-status").html("&nbsp;");
		this.element.find(".datt-progress").width(0);
	};

	DProgress.prototype.setProgress = function(percentage){
		this.element.attr("class", "inprogress");
		this.element.find(".datt-progress").width(percentage + "%")
	}

	DProgress.prototype.setComplete = function(){
		this.element.attr("class", "complete");
		this.element.find(".datt-progress").width(0);
	}

	DProgress.prototype.setError = function(){
		this.element.attr("class", "error");
		this.element.find(".datt-progress").width(0);
	}

	DProgress.prototype.setCancelled = function(){
		this.element.attr("class", "cancelled");
		this.element.find(".datt-progress").width(0);
	}

	DProgress.prototype.setStatus = function(status){
		this.element.find(".datt-status").html(status);
	}

	var _upload = Ibos.dialogUpload =  function(opts) {
		var swfPath, uploadInstance;uploadInstance
		if(typeof SWFUpload !== "undefined") {
			Ibos.dialogUploadInstance = Ibos.upload.attach($.extend(true, {
		        button_image_url: "",

		        swfupload_loaded_handler: null,

		        file_queued_handler: function(file){
		        	var progress = new DProgress(file, this.customSettings.containerId);
		        	progress.setStatus(U.lang("UPLOAD.WAITING"));
		        },

		        file_queue_error_handler: function(file, error, message){
		        	try{
		        		SWFUpload.defaults.file_queue_error_handler.apply(this, arguments);
		        		var progress = new DProgress(file, this.customSettings.containerId);
			        	progress.setError();
		        		progress.setStatus(SWFUpload.getErrorInfo(error, message));
		        	} catch(e){
		        		this.debug(e);
		        	}
		        },

		        upload_progress_handler: function(file, loaded, total){
		        	try {
		        		var percent = Math.ceil((loaded / total) * 100),
		        			progress = new DProgress(file, this.customSettings.containerId);

		        		progress.setProgress(percent);
		        		progress.setStatus(percent + " %...");
		        	} catch(e){
		        		this.debug(e);
		        	}
		        },

		        upload_error_handler: function(file, error, message){
		        	try {
		        		SWFUpload.defaults.upload_error_handler.apply(this, arguments);
		        		var progress = new DProgress(file, this.customSettings.containerId);
		        		progress.setError();
		        		progress.setStatus(SWFUpload.getErrorInfo(error, message));
		        	} catch (e) {
		        		this.debug(e);
		        	}
		        },

		        upload_success_handler: function(file, serverData, response){
		        	try {
		        		var that = this,
		        			cs = this.customSettings,
		        			data = serverData && eval('('+serverData+')');

		        		var progress = new DProgress(file, cs.containerId);

		        		progress.setComplete();
		        		progress.setStatus(U.lang("UPLOAD.UPLOAD_COMPLETE"));
		        		progress.element.prepend('<i class="datt-icon"><img src="' + (data.imgUrl || data.icon || "") + '"/ width="44" height="44"></i>');

		        		cs.success && cs.success.call(this, file, data, response);
		        	} catch(e){
		        		this.debug(e);
		        	}
		        }
		    }, opts));
			
		} else {
			swfPath = Ibos.app.getStaticUrl("/js/lib/SWFUpload");
			
			$.getScript(swfPath + "/swfupload.packaged.js")
			.done(function(){

				$.getScript(swfPath + "/handlers.js")
				.done(function(){
					_upload (opts);
				});
			});
		}
	}
})();