/* 
 * 全局通用组件——信息导入配置
 * @param [Number]     [stateIndex]     插件当前状态;
 * @param [String]     [tpl]            数据导入对应模板;
 * @param [Boolean]    [lock]           页面锁定;
 * @param [Function]   [closefn]        窗口关闭回调;
 * @param [Number]     [per]            每次导入的数据量,默认10;
 * @param [String]     [left]           窗口左定位，默认"50%";
 * @param [String]     [right]          窗口上定位，默认"50%";
 * @param [Number]     [zIndex]         窗口层级;
 * @return [Object]    [importDialog]   数据导入弹窗实例
 */
;
(function (win, factory, $) {
	win['importData'] = factory($);
})(window, function ($) {
	var _tmplStep = {
		'step1': [
			'<div class="import-item">',
			'<div class="span12 mt20 mb">',
			'<i class="o-import-step1"></i>',
			'</div>',
			'</div>',
			'<div class="import-item">',
			'<div class="import-item-mc">',
			'<div class="import-content import-upload-tips">',
			'<p class="xwb mtm mbm">请导入文件</p>',
			'<div class="import-upload">',
			'<div class="import-btn">',
			'<span id="import_btn"></span>',
			'</div>',
			'<i class="o-import-upload"></i>',
			'</div>',
			'</div>',
			'<div class="import-file-target">',
			'<div class="attl" id="file_target"></div>',
			'</div>',
			'<input type="hidden" name="attachmentid" id="attachmentid" />',
			'<div class="file-sheets"></div>',
			'</div>',
			'<div class="import-item-mc">',
			'<div class="import-content">',
			'<p class="xwb mtm mbm">导入使用提示</p>',
			'<ol>',
			'<li>导入文件格式类型支持 <span class="xco">csv、xls(x)</span> ；</li>',
			'<li>每次仅支持单个文件上传</li>',
			'<li>如果Excel文件有密码保护，请 <span  class="xco">先清除密码</span> ；</li>',
			'<li>表格第一行必须为列名，数据从第二行开始导入；</li>',
			'<li>使用csv格式会占用<span class="xco">更少内存，耗时更少</span>，建议使用<span class="xco">csv格式</span></li>',
			'<li>字段规则等说明 <a href = "http://doc.ibos.com.cn/article/detail/id/335" target = "_blank" >传送门</a></li>',
			'</ol>',
			'</div>',
			'</div>',
			'</div>'
		].join(''),
		'step2': [
			'<div class="import-item">',
			'<div class="span12 mt20 mb">',
			'<i class="o-import-step2"></i>',
			'</div>',
			'</div>',
			'<div class="import-item" style="max-height: 220px; overflow-y:auto;">',
			'<div class="import-table">',
			'<div class="import-item-field">',
			'<p class="xwb">导入字段</p>',
			'</div>',
			'<div class="import-item-field">',
			'<p class="xwb">目标字段</p>',
			'</div>',
			'</div>',
			'<div class="field-block">',
			'</div>',
			'</div>',
			'<div class="import-item">',
			'<div class="import-table">',
			'<div class="import-item-field">',
			'<p class="xwb">如何处理重复</p>',
			'</div>',
			'</div>',
			'<div class="import-table">',
			'<div class="import-item-field">',
			'<select name="checkDone">',
			'<option value="new" selected>创建新记录</option>',
			'<option value="cover">覆盖旧记录</option>',
			// '<option value="nothing">不做任何重复检查</option>',
			'</select>',
			'</div>',
			'</div>',
			'</div>'
		].join(''),
		'step3': [
			'<div class="import-item">',
			'<div class="span12 mt20">',
			'<i class="o-import-step3"></i>',
			'</div>',
			'<p class="ml mbm import-count"></p>',
			'</div>',
			'<div class="import-item">',
			'<ul class="import-res">',
			'</ul>',
			'</div>'
		].join('')
	},
	_tmplBox = [
		'<div class="d-outer aui_import">',
		'<div class="d-inner" style="min-width: 592px;">',
		'<table class="d-dialog">',
		'<tbody>',
		'<tr>',
		'<td class="d-header" colspan="2">',
		'<div class="d-titleBar">',
		'<div class="d-title"></div>',
		'<a class="d-close" href="javascript:;">x</a>',
		'</div>',
		'</td>',
		'</tr>',
		'<tr>',
		'<td class="d-main" style="width:auto; height:auto;">',
		'<div class="d-content">',
		'<div class="" style="min-width: 592px; min-height: 300px;">',
		'<form class="form-horizontal form-compact form_mc">',
		'</form>',
		'</div>',
		'</div>',
		'</td>',
		'</tr>',
		'<tr>',
		'<td class="d-footer" colspan="2">',
		'<div class="d-buttons btn_mc">',
		'</div>',
		'</td>',
		'</tr>',
		'</tbody>',
		'</table>',
		'</div>',
		'</div>'
	].join(''),
			ajaxData = {
				parseFile: function (param) {
					var url = Ibos.app.url('main/import/sheet');
					return $.post(url, param, $.noop, 'json');
				},
				getModuleField: function (param) {
					var url = Ibos.app.url('main/import/settingColumns');
					return $.post(url, param, $.noop, 'json');
				},
				setModuleField: function (param) {
					var url = Ibos.app.url('main/import/import');
					return $.post(url, param, $.noop, 'json');
				}
			},
	configTip = {
		'unique': '唯一',
		'required': '必填',
		'mobile': '手机',
		'email': '邮箱',
		'datetime': '日期'
	};

	function tipLanChange(rule) {
		var i, len,
				res = [];

		for (i = 0, len = rule.length; i < len; i++) {
			res.push(configTip[rule[i]]);
		}

		return res.join('、');
	}

	var Button = function (config) {
		this.text = config.text;
		this.type = config.type || 'normal'; // normal primary warning
		this.evts = config.fn;
		return this.init();
	};

	Button.prototype.init = function () {
		var btn = document.createElement('input'),
				clsn = ["btn"],
				that = this;

		btn.type = 'button';
		btn.value = this.text;
		this.type === 'primary' ? clsn.push("btn-primary") : this.type === 'warning' ? clsn.push('btn-warning') : '';
		btn.className = clsn.join(' ');

		if (that.evts) {
			$(btn).on('click', function () {
				that.evts();
			});
		}

		return $(btn);
	}

	var Tmpl = function (config) {
		this.title = config.title;
		// 模板关闭时触发
		this.closefn = config.closefn;
		this.btns = config.btns; // [{text: "下一步", type: "primary", fn: function(){}}]
		this.step = config.step || 'step1'; // step1 step2 step3
		this.init();

		return this;
	}

	Tmpl.prototype = {
		constructor: Tmpl,
		init: function () {
			this.createDom();
			this.bindEvt();
		},
		createDom: function () {
			var _tmpl = '';
			this.$box = $(_tmplBox);
			$.extend(this, {
				$title: this.$box.find('.d-title'),
				$close: this.$box.find('.d-close'),
				$form_mc: this.$box.find('.form_mc'),
				$btn_mc: this.$box.find('.btn_mc')
			});

			_tmpl = $(_tmplStep[this.step]);
			this.$form_mc.append(_tmpl);
			this.$title.text(this.title);
		},
		bindEvt: function () {
			var that = this;
			that.$close.on('click', function (e) {
				that.closefn && that.closefn();
			});

			if (Array.isArray(that.btns)) {
				$.each(that.btns, function (i, e) {
					var btn = new Button({
						text: e.text,
						type: e.type,
						fn: e.fn
					});
					that.$btn_mc.append(btn);
				})
			} else {
				that.$btn_mc.append(new Button(that.btns));
			}
		},
		addBtn: function (btn) {
			this.$btn_mc.append(new Button(btn));
			return true;
		}
	}

	var importDialog = function (options) {
		if (!(this instanceof importDialog)) {
			return new importDialog(options);
		}

		options = $.extend({}, importDialog.defaults, options);
		this.stateIndex = 0;
		this.closefn = options.closefn || '';
		this.tpl = options.tpl;
		this.module = options.module;
		if (!this.module || !this.tpl) {
			console.error('配置模块为必填参数项');
			return false;
		}
		this.lock = options.lock;
		this.per = options.per;
		// css
		this.top = options.top;
		this.left = options.left;
		this.zIndex = options.zIndex;

		this.init();
		return this;
	};

	importDialog.defaults = {
		lock: false,
		per: 500,
		top: "50%",
		left: "50%",
		zIndex: 6000
	}

	importDialog.prototype = {
		constructor: importDialog,
		init: function () {
			this.createDom();
			this.bindEvt();
			this.initUpload();
		},
		createDom: function () {
			// 创建容器
			var that = this;
			this.$box = $('<div class="import_dialog"></div>');
			// 锁定模板
			if (this.lock) {
				this.$mask = $('<div class="aui_mask"></div>');
				this.$mask.css('zIndex', this.zIndex++);
				this.$box.append(this.$mask);
			}

			this.$box.css({// this -> Dialog
				'top': this.top,
				'left': this.left
			});

			this._tmpl = [new Tmpl({
					title: '导入数据',
					closefn: function () {
						that.closed(that.closefn);
					}, // this -> Tmpl
					step: 'step1',
					btns: [{
							'text': '取消',
							'type': 'normal',
							'fn': function () {
								that.closed(that.closefn) // this -> Button
							}
						}, {
							'text': '下载模板',
							'type': 'normal',
							'fn': function () {
								window.location.href = Ibos.app.url('main/import/downloadTpl', {tpl: that.tpl, module: that.module});
							}
						}]
				}), new Tmpl({
					title: '数据选控',
					closefn: function () {
						that.closed(that.closefn);
					},
					step: 'step2',
					btns: [{
							'text': '上一步',
							'type': 'normal',
							'fn': function () {
								that.transition('pre');
							}
						}, {
							'text': '开始导入',
							'type': 'primary',
							'fn': function () {
								that.transition('next');
							}
						}]
				}), new Tmpl({
					title: '导入完成',
					closefn: function () {
						that.closed(that.closefn);
					},
					step: 'step3',
					btns: [{
							'text': '上一步',
							'type': 'normal',
							'fn': function () {
								that.transition('pre');
							}
						}, {
							'text': '完成',
							'type': 'primary',
							'fn': function () {
								that.transition('done');
							}
						}]
				})];

			that.$main = [];
			$.each(this._tmpl, function (i, e) {
				that.$box.append(e.$box);
				that.$main.push(e.$box);
				e.$box.css('zIndex', that.zIndex++).hide();
			});

			this.stateIndex === 0 && this.$main[this.stateIndex].show();
			document.body.appendChild(this.$box[0]);

		},
		transition: function (act) {
			// 状态机
			var that = this,
					fieldArray,
					checkOption = {},
					fieldRelation = {};

			this.$main[this.stateIndex].hide('slow');

			switch (act) {
				case 'pre':
					this.stateIndex--;
					break;
				case 'next':
					this.stateIndex++;
					break;
				case 'done':
					this.stateIndex = 0;
					this.closed();
					break;
				default:
					this.exportErr();
					break;
			}

			this.$main[this.stateIndex].show('slow');

			switch (this.stateIndex) {
				case 0:
					// start
					break;
				case 1:
					// setting
					act === 'next' && this.getField();
					break;
				case 2:
					// done
					fieldArray = this.getSelectValue();
					try {
						$.each(that.tplFieldArray, function (i, e) {
							fieldRelation[e] = fieldArray[i];
						});
					} catch (e) {
						Ui.tip('无法获取对应模板数据', 'warning');
						return false;
					}

					// 清空原有记录
					$('.import-count').empty();
					$('.import-res').empty();
					this.importLoad({
						tpl: that.tpl,
						op: 'start',
						per: that.per,
						times: 1,
						fieldRelation: fieldRelation,
						checkOption: $('[name="checkDone"]').val()
					});
					break;
				default:
					break;
			}
		},
		initUpload: function () {
			var that = this;
			that.$upload = $('.import-upload-tips');

			Ibos.upload.attach({
				post_params: {module: 'temp'},
				file_upload_limit: 1,
				button_placeholder_id: "import_btn",
				button_width: "100",
				button_height: "40",
				button_image_url: "",
				custom_settings: {
					containerId: "file_target",
					inputId: "attachmentid",
					success: function (file, data) {
						// aid为0时，表示上传不成功，通用上传
						if (data.aid !== 0) {
							that.parseFile(data, 1);
						} else {
							Ui.tip(data.msg, 'danger');
							return false;
						}
					}
				}
			});
		},
		bindEvt: function () {
			var that = this;
			$(document).on('closed.dialog', function () {
				that.$box.remove();
			});
		},
		parseFile: function (data, init) {
			var that = this,
					param = {
						tpl: this.tpl,
						module: this.module,
						url: this.fileUrl || (this.fileUrl = data.url),
						sheet: $('[name="sheetnames"]').val() || 0,
						init: init || 0
					};

			that._tmpl[that.stateIndex].$form_mc.waiting(null, 'normal', true);
			ajaxData.parseFile(param).done(function (res) {
				if (res.isSuccess) {
					var sheets = res.data.sheetnames,
							sheet = res.data.sheet,
							selStr = '<select name="sheetnames" class="dib span8">';
					$.each(sheets, function (i, e) {
						selStr += '<option value="' + i + '" ' + (i === sheet ? 'selected' : '') + '>' + e + '</option>';
					});
					selStr += '</select>';

					divStr = '<div class="import-content"><span class="mrs">选择工作表</span>' + selStr + '</div>';
					$('.file-sheets').empty().append($(divStr));
					// 文件上传，添加下一步按钮
					if (!that.hasInit) {
						that._tmpl[that.stateIndex].addBtn({
							'text': '下一步',
							'type': 'primary',
							'fn': function () {
								that.transition('next');
							}
						});
						that.hasInit = true;
					}

					that._tmpl[that.stateIndex].$form_mc.waiting(false);
					Ui.tip('哦荷，文件上传成功了～');
					return that;
				} else {
					Ui.tip(res.msg, "warning");
					return false;
				}
			}).done(function (res) {
				$('[name="sheetnames"]').on('change', function (evt) {
					that.parseFile();
				});
			});
		},
		getField: function () {
			var that = this,
					param = {
						tpl: this.tpl,
						url: this.fileUrl,
						sheet: $('[name="sheetnames"]').val()
					};

			that._tmpl[that.stateIndex].$form_mc.waiting(null, 'normal', true);
			ajaxData.getModuleField(param).done(function (res) {
				// 获取导入字段和目标字段
				if (res.isSuccess) {
					var fieldArray = res.data.fieldArray,
							tplFieldArray = res.data.tplFieldArray,
							rule = res.data.rule,
							_temp = '',
							_field = '',
							lenF = fieldArray.length,
							lenT = tplFieldArray.length,
							i, j;

					that.tplFieldArray = tplFieldArray;

					for (j = 0; j < lenT; j++) {
						var _options = '';
						for (i = 0; i < lenF; i++) {
							_options += '<option value="' + fieldArray[i] + '" ' + (i === j ? 'selected' : '') + '>' + fieldArray[i] + '</option>';
						}

						_options += '<option value="">--不导入对应字段--</option>';
						_temp =
								'<div class="import-table field-data">' +
								'<div class="import-item-field">' +
								'<select name="field_' + j + '">' +
								_options +
								'</select>' +
								'</div>' +
								'<div class="import-item-field">' +
								'<p>' + tplFieldArray[j] + '<small class="mlm xcr">' + tipLanChange(rule[tplFieldArray[j]]) + '</small></p>' +
								'</div>' +
								'</div>';
						_field += _temp;
					}

					that._tmpl[that.stateIndex].$form_mc.waiting(false);
					that.$box.find('.field-block').empty().append($(_field));
					return that;
				} else {
					Ui.tip(res.msg, 'warning');
					return false;
				}
			});
		},
		importLoad: function (param) {
			var that = this;

			ajaxData.setModuleField(param).done(function (res) {
				if (res.isSuccess) {
					var data = res.data,
							resBox = $('.import-res');
					if (data.op === 'continue') {
						var i, len, tmp,
								text = '',
								queue = data.queue;

						for (i = 0, len = queue.length; i < len; i++) {
							tmp = queue[i];
							text += tmp.status ? '<li>' + tmp.text + '</li>' : '<li><span class="xcr">' + tmp.text + '</span></li>';
						}
						;

						resBox.append($(text));
						resBox[0].scrollTop = resBox[0].scrollHeight;
						that.importLoad({op: data.op, per: that.per, times: data.times});
					} else if (data.op === 'end') {
						var text = '导入总数' + (data.success + data.failed) + '条&nbsp;&nbsp;' + '成功' + data.success + '条&nbsp;&nbsp;' + '<span class="xcr">失败' + data.failed + '条</span>';

						$('.import-count')[0].innerHTML = text;
						if ((!that.hasRes) && data.failed != 0) {
							that._tmpl[that.stateIndex].addBtn({
								'text': '导出错误数据',
								'type': 'primary',
								'fn': function () {
									that.transition('export');
								}
							});
							that.hasRes = true;
						}

						return that;
					}
				} else {
					Ui.tip(res.msg, "warning");
					return false;
				}
			})
		},
		getSelectValue: function () {
			var $select = $('.field-data'),
					len = $select.length,
					arr = [],
					i = 0,
					$elem;

			for (; i < len; i++) {
				arr.push($('[name="field_' + i + '"]').val());
			}

			return arr;
		},
		closed: function (callback) {
			$(document).trigger('closed.dialog');
			callback && callback.call(this);
			return true;
		},
		exportErr: function () {
			var that = this;
			window.location.href = Ibos.app.url('main/import/exportError', {tpl: that.tpl, module: that.module});
		},
		// 销毁实例
		destory: function () {
			var that = this,
					key = Object.getOwnPropertyNames(that);

			$.each(key, function (i, e) {
				delete that[e];
			});

			return that;
		}
	}

	return importDialog;
}, jQuery, undefined)
