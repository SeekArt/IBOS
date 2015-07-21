(function(){
	var upgradeTip = {
		el: "#upgrade_tip_block",
		init: function(tplUrl, version){
			var that = this;
			if(!$(this.el).length){
				Ibos.statics.load({type: "css", url: Ibos.app.getStaticUrl("/js/app/upgradeTip/upgradeTip.css")})
				Ibos.statics.load({ type: "html",
					url: Ibos.app.getStaticUrl(tplUrl) 
				}).done(function(html){ 
					$("body").append($.template(html, {version: version}));
					this.$el = $(that.el);
					this.$el.on("click", "#upgrade_close_btn", function(){
						that.hide();
					});
					that.show();
				})
			}
		},
		show: function(){
			$(this.el).animate({bottom: "0", opacity: "1"}, 4000);
		},
		hide: function(){
			$(this.el).animate({bottom: "-282px", opacity: "0"}, 2000);
		}
	}
	Ibos.upgradeTip = upgradeTip;

	Ibos.evt.add({
		//标记已阅读更新公告
		"isRead": function(param, elem){
			var url = "";
			$.post(url, {op: "isRead"});
		}
	});
})();
