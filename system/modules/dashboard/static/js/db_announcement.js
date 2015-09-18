(function(){
	/* add edit */
	//日期选择器
	$("#date_start").datepicker({
		pickTime: true,
		pickSeconds: false,
		format: 'yyyy-mm-dd hh:ii',
		target: $("#date_end")
	});

	// 表单提交时，把subject的html写入input
	var subject = $('#subject');
	if( subject.length ){
		$('#sys_announcement_form').on('submit', function() {
			subject.val("<span style='" + $('#anc_title')[0].style.cssText + "'>" + $('#anc_title').html() + "</span>");
		});
	}

	/* add */
	//内容输入提示
	var htmlTemp = "<p><strong>文字公告: </strong>直接输入公告内容</p>"
				 + "<p><strong>网址链接: </strong>请输入公告的链接地址, 如某个主题地址: http://xxx.xxx/xxx.php?id=xxx</p>";
	$("#an_content").popover({
		title: Ibos.l("DB.TIP"),
		container: "body",
		html: true,
		content: htmlTemp
	});


	//编辑器
	var $ancTitle = $('#anc_title'),
	$ancContent = $ancTitle.find("span");
	if ($ancContent.length) {
	    $ancTitle[0].style.cssText = $ancContent[0].style.cssText;
	    $ancTitle.html($ancContent.text());
	}

	var editer = {
		onSetColor: function(value) {
			$ancTitle.css("color", value ? value : "");
		},
		onSetBold: function(isBold) {
			$ancTitle.css("font-weight", isBold ? "700" : "400");
		},
		onSetItalic: function(isItalic) {
			$ancTitle.css("font-style", isItalic ? "italic" : "normal");
		},
		onSetUnderline: function(hasUnderline) {
			$ancTitle.css("text-decoration", hasUnderline ? "underline" : "none");
		}
	}
	
	if( $ancTitle.html() === "" && $ancTitle.length){
		//编辑器 add
		new P.SimpleEditor($("#anc_title_editor"), editer);
	}else if($ancTitle.length){
		//编辑器 edit
		var editEditer = {
			color: $ancTitle.css("color"),
			bold: $ancTitle.css("font-weight") == "bold" || $ancTitle.css("font-weight") == 700,
			italic: $ancTitle.css("font-style") == "italic",
			underline: $ancTitle.css("text-decoration").indexOf("underline") !== -1
		};
		editer = $.extend({}, editer, editEditer);

		new P.SimpleEditor($("#anc_title_editor"), editer);
	}

	//setup
	(function() {
        // 删除选中
        var sysAnnForm = $("#sys_announcement_form");
        $('[data-act="del"]').on('click', function() {
            var id = '', 
                url = Ibos.app.url("dashboard/announcement/del");
            $('[data-check="id"]:checked').each(function() {
                id += this.value + ',';
            });
            if (id !== '') {
                sysAnnForm.attr('action', url).submit();
            } else {
                $.jGrowl(Ibos.l("DATABASE.AT_LEAST_ONE_RECORD"), {theme: 'error'});
                return false;
            }
        });
        // 排序
        $('[data-act="sort"]').on('click', function() {
            var url = Ibos.app.url("dashboard/announcement/setup");
            sysAnnForm.attr('action', url).submit();
        });
    })();
})();