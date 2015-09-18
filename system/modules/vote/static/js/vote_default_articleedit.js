// 投票项数验证，至少两条有效数据
$("#article_form").on("submit", function() {
	var isVoteEnabled = $("#voteStatus").prop("checked"),
			$items;
	if (isVoteEnabled) {
		$items = Vote.getValidItem();
		if ($items.length < 2) {
			Ui.tip(U.lang("VOTE.WRITE_AT_LEAST_TWO_ITEM"), "warning");
			return false;
		}
	}
});

// 还原投票数据
$(function() {
	if( ArticleEdit.voteItemList ){
		var voteData = ArticleEdit.voteData,
				voteItemList = voteData.voteItemList,
				txtItemCount = 0,
				picItemCount = 0,
				voteItem;
		// 还原文字投票列表
		if (voteData.vote.type === "1") {
			for (var i = 0; i < voteItemList.length; i++) {
				voteItem = voteItemList[i];
				Vote.textList.addItem({id: voteItem.itemid, content: voteItem.content});
				txtItemCount++;
			}
			$("#vote_max_select").val(voteData.vote.maxselectnum);
			// 还原图片投票列表
		} else {
			for (var i = 0; i < voteItemList.length; i++) {
				voteItem = voteItemList[i];
				Vote.picList.addItem({
					id: voteItem.itemid,
					content: voteItem.content,
					picpath: voteItem.picpath,
					thumburl: voteItem.thumburl
				});
				picItemCount++;
			}
			$("#picvote_max_select").val(voteData.vote.maxselectnum);
		}
		// 保证至少显示三条
		for (; txtItemCount < 3; txtItemCount++) {
			Vote.textList.addItem({content: ""});
		}
		for (; picItemCount < 3; picItemCount++) {
			Vote.picList.addItem({content: "", picpath: "", thumburl: ""});
		}
		// 没有投票数据时，直接输出三个空值
	}else{
		for (var i = 0; i < 3; i++) {
			Vote.textList.addItem({content: ""});
			Vote.picList.addItem({content: "", picpath: "", thumburl: ""});
		}
	}
});
