require.config({
	baseUrl: G.STATIC_URL + "/js",

	paths: {
		"underscore": "lib/underscore/underscore",

		"underscoreString": "lib/underscore/underscore.string",

		"backbone": "lib/backbone/backbone",

		"backboneLocalstorage": "lib/backbone/backbone.localStorage",

		"artDialog": "lib/artDialog/artDialog.min",

		"zTree": "lib/zTree/jquery.ztree.all.min",

		"select2": "lib/Select2/select2",

		"userSelect": "app/ibos.userSelect",

		"charCount": "app/ibos.charCount",

		"pagination": "lib/jquery.pagination",

		"moment": "lib/moment.min",

		"ueditor": "lib/ueditor/editor_all_min",

		"pSelect": "app/ibos.pSelect",

		"swfUpload": "lib/SWFUpload/swfupload.packaged",
		"swfUploadHandler": "lib/SWFUpload/handlers",

		"datatables": "lib/dataTable/js/jquery.dataTables",

		"echarts": "lib/echarts/echarts-plain",

		"emotion": "src/emotion",

		"atwho": "lib/atwho/jquery.atwho",

		"dateRangePicker": "lib/daterangepicker/daterangepicker"
	},

	shim: {
		"underscore": {
			exports: "_"
		},

		"underscoreString": {
			deps: ["underscore"],
			exports: "_"
		},

		"backbone": {
			deps: ["underscore", "jquery"],
			exports: "Backbone"
		},

		"backboneLocalstorage": {
			deps: ["backbone"],
			exports: "Backbone"
		},
		
		"artDialog": {
			deps: ["css!lib/artDialog/skins/ibos.css"],
			exports: "jQuery.artDialog",
			init: function(){
				var d = $.artDialog;
				// 兼容新旧版本 dialog 系列方法
				$.extend(Ui, {
					/**
					 * 全局对话框，基于artDialog
					 * @method dialog
					 * @param {Object} options 配置
					 * @return {Object} artDialog实例
					 */
					dialog: d,
					/**
					 * 全局警告框，基于artDialog，模态
					 * @method alert
					 * @param  {String}  msg 提示文本
					 * @param  {Function}  ok 确定后的回调
					 * @return {Object} artDialog实例
					 */
					alert: d.alert,
					/**
					 * 全局确定框，基于artDialog，模态
					 * @method confirm
					 * @param  {String} msg 提示文本
					 * @param  {Function}  ok 确定后的回调
					 * @param  {Function}  cancel 取消后的回调
					 * @return {Object} artDialog实例
					 */
					confirm: d.confirm,
					/**
					 * 全局信息接收框，基于artDialog，模态
					 * @method prompt
					 * @param  {String} msg 提示文本
					 * @param  {Function}  ok 确定后的回调， 输入的文本会作为首参数传入
					 * @param  {Function}  cancel 取消后的回调
					 * @return {Object} artDialog实例
					 */
					prompt: d.prompt,
					tips: d.tips,
					/**
					 * ajax对话框，基于artDialog
					 * @method ajaxDialog
					 * @param  {String} url ajax地址
					 * @param  {Object}  options 配置，与Ui.dialog相同
					 * @return {Object} artDialog实例
					 */
					ajaxDialog: d.load,
					/**
					 * 框架的对话框，基于artDialog
					 * @method openFrame
					 * @param  {String} url 框架页地址
					 * @param  {Object}  options 配置，与Ui.dialog相同
					 * @return {Object} artDialog实例
					 */
					openFrame: d.open,
					/**
					 * 获取Dialog实例
					 * @method getDialog
					 * @param  {String} [id] dialog的自定义id, 为空时获取所有对话框实例
					 * @return {Object} artDialog实例
					 */
					getDialog: d.get,
					/**
					 * 关闭对话框
					 * @method closeDialog
					 * @param  {String} [id] dialog的自定义id, 为空时关闭所有对话框实例
					 * @return {Object} artDialog实例
					 */
					closeDialog: function(id) {
						// 没有传参时，关闭所有弹窗
						if(typeof id === "undefined") {
							for(var i in d.list){
								if(d.list.hasOwnProperty(i)){
									d.list[i].close();
								}
							}
						} else {
							var dl = this.getDialog(id);
							dl && dl.close();
						}
					}
				});
			}
		},

		"zTree": {
			deps: ["css!lib/zTree/css/ibos/ibos.css"],
			exports: "jQuery"
		},

		"select2": {
			deps: ["css!lib/Select2/select2.css"],
			exports: "jQuery"
		},

		"userSelect": {
			deps: ["artDialog", "zTree", "select2"],
			exports: "jQuery"
		},

		"ueditor": {
			deps: ["lib/ueditor/editor_config"],
			exports: "UE"
		},

		"charCount": { exports: "jQuery" },

		"pagination": { exports: "jQuery" },

		"pSelect": { exports: "jQuery" },

		"moment": { exports: "moment" },

		"swfUpload": { exports: "SWFUpload" },
		
		"swfUploadHandler": {
			deps: ["swfUpload"],
			exports: "Ibos"
		},

		"datatables": {
			deps: ["css!lib/dataTable/css/jquery.dataTables_ibos.min.css"]
		},

		"echarts": {
			exports: "echarts"
		},

		"emotion": {
			deps: ["css!../css/emotion.css"],
			exports: "jQuery"
		},

		"atwho": {
			deps: ["css!lib/atwho/jquery.atwho.css"]
		},

		"dateRangePicker": {
			deps: ["css!lib/daterangepicker/daterangepicker-ibos.css"],
			exports: "jQuery"
		}
	}
});

(function(){
	require([
		'underscoreString',
		'backbone'
	], function(_, Backbone) {
		Backbone.emulateHTTP = true;
		Backbone.emulateJSON = true;

		var app = Ibos.app;
		var urlParam = U.getUrlParam();
		
		// 如果 url 参数中包含路由，根据路由中的模块信息加载入口 JS
		if(urlParam && urlParam.r){
			// 模块名（后端意义上的模块）
			var modName = _.str.strLeft(urlParam.r, "/");
			// 静态文件地址
			var assetUrl = app.getAssetUrl(modName);

			if(assetUrl){
				// 从入口模块返回路由与脚本的对应表，根据对应关系加载相关脚本
				require([assetUrl + "/js/" + modName + ".js"], function(route){
					if(route[urlParam.r]) {
						require(_.result(route, urlParam.r));
					}
				});
			}
		}
	});

	//@Notice: 下面是部分目前被合并成通用文件，但日后会作为模块定义的插件
	define("datetimepicker", [], jQuery);
})();