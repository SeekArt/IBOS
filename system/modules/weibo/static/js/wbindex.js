/**
 * 微博个人中心页
 * 2014-01-10
 * @author inaki
 */

(function() {
	var WbIndex = {
		getNextRelation: function(param, callback) {
			$.get(Ibos.app.url('weibo/personal/getrelation'), param, callback, "json");
		}
	};

	Ibos.evt.add({
		// 下一人际列表
		nextRelation: function(param, elem) {
			var $elem = $(elem),
				$relationBox,
				$relationContent,
				offset = $elem.data('offset');
			// 读取中时，返回，避免重复加载
			if ($elem.data("isLoading")) {
				return false;
			}
			param.offset = offset;
			$relationBox = $elem.closest('[data-node-type="relationBox"]');
			$relationContent = $relationBox.find('[data-node-type="relationContent"]');
			$relationContent.waiting(null, 'small', true);
			$elem.data("isLoading", true);
			WbIndex.getNextRelation(param, function(res) {
				var $newList,$oldList;
				if (res.isSuccess) {
					$relationContent.waiting(false);
					if (res.data) {
						$elem.data('offset',res.offset);
						$oldList = $relationContent.find('[data-node-type="relationList"]');
						$newList = $(res.data).css("left", "100%").appendTo($relationContent);
						// 列表左滑
						$oldList.animate({"left": "-100%"}, function() {
							$(this).remove();
						});
						$newList.animate({"left": 0});
					} else {
						// 没有更多了？？
					}
					$elem.removeData("isLoading");
				}
			});
		}
	});
})();
