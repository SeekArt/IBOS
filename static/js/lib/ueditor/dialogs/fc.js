/**
 * 模板方法
 * jq.web.template - a javascript template library
 * Templating from John Resig - http://ejohn.org/ - MIT Licensed
 * @method $.template
 * 
 */
(function(window, $) {
	$["template"] = function(tmpl, data) {
		return (template(tmpl, data));
	};
	$["tmpl"] = function(tmpl, data) {
		return $(template(tmpl, data));
	};
	var template = function(str, data) {
		//If there's no data, let's pass an empty object so the user isn't forced to.
		if (!data)
			data = {};
		return tmpl(str, data);
	};
	
	(function() {
		var cache = {};
		this.tmpl = function tmpl(str, data) {
			var fn = !/\W/.test(str) ? 
			cache[str] = cache[str] ||
			tmpl(document.getElementById(str).innerHTML)
			:
			new Function("obj",
				"var p=[],print=function(){ p.push.apply(p,arguments);};" + 
				"with(obj){p.push('" + str.replace(/[\r\t\n]/g, " ")
					.replace(/'(?=[^%]*%>)/g, "\t")
					.split("'").join("\\'")
					.split("\t").join("'").replace(/<%=(.+?)%>/g, "',$1,'")
					.split("<%").join("');")
					.split("%>").join("p.push('") + "');}return p.join('');");
			return data ? fn(data) : fn;
		};
	})();
})(window, window.jQuery);

var Fc = function(editor, tpl){
	this.editor = editor;
	this.tpl = tpl;
}

Fc.prototype.restoreValue = function(){}
Fc.prototype.getTemplate = function(data){
	return $.template(this.tpl, data);
}
Fc.prototype.addControl = function(data, update){
	var html = this.getTemplate(data),
		r, 
		p;
	
	if(data.type === "listview" && !update) {
		r = this.editor.selection.getRange();
		// 调整边缘至只选中ic元素
		r.adjustmentBoundary();
		p = UE.dom.domUtils.findParentByTagName(r.startContainer, "p", true);
		if(p){
			r.setStartAfter(p);
			r.collapse(true);
			r.select();
		}
	}
	this.editor.execCommand("insertHtml", html);
	r = this.editor.selection.getRange();
	// 定位光标位置(会导致多出&#8302;无效字符而出现吞字的情况)
	// r.setCursor();
	// this.editor.execCommand("insertHtml", " ")
}

Fc.prototype.updateContorl = function(node, data){
	// 更新时，先选中原节点，插入时替换掉
	this.editor.selection.getRange().selectNode(node).select();
	//
	UE.dom.domUtils.remove(node);
	this.addControl(data, true);
}

Fc.prototype.getControlData = function(node){
	var data = $(node).data();
	data.lvColvalue += ""; 
	data.lvTitle += ""; 
	return data;
}

var fcUtil = {
	//FLAG: "`",
	splitVal: function(value) {
		return (typeof value !== "undefined") ? ("" + value).replace(/`/g, "\n") : "";
	},

	joinVal: function(value) {
		return (typeof value !== "undefined") ? ("" + value).replace(/\n|\r/g, "`") : "";
	},

	createFieldTpl: function(tpl, field, check, data, allowEmpty) {
		var ret = "",
			fArr, cArr;

		tpl = "" + tpl;

		if(field == null) {
			return tpl;
		}

		check = (check == null) ? "" : check;

		fArr = ("" + field).split("`");
		cArr = ("" + check).split("`");

		for(var i = 0; i < fArr.length; i++){
			if(allowEmpty || fArr[i] !== ""){
				ret += $.template(tpl, $.extend({
					f: fArr[i],
					c: $.inArray(fArr[i], cArr) !== -1 
				}, data))
			}
		}

		return ret;
	},

	createOptionTpl: function(field, check, allowEmpty) {
		var tpl = '<option value="<%=f%>" <% if(c){ %> selected="true" <% } %>> <%=f%></option>';
		return this.createFieldTpl(tpl, field, check, null, allowEmpty);
	},

	createRadioTpl: function(field, check, data, allowEmpty) {
		var tpl = '<label style="padding: 0 5px;"> <input type="radio" name="<%=title%>" value="<%=f%>" <% if(c){ %>checked="true"<% } %> > <%=f%></label>';
		return this.createFieldTpl(tpl, field, check, data, allowEmpty);
	},

	createCheckboxTpl: function(field, check, data, allowEmpty) {
		var tpl = '<label> <input type="checkbox" name="<%=title%>" value="<%=f%>" <% if(c){ %>checked="true"<% } %> > <%=f%></label>';
		return this.createFieldTpl(tpl, field, check, data, allowEmpty);
	}
}