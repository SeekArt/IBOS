var VoteImage = VoteImage || {};
VoteImage.op = {
	/**
	 * 点击投票
	 * @method clickVote
	 * @param  {Object} param 传入JSON格式数据
	 * @return {Object}       传出deffered对象
	 */
	clickVote : function(param){
		var url = Ibos.app.url("article/default/index");
		param = $.extend({}, param, {op: "clickVote"});
		return $.post(url, param, $.noop);
	},
	/**
	 * 获取投票数量
	 * @method getVoteCount
	 * @param  {Object} param 传入JSON格式数据
	 * @return {Object}       传出deffered对象
	 */
	getVoteCount : function(param){
		var url = Ibos.app.url("article/default/index");
		param = $.extend({}, param, {op: "getVoteCount"});
		return $.post(url, param, $.noop);
	}
};

/**
 * Vote 投票模块 JS
 * @method votePic
 * @param  {Object} $ctx     传入Jquery节点对象
 * @param  {Object} selector 传入Jquery节点对象
 * @param  {Number} maxNum   传入最大选择数目
 */
VoteImage.votePic = function($ctx, selector, maxNum){

	selector = selector || "[data-type='voteitem']";
	$ctx = ($ctx && $ctx.length) ? $ctx : $("#vote");
	maxNum = maxNum || 1;

	var selCheckbox = "input[type='checkbox']",
		lastId;

	var _getChecked = function(){
		return $ctx.find(selector + ".active");
	};
	var _getCheckedNum = function(){
		return _getChecked().length;
	};
	var getCheckedValue = function(){
		var $checked = _getChecked();
		var arr = [];
		$checked.each(function(){
			arr.push($.attr(this, "data-id"));
		});
		return arr.join(",");
	};

	var uncheck = function(id){
		$ctx.find(selector).filter("[data-id='" + id + "']").removeClass("active");
	};
	var check = function(id){
		var checkedNum = _getCheckedNum($ctx, selector);
		// 如果选项小于最大可选数
		if(checkedNum < maxNum){
			$ctx.find(selector).filter("[data-id='" + id + "']").addClass("active");
			// 记录上次选中的id
			lastId = id;
		// 大于最大可选数时，当前选中的会替代上个选中的选项
		} else {
			if(lastId){
				// 取消上次选中项
				uncheck(lastId);
				check(id);
			}
		}
	};
	var _bind = function(){
		$ctx.on("click.vote", selector, function(){
			var id = $.attr(this, "data-id");
			if(!id){
				return false;
			}
			// 此处有些性能浪费
			if($(this).hasClass("active")){
				uncheck(id);
			}else{
				check(id);
			}
		});
	};

	_bind();
	return {
		val: function(){
			return getCheckedValue();
		},
		check: check,
		uncheck: uncheck,
		enable: function(){
			_bind();
		},
		disable: function(){
			$ctx.off("click.vote");
		}
	};
};

$(function(){
	var $vote = $("#vote"),
		max = VoteImage.max,
		vote = VoteImage.votePic($vote, 'li', max);

	 $('#vote_submit').on('click',function(){
	     var $elem = $(this),
	     	 relatedmodule = $('#relatedmodule').val(),
	         relatedid = $('#relatedid').val(),
	         voteItemids = vote.val();

			if(!voteItemids){
	            Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
				return false;
			}
		var param = {
			relatedmodule: relatedmodule,
			relatedid:relatedid,
			voteItemids:voteItemids
		};
		VoteImage.op.clickVote(param).done(function(data) {
			if(typeof data === 'object'){
				var voteItemList = data.voteItemList,
					htmlStr = "";
	            for(var i = 0; i < voteItemList.length; i++){
	                 var data = {
	                        picpath: voteItemList[i].picpath,
	                        content: voteItemList[i].content,
	                        percentage:voteItemList[i].percentage,
	                        number: voteItemList[i].number
	                    };

	                htmlStr += $.template('vote_pic_template', data);
	            }
	            $vote.html(htmlStr)
	            .parent().after(Ibos.l("VOTE.HAS_VOTE_THANKS"));

	            $elem.remove();
	            // 已投过则禁止投票
	            vote.disable();
	        }
				
			window.setTimeout(function(){
				var voteCountParam = {relatedmodule: relatedmodule,relatedid:relatedid};

				VoteImage.op.getVoteCount(voteCountParam).done(function(data) {
					$('.plate em').html(data);
				});
			},100);
		});
	 });			
});
