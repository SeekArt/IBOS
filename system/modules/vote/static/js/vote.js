var Vote = {
	$tab: $('#vote_tab'),
	$type: $("#vote_type"),
	// votetype: 投票类型 "1"为文字投票，"2"为图片投票
	/**
	 * 获取投票的类型
	 * @method getVoteType
	 * @return {Object} 返回Jquery节点对象
	 */
	getVoteType: function(){
		return this.$type.val();
	},
	/**
	 * 设置投票类型
	 * @method setVoteType
	 * @param {String} val 传入投票类型
	 */
	setVoteType: function(val){
		this.$type.val(val);
	},
	/**
	 * 获取时间戳
	 * @method getPeriodTimestamp
	 * @param  {String} type 传入投票类型
	 * @return {Number}      传出时间数字      
	 */
	getPeriodTimestamp: function(type){
		var now = +new Date,
			MS_PER_DAY = 86400000,
			dayCount = {
				"1": 7,
				"2": 30,
				"3": 182,
				"4": 365
			};
		return now + (dayCount[type] ? dayCount[type] * MS_PER_DAY : 0);
	},
	/**
	 * 获取最大选择模板
	 * @method getMaxSelectTpl
	 * @param  {Number} count 传入最大值
	 * @return {String}       传出模块内容
	 */
	getMaxSelectTpl: function(count){
		var tpl = "";
		for(var i = 1; i <= count; i++){
			tpl += '<option value="' + i + '">' + (i === 1 ? U.lang("VOTE.SINGLE_ITEM") : U.lang("VOTE.MAX_ITEM", { count: i})) + '</option>';
		}	
		return tpl;
	},
	/**
	 * 拿到当前投票类型有效的项数，即不为空的项数
	 * @method getValidItem
	 * @return {Object} 返回jquery节点对象
	 */
	getValidItem: function(){
		var type = this.getVoteType();
		return $("[name*='" + (type == "1" ? "vote" : "imageVote") + "[voteItem]'][value!='']");
	}
};

//设置投票项类型，内容/图片
Vote.tab = new P.Tab(Vote.$tab, "a", function($elem){
	$elem.parent().addClass("active").siblings().removeClass("active");
	Vote.setVoteType($elem.attr("data-value"));
});
	
(function(){
	/* 文字投票 start */
	var txtVoteDom = {
	    $list: $("#vote_text_list"),
	    $add: $("#vote_text_add")
	};
	var txtVoteList = new Ibos.OrderList(txtVoteDom.$list, "vote_text_tpl");
	var refreshTxtMaxSelect = function(){
	    $('#vote_max_select').html(Vote.getMaxSelectTpl(txtVoteList.getItemData().length));
	};
	// 添加一个投票项
	txtVoteDom.$add.on("click", function() {
	    txtVoteList.addItem({ content: "" });
	});
	// 删除一个投票项
	txtVoteDom.$list.on("click", "[data-item-remove]", function(){
	    txtVoteList.removeItem($.attr(this, "data-item-remove"));
	})
	// 更新最大可数数
	.on("list.add list.remove", function(){
	    refreshTxtMaxSelect();
	});

	Vote.textList = txtVoteList;
})();

(function(){
	/* 图片投票 start */
	var picVoteDom = {
	        $list: $("#vote_pic_list"),
	        $add: $("#vote_pic_add")
	    },
	    picVoteList = new Ibos.OrderList(picVoteDom.$list, "vote_pic_tpl"),
	    refreshPicMaxSelect = function(){
	        $('#picvote_max_select').html(Vote.getMaxSelectTpl(picVoteList.getItemData().length));
	    },
	    picUploadSettings = $.extend({
	        file_post_name:                 'Filedata',
	        post_params:                    {module:'vote'},
	        button_width:                   "80",
	        button_height:                   "60",
	        custom_settings: {
	            success: function(file, data){
	                $(this.movieElement).siblings("[data-picpath]").val(data.url);
	            }
	        }
	    }, Ibos.app.getPageParam("voteUploadSettings"));

	picVoteDom.$list.on("list.add", function(evt, d){
	    var settings = $.extend({
	        button_placeholder_id: "vote_pic_upload_" + d.data.id
	    }, picUploadSettings);
	    Ibos.imgUpload(settings);
	    refreshPicMaxSelect();
	})
	// 删除一个投票项
	.on("click", "[data-item-remove]", function(evt, d){
	    picVoteList.removeItem($.attr(this, "data-item-remove"));
	})
	.on("list.remove", function(){
	    refreshPicMaxSelect();
	});

	// 添加一个投票项
	picVoteDom.$add.on("click", function() {
	    picVoteList.addItem({ thumburl: "", picpath: "", content: "" });
	});
	/* 图片投票 end */
	Vote.picList = picVoteList;
})();
	
$(function(){
	// 自定义日期
	$('#vote_txt_deadline_date, #vote_pic_deadline_date').datepicker({ startDate: new Date() });

	$('#vote_txt_deadline, #vote_pic_deadline').change(function(){
	    var $date = $("#" + this.id + "_date");
	    // 显示日期选择器
	    $date.datepicker("setDate", new Date(Vote.getPeriodTimestamp(this.value)));
	});
	
	$('#vote_max_select').on('change',function(){
	    $('#vote_ismulti').val(this.value === "1" ? 0 : 1);
	});
	$('#picvote_max_select').on('change',function(){
	    $('#picvote_ismulti').val(this.value === "1" ? 0 : 1);
	});

});



