var VoteTextView = VoteTextView || {};
VoteTextView.op = {
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
(function(){
	$vote = $("#vote_text");
	var max = VoteTextView.max;
	var voteText = function($ctx, maxNum){
		var getChecked = function(){
				return $vote.find('[data-type="vote"]:checked');
			},
			getValue = function(){
				var arr = [];
				var $checked = getChecked();
				$checked.each(function(){
					arr.push(this.value);
				});
				return arr.join(",");
			},
			check = function(id){
				$vote.find('[data-type="vote"]').filter('[value="' + id + '"]').label("check");
			},
			uncheck = function(id){
				$vote.find('[data-type="vote"]').filter('[value="' + id + '"]').label("uncheck");
			},
			lastId;

		$vote.on("change", '[data-type="vote"]', function(){
			var id = this.value,
				checkNum = getChecked().length;
			if(checkNum > max){
				lastId && uncheck(lastId);
			}
			lastId = id;
		});
		return {
			val: getValue,
			check: check,
			uncheck: uncheck
		};
	};
	var vote = voteText($vote, max);


	function voteSubmit(){
		var relatedmodule = $('#relatedmodule').val();
			relatedid = $('#relatedid').val();
			voteItemids = vote.val();

		if(!voteItemids){
			$.jGrowl("请至少选择一个投票项", { theme: "warning" });
			return false;
		}
		
		var param = {
			relatedmodule: relatedmodule,
			relatedid:relatedid,
			voteItemids:voteItemids
		};
		VoteTextView.op.clickVote(param).done(function(data) {
			if(isNaN(data)){
				var str = '',
					voteItemList = data.voteItemList;
				
				for(var i=0; i< voteItemList.length; i++){

					str +="<div class='vote-item clearfix'>"+
						"<label>"+
							voteItemList[i]['content']+
						"</label>"+
						"<div class='pgb'>"+
							"<div class='pgbr' style='width: "+voteItemList[i]['percentage']+"; background-color: "+voteItemList[i]['color_style']+";'></div>"+
							"<div class='pgbs' style='left: "+voteItemList[i]['percentage']+"'>"+
								voteItemList[i]['number']+"("+voteItemList[i]['percentage']+")"+
							"</div>"+
						"</div>"+
					"</div>";
				}
				str+= "<p>您已经投过票，谢谢您的参与</p>";
				$vote.html(str);

				var param ={relatedmodule: relatedmodule,relatedid:relatedid};
				VoteTextView.op.getVoteCount(param).done(function(res) {
					$('#voter_num').html(res);
				});
			}else{
				if(data=== -1){

				}
			}
		});
	}
	$('#vote_submit').click(voteSubmit);
})();