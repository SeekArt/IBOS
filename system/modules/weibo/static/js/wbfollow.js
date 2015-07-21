/**
 * 我的关注
 * wbfollow.js
 * 2014-01-11
 */


$(function() {
	var WbFollow = {
		"loadMoreFollow": function(param, callback) {
			$.get(Ibos.app.url('weibo/personal/loadfollow'), param, callback, "json");
		}
	};
	Ibos.evt.add({
		"loadMoreFollow": function(param, elem) {
			var $elem = $(elem);
			if ($elem.data("isLoading")) {
				return false;
			}
			param.offset = $elem.data('offset');
			$elem.button({loadingText: "<i class='loading-mini'></i> " + U.lang("WB.ISLOADING")}).button("loading");
			$elem.data("isLoading", true);
			WbFollow.loadMoreFollowing(param, function(res) {
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
					if (!res.more) {
						$('[data-node-type="loadMoreFollowBtn"]').hide();
					}
				}
			});
		}
	});
});