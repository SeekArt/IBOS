
$(function() {
	var $slide = $("#personal_info");
	//侧栏信息栏的现实与隐藏的操作，已经搜索栏长度的改变
	var Contact = {
		sidebarDisplay:{
			show: function($elem) {
				$elem.animate({
					width: '520px',
					marginLeft: '261px'
				}, 200);
			},
			hide: function($elem) {
				$elem.animate({
					width: '0',
					marginLeft: '780px'
				}, 200);
			}
		},
		searchToggle: {
			expand: function($search){
				$search.removeClass('w230').addClass('span7');
			},
			collapse: function($search){
				$search.removeClass('span7').addClass('w230');
			}
		},
		avatarUpLoad: function(uploadParam) {
			var attachUpload = Ibos.upload.image($.extend({
				button_placeholder_id: "upload_img",
				file_size_limit: "2000", //设置图片最大上传值
				button_width: "100",
				button_height: "100",
				button_image_url: "",
				custom_settings: {
					//头像上传成功后的操作
					success: function(file, data) {
						if(data.IsSuccess){
							// 上传头像的路径
							$("#img_src").val(data.file);
							//将上传后的图片显示出来
							$("#portrait_img").css("display", "block").attr("src", data.data);
						
							//当头像上传成功后,鼠标移入移除时,显示和隐藏头像的覆盖层
							$("#pc_avatar_wrap").hover(function() {
								$("#tip_tier").toggle();
							});
						} else {
							Ui.tip(data.msg, 'danger');
							return false;
						}
					},
					progressId: "portrait_img_wrap"
				}
			}, uploadParam));
		},
		//计算侧栏信息栏和字母导航栏的定位及高度的计算
		calculateSidebar: function(){
			var cwtop = $('#cl_list_header').offset().top,
			dctop = $(document).scrollTop(),
			windowHeight = $(window).height(),
			mcheight = $('.mc').height();

			var slidtop = dctop - cwtop;
				linkheight = mcheight - slidtop,
				rollingSlideHeight = linkheight + 'px',
				mcheightval = mcheight + 'px',
				slidtopval = -slidtop + 'px',
				nletterHeightVal = mcheight - 60 + 'px',
				rletterHeightVal = linkheight - 60 + 'px',
				$rollingSidebar = $("#cl_rolling_sidebar"),
				$addWrap = $("#add_contacter_wrap"),
				$letterSidebar = $("#cl_letter_sidebar"),
				$funbar = $("#cl_funbar");

			if (slidtop > 0) {
				$addWrap.css({"top": '60px', "height": rollingSlideHeight});
				$rollingSidebar.css({"top": '60px', "height": rollingSlideHeight});
				$letterSidebar.css({'height': rletterHeightVal})
					.addClass('sidebar-rolling').removeClass('sidebar-normal');
				$funbar.addClass('funbar-rolling').removeClass('funbar-normal');
			} else {
				$addWrap.css({"top": slidtopval, "height": mcheightval});
				$rollingSidebar.css({"top": slidtopval, "height": mcheightval});
				$letterSidebar.css({'height': nletterHeightVal})
					.addClass('sidebar-normal').removeClass('sidebar-rolling');
				$funbar.addClass('funbar-normal').removeClass('funbar-rolling');
			}
		},
		formatUserInfo: function(param){
			if(param.indexOf("u") == 0){
				var arr = param.split(",");
				var data = $.map(arr, function(uid){
					 	var data = Ibos.data.getUser(uid);
					  	return { uid: uid.slice(2), name: data.name, avatar: data.avatar_big, phone: data.phone }
					});
				return data;
			}else{
				var avatar = Ibos.app.g("emptyAvatar");
				return [{name: "未知", avatar: avatar, phone: param}];
			}
		}
	}

	$(document).ready(function() {
		Contact.calculateSidebar();

		$("#search_area").focus();
	});

	//当调整浏览器窗口时，计算侧栏，字母导航栏的位置和高度
	$(window).resize(function() {
		Contact.calculateSidebar();
	});

	//当移动滚动条时，计算侧栏，字母导航栏的位置和高度
	$(window).scroll(function() {
		Contact.calculateSidebar();
	});

	//TODO：上传头像地址
	// 头像上传初始化
	Contact.avatarUpLoad({
		upload_url: Ibos.app.g("")
	});

	//初始化添加外部联系人的生日日期选择
	$("#date_time").datepicker();

	$.formValidator.initConfig({
		formId: "add_user_form"
	});

	$("#add_user_name").formValidator()
	.regexValidator({
		regExp: "notempty",
		dataType: "enum",
		onError: "姓名不能为空！" 
	});

	$("#add_user_phone").formValidator()
	.regexValidator({
		regExp: "mobile",
		dataType: "enum",
		onError: "手机格式不正确！"
	});

	$("#add_user_email").formValidator()
	.regexValidator({
		regExp: "email",
		dataType: "enum",
		onError: "邮箱格式不正确！"
	});

	$("#add_user_qq").formValidator()
	.regexValidator({
		regExp: "qq",
		dataType: "enum",
		onError: "QQ格式不正确！"
	});

	//TODO: 添加外部联系人
	$("#add_user_form").submit(function() {
		$("#save_info_btn").trigger("click");
		var self = $(this),
			isPass = $.formValidator.pageIsValid();
		if(isPass){
			$.post("", self.serialize(), function(res){
				if(res.isSuccess){
					$("#close_add_wrap").trigger("click");
					window.location.reload();
					Ibos.l("成功添加外部联系人！", "success");
				}
			}, "json");
		}
		return false;
	});

	//公司通讯录，点击列表单行，侧栏信息显示，改变选择行背景色
	$(".contact-list").on('click', "tr", function() {
		var $elem = $(this),
			$sidebar = $("#cl_rolling_sidebar"),
			$search = $('#name_search'),
			id = $elem.attr('data-id'),
			bgVal = $elem.attr("data-bg");
		$('tr').removeClass('active');
		$elem.addClass('active');
		Contact.sidebarDisplay.show($sidebar);
		Contact.searchToggle.collapse($search);
		$("#personal_info").waitingC();
		$.post(Ibos.app.url('contact/default/ajaxApi', {op: 'getProfile', uid: id}), function(res) {
			if (res.isSuccess) {
				var uid = "u_" + res.user.uid,
					formatdata = Contact.formatUserInfo(uid);
				Ibos.app.s({formatdata: formatdata});
				var user = res.user,
					tpl = $.tmpl("tpl_rolling_sidebar", {user: res.user}),
					$sidebar = $("#cl_rolling_sidebar");
				$sidebar.html("").append(tpl);
				$("#personal_info").stopWaiting();

				//判断用户是否有私信功能
				$("#card_pm").toggle(res.uid != user.uid);

				//判断用户是否为常用联系人
				if (res.cuids.length > 0 && $.inArray(id.toString(), res.cuids) !== -1) {
					$("#card_mark").attr("class", "o-si-mark");
				} else {
					$("#card_mark").attr("class", "o-si-nomark");
				}

			}
		});
	});

	//公司通讯录，点击添加常用联系人
	$(".o-nomark, .o-mark").on('click', function(evt) {
		evt.stopPropagation();
		var $elem = $(this),
			toFocus = $elem.hasClass("o-mark"),
			status = toFocus ? 'unmark' : 'mark',
			id = $elem.attr('data-id'),
			$trelem = $("i[data-id='" + id + "']"),
			$aelem = $("a[data-id='" + id + "']"),
			url = Ibos.app.url('contact/default/ajaxApi'),
			param = {op: 'changeConstant', cuid: id, status: status};
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				//调整列表中对应行中标识是否为常用联系人
				$trelem.attr({'class': (toFocus ? 'o-nomark' : 'o-mark'),
							'title': (toFocus ? Ibos.l("CONTACT.ADD_TOP_CONTACTS") : Ibos.l("CONTACT.CANCLE_TOP_CONTACTS"))});
				//调整侧栏中标识是否为常用联系人
				$aelem.attr({'class': (toFocus ? 'o-si-nomark' : 'o-si-mark')});
				Ui.tip(Ibos.l("OPERATION_SUCCESS"));
			}
		});
	});

	//常用联系人，点击取消常用联系
	$(".o-mark", ".common-uer-table").on("click", function(evt) {
		evt.stopPropagation();
		var $elem = $(this),
				$tr = $elem.closest("tr"),
				id = $elem.attr('data-id'),
				url = Ibos.app.url('contact/default/ajaxApi'),
				param = {op: 'changeConstant', cuid: id, status: 'unmark'};
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				$tr.remove();
				Ui.tip(Ibos.l("OPERATION_SUCCESS"));
			}
		});
	});

	//侧栏个人信息头像点击添加常用联系人操作
	$("#cl_rolling_sidebar").on('click', "#card_mark", function() {
		var $elem = $(this),
			toFocus = $elem.hasClass("o-si-mark"),
			status = toFocus ? 'unmark' : 'mark',
			id = $elem.attr('data-id'),
			$trelem = $("i[data-id='" + id + "']"),
			$mark = $("#card_mark"),
			url = Ibos.app.url('contact/default/ajaxApi'),
			param = {op: 'changeConstant', cuid: id, status: status};
		$mark.waiting(null, "mini", true);
		$.post(url, param, function(res) {
			if (res.isSuccess) {
				$elem.attr({"class" : (toFocus ? 'o-si-nomark' : 'o-si-mark'),
					"title" : (toFocus ? Ibos.l("CONTACT.ADD_TOP_CONTACTS") : Ibos.l("CONTACT.CANCLE_TOP_CONTACTS"))
				});
				//调整列表中对应用户是否为常用联系人的标识
				$trelem.attr({'class': (toFocus ? 'o-nomark' : 'o-mark'),
					'title': (toFocus ? Ibos.l("CONTACT.ADD_TOP_CONTACTS") : Ibos.l("CONTACT.CANCLE_TOP_CONTACTS"))
				});
				
				$mark.waiting(false); 
				Ui.tip(Ibos.l("OPERATION_SUCCESS"));
			}
		});
	});

	//点击字母导航，滚动条滚动到对应字母位置
	$(".letter-mark").on("click", function(){
		var $elem = $(this),
			$mark = $(".letter-mark");
		$mark.removeClass('active');
		$elem.addClass('active');

		var id = $elem.attr("data-id"),
			targetid = "#target_" +  id,
			target = "target_" + id;
		$(".cl-letter-title").removeClass("active");
		Ui.scrollYTo(target, -120, function(){ $(targetid).addClass("active") });
	});

	// 搜索
	$(document).keyup(function(event) {
		var searchStr = $("#search_area").val().toLowerCase(),
				nTrs = $(".contact-list-item"),
				$noDataTip = $(".inexist-data");
		nTrs.each(function() {
			var $elem = $(this),
				pregName = $elem.attr("data-preg");
			if (pregName.indexOf(searchStr) === -1) {
				$elem.removeClass('show').addClass('hide');
			} else {
				$elem.removeClass('hide').addClass('show');
			}
		});
		var groupItems = $(".group-item");
		groupItems.each(function() {
			var $elem = $(this),
			$userItem = $elem.find(".contact-list-item.show");
			if ($userItem.length == 0) {
				$elem.removeClass('show').addClass('hide');
			} else {
				$elem.removeClass('hide').addClass('show');
			}
		});

		if(searchStr === ""){
			$(".group-item").removeClass("hide").addClass("show");
		}
	});

	//sidebar高亮显示
	$(".org-dept-table tr").on("click", function(){
		var $elem = $(this);
		$(".org-dept-table tr").removeClass("active");
		$elem.addClass("active");
	});

	setInterval(function(){
		Contact.calculateSidebar();

		//当搜索结果无数据时的信息提示
		var $data = $(".group-item.hide"),
			hideDataLength = $data.length,
			allDataLength = $(".exist-data .group-item").length,
			$noDataTip = $(".inexist-data");
		$noDataTip.toggle(allDataLength == hideDataLength);
	}, 200);

	var isInit = false;
	Ibos.evt.add({
		//关闭侧栏个人信息
		"closeSidebar": function(param, elem){
			var $sidebar = $("#cl_rolling_sidebar"),
				$search = $('#name_search');
			Contact.sidebarDisplay.hide($sidebar);
			Contact.searchToggle.expand($search);
			$("tr", ".contact-list").removeClass('active');
		},
		//打印通讯录
		"printCont": function(param, elem){
			var $this = $(elem),
				uids = $this.attr("data-uids"),
				url = Ibos.app.url('contact/default/printContact');
			$.post(url, {uids: uids, deptid: Ibos.app.g("deptid")}, function(res) {
				if (res.isSuccess) {
					if(!isInit){
						$("body").append(res.view);
						isInit = true;
					}
				}
				window.print();
			});
		},
		//导出通讯录
		"educeCont": function(param, elem){
			var $this = $(elem),
				uids = $this.attr("data-uids"),
				url = Ibos.app.url('contact/default/export');
			window.location = url + '&uids=' + uids;
		},
		"addContacter": function(param, elem){
			var $wrap = $("#add_contacter_wrap"),
				$search = $("#name_search");
			Contact.sidebarDisplay.show($("#add_contacter_wrap"));
			Contact.searchToggle.collapse($search);
			$.formValidator.resetTipState();
			$.formValidator.reloadAutoTip();
		},
		"closeAddMunberWrap": function(param, elem){
			var $wrap = $("#add_contacter_wrap"),
				$search = $("#name_search");
			$.formValidator.resetTipState();
			Contact.sidebarDisplay.hide($("#add_contacter_wrap"));
			Contact.searchToggle.expand($search);
		}
	});
});