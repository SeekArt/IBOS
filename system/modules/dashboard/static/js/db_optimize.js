$(function(){
	//search
	(function() {
		// 鼠标经过提示
		$('#sphinxSubIndex').popover({
			title:  U.lang("DB.TIP"),
			content: Ibos.l("OPTIMIZE.SPHINX_SUBINDEX_IP"),
			trigger: "focus",
			placement: 'top'
		});
		$('#sphinxMsgIndex').popover({
			title: U.lang("DB.TIP"),
			content: Ibos.l("OPTIMIZE.SPHINX_MSGINDEX_TIP"),
			trigger: "focus",
			placement: 'top'
		});
		$("#sphinxMaxQueryTime").popover({
			title: U.lang("DB.TIP"),
			content: Ibos.l("OPTIMIZE.SPHINX_MAXQUERY_TIP"),
			trigger: "focus",
			placement: 'top'
		});
		$("#sphinxLimit").popover({
			title: U.lang("DB.TIP"),
			content:Ibos.l("OPTIMIZE.SPHINX_LIMIT_TIP"),
			trigger: "focus",
			placement: 'top'
		});

		// 选择提示
		$("#sphinxRank").on('change', function() {
			var target = $(this).siblings('p'), value = this.value;
			switch (value) {
				case 'SPH_RANK_PROXIMITY_BM25':
					target.html( Ibos.l("OPTIMIZE.SPH_RANK_PROXIMITY_BM25_DESC") );
					break;
				case 'SPH_RANK_BM25':
					target.html(Ibos.l("OPTIMIZE.SPH_RANK_BM25_DESC"));
					break;
				case 'SPH_RANK_NONE':
					target.html(Ibos.l("OPTIMIZE.SPH_RANK_NONE_DESC"));
					break;
			}
		});
		
		$('#sphinxRank').change();
	})();
	

	//sphinx
	(function() {
		// 鼠标经过提示
		$('#sphinxHost').popover({
			title: U.lang("DB.TIP"),
			content: U.lang("DB.SPHINX_HOST_TIP"),
			trigger: "focus",
			placement: 'top'
		});
		$("#sphinxPort").popover({
			title: U.lang("DB.TIP"),
			content: U.lang("DB.SPHINX_PORT_TIP"),
			trigger: "focus",
			placement: 'top'
		});
	})();
});