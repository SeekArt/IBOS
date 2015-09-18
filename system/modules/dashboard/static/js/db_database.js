$(function(){
	/**
	 * 表格处理方法集
	 * @class customTable
	 */

	var customTable = {
		/**
		 * 开合对应tbody
		 * @method collapseline
		 * @param {Jquery} target 对应的tbody的对象
		 */
		collapseLine: function(target){
			var display = target.css("display");
			if(display === "none"){
				customTable.showLine(target);
			}else{
				customTable.hideLine(target);
			}
			return false;
		},
		/**
		 * 展开对应tbody
		 * @method showLine
		 * @param {Jquery} target 对应的tbody的对象
		 */
		showLine: function(target){
			if(target.css("display") === "none"){
				target.show().prev().addClass("active");
			}
		},
		/**
		 * 收起对应tbody
		 * @method hideLine
		 * @param {Jquery} target 对应的tbody的对象
		 */
		hideLine: function(target){
			if(target.css("display") !== "none"){
				target.hide().prev().removeClass("active");
			}
		}
	};


	//backup
	// 显示更多提示
	$("#tip_more_ctrl").on("click", function() {
		$("#tip_more").toggle();
		$(this).remove();
	});

	// 展开更多选项 
	var backupMode = $("#backup_mode");
	$("#more_option").on("change", function() {
		backupMode.toggle()
		$("#backup_option").toggle();
	});

	$("[type='radio']", backupMode).on("change", function() {
		$("#file_size_limit").toggle();
	});

	$("#backup_type").find("[type='radio']").on("change", function() {
		$("#table_list").toggle();
	});






	//restore
	$("#restore_table").on("click", "[data-act='collapse']", function() {
		var ctrl = this,
				targetSelector = ctrl.getAttribute("data-target") || ctrl.getAttribute("href"),
				target = $(targetSelector);
		customTable.collapseLine(target);
	});

	// 导入
	$('[data-act="import"]').on('click', function() {
		var id = $(this).data('id'), url = 'data/restore.php?op=restore&autorestore=yes&id=' + id;
		var dialog = $.artDialog({
			title: Ibos.l("DATABASE.CONFIRM_ACTION"),
			lock: true,
			content: Ibos.l("DATABASE.CONFIRM_IMPORT"),
			id: 'confirm_import_act',
			ok: function() {
				window.location.href = url;
			}
		});
	});

	// 删除选中
	$('[data-act="del"]').on('click', function() {
		var id = '';
		$('[data-check="key"]:checked').each(function() {
			id += this.value + ',';
		});
		if (id !== '') {
			$('#sys_dbrestore_form').submit();
		} else {
			$.jGrowl(Ibos.l("DATABASE.AT_LEAST_ONE_RECORD"), {theme: 'error'});
			return false;
		}
	});

	$('[data-act="decompress"]').on('click', function() {
		var id = $(this).data('id'), url = 'data/restore.php?op=restorezip&id=' + id;
		$.artDialog({
			title: Ibos.l("DATABASE.CONFIRM_ACTION"),
			lock: true,
			content: Ibos.l("DATABASE.CONFIRM_DECOMPRESS"),
			id: 'confirm_import_act',
			ok: function() {
				window.location.href = url;
			}
		});
	});
})