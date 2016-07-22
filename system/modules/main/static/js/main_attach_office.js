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

// 默认参数
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
	 * 初始化
	 * @method _init
	 */
	_init: function() {
		if (!$(this.settings.obj).is('object')) {
			$.error("(OCX): "+ Ibos.l("MAIN.ERROR_DOC_OBJECT"));
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
			 // if ($.browser.msie) {
				this._openDocumentByOP(this.settings.op);
			 	this.onDocumentOpened();
			 // }
		} catch (err) {
			var msg = Ibos.l("MAIN.DOC_OPEN");
			if (window.confirm(msg)) {
				this.settings.obj.BeginOpenFromURL(this.settings.attachUrl, true, (this.settings.op === 'edit' ? false : true), "WPS.Document");
			}
		} finally {

		}
	},
	/**
	 *
	 * @param {type} op
	 * @returns {undefined}
	 */
	/**
	 * 打开文档的方式
	 * @method _openDocumentByOP
	 * @param  {String]} op 文档打开的方式
	 */
	_openDocumentByOP: function(op) {
		var _this = this;
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
					this.settings.obj.BeginOpenFromURL(this.settings.attachUrl, false, true);
					_this.isOpen(function(){
						if( _this.settings.obj.DocType == 1 ){
							_this.showRevisions(false);
							_this.setMarkModify(false);
						}
						_this.setReadOnly(true);
					});
				}
				break;
			case 'edit':
				if (this.settings.attachUrl) {
					this.settings.obj.BeginOpenFromURL(this.settings.attachUrl, true, false);
					_this.isOpen(function(){
						if( _this.settings.obj.DocType == 1 ){
							_this.showRevisions(true);
							_this.setMarkModify(true);
						}
					});
				} else {
					this.settings.obj.CreateNew("Word.Document");
				}
				break;
			default:
				$.error("(OCX): "+ Ibos.l("MAIN.UNKNOWN_OPEN_OPERATE"));
				break;
		}
	},
	// 判断是否已经打开文档
	isOpen: function(callback){
		var timer = null,
			_this = this;
		timer = setInterval(function(){
			if( _this.settings.obj && _this.settings.obj.ActiveDocument){
				callback && callback();
				clearInterval(timer);
			}
		}, 100);
	},
	/**
	 * 文档打开
	 * @method onDocumentOpened
	 */
	onDocumentOpened: function() {
		try {
			this.settings.docOpen = true;

			this.settings.obj.Caption = this.settings.fileName;
			if (this.settings.fileName.indexOf(".ppt") < 0 && this.settings.fileName.indexOf(".PPT") < 0) {
				this._setDocUser(this.settings.user);
			}

			switch (this.settings.op) {
				case "edit":
					this.settings.obj.setReadOnly(false);
					break;
				case "read":
					this.settings.obj.setReadOnly(this.settings.op == "read" ? true : false);
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
		} catch (err) {
			window.status = "OnDocumentOpened"+ Ibos.l("MAIN.EVENT_SCRIPT_ERROR") + err.number + ":" + err.description;
		}
	},
	/**
	 * 设置文档为只读
	 * @method setReadOnly
	 * @param {String} boolvalue 是否为只读
	 */
	setReadOnly: function(boolvalue) {
		var appName, i;
		try {
			boolvalue && (this.settings.obj.IsShowToolMenu = false);

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
			alert( Ibos.l("MAIN.ERROR") +"：" + err.number + ":" + err.description);
		}
	},
	/**
	 * 设置用户名
	 * @method _setDocUser
	 * @param {String} cuser 传入用户名的值
	 */
	_setDocUser: function(cuser) {
		if (!this.settings.docOpen) {
			return;
		}
		with (this.settings.obj.ActiveDocument.Application) {
			UserName = cuser;
		}
	},
	/**
	 * 如果原先的表单定义了OnSubmit事件，保存文档时首先会调用原先的事件。
	 * @method _beforeSubmit
	 * @return {Boolean} 返回真假
	 */
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
	 * @param 	{Object} 	paraObj 传入Object对象
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
					if (elObj.checked && elObj.name) {
						paraObj.PARA += (elObj.name + "=" + escape(elObj.value) + "&");
					}
					break;
				case "select-multiple":
					if (elObj.name) {
						for (j = 0; j < elObj.options.length; j++) {
							optionItem = elObj.options[j];
							if (optionItem.selected) {
								paraObj.PARA += (elObj.name + "=" + escape(optionItem.value) + "&");
							}
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
	/**
	 * 获取日志
	 * @method _getLog
	 */
	_getLog: function() {

	},
	/**
	 * 设置页面布局
	 * @method changeLayout
	 */
	changeLayout: function() {
		try {
			this.settings.obj.showdialog(5);
		} catch (err) {
			alert(Ibos.l("MAIN.ERROR") + "：" + err.number + ":" + err.description);
		}
	},
	/**
	 * 打印文档
	 * @method  printDoc
	 */
	printDoc: function() {
		try {
			this.settings.obj.printout(true);
		} catch (err) {
			if (err.number != -2147467260) {
				alert(Ibos.l("MAIN.ERROR") + "：" + err.number + ":" + err.description);
			}
		}
	},
	/**
	 * 保存文档为PDF到本地
	 * @method saveAsPDFFile
	 * @param  {type} IsPermitPrint
	 * @param  {type} IsPermitCopy
	 */
	saveAsPDFFile: function(IsPermitPrint, IsPermitCopy) {
		try {
			this.settings.obj.SaveAsPDFFile('', true, '', true, false, '', IsPermitPrint, IsPermitCopy);
		}
		catch (err) {
			if (err.number == -2147467259) {
				if (window.confirm( Ibos.l("MAIN.FUNCTION_NEED_SUPPORT") )) {
					window.location.href = 'http://www.ibos.com.cn/download/PDFCreator-1_2_3_setup.zip';
				}
			}
		}

	},
	/**
	 * 屏蔽by banyan
	 * @method showLog
	 */
	showLog: function() {
		/*if (this.settings.ocxlog.style.display === "none") {
			this.settings.ocxlog.style.display = "block";
			this.settings.obj.style.display = "none";
			if (this.settings.ocxlog.innerText === "") {
				this._getLog();
			}
		} else {
			this.settings.ocxlog.style.display = "none";
			this.settings.obj.style.display = "block";
		}*/
		if ( $(this.settings.ocxlog).is(":hidden") ) {
			if (this.settings.ocxlog.innerText === "") {
				this._getLog();
			}
		}
		$(this.settings.ocxlog).toggle();
		$(this.settings.obj).toggle();
	},
	/**
	 * 此函数用来保存当前文档。主要使用了控件的SaveToURL函数。有关此函数的详细用法，请参阅手册
	 * @method saveDoc
	 * @param  {Boolean} opflag 操作标签
	 */
	saveDoc: function(opflag) {
		var retStr = new String(),
			paraObj = new Object();
		paraObj.PARA = "";
		paraObj.FFN = "";
		try {
			if (!this._beforeSubmit()) {
				return false;
			}
			document.forms[0].docsize = this.settings.obj.DocSize;
			this._genDominoPara(paraObj);
			if (!paraObj.FFN) {
				alert( Ibos.l("MAIN.PARAM_ERROR") );
				return false;
			}
			if (!this.settings.docOpen) {
				alert( Ibos.l("MAIN.NOT_OPEN_DOC") );
				return false;
			}

			switch (this.settings.op) {
				case "1":
				case "2":
				case '3':
					retStr = this.settings.obj.SaveToURL(this.settings.actionUrl, paraObj.FFN, paraObj.PARA + "filepath=" + this.settings.attachUrl, this.settings.fileName, 0);
					document.all("attachmentid").value = retStr;
					if (opflag === 1) {
						this.settings.docOpen = false;
						window.close();
					}
					break;
				case "edit":
					// lock_ref();
					retStr = this.settings.obj.SaveToURL(this.settings.actionUrl, paraObj.FFN, paraObj.PARA + "filepath=" + this.settings.attachUrl, this.settings.fileName, 0);
					retStr = $.parseJSON(retStr);
					alert(this.settings.fileName + "--" + retStr.msg);
					if (opflag === 1) {
						this.settings.docOpen = false;
						window.open('','_parent','');
						window.close();
					}
					break;
				case "5":
				case "6":
				case "read":
					alert( Ibos.l("MAIN.DOC_READER_STATU") );
					break;
				default:
					break;
			}
		}
		catch (err) {
			alert( Ibos.l("MAIN.NOT_SAVE_URL") + "：" + err.number + ":" + err.description);
		}
	},
	/**
	 * 打开或者关闭修订模式
	 * @method  setMarkModify
	 * @param 	{Boolean} 	bool
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
	 * @method  showRevisions
	 * @param 	{Boolean} 	bool
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
	 * @method  addPictureFromLocal
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
	 * @method  addSignFromLocal
	 * @param 	{type} 		key
	 * @returns {undefined}
	 */
	addSignFromLocal: function(key) {
		if (this.settings.docOpen) {
			// if (this.settings.obj.IsNTKOSecSignInstalled()) {
			// 	this.settings.obj.AddSecSignFromLocal(this.settings.user, "", true, 0, 0, 1);
			// } else {
			// 	this.settings.obj.AddSignFromLocal(this.settings.user, "", true, 0, 0, key);
			// }
			this.settings.obj.AddSignFromLocal(this.settings.user, "", true, 0, 0, key);
		}
	},
	/**
	 * 开始手写签名
	 * @method  handSign
	 * @param {type} key
	 * @returns {undefined}
	 */
	handSign: function(key) {
		if (this.settings.docOpen) {
			// if (this.settings.obj.IsNTKOSecSignInstalled()) {
			// 	this.settings.obj.AddSecHandSign(this.settings.user, 0, 0, 1);
			// } else {
			// 	this.settings.obj.DoHandSign(this.settings.user, 0, 0x000000ff, 2, 100, 50, null, key);
			// }
			this.settings.obj.DoHandSign(this.settings.user, 0, 0x000000ff, 2, 100, 50, null, key);
		}
	},
	/**
	 * 开始全屏手写签名
	 * @method  fullHandSign
	 * @param 	{type} 		key
	 * @returns {undefined}
	 */
	fullHandSign: function(key) {
		if (this.settings.docOpen) {
			// if (this.settings.obj.IsNTKOSecSignInstalled()) {
			// 	this.settings.obj.AddSecHandSign(this.settings.user, 0, 0, 1);
			// } else {
			// 	this.settings.obj.DoHandSign2(this.settings.user, key, 0, 0, 0, 100);
			// }
			this.settings.obj.DoHandSign2(this.settings.user, key, 0, 0, 0, 100);
		}
	},
	/**
	 * 开始手绘
	 * @method  handDraw
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
	 * @method  fullHandDraw
	 * @returns {undefined}
	 */
	fullHandDraw: function() {
		(this.settings.docOpen) && this.settings.obj.DoHandDraw2();
	},
	/**
	 * 检查签名结果
	 * @method  checkSign
	 * @param 	{type} 		key
	 * @returns {undefined}
	 */
	checkSign: function(key) {
		if (this.settings.docOpen) {
			var ret = this.settings.obj.DoCheckSign(false, key);
			// 可选参数 IsSilent 缺省为FAlSE，表示弹出验证对话框,否则，只是返回验证结果到返回值
		}
	},
	/**
	 * 添加文档头部
	 * @method  addDocHeader
	 * @param 	{String} 	url 传入地址
	 * @returns {undefined}
	 */
	addDocHeader: function(url) {
		try {
			//选择对象当前文档的所有内容
			var curSel = this.settings.obj.ActiveDocument.Application.Selection;
			this.setMarkModify(false);
			curSel.WholeStory();
			curSel.Cut();
			//插入模板
			url = this.settings.staticurl + "wordmodel/view/get.php?model=" + url;
			this.settings.obj.AddTemplateFromURL(url);
			var bookMarkName = "zhengwen";
			if (!this.settings.obj.ActiveDocument.BookMarks.Exists(bookMarkName)) {
				alert( Ibos.l("MAIN.TMPL_TITLE_NOT_EXIST") + bookMarkName + Ibos.l("MAIN.SKILL_SUPPORT_CHARGE") );
				return;
			}
			var bkmkObj = this.settings.obj.ActiveDocument.BookMarks(bookMarkName);
			var saveRange = bkmkObj.Range;
			saveRange.Paste();
			this.settings.obj.ActiveDocument.Bookmarks.Add(bookMarkName, saveRange);
			this.setMarkModify(true);
		}
		catch (err) {
			alert( Ibos.l("MAIN.ERROR") + "：" + err.number + ":" + err.description);
		}
	}
};
Ibos.evt.add({
	// 保存
	"save": function(param, elem) {
		return officeOcx.saveDoc(param.flag);
	},
	// 页面设置
	"chgLayout": function(param, elem) {
		return officeOcx.changeLayout();
	},
	// 打印
	"print": function(param, elem) {
		return officeOcx.printDoc();
	},
	// 导出PDF
	"export": function(param, elem) {
		return officeOcx.saveAsPDFFile(true, true);
	},
	// 此功能暂先不做
	"showLog": function(param, elem) {

	},
	// 保留痕迹与否
	"setMarkModify": function(param, elem) {
		var $elem = $(elem);
		return officeOcx.setMarkModify($elem.prop('checked'));
	},
	// 显示痕迹与否
	"showRevisions": function(param, elem) {
		var $elem = $(elem);
		return officeOcx.showRevisions($elem.prop('checked'));
	},
	// 模板套红
	"selectWord": function(param, elem) {
		if( officeOcx.settings.attachName.indexOf('xls') != -1){
			alert('excel不能使用插入套红');
			return;
		}
		var url = officeOcx.settings.staticurl + 'wordmodel/view/index.php';
		var myleft = (screen.availWidth - 650) / 2;
		var paramStr = "menubar=0,toolbar=0,status=0,resizable=1,scrollbars=1";
		paramStr += ",top=0,left=" + myleft + ",height=350,width=400";
		window.open(url, 'select', paramStr);
	},
	// 插入本地图片
	"addPictureFromLocal": function(param, elem) {
		return officeOcx.addPictureFromLocal();
	},
	// 全屏手写签名
	"fullHandSign": function(param, elem) {
		var key = param.key;
		return officeOcx.fullHandSign(key);
	},
	// 全屏手工绘图
	"fullHandDraw": function(param, elem) {
		return officeOcx.fullHandDraw();
	},
	// 插入手写签名
	"handSign": function(param, elem) {
		var key = param.key;
		return officeOcx.handSign(key);
	},
	// 插入手工绘图
	"handDraw": function(param, elem) {
		return officeOcx.handDraw();
	},
	// 加盖本地电子印章
	"addSignFromLocal": function(param, elem) {
		var key = param.key;
		return officeOcx.addSignFromLocal(key);
	},
	// 加盖服务器电子印章
	"addSignFromServer": function(param, elem) {
		// 暂时先不做
	},
	// 验证签名及印章
	"checkSign": function(param, elem) {
		var key = param.key;
		return officeOcx.checkSign(key);
	}
});
