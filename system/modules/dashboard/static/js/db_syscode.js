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
	},
	/**
	 * 新增一行
	 * @method addLine
	 * @param {Jquery} target 行插入的容器
	 * @param {String} [temp=""] Html模板，没有时为空行
	 * @param {String} [id] 行ID，同时作为表单name序号
	 * @param {String} [prefix] ID前缀
	 * @parma {Function} [callback] 回调
	 */
	addLine: function(target, temp, id, prefix, callback){
		var line;
		if(typeof temp === "string"){
			line = $("<tr>").addClass("msts-last");
			id && (temp = temp.replace(/@/g, id));
			line.html(temp||"");
		}else{
			line = temp;
		}
		id && line.attr("id", id);
		target.children().last().removeClass();
		target.append(line);
		callback && callback(line)
		//展开对应tbody
		customTable.showLine(target);
		return false;
	},
	/**
	 * 删减一行
	 * @method removeLine
	 * @param {Jquery} line 要删减的行对象
	 */
	removeLine: function(line){
		var target = line.parent(),
			//当前tbody内行数
			length = target.children().length;
		//当删除行为最后一行时
		if(line.index() === length -1){
			line.prev().addClass("msts-last");
		}
		line.remove();
		//若当前tbody已空，则折叠
		target.children().length == 0 && customTable.hideLine(target);
	},
	/**
	 * 重置行内输入框的值
	 * @method resetLine
	 * @param {Jquery} line 要重置的行对象
	 */
	resetLine: function(line){
		line.find("input[type='text'], textarea").val("");
		return false;
	}
};


$(function() {
	$("#system_code_table").on("click", 'a', function() {
		var self = $(this),
			act = self.attr("data-act"),
			target = self.attr("data-target");
		switch (act) {
			case "collapse":
				customTable.collapseLine($(target));
				break;
			case "add":
				var pid = $(target).data('id'), d = new Date(),
					newCode = $.template('new_code', {id: d.getTime(), pid: pid});
				customTable.addLine($(target), newCode);
				break;
			case "remove":
				var line = self.parents("tr").eq(0);
				if (target !== '') {
					var removeIdObj = $('#removeId'),
						removeId = removeIdObj.val(),
						removeIdSplit = removeId.split(',');
					removeIdSplit.push(target);
					removeIdObj.val(removeIdSplit.join());
				}
				customTable.removeLine(line);
				break;
		}
	});
	// 检查输入情况
	$('#sys_code_form').on('submit', function() {
		var errCount = 0;
		$('[data-type="number"],[data-type="name"]').each(function() {
			if ($.trim(this.value) === '') {
				$(this).blink().focus();
				errCount++;
				return false;
			}
		});
		return errCount ? false : true;
	});
});