/**
 * 私信详细页
 * @version $Id$
 */

var Pm = {
	op: {
		// 读取私信信息
		loadMsg: (function(){
			var _loading = false;

			return function(param, callback){
				param = $.extend({
					sinceid: 0
				}, param);
				if(!param.listid || _loading) {
					return false;
				}
				$.post(Ibos.app.url("message/pm/loadmessage"), param, function(){
					_loading = false;
					callback && callback.apply(this, arguments);
				}, "json");
				_loading = true;
			}
		})(),

		// 发送私信信息
		sendMsg: function(param, callback){
			param = param || {};
			if(param.to && typeof param.id !== "undefined") {
				$.post(Ibos.app.url("message/pm/reply"), param, callback, "json")
			}
		}
	}
};

$(function(){
	var listId = Ibos.app.g("listId"),
		sinceId = 0,
		maxId = 0,
		type = Ibos.app.g("listType"),
		defalutSince = Ibos.app.g("defalutSince");
	// 每次发送私信后，会进入3秒锁定时间，在此期间不允许再次发送
	var submitLock = false;

	// 加载更多私信
	var loadMore = function(){
		var $list = $("#msg_pm_list"),
			$loadBtn = $("#load_more_btn");

		// 更改“加载更多”按钮状态
		$loadBtn.button("loading");

		Pm.op.loadMsg({ sinceid: defalutSince, maxid: maxId, listid: listId, type: type }, function(res){
			$loadBtn.button("reset");

			$list.append(res.data);

			maxId = res.maxid;
			if (sinceId <= 0) {
				sinceId = res.sinceid;
			}

			// 如果已经读取至第一条，则隐藏加载更多按钮
			if (0 === res.maxid) {
				$loadBtn.hide();
			}
		})
	}

	// 加载最新的私信
	var loadNew = function(){
		var $list = $("#msg_pm_list");

		Pm.op.loadMsg({ sinceid: sinceId, listid: listId, type: type }, function(res){
			$list.prepend(res.data);
			if (res.sinceid > 0) {
				sinceId = res.sinceid;
			}
		})
	}

	// 发送
	var sendMsg = function(){
		var $submitBtn = $("#pm_submit"),
			$replyTextarea = $("#reply_content");

		$submitBtn.button("loading");

		Pm.op.sendMsg({
			to: Ibos.app.g("toUid"),
			id: listId,
			replycontent: $replyTextarea.val()
		}, function(res){
			if (res.IsSuccess) {
				// 重置表单
				$submitBtn.button("reset");
				submitLock = true;
				setTimeout(function(){
					submitLock = false;
				}, 5000)
				$replyTextarea.val("").focus();
				// 重读私信列表
				loadNew();
			} else {
				Ui.tip(res.data, "danger");
			}
		})
	}

	var validated = false;

	loadMore();
	$("#load_more_btn").on("click", loadMore);

	// 短轮询加载信息（即时聊天）
	setInterval(loadNew, 5000);

	var $replyContent = $("#reply_content"),
		$submitBtn = $("#pm_submit");

	// 输入字符计数
	$replyContent.charCount({ display: "pm_charcount" })
	.on("countchange", function(evt, data){
		// 字数不合验证规则时，禁用提交按钮
		validated = data.count > 0 && data.remnant >= 0;
		$submitBtn.prop("disabled", submitLock || !validated);
	})
	.on("keydown", function(evt){
		if(evt.ctrlKey && evt.which === 13) {
			validated && sendMsg();
		}
	})

	$submitBtn.on("click", sendMsg);


	// 初始化表情功能
	$("#pm_exp").ibosEmotion({ target: $replyContent })
});