(function(P){
	var Editor = function($elem, settings){
		var me = this;

		this.$elem = $elem;
		this.settings = settings || [];
		this.buttons = {};
		this.values = {};

		var createButton = function(settings) {
			var $btn = $('<a href="javascript:;" class="editor-btn editor-btn-' + settings.name + '">' + settings.text + '</a>');
			me.values[settings.name] = settings.value;
			me.buttons[settings.name] = $btn;
			settings.init && settings.init.call(me, $btn, settings);
			return $btn;
		}

		if(this.settings.length) {
			for(var i = 0; i < this.settings.length; i++){
				var $btn = createButton(this.settings[i]);
				$elem.append($btn);
			}
		}
	}

	Editor.prototype = {
		constructor: Editor,

		getSetting: function(name){
			for(var i = 0; i < this.settings.length; i++) {
				if(this.settings[i].name === name) {
					return this.settings[i];
				}
			}
			return null;
		},

		getElem: function(name) {
			return this.buttons[name];
		},

		updateValue: function(name, value){
			if(name in this.values){
				this.values[name] = value;
			}
		},

		getValue: function(name){
			if(typeof name!== "undefined") {
				return this.values[name] || null;
			}
			return this.values;
		},

		setValue: function(name, value){
			var _setValue = function(name, value){
				var setting = this.getSetting(name);
				if(setting){
					// 当配置了onchange属性时，执行该函数时，并只有返回值为true时更新值.
					if(setting.onchange){
						if(!setting.onchange.call(this, name, value)){
							return false;
						}
					}
					// 否则直接更新值
					this.updateValue(name, value);
					this.$elem.trigger("editor.change", {
						name: name,
						value: value
					})
				}
			}
			// 
			if(typeof name === "string") {
				return _setValue.call(this, name, value)
			// 假设为键值对
			} else {
				for(var i in name) {
					_setValue.call(this, i, name[i])
				}
			}
		}
	}

	// Ibos.Plugins.Editor = Editor;

	var defaultInit = function($btn, setting) {
		var me = this,
			value;
		if(!!setting.value) {
			$btn.addClass("active");
		}

		$btn.on("click", function(){
			value = me.getValue(setting.name);
			me.setValue(setting.name, !value);
		})
	};

	var defaultChange = function(name, value) {
		var $btn = this.getElem(name);
		if(!!value) {
			$btn.addClass("active")
		} else {
			$btn.removeClass("active")
		}
		return true;
	}

	var pickerInit = function($btn, setting) {
		var me = this;
		if(setting.value) {
			$btn.css("background-color", "#" + setting.value);
		}
		$btn.colorPicker({
			onPick: function(hex){
				me.setValue(setting.name, hex)
			}
		});
	}

	var pickerChange = function(name, value){
		var $btn = this.getElem(name);
		if(value != null) {
			$btn.css("background-color", value);
			return true;
		}
	}

	var buttonSettings = [
		{
			name: "color",
			text: "",
			init: pickerInit,
			onchange: pickerChange
		},
		{ 
			name: "bold",
			text: "B",
			init: defaultInit,
			onchange: defaultChange
		},
		{ 
			name: "italic",
			text: "I",
			init: defaultInit,
			onchange: defaultChange
		},
		{ 
			name: "underline",
			text: "U",
			init: defaultInit,
			onchange: defaultChange
		}
	]
	var miniEditor = function($elem){
		return new Editor($elem, buttonSettings);
	}

	P.Editor = Editor;
	P.miniEditor = miniEditor;

})((typeof Ibos !== "undefined" && Ibos.Plugins) || window);