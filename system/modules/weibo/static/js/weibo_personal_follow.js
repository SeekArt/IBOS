/**
 * 我的关注
 * wbfollow.js
 * 2014-01-11
 */

 var WbFollow = {
 	op : {
 		/**
 		 * 读取更多的关注
 		 * method loadMoreFollow
 		 * @param  {Object} param 传入JSON格式数据
 		 * @return {Object}       返回deffered对象
 		 */
 		loadMoreFollow: function(param) {
 			var url = Ibos.app.url('weibo/personal/loadfollow');
 			return $.get(url, param, $.noop, "json");
 		}
 	}
 };
$(function() {
	Ibos.evt.add({
		"loadMoreFollow": function(param, elem) {
			var $elem = $(elem);
			if ($elem.data("isLoading")) {
				return false;
			}
			param.offset = $elem.data('offset');
			$elem.button({loadingText: "<i class='loading-mini'></i> " + Ibos.l("WB.ISLOADING")}).button("loading");
			$elem.data("isLoading", true);
			WbFollow.op.loadMoreFollowing(param).done(function(res) {
				if (res.isSuccess) {
					$elem.data('offset',res.offset);
					if (res.data) {
						// 插入内容
						$('[data-node-type="followList"]').append(res.data);
						// 重置状态
						$elem.button("reset");
						$elem.removeData("isLoading");
					}
					// 如果已经没有更多了，则隐藏查看更多按钮
					(!res.more) && $('[data-node-type="loadMoreFollowBtn"]').hide();
				}
			});
		}
	});
});