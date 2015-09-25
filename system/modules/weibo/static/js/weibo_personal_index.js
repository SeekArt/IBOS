/**
 * 微博个人中心页
 * 2014-01-10
 * @author inaki
 */
 var WbIndex = {
 	op : {
  		/**
  		 * 获取下一个人际列表
  		 * method getNextRelation
  		 * @param  {Object} param 传入JSON格式数据
  		 * @return {Object}       返回deffered对象
  		 */
 		getNextRelation: function(param) {
 			var url = Ibos.app.url('weibo/personal/getrelation');
 			return $.get(url, param, $.noop, "json");
 		}	
 	}
 };
$(function() {
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
			WbIndex.op.getNextRelation(param).done(function(res) {
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
					}
					$elem.removeData("isLoading");
				}
			});
		}
	});
});
