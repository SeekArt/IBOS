/**
 * tangerocx.js
 * NTKO OFFICE文档组件 js
 * IBOS
 * @author		banyan
 * @version	$Id$
 * @modified	2014-3-6
 */

var OCX = function(settings) {
	this.settings = $.extend({}, OCX.defaults, settings);
	this._init();
};

OCX.defaults = {
	docOpen: false,
	op: '',
	fileName: '',
	attachName: '',
	attachUrl: '',
	actionUrl: '',
	pathUrl: '',
	obj: {},
	logobj: {},
	user: ''
};
OCX.prototype = {
	constructor: OCX,
	/**
	 * 
	 * @returns {undefined}
	 */
	_init: function() {
		if (!$(this.settings.obj).is('object')) {
			$.error("(OCX): 错误的文档对象");
		}
		// 读取/设置是否使用UTF-8在智能提交中传输网页数据
		this.settings.obj.IsUseUTF8Data = (document.charset === "utf-8");
		// 设置新建菜单
		this.settings.obj.FileNew = false;
		// 设置关闭菜单
		this.settings.obj.FileClose = false;
		// 设置打开菜单
		this.settings.obj.FileOpen = false;
		//设置保存菜单
		this.settings.obj.FileSave = false;
		//设置另存为菜单
		this.settings.obj.FileSaveAs = false;
		// 替换&
		var re = /&amp;/g;
		this.settings.attachUrl = this.settings.attachUrl.replace(re, "&");
		try {
			if ($.browser.msie) {
				this._openDocumentByOP(this.settings.op);
				this.onDocumentOpened();
			}
		} catch (err) {
			var msg = '不能使用微软Office软件打开文档！\n\n是否尝试使用金山WPS文字处理软件打开文档？';
			if (window.confirm(msg)) {
				if (this.settings.op === 'edit') {
					this.settings.obj.BeginOpenFromURL(this.settings.attachUrl, true, false, "WPS.Document");
				} else {
					this.settings.obj.BeginOpenFromURL(this.settings.attachUrl, true, true, "WPS.Document");
				}
			}
		} finally {

		}
	},
	/**
	 * 
	 * @param {type} op
	 * @returns {undefined}
	 */
	_openDocumentByOP: function(op) {
		switch (op) {
			case 'newdoc':
				this.settings.obj.CreateNew('Word.Document');
				break;
			case 'newxls':
				this.settings.obj.CreateNew('Excel.Sheet');
				break;
			case 'read':
				if (this.settings.attachUrl) {
					// 以异步方式开始打开URL文档。该方法执行完毕，控件将从URL下载指定文档并打开。
					this.settings.obj.BeginOpenFromURL(this.settings.attachUrl);
				}
				break;
			case 'edit':
				if (this.settings.attachUrl) {
					this.settings.obj.BeginOpenFromURL(this.settings.attachUrl, true, false);
				} else {
					this.settings.obj.CreateNew("Word.Document");
				}
				break;
			default:
				$.error("(OCX): 未知打开操作");
				break;
		}
	},
	onDocumentOpened: function() {
//		var s;
		try {
			this.settings.docOpen = true;
//			if (0 == str.length) {
//				var str = this.settings.fileName;
//			}
			this.settings.obj.Caption = this.settings.fileName;
			if (this.settings.fileName.indexOf(".ppt") < 0 && this.settings.fileName.indexOf(".PPT") < 0) {
				this._setDocUser(this.settings.user);
			}
//			s = "未知应用程序";
//			if (obj) {
			switch (this.settings.op) {
				case "edit":
					this.setReadOnly(false);
					break;
				case "read":
					this.setReadOnly(this.settings.op == "read" ? false : true);
					if (this.settings.fileName.indexOf(".xls") > 0 || this.settings.fileName.indexOf(".XLS") > 0) {
						var sheets = this.settings.obj.ActiveDocument.Sheets;
						var sc = sheets.Count;
						for (var i = 1; i <= sc; i++) {
							sheets(i).EnableSelection = 1;
						}
					}
					break;
				default:
					break;
			}
//				s = obj.Application.Name;
//			}
		} catch (err) {
			window.status = "OnDocumentOpened事件的Script产生错误。" + err.number + ":" + err.description;
		}
	},
	//设置文档为只读
	setReadOnly: function(boolvalue) {
		var appName, i;
		try {
			if (boolvalue) {
				this.settings.obj.IsShowToolMenu = false;
			}
			if (!this.settings.docOpen) {
				return;
			}
			with (this.settings.obj.ActiveDocument) {
				appName = new String(Application.Name);
				// Word
				if ((appName.toUpperCase()).indexOf("WORD") > -1) {
					if (ProtectionType != -1 && !boolvalue) {
						Unprotect();
					}
					if (ProtectionType == -1 && boolvalue) {
						Protect(2, true, "");
					}
				} else if ((appName.toUpperCase()).indexOf("EXCEL") > -1) {
					// EXCEL
					for (i = 1; i <= Application.Sheets.Count; i++) {
						if (boolvalue) {
							Application.Sheets(i).Protect("", true, true, true);
						} else {
							Application.Sheets(i).Unprotect("");
						}
					}
					if (boolvalue) {
						Application.ActiveWorkbook.Protect("", true);
					} else {
						Application.ActiveWorkbook.Unprotect("");
					}
				}
			}
		} catch (err) {
			alert("错误：" + err.number + ":" + err.description);
		}
	},
	//设置用户名
	_setDocUser: function(cuser) {
		if (!this.settings.docOpen) {
			return;
		}
		with (this.settings.obj.ActiveDocument.Application) {
			UserName = cuser;
		}
	},
	//如果原先的表单定义了OnSubmit事件，保存文档时首先会调用原先的事件。
	_beforeSubmit: function() {
		var form = document.forms[0];
		if (form.onsubmit) {
			var retVal = form.onsubmit();
			if (typeof retVal == "boolean" && retVal == false) {
				return false;
			}
		}
		return true;
	},
	/**
	 * 此函数用来产生自动将表单数据创建成为控件的SaveToURL函数所需要的参数。返回
	 * 一个paraObj对象。paraObj.FFN包含表单的最后一个<input type=file name=XXX>的name
	 * paraObj.PARA包含了表单的其它数据，比如：f1=v1&f2=v2&f3=v3.其中,v1.v2.v3是经过
	 * javascript的escape函数编码的数据。
	 * @param {type} paraObj
	 * @returns {undefined}
	 */
	_genDominoPara: function(paraObj) {
		var fmElements = document.forms[0].elements;
		var i, j, elObj, optionItem;
		for (i = 0; i < fmElements.length; i++) {
			elObj = fmElements[i];
			switch (elObj.type) {
				case "file":
					paraObj.FFN = elObj.name;
					break;
				case "reset":
					break;
				case "radio":
				case "checkbox":
					if (elObj.checked) {
						paraObj.PARA += (elObj.name + "=" + escape(elObj.value) + "&");
					}
					break;
				case "select-multiple":
					for (j = 0; j < elObj.options.length; j++) {
						optionItem = elObj.options[j];
						if (optionItem.selected) {
							paraObj.PARA += (elObj.name + "=" + escape(optionItem.value) + "&");
						}
					}
					break;
				default: // text,Areatext,selecte-one,password,submit,etc.
					if (elObj.name) {
						paraObj.PARA += (elObj.name + "=" + escape(elObj.value) + "&");
					}
					break;
			}
		}
	},
	_getLog: function() {

	},
	/**
	 * 设置页面布局
	 * @returns {undefined}
	 */
	changeLayout: function() {
		try {
			this.settings.obj.showdialog(5);
		} catch (err) {
			alert("错误：" + err.number + ":" + err.description);
		}
	},
	/**
	 * 打印文档
	 * @returns {undefined}
	 */
	printDoc: function() {
		try {
			this.settings.obj.printout(true);
		} catch (err) {
			if (err.number != -2147467260) {
				alert("错误：" + err.number + ":" + err.description);
			}
		}
	},
	/**
	 * 保存文档为PDF到本地
	 * @param {type} IsPermitPrint
	 * @param {type} IsPermitCopy
	 * @returns {undefined}
	 */
	saveAsPDFFile: function(IsPermitPrint, IsPermitCopy) {
		try {
			this.settings.obj.SaveAsPDFFile('', true, '', true, false, '', IsPermitPrint, IsPermitCopy);
		}
		catch (err) {
			if (err.number == -2147467259) {
				if (window.confirm("该功能需要软件【PDFCreator】支持,点击确定前往官网下载安装。")) {
					window.location.href = 'http://www.ibos.com.cn/download/PDFCreator-1_2_3_setup.zip';
				}
			}
		}

	},
	/**
	 * 屏蔽by banyan
	 * @returns {undefined}
	 */
	showLog: function() {
		if (this.settings.ocxlog.style.display === "none") {
			this.settings.ocxlog.style.display = "block";
			this.settings.obj.style.display = "none";
			if (this.settings.ocxlog.innerText === "") {
				this._getLog();
			}
		} else {
			this.settings.ocxlog.style.display = "none";
			this.settings.obj.style.display = "block";
		}
	},
	/**
	 * 此函数用来保存当前文档。主要使用了控件的SaveToURL函数。有关此函数的详细用法，请参阅手册
	 * @param {type} opflag
	 * @returns {undefined}
	 */
	saveDoc: function(opflag) {
		var retStr = new String;
		var paraObj = new Object();
		paraObj.PARA = "";
		paraObj.FFN = "";
		try {
			if (!this._beforeSubmit()) {
				return false;
			}
			document.forms[0].docsize.value = this.settings.obj.DocSize;
			this.__genDominoPara(paraObj);
			if (!paraObj.FFN) {
				alert("参数错误：控件的第二个参数没有指定。");
				return false;
			}
			if (!this.settings.docOpen) {
				alert("没有打开的文档。");
				return false;
			}

			switch (this.settings.op) {
				case "1":
				case "2":
				case '3':
					retStr = this.settings.obj.SaveToURL(this.settings.actionUrl, paraObj.FFN, "", this.settings.fileName, 0);
					document.all("attachmentid").value = retStr;
					if (opflag === 1) {
						this.settings.docOpen = false;
						window.close();
					}
					break;
				case "edit":
					lock_ref();
					retStr = this.settings.obj.SaveToURL(this.settings.actionUrl, paraObj.FFN, "", this.settings.fileName, 0);
					alert(retStr);
					if (opflag === 1) {
						this.settings.docOpen = false;
						window.close();
					}
					break;
				case "5":
				case "6":
				case "read":
					alert("文档处于阅读状态，您不能保存到服务器。");
				default:
					break;
			}
		}
		catch (err) {
			alert("不能保存到URL：" + err.number + ":" + err.description);
		}
	},
	/**
	 * 进入或退出痕迹保留状态
	 * @param {type} bool
	 * @returns {undefined}
	 */
	setMarkModify: function(bool) {
		if (!this.settings.docOpen) {
			return;
		}
		this.settings.obj.ActiveDocument.TrackRevisions = bool;
	},
	/**
	 * 显示/不显示修订文字
	 * @param {type} bool
	 * @returns {undefined}
	 */
	showRevisions: function(bool) {
		if (!this.settings.docOpen) {
			return;
		}
		this.settings.obj.ActiveDocument.ShowRevisions = bool;
	},
	/**
	 * 从本地增加图片到文档指定位置
	 * @returns {undefined}
	 */
	addPictureFromLocal: function() {
		if (this.settings.docOpen) {
			this.settings.obj.AddPicFromLocal(
					"", //路径
					true, //是否提示选择文件
					true, //是否浮动图片
					100, //如果是浮动图片，相对于左边的Left 单位磅
					100); //如果是浮动图片，相对于当前段落Top
		}
	},
	/**
	 * 从本地增加电子印章
	 * @param {type} key
	 * @returns {undefined}
	 */
	addSignFromLocal: function(key) {
		if (this.settings.docOpen) {
			if (this.settings.obj.IsNTKOSecSignInstalled()) {
				this.settings.obj.AddSecSignFromLocal(this.settings.user, "", true, 0, 0, 1);
			} else {
				this.settings.obj.AddSignFromLocal(this.settings.user, "", true, 0, 0, key);
			}
		}
	},
	/**
	 * 开始手写签名
	 * @param {type} key
	 * @returns {undefined}
	 */
	handSign: function(key) {
		if (this.settings.docOpen) {
			if (this.settings.obj.IsNTKOSecSignInstalled()) {
				this.settings.obj.AddSecHandSign(this.settings.user, 0, 0, 1);
			} else {
				this.settings.obj.DoHandSign(this.settings.user, 0, 0x000000ff, 2, 100, 50, null, key);
			}
		}
	},
	/**
	 * 开始全屏手写签名
	 * @param {type} key
	 * @returns {undefined}
	 */
	fullHandSign: function(key) {
		if (this.settings.docOpen) {
			if (this.settings.obj.IsNTKOSecSignInstalled()) {
				this.settings.obj.AddSecHandSign(this.settings.user, 0, 0, 1);
			} else {
				this.settings.obj.DoHandSign2(this.settings.user, key, 0, 0, 0, 100);
			}
		}
	},
	/**
	 * 开始手绘
	 * @returns {undefined}
	 */
	handDraw: function() {
		if (this.settings.docOpen) {
			this.settings.obj.DoHandDraw(
					0, //笔型0－实线 0－4 //可选参数
					0x00ff0000, //颜色 0x00RRGGBB//可选参数
					3, //笔宽//可选参数
					200, //left//可选参数
					50//top//可选参数
					);
		}
	},
	/**
	 * 开始全屏手绘
	 * @returns {undefined}
	 */
	fullHandDraw: function() {
		if (this.settings.docOpen) {
			this.settings.obj.DoHandDraw2();
		}
	},
	/**
	 * 检查签名结果
	 * @param {type} key
	 * @returns {undefined}
	 */
	checkSign: function(key) {
		if (this.settings.docOpen) {
			var ret = this.settings.obj.DoCheckSign(false, key);
			// 可选参数 IsSilent 缺省为FAlSE，表示弹出验证对话框,否则，只是返回验证结果到返回值
		}
	},
	addDocHeader: function(url) {
		try {
			//选择对象当前文档的所有内容
			var curSel = this.settings.obj.ActiveDocument.Application.Selection;
			this.setMarkModify(false);
			curSel.WholeStory();
			curSel.Cut();
			//插入模板
			url = this.settings.pathUrl + "wordmodel/view/get.php?model=" + url;
			this.settings.obj.AddTemplateFromURL(url);
			var bookMarkName = "zhengwen";
			if (!this.settings.obj.ActiveDocument.BookMarks.Exists(bookMarkName)) {
				alert("Word 模板中不存在名称为：\"" + bookMarkName + "\"的书签！\n关于套红模版制作，请咨询技术支持人员。");
				return;
			}
			var bkmkObj = this.settings.obj.ActiveDocument.BookMarks(bookMarkName);
			var saveRange = bkmkObj.Range;
			saveRange.Paste();
			this.settings.obj.ActiveDocument.Bookmarks.Add(bookMarkName, saveRange);
			this.setMarkModify(true);
		}
		catch (err) {
			alert("错误：" + err.number + ":" + err.description);
		}
	}
};
Ibos.evt.add({
	/**
	 * 保存
	 */
	"save": function(param, elem) {
		return OCX.saveDoc(param.flag);
	},
	/**
	 * 页面设置
	 */
	"chgLayout": function(param, elem) {
		return OCX.changeLayout();
	},
	/**
	 * 打印
	 */
	"print": function(param, elem) {
		return OCX.printDoc();
	},
	/**
	 * 导出PDF
	 */
	"export": function(param, elem) {
		return OCX.saveAsPDFFile(true, true);
	},
	/**
	 * 此功能暂先不做
	 */
	"showLog": function(param, elem) {

	},
	/**
	 * 保留痕迹与否
	 */
	"setMarkModify": function(param, elem) {
		var $elem = $(elem);
		return OCX.setMarkModify($elem.prop('checked'));
	},
	/**
	 * 显示痕迹与否
	 */
	"showRevisions": function(param, elem) {
		var $elem = $(elem);
		return OCX.showRevisions($elem.prop('checked'));
	},
	/**
	 * 模板套红
	 */
	"selectWord": function(param, elem) {
		var url = OCX.settings.pathUrl + 'wordmodel/view/index.php';
		var myleft = (screen.availWidth - 650) / 2;
		var paramStr = "menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1";
		paramStr += ",top=0,left=" + myleft + ",height=350,width=400";
		window.open(url, 'select', paramStr);
	},
	/**
	 * 插入本地图片
	 */
	"addPictureFromLocal": function(param, elem) {
		return OCX.addPictureFromLocal();
	},
	/**
	 * 全屏手写签名
	 */
	"fullHandSign": function(param, elem) {
		var key = param.key;
		return OCX.fullHandSign(key);
	},
	/**
	 * 全屏手工绘图
	 */
	"fullHandDraw": function(param, elem) {
		return OCX.fullHandDraw();
	},
	/**
	 * 插入手写签名
	 */
	"handSign": function(param, elem) {
		var key = param.key;
		return OCX.handSign(key);
	},
	/**
	 * 插入手工绘图
	 */
	"handDraw": function(param, elem) {
		return OCX.handDraw();
	},
	/**
	 * 加盖本地电子印章
	 */
	"addSignFromLocal": function(param, elem) {
		var key = param.key;
		return OCX.addSignFromLocal(key);
	},
	/**
	 * 加盖服务器电子印章
	 */
	"addSignFromServer": function(param, elem) {
		// 暂时先不做
	},
	/**
	 * 验证签名及印章
	 */
	"checkSign": function(param, elem) {
		var key = param.key;
		return OCX.checkSign(key);
	}
});