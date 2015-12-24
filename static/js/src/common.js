/**
 * common.js
 * 用于放置全局通用类及方法，全局事件等
 * IBOS
 * @author		inaki
 * @version		$Id: common.js 582 2013-06-13 09:50:56Z gzzcs $
 */
/**
 * Core
 * 命名空间注册机制
 * 继承机制
 * Event
 * 事件机制
 * Dom, Ui, Util

 * param type
 * Jquery  jquery查询得到的对象
 * Element 原生节点对象
 * Event   事件
 * Jevent  jq事件
 * String, Number, Object, Array, Function, RegExp...
 * 
 */

/**
 * 全局命名空间
 * @class Ibos
 * @static
 */
var Ibos = Ibos || {};
var L = L || {};

/**
 * 语言包入口
 * 读取语言包, 语言包定义于全局变量 L
 * 当data参数存在时，会调用模板
 * @method l
 * @for Ibos
 * @param {String} langNS 语言包命名空间
 * @param {Object} data  模板数据，当data === true 时，调用 L 对象
 */ 
Ibos.l = function(langNS, data) {
	var _langNs = langNS,
		lang;

	if(typeof L == "undefined"){
		return langNS;
	}

	lang = L;

	langNS = (langNS || "").split(".");

	for (var i = 0, ns; ns = langNS[i++];) {
		lang = lang[ns];
		if (!lang) {
			break;
		}
	}
	return data ? (data === true ? $.template(lang, L) : $.template(lang, data)) : lang || _langNs;
};

/**
 * 工具类，包括一些工具函数 
 * @class U
 * @static
 */
var U = Ibos.Util = (function(){

	var U = {
		/**
		 * 判断值是否未定义， 未定义时返回true
		 * @method isUnd
		 * @param {Any} any  
		 * @return {Boolean}
		 */
		isUnd: function(prop){
			return prop === void 0;
			//return typeof prop === "undefined";
		},

		/**
		 * 内置正则集
		 * @attribute reg
		 * @type {Object}
		 */
		reg: {
			'int': /^-?\d+$/,  
			positiveInt: /^[1-9]\d*$/,  // 正整数
			decimals: /^-?(([1-9]\d*(\.\d+))|(0\.\d+))$/, // 小数（不包含整数）
			positiveDecimals: /^(([1-9]\d*(\.\d+))|(0\.\d+))$/,  // 正小数
			url: /^(http[s]?:\/\/)?([\w-]+\.)+[\w-]+([\w-.\/?%&=]*)?$/,
			email: /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/,
			zipcode: /^\d{6}$/,
			notempty: /\S+/,
			mobile: /^1\d{10}$/,
			tel: /^(([0\+]\d{2,3}-)?(0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/,	//电话号码的函数(包括验证国内区号,国际区号,分机号),
			currency: /^-?(\d{1,3})((\,\d{3})+)?(\.\d+)?$/ 			// 货币格式(包括千分号)
		},

		/**
		 * 判断字符串是否符合某一类型正则
		 * @method regex
		 * @param  {String} str  要验证的字符串
		 * @param  {String} type 正则类型，参考 U.reg
		 * @see   U.reg
		 * @return {[type]}      [description]
		 */
		regex: function(str, type) {
			if(type && this.reg[type] && this.reg[type].test(str)){
				return true;
			}
			return false;
		},

		/**
		 * 判断值是否正整数
		 * @method isPositiveInt
		 * @param  {Number|String}  value 
		 * @return {Boolean}
		 */
		isPositiveInt: function(value) {
			return this.regex("" + value, 'positiveInt')
		},

		/**
		 * 基于属性对比两个对象是否相等
		 * @isEqualObject 
		 * @param  {Object}  a 源对象
		 * @param  {Object}  b 作为比较对象
		 * @return {Boolean}   
		 */
		isEqualObject: function(a, b){
			var ret = true;
			if(typeof a !== "object" || typeof b !== "object") {
				return false;
			}
			for(var i in a) {
				if(a.hasOwnProperty(i)){
					if(typeof a[i] === "object") {
						ret = this.isEqualObject(a[i], b[i]);
						if(!ret) {
							return false;
						}
					} else if(a[i] !== b[i]) {
						return false;
					}
				}
			}
			return ret;
		},

		/**
		 * 获取url中参数段
		 * @method getUrlParam
		 * @param  {String} [url=window.location.href] url地址
		 * @return {Object}                            参数集
		 */
		getUrlParam: function(url){
			var ret = {},
				index,
				lastIndex,
				str;

			url = url || window.location.href;
			index = url.indexOf("?") + 1;
			lastIndex = url.indexOf("#");
			lastIndex = (lastIndex == -1 ? url.length : lastIndex);

			str = url.substring(index, lastIndex);

			$.each(str.split("&"), function(index, s){
				var kv = s.split("=");
				if(kv.length > 1) {
					ret[kv[0]] = kv[1]
				}
			});

			return ret;
		},

		/**
		 * 格式文件大小，将数字转为合理格式
		 * formatFileSize(1024) => 1.00KB
		 * @method formatFileSize
		 * @param  {Number} size 		文件大小
		 * @param  {Number} [dec=0]  	小数位，即默认保留多少位小数
		 * @return {String}      		格式化的文件大小
		 */
		formatFileSize: function(size, dec){
			size = parseInt(size, 10);
			dec = dec || 0;
			if(isNaN(size)){
				return '0B';
			} else {
				var unit = ["B", "KB", "MB", "GB"];
				var i = 0;
				while(size >= 1024){
					size = size/1024;
					if(++i == 3) break;
				}
				return size.toFixed(dec) + unit[i];
			}
		},
		/**
		 * 为表单控件添加值(主要作用于text,hidden,textarea)，格式为 "a,b,c"
		 * @method addValue 
		 * @param {String|Element|Jquery} selector  选择器
		 * @param {String} value    				目标值
		 */
		addValue: function(selector, value){
			var oldVal = $(selector).val();
			$(selector).val(oldVal ? oldVal + "," + value : value);
		},
		/**
		 * 从表单控件中移除值(主要作用于text,hidden,textarea)，格式为 "a,b,c"
		 * @method removeValue
		 * @param {String|Element|Jquery} selector  选择器
		 * @param {String} value    				目标值
		 */
		removeValue: function(selector, value){
			var oldVal = $(selector).val();
			if($.trim(oldVal) !== ""){
				oldVal = ("," + oldVal + ",").replace(new RegExp("," + value + ",", "g"), ",");
				$(selector).val(oldVal.slice(1, -1));
			}
		},
		hasValue: function(selector, value){
			var oldVal = $(selector).val();
			return ("," + oldVal + ",").indexOf("," + value + ",") !== -1;
		}
	};

	/**
	 * 设置cookie
	 * @method setCookie
	 * @param  {String} name    cookie标识符
	 * @param  {String} value   cookie值，为空时删除该cookie
	 * @param  {Number} [seconds] cookie有效周期，单位为秒，当传入值小于0时，会删除该cookie。
	 * @return {[type]}         [description]
	 */
	U.setCookie = function(name, value, seconds, path, domain, secure) {
		var SECOND_PER_MONTH = 2592000,
			expires = new Date();
		if(value === '' || seconds < 0) {
			seconds = -SECOND_PER_MONTH;
		}
		seconds = this.isUnd(seconds) ? SECOND_PER_MONTH : seconds;
		path = path || Ibos.app.g('cookiePath') || "";
		domain = domain || Ibos.app.g('cookieDomain') || "";
		expires.setTime(expires.getTime() + seconds * 1000);
		document.cookie = escape((Ibos.app.g('cookiePre') || "") + name) + '=' + escape(value) + 
		(expires ? '; expires=' + expires.toGMTString() : '') +
		(path ? '; path=' + path : '') +
		(domain ? '; domain=' + domain : '') +
		(secure ? '; secure': '');
	},

	/**
	 * 获取cookie值
	 * @method getCookie
	 * @param  {String}  name                 cookie标识符
	 * @param  {Boolean} [nounescape = false] 是否不使用unescape解码
	 * @return {String}                       cookie值
	 */
	U.getCookie = function(name, nounescape) {
		name = (Ibos.app.g('cookiePre') || "") + name;
		var cookieStart = document.cookie.indexOf(name),
			cookieEnd = document.cookie.indexOf(";", cookieStart);
		if(cookieStart == -1) {
			return '';
		} else {
			var v = document.cookie.substring(cookieStart + name.length + 1, (cookieEnd > cookieStart ? cookieEnd : document.cookie.length));
			return !nounescape ? unescape(v) : v;
		}
	},

	/**
	 * 清除所有cookie值
	 * @method clearCookie
	 * @return {[type]} [description]
	 */
	U.clearCookie = function(){
		var reg = /[^ =;]+(?=\=)/g,
			keys = document.cookie.match(reg);
		if(keys) {
			for(var i = keys.length; i--;){
				document.cookie=keys[i]+'=0;expires=' + new Date(0).toUTCString();
			}
		}
	}

	/**
	 * 枚举出对象上所有可枚举的属性名
	 * @method keys
	 * @param  {Object} obj  要进行枚举的对象
	 * @return {Array}       属性名组成的数组
	 */
	U.keys = Object.keys || function(obj) {
	    obj = Object(obj)
	    var arr = []    
	    for (var a in obj) arr.push(a)
	    return arr
	};

	/**
	 * 对调对象的属性名与属性值
	 * @method invertKeys
	 * @param  {Object} obj  要进行key-value对调的对象
	 * @return {Object}      对调完成后的新对象，不指向原对象
	 */
	U.invertKeys = function(obj) {
	    obj = Object(obj)
	    var result = {}
	    for (var a in obj) result[obj[a]] = a
	    return result
	}

	/**
	 * 获取字符串长度
	 * 字母长度为0.5，中文长度为1，对url会做特殊处理
	 * @method getCharLength
	 * @param String str 要计算长度的字符串
	 * @return {Number}  经过计算的长度值
	 */
	U.getCharLength = (function() {
		var byteLength = function(str) {
			if (typeof str == "undefined") {
				return 0
			}
			
			var sChar = str.match(/[^\x00-\x80]/g);
			// 匹配出中文字符的数目并再次加上，这样子每个中文字符占位为2
			return (str.length + (!sChar ? 0 : sChar.length))
		};

		return function(str, opt) {
			opt = opt || {};
			opt.max = opt.max || 140;
			// opt.min = opt.min || 41;
			opt.surl = opt.surl || 20;
			var p = $.trim(str).length;
			if (p > 0) {
				// 下面这段正则用于匹配URL
				var result = str.match(/(http|https):\/\/[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)+([-A-Z0-9a-z\$\.\+\!\_\*\(\)\/\,\:;@&=\?~#%]*)*/gi) || [];
				var total = 0;
				for (var m = 0,
						len = result.length; m < len; m++) {
					var o = byteLength(result[m]);

					total += o <= opt.max ? opt.surl : (o - opt.max + opt.surl)

					str = str.replace(result[m], "")
				}
				return Math.ceil((total + byteLength(str)) / 2)
			} else {
				return 0
			}
		}
	})();

	/**
	 * 对HTML进行实体转义处理的函数集
	 */
	U.entity = (function(){
		var entityMap = {
		    escape: {
		      '&': '&amp;',
		      '<': '&lt;',
		      '>': '&gt;',
		      '"': '&quot;',
		      "'": "&apos;"
		    }
		}
		entityMap.unescape = U.invertKeys(entityMap.escape);
		var entityReg = {
		    escape: RegExp('[' + U.keys(entityMap.escape).join('') + ']', 'g'),
		    unescape: RegExp('(' + U.keys(entityMap.unescape).join('|') + ')', 'g')
		}
		 
		/**
		 * 将HTML特殊字符转义为实体
		 * @method entity.escape
		 * @param  {String} html html文本
		 * @return {String}      转义后的文本
		 */
		function escape(html) {
		    if (typeof html !== 'string') return ''
		    return html.replace(entityReg.escape, function(match) {
		        return entityMap.escape[match]
		    })
		}
		/**
		 * 将HTML实体转义为特殊字符
		 * @method entity.unescape
		 * @param  {String} html 转义后的html文本
		 * @return {String}      html文本
		 */
		function unescape(str) {
		    if (typeof str !== 'string') return ''
		    return str.replace(entityReg.unescape, function(match) {
		        return entityMap.unescape[match]
		    })    
		}
		return {
			escape: escape,
			unescape: unescape
		}
	})();

	/**
	 * 预读取图片
	 * @method loadImage
	 * @param  {String}   url      文件地址
	 * @param  {Function} load     读取成功回调
	 * @param  {Function} error    读取失败回调
	 * @return {[type]}            [description]
	 */
	U.loadImage = function(url, load, error){
		var img = new Image(),
			loaded = false;
		img.onload = function(){
			img.onload = img.onerr = null;
			!loaded && load && load(img);
			loaded = true;
		}
		// 加载错误
		img.onerror = function () {
			img.onload = img.onerror = null;
			error && error(img);
		};
		img.src = url;
		if(img.complete){
			loaded = true;
			load && load(img);
		}
	};

	/**
	 * 用于动态加载JS、CSS文件
	 * @method loadFile
	 * @param {DocumentElement} 文档对象
	 * @param {Object} 文件配置信息 {src|href, tag, id, *}
	 * @param {Function} 加载成功回调
	 * @return {[type]} [description]
	 */
	U.loadFile = (function () {
        var tmpList = [];
        function getItem(doc,obj){
            try{
                for(var i= 0,ci;ci=tmpList[i++];){
                    if(ci.doc === doc && ci.url == (obj.src || obj.href)){
                        return ci;
                    }
                }
            }catch(e){
                return null;
            }

        }
        return function (doc, obj, fn) {
            var item = getItem(doc,obj);
            if (item) {
                if(item.ready){
                    fn && fn();
                }else{
                    item.funs.push(fn)
                }
                return;
            }
            tmpList.push({
                doc:doc,
                url:obj.src||obj.href,
                funs:[fn]
            });
            if (!doc.body) {
                var html = [];
                for(var p in obj){
                    if(p == 'tag')continue;
                    html.push(p + '="' + obj[p] + '"')
                }
                doc.write('<' + obj.tag + ' ' + html.join(' ') + ' ></'+obj.tag+'>');
                return;
            }
            if (obj.id && doc.getElementById(obj.id)) {
                return;
            }
            var element = doc.createElement(obj.tag);
            delete obj.tag;
            for (var p in obj) {
                element.setAttribute(p, obj[p]);
            }
            element.onload = element.onreadystatechange = function () {
                if (!this.readyState || /loaded|complete/.test(this.readyState)) {
                    item = getItem(doc,obj);
                    if (item.funs.length > 0) {
                        item.ready = 1;
                        for (var fi; fi = item.funs.pop();) {
                            fi();
                        }
                    }
                    element.onload = element.onreadystatechange = null;
                }
            };
            element.onerror = function(){
                throw Error('The load '+(obj.href||obj.src)+' fails')
            };
            doc.getElementsByTagName("head")[0].appendChild(element);
        }
    })();

    U.loadCss = function(href, callback){
    	if(document.getElementById(href)){
    		return false;
    	}
    	U.loadFile(document, {
    		tag: "link",
    		rel: "stylesheet",
    		href: href,
    		id: href
    	}, callback)
    };

	/**
	 * 用于将经过Jquery 系列化的表单数组转化为对象
	 * @method serializedToObject
	 * @deprecated            使用 $.fn.serializeObject 代替
	 * @param  {Array} array  系列化后得到的数组
	 * @return {Object}       解析后的对象
	 */
	U.serializedToObject = function(serialized) {
		var data = {},
			name,
			val;

		if(serialized && serialized.length) {

			for (var i = 0; i < serialized.length; i++) {
				name = serialized[i].name;
				val = serialized[i].value;

				// 如果已经存在同名键，则将值存为数组
				if(data[name]) {
					if(!$.isArray(data[name])) {
						data[name] = [data[name]];
					}
					data[name].push(val);
				} else {
					data[name] = val;
				}
			}

		}
		return data;
	};

	/**
	 * 根据name属性获取选中的复选框
	 * @method getChecked
	 * @param  {String} name 表单控件的name
	 * @param  {Jquery} [$ctx] 上下文
	 * @return {Jquery}      复选框的jq对象数组
	 */
	U.getChecked = function(name, $ctx){
		return $("input[type='checkbox'][name='" + name + "']:checked", $ctx);
	};

	/**
	 * 根据name属性获取选中的复选框值，返回以“,”分隔的字符串
	 * @method getCheckedValue 
	 * @param  {String} name 表单控件的name
	 * @param  {Jquery} $ctx 上下文
	 * @return {String}      复选框的值字符串
	 */
	U.getCheckedValue = function(name, $context){
		var $checkeds = this.getChecked(name, $context);

		return $checkeds.map(function(){
			return this.value;
		}).get().join(",");
	}

	U.lang = Ibos.l;

	/**
	 * 获取唯一随机值
	 * @method uniqid
	 * @param  {String} [prefix]       值前缀
	 * @param  {Boolean} [more_entropy] 当此参数为true时，会加长id以增强随机性
	 * @return {String}                随机值
	 */
	U.uniqid = function(prefix, more_entropy) {
		// + 	 original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +     revised by: Kankrelune (http://www.webfaktory.info/)
		// %     note 1: Uses an internal counter (in php_js global) to avoid collision
		if (typeof prefix === 'undefined') {
			prefix = "";
		}

		var retId;
		var formatSeed = function(seed, reqWidth) {
			seed = parseInt(seed, 10).toString(16); // to hex str
			if (reqWidth < seed.length) { // so long we split
				return seed.slice(seed.length - reqWidth);
			}
			if (reqWidth > seed.length) { // so short we pad
				return Array(1 + (reqWidth - seed.length)).join('0') + seed;
			}
			return seed;
		};

		// BEGIN REDUNDANT
		// END REDUNDANT
		if (!this.uniqidSeed) { // init seed with big random int
			this.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
		}
		this.uniqidSeed++;

		retId = prefix; // start with prefix, add current milliseconds hex string
		retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
		retId += formatSeed(this.uniqidSeed, 5); // add seed hex string
		if (more_entropy) {
			// for more entropy we add a float lower to 10
			retId += (Math.random() * 10).toFixed(8).toString();
		}

		return retId;
	};

	return U;
})();

// Debug
Ibos.print = function(){ console && console.log(arguments); };
Ibos.time = function(name, time, func){
	console.time(name||"untitle");
	for(var i = 0; i < (time || 1000); i++) {
		func();
	}
	console.timeEnd(name||"untitle");
};
Ibos.disableStyle = function(){
	$.each(document.styleSheets, function(i, stylesheet){
		stylesheet.disabled = true;
	});
};

/**
 * 与应用相关的数据及操作
 * @class Ibos.app
 * @static
 */
Ibos.app = (function(){

	var app = {};
	var _pageParam = {};

	/**
	 * 设置全局参数，等效于setPageParam函数
	 * @method s
	 * @param {String} key  参数名
	 * @param {Any} value   参数值
	 * @example
	 * 		Ibos.app.s("name", "ink");
	 * 		Ibos.app.s({ "name": "ink" });
	 * @return {} 
	 */
	app.s = app.setPageParam = function(key, value){
		if(typeof key === "string") {
			_pageParam[key] = value;
		} else {
			$.extend(_pageParam, key);
		}
	};

	/**
	 * 获取全局参数，等效于getPageParam函数
	 * @method g
	 * @param {String} key       参数名
	 * @param {Any}    [defVal]  默认值，当key获取不到值时使用
	 * @example
	 * 		Ibos.app.g("name");
	 * 		// ==> "ink"
	 * 		
	 * @return {Any}             全局参数中存储的值
	 */
	app.g = app.getPageParam = function(key, defVal) {
		// G 为页面上定义的全局变量
		return typeof _pageParam[key] !== "undefined" ? 
			_pageParam[key] :
			typeof G !== "undefined" && typeof G[key] !== "undefined" ?
			G[key] :
			defVal ? defVal : null;
	};

	/**
	 * 获取路由
	 * @method url
	 * @param  {String} route   由三个子参数组成的字符： 模块/控制器/动作
	 * @param {Object} [param]  作为url参数的对象，{a: 1, b: 1}将解析为 a=1&b=1的格式
	 * @example 
	 *  	Ibos.app.url('main/default/index');
	 *  	// ==> localhost/?r=main/default/index
	 *  	Ibos.app.url('main/default/index', { op: "add" });
	 *  	// ==> localhost/?r=main/default/index&op=add
	 *  	
	 * @return {String}          Url地址
	 */
	app.url = function(route, param){
		route += "";
		if((route).split("/").length !== 3) {
			// $.error("app.url: 参数route错误");
		}else {
			param = param ? '&' + $.param(param) : '';
			return this.g("SITE_URL") + "?r=" + route + param;
		}
	};

	/**
	 * 获取静态文件夹地址，当无参数时，返回静态文件夹地址，传入相对地址时，返回最终文件地址
	 * @method getStaticUrl
	 * @param  {String} [url]  目标文件相对静态文件夹的地址
	 * @return {String}
	 */
	app.getStaticUrl = function(url){
		return this.g("STATIC_URL") + (url || "");
	};

	app.getModulePath = function(mod){
		return mod ? this.g("SITE_URL") + "system/modules/" + mod : ""
	};

	/**
	 * 获取指定模块静态文件地址
	 * @method getAssetUrl
	 * @param  {String} mod     目标模块
	 * @param  {String} [path]  具体文件地址
	 * @example
	 * 	Ibos.app.getAssetUrl("main", "/js/index.js");
	 * @return
	 */
	app.getAssetUrl = function(mod, path){
		return (this.g("mods") && this.g("mods")[mod] ?
			this.g("mods")[mod].assetUrl:
			"") + 
			(path || "");
	};

	/**
	 * 获取事件参数，即节点上data-param属性解析后的对象
	 * @method getEvtParams
	 * @param  {HTMLElement} elem  元素节点
	 * @example
	 * 		**HTML**
	 * 		<div id="a" data-param='{ "b": 1, "c": "HEHE"}'></div>
	 * 		**Script**
	 * 		app.getEvtParams(document.getElementById("a"));
	 * 		// ==> { b: 1, c: "HEHE" }
	 * 		
	 * @return {Object}            解析后的数据
	 */
	app.getEvtParams = function(elem) {
		var param = $.attr(elem, "data-param");
		return param ? $.parseJSON(param) : {};
	};

	app.assetUrl = (function () {
		var route = U.getUrlParam().r;
		if(route) {
			return app.getAssetUrl(route.substring(0, route.indexOf("/")));
		}
		return "";
	})();
	
	return app;
})();


/**
 * 全局设置，包括一些通用设置及通用插件默认配置
 */
Ibos.settings = {
	KEYCODE: {
		ENTER: 13,
		DELETE: 46,
		BACKSPACE: 8,
		UP: 38,
		DOWN: 40
	},

	imageTypes: "*.gif;*.jpg;*.jpeg;*.png;",

	dataTable: {
		// --- Data
		processing: true,
		serverSide: true,

		// --- Language
		language: $.extend({}, Ibos.l("DATATABLE"), {
			processing: '<img src="' + Ibos.app.getStaticUrl("/image/loading.gif") + '">' //"加载中..."
		}),

		// --- Pagination
		pageLength: 10,
		pagingType: "full_numbers",
		lengthMenu: [5, 10, 20 ],

		dom: "rt<'page-list-footer'lp>"
	},

	zTree: {
        data: {
            simpleData: {
                enable: true
            }
        },
        view: {
            showLine: false,
            selectedMulti: false,
            showIcon: false
        }
    }
};


/**
 * 静态资源加载方法集
 * 以路径作为依据避免 js 重复执行
 */
Ibos.statics = {
	_fileList: {},

	defaults: {
		url: "",
		type: "js",
		callback: null,
		reload: false
	},

	// css 及 js 类型在读取时会自动执行
	// css 回调没有返回值
	load: function(options){ // { url, type[js], callback, reload[false] };
		var that = this;
		var def = $.Deferred();

		if(typeof options == "string") {
			options = $.extend(true, {}, this.defaults, {
				url: options
			});
		} else {
			options = $.extend(true, {}, this.defaults, options);
		}

		if(!options.url) {
			return false;
		}

		// 如果存在缓存且不强制要求重读
		if(this._fileList[options.url] && !options.reload) {
			def.resolve(this._fileList[options.url]);
			options.callback && options.callback(this._fileList[options.url]);
		// 否则请求对应文件
		} else {
			this._load(options, function(res){
				that._fileList[options.url] = res;
				def.resolve(res);
				options.callback && options.callback(res);
			});
		}

		return def;
	},

	// 按顺序加载
	loads: function(arr){
		var that = this;
		var ret = [];
		var def = $.Deferred();

		var loadStart = function(_arr){
			this.load(_arr.shift()).done(function(res){
				ret.push(res);

				if(_arr.length) {
					loadStart.call(that, _arr);
				} else {
					def.resolve.apply(def, ret);
				}
			});
		}
		if($.isArray(arr)) {
			loadStart.call(that, arr);
		}

		return def;
	},

	_load: function(options, callback) {
		switch(options.type) {
			case "js": 
				$.getScript(options.url, callback);
				break;
			case "css":
				U.loadCss(options.url, callback);
				break;
			case "html":
				$.get(options.url, callback, "html");
				break;
			default:
				$.get(options.url, callback, options.type);
				break;
		}
	}
};


// @deprecated 
Ibos.Event = function($ctx, type, flag){
	var that = this;
	type = type || "click";
	flag = flag || type;

	$ctx = $ctx && $ctx.length ? $ctx : $(document);
	$ctx.on(type, "[data-" + flag + "]", function(){
		var evtName = $.attr(this, "data-" + flag),
			params = Ibos.app.getEvtParams(this);
		that.fire(evtName, params, $(this));
	})
	this._evts = {};
}

Ibos.Event.prototype = {
	constructor: Ibos.Event,
	// 查询是否存在指定标识的事件处理器，存在且为函数时返回true
	has: function(name){
		return name in this._evts && $.isFunction(this._evts[name]);
	},
	add: function(name, evts){
		// 若首选项为字符串，则作为模块标识符
		if(typeof name === "string"){
			this._evts[name] = evts;
		} else {
			evts = name;
			$.extend(this._evts, evts);
		}
	},
	remove: function(name){
		delete this._evts[name];
	},

	fire: function(name, params, $elem){
		if(this.has(name)){
			this._evts[name].call(this._evts.click, params, $elem);
		}
	}
}

Ibos.events = new Ibos.Event();

/**
 * 全局事件委派
 * 依赖于节点触发，节点data-action属性对应事件名
 * @class Ibos.evt
 * @todo  只作为点击事件委派
 * @static
 */
Ibos.evt = (function(){
	var _events = {}
	var methods = {
		/**
		 * 添加全局委派事件
		 * 传入节点上data-param属性及节点本身作为参数
		 * @method add
		 * @param {String|Object} [evtType=click]  当为字符串时，作为事件类型，为对象时，作为事件集
		 * @param {Object} handlers                事件集
		 * @example
		 * 	Ibos.evt.add({
		 * 		"publish": function(){ console.log("publish") }
		 * 	});
		 */
		add: function(evtType, handlers){
			if(typeof evtType !== "string") {
				handlers = evtType;
				evtType = "click";
			};
			// 如果事件类型已经存在，则加入新的事件
			if(!_events[evtType]) {
				_events[evtType] = {};
				$(document).on(evtType + ".ink", "[data-action], [data-act]", function(evt){
					var act = $.attr(this, "data-action") || $.attr(this, "data-act"),
						params = Ibos.app.getEvtParams(this);
					_events[evtType][act] && _events[evtType][act].call(this, params, this, evt);
				})
			}
			
			$.extend(_events[evtType], handlers);
		},

		/**
		 * 移除全局委派事件
		 * @method remove
		 * @param {String} handlerName     事件名
		 * @param {String} [evtType=click] 事件类型
		 * @return {} 
		 */
		remove: function(handlerName, evtType){
			evtType = evtType || "click";
			if(_events[evtType] && _events[evtType][handlerName]) {
				delete _events[evtType][handlerName];

				if($.isEmptyObject(_events[evtType])){
					delete _events[evtType];
					$(document).off(evtType + ".ink");
				}
			}
		},

		get: function(handlerName, evtType) {
			evtType = evtType || "click";
			if(_events[evtType] && _events[evtType][handlerName]) {
				return _events[evtType][handlerName]
			}
		},

		/**
		 * 触发全局委派事件
		 * @method fire
		 * @param  {String} handlerName         事件名
		 * @param  {String} [evtType="click"]   事件类型
		 * @return {Any}                        事件执行后的结果
		 */
		fire: function(handlerName, evtType) {
			var argu = Array.prototype.slice.call(arguments, 2);
			evtType = evtType || "click";
			if(_events[evtType] && _events[evtType][handlerName]) {
				return _events[evtType][handlerName].apply(null, argu)
			}
		}
	}
	return methods;
})();

/**
 * 全局核心函数集
 * @class Ibos.core
 * @static
 */
Ibos.core = {
	/**
	 * 用于继承父类
	 * @method inherits
	 * @param  {Function} _subClass   父类
	 * @param  {Function} _superClass 子类
	 * @example
	 * 		var A = function(){};
	 * 		A.prototype.a = 1;
	 * 		
	 * 		var B = function(){};
	 * 		B.prototype.b = 2;
	 * 		
	 * 		Ibos.core.inherits(B, A);
	 * 		
	 * 		var ins = new B
	 * 		ins.a; // ==> 1
	 * @return {Function}             子类
	 */
	inherits: function(_subClass, _superClass){
		var _F = function(){};
		_F.prototype = _superClass.prototype;
		_subClass.prototype = $.extend(new _F(), { _super: _superClass, constructor: _subClass }, _subClass.prototype);
		_F.prototype = null;
		return _subClass;
	}
};


(function(window, $){
	var _slice = Array.prototype.slice,
		_call = function(func){
			var args;
			if($.isFunction(func)){
				args = _slice.call(arguments, 1);
				return func.apply(this, args);
			}
		}

	/**
	 * 节点处理函数集
	 * @class Dom
	 * @static
	 */
	window.Dom = Ibos.Dom = (function(){
		var Dom = {
			/**
			 * 通过 id 获取节点，等同于document.getElementById(id);
			 * @method byId
			 * @param  {String} id     节点id
			 * @return {HTMLElment}    节点，没有时为null;
			 */
			byId: function(id){
				return document.getElementById(id) || null;
			},
			/**
			 * 获取节点或节点对应的jq对象
			 * @method getElem
			 * @param  {String|HTMLElement|Jquery} id   节点id或节点本身或对应jquery对象
			 * @param  {Boolean} toJq                   当此值为true时，返回jq对象，否则返回节点
			 * @return {HTMLElement|Jquery}             
			 */
			getElem: function(id, toJq){
				var node, isJq = false;
				if(typeof id === "string"){
					node = document.getElementById(id)
				} else {
					node = id;
					if(id && !id.nodeType) {
						isJq = true;
					}
				}
				return toJq ? (isJq ? node : $(node)) : (isJq ? node[0] : node);
			}
		}

		return Dom;
	})();

	//Plugins
	(function(){
		// 
		// data-selected
		var PseudoSelect = function($ctrl, $menu, options){
				this.$ctrl = $ctrl;
				this.$menu = $menu;
				this.options = options || {};
				this._init();
			}
		PseudoSelect.prototype = {
			constructor: PseudoSelect,
			_init: function(){
				if(!this.$menu || !this.$menu.length){
					return false;
				}
				this._initSelected();
				this._bindSelectEvent();
			},
			_initSelected: function(){
				var value = this.$ctrl.attr("data-selected");
				if(typeof value !== "undefined") {
					this.select(value, true);
				}	
			},
			_bindSelectEvent: function(){
				var that = this;
				this.$menu.on("click", "li", function(){
					var value = $.attr(this, "data-value");
					that.select(value)
				})
			},
			_setSelectedValue: function(value, text){
				var tpl = "";
				this.$ctrl.attr("data-selected", value);
				this.selectedValue = value;
				if(this.options.template) { 
					tpl = $.template(this.options.template, {text: text});
				} else {
					tpl = text;
				}
				this.$ctrl.html(tpl);
			},
			_getItemText: function($item){
				return $item.attr("data-text")||$item.text();
			},

			select: function(value, toRefresh){
				var that = this;
				if(value === this.selectedValue){
					return false;
				}
				toRefresh = (toRefresh === false) ? false : true;
				this.$menu.find("li").each(function(){
					var $item = $(this),
						itemValue = $.attr(this, "data-value"),//; || $.trim($.text(this));
						itemText;
					$(this).removeClass("active");
					if(itemValue && value === itemValue){
						$(this).addClass("active");
						if(toRefresh){
							itemText = that._getItemText($item);
							that._setSelectedValue(itemValue, itemText);
							that.$ctrl.trigger({
								type: "select",
								selected: itemValue,
								selectedItem: $item
							})
						}
					}
					if(value === ""){
						that._setSelectedValue("", "");
						that.$ctrl.trigger({
							type: "select",
							selected: "",
							selectedItem: null
						})
					}
				})
			},

			reset: function(){
				this.select("");
			}
		}

		$(function(){
			$(document).find("[data-toggle='dropdown'][data-toggle-role='select']").each(function(){
				var $ctrl = $(this),
					$menu = $ctrl.siblings(".dropdown-menu");
				$ctrl.data("select", new PseudoSelect($ctrl, $menu));
			})
		});
		
		// Simple Editor 
		/**
		 * [SimpleEditor description]
		 * @deprecated
		 * @param {[type]} $el     [description]
		 * @param {[type]} options [description]
		 */
		var SimpleEditor = function($el, options){
			this.$el = $el;
			this.options = $.extend({}, SimpleEditor.defaults, options);
			this._init();
		}
		SimpleEditor.defaults = {
			color: '',
			bold: false,
			italic: false,
			underline: false,
			onSetColor: null,
			onSetBold: null,
			onSetItalic: null,
			onSetUnderline: null
		};
		SimpleEditor.prototype = {
			_init: function(){
				this._initContainer();
				this._createBtns();
			},
			_initContainer: function(){
				var $el = this.$el,
					cls = 'editor-btnbar';
				$el.addClass(cls);
			},
			_createBtns:function(){
				this.colorBtn = this._createColorBtn();
				this.boldBtn = this._createBoldBtn();
				this.italicBtn = this._createItalicBtn();
				this.underlineBtn = this._createUnderlineBtn();
				this.$el.append(this.colorBtn, this.boldBtn, this.italicBtn, this.underlineBtn);
			},
			_createBtn: function(){
				return $('<a href="javascript:;" class="editor-btn">')
			},
			// 通常按钮比较相似，共用一个创建的方法，包括bold, italic, underline
			_createNormalBtn: function(type, clickHandler){
				var item = this._createBtn(),
					that = this;
				this.options[type] && item.addClass('active');
				if(typeof clickHandler === 'function'){
					item.on('click', clickHandler);
				}
				return item;
			},
			_createColorBtn:function(){
				var that = this,
					item = this._createBtn();
				this.options.color && item.css('background-color', '#' + this.options.color);
				//跟$.fn.colorPicker的组合
				item.colorPicker({
					mode: 'simple',
					onPick: function(hex, text){
						that.setColor(hex, text);
					}
				})
				return item;
			},
			_createBoldBtn: function(){
				var that = this,
					item = this._createNormalBtn('bold', function(){
						that.setBold(!that.options.bold)
					});
				item.css('font-weight', 700).html('B').attr('title', '粗体');
				return item;
			},
			_createItalicBtn: function(){
				var that = this,
					item = this._createNormalBtn('italic', function(){
						that.setItalic(!that.options.italic)
					})
				item.css('font-style', 'italic').html('I').attr('title', '斜体');
				return item;
			},
			_createUnderlineBtn: function(){
				var that = this,
					item = this._createNormalBtn('underline', function(){
						that.setUnderline(!that.options.underline)
					});
				item.css('text-decoration', 'underline').html('U').attr('title', '下划线');
				return item;
			},
			// 设置bold，italic,underline状态的形式相似
			_setStatus: function($el, status, callback){
				var method = status ? 'addClass' : 'removeClass',
					currentStatus = $el.hasClass('active');
				if(status !== currentStatus){
					$el[method]('active');
				}
				_call.call(this, callback, status);
			},
			setColor: function(hex, text){
				text = hex ? text : '';
				this.colorBtn.css('background-color', hex)
				.attr('title', text);
				this.options.color = hex;
				_call.call(this, this.options.onSetColor, hex, text);
			},
			setBold: function(flag){
				if(arguments.length === 0){
					flag = true
				}
				flag = !!flag;
				this._setStatus(this.boldBtn, flag, function(status){
					this.options.bold = status;
					_call.call(this, this.options.onSetBold, status);
				})
			},
			setItalic: function(flag){
				if(arguments.length === 0){
					flag = true
				}
				flag = !!flag;
				this._setStatus(this.italicBtn, flag, function(status){
					this.options.italic = status;
					_call.call(this, this.options.onSetItalic, status);
				})
			},
			setUnderline: function(flag){
				if(arguments.length === 0){
					flag = true
				}
				flag = !!flag;
				this._setStatus(this.underlineBtn, flag, function(status){
					this.options.underline = status;
					_call.call(this, this.options.onSetUnderline, status);
				})
			}
		}

		var Tab = function($context, selector, callback){
			var that = this;

			this.$context = ($context && $context.length) ? $context : $(document.body);
			this.selector = selector || "a";
			this.callback = callback;

			$context.on("click", selector, function(){
				that._tab($(this), callback);
			});
		}
		Tab.prototype = {
			constructor: Tab,
			_tab: function($elem, callback){
				var target = $elem.attr("data-target"),
					$target = $(target);
				$target.show().siblings().hide();
				_call.call(this, callback, $elem, $target);
			},
			on: function(selector){
				var $elem = this.$context.find("[data-target='" + selector + "']");
				this._tab($elem, this.callback);
			}
		};

		var commonTab = function($context){
			return new Tab($context, "a", function($ctrl){
				$ctrl.parent().addClass("active").siblings().removeClass("active");
			});
		}

		window.P = Ibos.Plugins = {
			PseudoSelect: PseudoSelect,
			SimpleEditor: SimpleEditor,
			Tab: Tab,
			commonTab: commonTab
		};
	})();

	/**
	 * UI层
	 * 主要是一些弹出组件，交互组件及宽高计算函数
	 * Dialog组件使用了插件artDialog
	 * Tip组件使用了插件jGrowl
	 * @class Ui
	 * @static
	 */
	(function(){
		var Ui = {};
		// Ui.themes = [ "3497DB", "A6C82F", "F4C73B", "EE8C0C", "E76F6F", "AD85CC", "98B2D1", "82939E"];

		(function(j){
			if(!j){ 
				//$.error("Ui.tip: 使用tip需要加载$.fn.jGrowl");
				return false;
			}
			/**
			 * 全局提示，基于jGrowl
			 * @method tip
			 * @param  {String} msg   提示的文本
			 * @param  {String} [theme=success] 主题(success|danger|warning|default|normal|info);
			 * @return {}
			 */
			Ui.tip = function(msg, theme) {
				msg = msg || "";
				if(msg.indexOf("@") == 0) {
					msg = Ibos.l(msg.substr(1)) || msg;
				}
				return $.jGrowl(msg, {theme: theme||'success'});
			};
		})($.jGrowl||top.$.jGrowl);

		(function(d){
			if(d){
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
				})
			}
		})($.artDialog||top.$.artDialog);


		/**
		 * 通用菜单类
		 * @class Menu
		 * @todo 扩展出子类AjaxMenu
		 * @todo 是否添加opiton.width跟option.height;
		 * @constructor
		 * @param {Jquery}  $menu                            作为菜单的节点
		 * @param {Object}  [options]                        配置项
		 *     @param {String}  [options.id]                 标识符，作为获取实例的依据
		 *     @param {Object}  [options.position]           定位方位，使用jqui的position方法, 参数一致
		 *     @param {String}  [options.content]            初始化时的内容
		 *     @param {Number}  [options.autoHide]           自动隐藏的延迟时间数，默认不自动隐藏
		 *     @param {Boolean} [options.clickToHide=false]  点击菜单以外的地方时隐藏
		 *     @_param {Number} [options.zIndex = 1001]      菜单z-index值
		 * @return {Menu}                                    菜单实例
		 */
		Ui.Menu = (function(){
			var menuid = 0;
			var instance = {};

			var Menu = function(menu, options){
				var me = this;
				this.$menu = Dom.getElem(menu, true);
				var opt = this.options = $.extend({}, Menu.defaults, options);

				// 添加新实例的引用
				if(opt.id){
					if(instance[opt.id]){
						return instance[opt.id];
					} else {
						instance[opt.id] = this;
						this.id = opt.id;
					}
				// 没有设置 id 时，使用自动menuid
				} else {
					this.id = "menu" + menuid++;
					instance[this.id] = this;	
				}

				this.$menu.css("position", "absolute").hide();
				this.showing = false;
				this.zIndex(opt.zIndex);

				// 悬停时中止自动隐藏
				if(opt.autoHide) {
					this.$menu.on({
						"mouseenter": function(){
							me._stopDelay("hide");
						},
						"mouseleave": function(){
							me.hide(me.options.autoHide);
						}
					})
				}

				// 有设置option.content属性时，初始化内容
				if(!U.isUnd(opt.content) && (typeof opt.content === "string" || opt.content.nodeType === 1)) {
					this.$menu.html(opt.content);
				}

				if(opt.clickToHide) {
					$(document).on("mousedown", function(evt){
						if(me.$menu[0] !== evt.target && !$.contains(me.$menu[0], evt.target)) {
							me.hide();
						}
					})
				}

				opt.addClass && this.$menu.addClass(opt.addClass)
			}
			Menu.defaults = {
				animate: false,
				zIndex: 1000
			}
			Menu._access = function(id, callback){
				if(U.isUnd(id)){
					for(var i in instance) {
						if(instance.hasOwnProperty(i)) {
							callback.call(instance[i])
						}
					}
					return instance;
				} else {
					if(id in instance) {
						callback.call(instance[id]);
						return instance[id];
					}
					return null;
				}
			}
			/**
			 * 显示指定菜单
			 * @show
			 * @static
			 * @param  {String} id 要显示菜单的id，为空时显示所有菜单
			 * @return {}
			 */
			Menu.show = function(id){
				this._access(id, function(){
					this.show();
				})
			}
			/**
			 * 隐藏指定菜单
			 * @hide
			 * @static
			 * @param  {String} id 要隐藏菜单的id，为空时隐藏所有菜单
			 * @return {}    
			 */
			Menu.hide = function(id){
				this._access(id, function(){
					this.hide();
				})
			}
			/**
			 * 获取指定id的菜单实例
			 * @param  {String} id  菜单实例的id
			 * @return {Menu}       菜单实例
			 */
			Menu.getIns = function(id){
				return id ? instance[id] : instance
			}
			Menu.prototype = {
				constructor: Menu,

				/**
				 * 定位菜单，直接使用了jqUi的position函数
				 * @method position
				 * @chainable
				 * @param  {Object} [opt] 定位配置项，详见jqUi position函数
				 * @return {Menu}       菜单实例
				 */
				position: function(opt){
					this.$menu.position($.extend({
						of: document.body
					}, opt));
					return this;
				},

				/**
				 * 设置菜单z-index值
				 * @method zIndex
				 * @chainable
				 * @param  {Number|String} z z-index值
				 * @return {Menu}       菜单实例
				 */
				zIndex: function(z){
					this.$menu.css("z-index", z);
					return this;
				},
				
				/**
				 * 显示菜单
				 * @method show
				 * @chainable
				 * @param  {Number} [delay] 延时显示
				 * @return {Menu}           菜单实例
				 */
				show: function(delay){
					var that = this,
						opt = this.options;
					if(this.showing){
						return this;
					}

					// 有设置option.content属性时，初始化内容
					// 如果options.content是一个函数，则在beforeshow执行
					if(!U.isUnd(opt.content) && typeof this.options.content === "function"){
						this.$menu.html(this.options.content.call(this) || "");
					}

					this.showing = true;
					// 设置了animate属性时，给予渐显过渡
					if(opt.animate) {
						this.$menu.fadeTo(0, 0).show().trigger("show")
						.fadeTo(200, 1);
						opt.show && opt.show.call(this);
					} else {
						this.$menu.show().trigger("show");
						opt.show && opt.show.call(this);
					}
					// 定位需要在$menu出现在页面之后才执行，否则会有定位误差
					this.position(opt.position);

					// 自动隐藏
					if(opt.autoHide) {
						setTimeout(function(){
							that.hide();
						}, opt.autoHide)
					}
					return this;
				},

				/**
				 * 隐藏菜单
				 * @method hide
				 * @chainable
				 * @param  {Number} [delay] 延时隐藏
				 * @return {Menu}           菜单实例
				 */
				hide: function(delay){
					this.showing = false;
					if(this.options.animate) {
						this.$menu.fadeOut(200).trigger("hide");
						this.options.hide && this.options.hide.call(this);
					} else {
						this.$menu.hide().trigger("hide");
						this.options.hide && this.options.hide.call(this);
					}
					return this;
				},

				/**
				 * 设置菜单内容
				 * @method setContent
				 * @chainable
				 * @param  {String|HTMLElement|Jquery} [content] 内容
				 * @return {Menu}           菜单实例
				 */
				setContent: function(content){
					this.$menu.html(content);
					this.position(this.options.position);
					return this;
				}

				// destory: function(){
				// 	delete this.constructor.instance[this.id];
				// }
			}

			return Menu;
		})();

		/**
		 * 通用弹出菜单类
		 * @class PopMenu
		 * @constructor
		 * @extends {Menu}
		 * @param {Jquery} $ctrl        作为触发器的节点
		 * @param {Jquery} $menu        作为菜单的节点
		 * @param {Object} [options]    配置项，详见Menu类
		 *     @param {String} [options.trigger]  触发方式，目前支持hover、click
		 * @return {Menu}               菜单实例
		 */
		Ui.PopMenu = function($ctrl, $menu, options){
			if(!$ctrl || !$ctrl.length) {
				return false
			}
			if(options.trigger === "click") {
				options.hideDelay = options.hideDelay ? options.hideDelay : 0;
				options.showDelay = options.showDelay ? options.showDelay : 0;
			}
			this._super.call(this, $menu, $.extend(true, {}, Ui.PopMenu.defaults, options));
			this.$ctrl = $ctrl;

			var that = this;
			var timer;

			if(!this.options.position.of){
				this.options.position.of = this.$ctrl;
			}

			var _show = function(){
				clearTimeout(timer)
				timer = setTimeout(function(){
					that.show();
				}, that.options.showDelay)
			};
			var _hide = function(e){
				clearTimeout(timer);
				// 当离开的瞬间重新进入菜单或菜单开关时，不隐藏
				if(that.options.trigger === "hover") {
					if(e.toElement === $ctrl[0] 
						|| e.toElement === $menu[0] 
						|| $.contains($ctrl[0], e.toElement)
						|| $.contains($menu[0], e.toElement)
					){
						return false;
					}
				}
				timer = setTimeout(function(){
					that.hide();
				}, that.options.hideDelay);
			}

			if(this.options.trigger === "hover") {
				this.$ctrl.on({
					"mouseenter.popmenu": _show,
					"mouseleave.popmenu": _hide
				});
				this.$menu.on({
					"mouseenter.popmenu": function(){
						clearTimeout(timer);
					},
					"mouseleave.popmenu": _hide
				});
			} else if(this.options.trigger === "click") {
				this.$ctrl.on("click.popmenu", function(){
					that.showing ? _hide() : _show();
				})
			}
		};
		Ibos.core.inherits(Ui.PopMenu, Ui.Menu);
		Ui.PopMenu.defaults = {
			position: {
				at: "left bottom",
				my: "left top"
			},
			trigger: "hover", // hover, click
			showDelay: 500,
			hideDelay: 500
		};

		$.extend(Ui, {
			modal: {
				show: function(options){
					var opt = $.extend({
						backgroundColor: '#FFF',
						opacity: '.7',
						zIndex: '2000'
					}, options)
					if(!this.$modal || !this.$modal.length){
						this.$modal = $("<div style='position:fixed; top:0; left:0; width:100%; height:100%; overflow:hidden'></div>").css("z-index", opt.zIndex).hide().appendTo(document.body);
						$("<div style='height:100%;'></div>").css(opt).appendTo(this.$modal);

						this.$modal.fadeIn(200)
						.on("mousedown", function(evt){ 
							evt.stopPropagation();
							evt.preventDefault();
						});
					}
				},
				
				hide: function(){
					var $modal = this.$modal;
					if($modal){
						$modal.fadeOut(200, function(){
							$modal.remove();
							Ui.modal.$modal = null
						});
					}
				},

				toggle: function(){
					if(this.$modal){
						this.hide();
					}else{
						this.show();
					}
				}
			},

			/**
			 * 聚集于指定上下文环境中的第一个指定控件上
			 * @method focusForm 
			 * @param  {Jquery}  [$context = $(document.body)] 			 上下文环境
			 * @param  {String}  [selector = 'input, textarea, select']  起作用的选择器
			 * @param  {Boolean} [cls = true]      						 是否为父节点添加has-focus类
			 * @return {[type]}          [description]
			 */
			focusForm: function($context, selector, cls){
				var $first;

				selector = selector || "input, textarea, select";
				cls = cls === false ? false : true;

				$first = $(selector, $context).eq(0).focus();

				cls && $first.parent().addClass("has-focus");
			},

			/**
			 * popover, 基于bootstrap $.fn.popover, 样式不同
			 */
			popover: function($elem, options) {
				$elem.popover($.extend({
					template: '<div class="popover popover-w"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
					placement: "bottom"
				}, options));
			},

			/**
			 * 滚动至指定元素的头部
			 * @method scrollYTo
			 * @for  Ui
			 * @param {HTMLElement|Jquery} elem 指定元素
			 * @param {Number} offset           位置偏移量调整
			 * @param {Function} [callback]     滚动完成后的回调函数
			 * @return {}
			 */
			scrollYTo: function(elem, offset, callback, time){
				var $body = (window.opera) ? (document.compatMode === "CSS1Compat" ? $('html') : $('body')) : $('html,body'),
					$elem = Dom.getElem(elem, true),
					top = $elem.offset().top + (offset || 0);
				time = time || 500;
				$body.stop().animate({ scrollTop: top }, time, callback);
			},
			/**
			 * 页面回到顶部
			 * @method scrollToTop
			 * @return {} 
			 */
			scrollToTop: function (callback) {
				this.scrollYTo($(document.body), 0, callback);
			},

			selectOne: function($elem, tag){
				tag = tag || $elem[0].nodeName;
				$elem.addClass("active").siblings(tag).removeClass("active");
			},

			/**
			 * 动态计算节点高度，让其相对某祖先节点撑开高度
			 * @method fillHeight
			 * @example
			 * @todo 完善
			 * <div  id='a' style='height: 100px;'>
			 * 		<div id='b'></div>
			 * </div>
			 * fillHeight('b', 'a') ==> <div id='b' style='height: 100px;'></div>
			 * @param  {Jquery} $elem   元素
			 * @param  {Jquery} $target 相对元素
			 * @return {}         
			 */
			fillHeight: function($elem, $target){
				var height,
					$child,
					$parent;
				$elem = $child = Dom.getElem($elem, true);
				$target = Dom.getElem($target, true);
				$parent = $elem.parent();
				height = $target.height();

				while($parent && $parent.length && ($.isWindow($target[0]) ? $parent[0].nodeName.toLowerCase() !== "html" : $parent[0] !== $target[0])) {
					height -= $parent.outerHeight() - $child.outerHeight();
					$child = $parent;
					$parent = $parent.parent();
				}
				// if(height > $elem.outerHeight()) {
					// 这里要考虑padding 跟 border
					$elem.css("min-height", height);
				// }
			},

			/**
			 * 新消息标题闪动提醒
			 * @method blinkTitle 
			 * @param  {String} text     提醒内容
			 * @param  {Number} [interval=1000] 闪动的时间间隔
			 * @param  {Number} [timeout]  超时
			 * @return {}
			 */
			blinkTitle: (function(){
				var timer,
					oldTitle = document.title,
					reset = function(){
						clearInterval(timer);
						document.title = oldTitle;
					}

				return function(text, interval, timeout){
					var _in = false;
					interval = interval || 1000;
					if(text === false) {
						reset();
						return false;
					}

					timer = setInterval(function(){
						document.title = _in ? oldTitle : text;
						_in = !_in
					}, interval);

					if(timeout) {
						setTimeout(function() {
							reset();
						}, timeout);
					}
				}
			})(),

			/**
			 * 设置复选框联动
			 * @method linkCheckbox
			 * @param  {String|Element|Jquery} main 主复选框
			 * @param  {String|Element|Jquery} sub  从复选框
			 * @return 
			 */
			linkCheckbox: function(main, sub){
				var $mainCheckbox = $(main),
					$subCheckboxs;

				if($mainCheckbox && $mainCheckbox.length) {
					$subCheckboxs = $(sub);
					// 当主复选框状态变化时，其它复选框跟随其状态变化
					$mainCheckbox.on("change", function(){
						var isChecked = this.checked;
						$subCheckboxs.each(function(){
							var labelIns;
							this.checked = isChecked;
							// 如果复选框是Label实例（即复选框），则调用实例刷新方法
							labelIns = $(this).data("label");
							labelIns && labelIns.refresh();
						})
					});

					$subCheckboxs.on("change", function(){
						if(!this.checked){
							var labelIns;
							$mainCheckbox.prop("checked", false);
							labelIns = $mainCheckbox.data("label");
							labelIns && labelIns.refresh();
						}
					});
				}
			}
		})
		/**
		 * 弹出提示框
		 * @method showPrompt
		 * @param  {String} content 提示内容
		 * @param  {Number} time    显示时间，秒数
		 * @return {ArtDialog}      弹窗实例
		 */
		Ui.showPrompt = function(content, time){
			var dialog = Ui.dialog || $.artDialog;

			return dialog({
				id: "prompt",
				padding: 0,
				title: false,
				cancel: false,
				zIndex: 9999,
				skin: "prompt-dialog"
			})
			.content("<div class='prompt'>" + (content||"") + "</div>")
			.time(time||1.5);
		}
		/**
		 * 弹出积分提示框
		 * 由于依赖于cookie，在实际中基本不会手动调用
		 * @method showCreditPrompt
		 * @return {}
		 */
		Ui.showCreditPrompt = function() {
			var remind = U.getCookie('creditremind').split('D'),
				base = U.getCookie('creditbase').split('D'),
				rule =  decodeURI(U.getCookie('creditrule', true)).replace(String.fromCharCode(9), ' '),
				info = [],
				promptTpl = '',
				names,
				arr;
			var _reset = function(){
				U.setCookie('creditremind', '');
				U.setCookie('creditbase', '');
				U.setCookie('creditrule', '');
			}
			var _createTpl = function(rule, entrys, values){
				var tpl = "";
				// 由于成长项最多只能定义5项
				for(var i = 1; i <= 5; i++){
					if(values[i] != '0' && entrys[i]){
						tpl += '<span>' + entrys[i] + ' <strong> ' + (values[i] >= 0 ? '+' : '') + values[i] + '</strong></span> ';
					}
				}
				if(tpl) {
					tpl = '<div class="point-tip"><div class="point-tip-title mbs">' + rule + '</div><div class="point-tip-body">' +
						tpl +  '</div></div>';
				}
				return tpl;
			}

			if(!Ibos.app.g('uid') || remind.length < 2 || remind[6] !== Ibos.app.g('uid')) {
				_reset();
				return;
			}

			names = Ibos.app.g('creditRemind').split(',');
			for(var i = 0; i < names.length; i++){
				arr = names[i].split('|');
				info[arr[0]] = arr[1];
			}

			promptTpl = _createTpl(rule, info, remind);
			if(promptTpl) {
				Ui.showPrompt(promptTpl)
			}
			_reset();
		}

		Ui.submenu = function(menu){
			var $menu = $(menu);

			$('[data-node="submenuToggle"]', $menu).each(function(){
				var $elem = $(this);
					$menu = $elem.find('[data-node="submenu"]');

				if($menu.length){
					var menuIns = new Ui.PopMenu($elem, $menu, {
						position: {
							at: "right top",
							my: "left top"
						},
						hideDelay: 0,
						showDelay: 0
					});
					// 阻止点击冒泡导致父菜单被关闭
					$menu.on("click", function(evt){
						evt.stopPropagation();
					})
				}
			});
		}

		Ui.cookieTip = function(msg, theme) {
			U.setCookie('globalRemind', encodeURI(msg));
			U.setCookie('globalRemindType', theme);
		};

		// 窗口提示
		Ui.Notification = {
			NAME: 'noti',

			LOGO: '/image/logo_pic.png',

			// cookie: {
			// 	NAME: 'allow_desktop_notify',
			// 	EXPIRES: 7776000
			// },

			// isEnabled: function() {
			// 	return U.getCookie(this.cookie.NAME) == "1";
			// },

			// enable: function() {
			// 	U.setCookie(this.cookie.NAME, "1", this.cookie.EXPIRES);
			// },

			// disable: function() {
			// 	U.setCookie(this.cookie.NAME, "");
			// },

			show: function(title, content, icon) {
				var that = this;
				var _show;
				icon = icon || Ibos.app.getStaticUrl(this.LOGO);

		    // 旧版本接口
				if (window.webkitNotifications) {
					var wnf = window.webkitNotifications;
					_show = function() {
						var notification = wnf.createNotification(icon, title, content);
						notification.replaceId = that.NAME;
						notification.onclick = function(){
							window.focus();
							window.open(Ibos.app.url("message/notify/index"));
						};
						notification.ondisplay = function(evt){
							setTimeout(function(){
								evt.currentTarget.cancel();
							}, 1e4);
						};
						notification.show();
						return notification;
					};

		      // 查看接口权限，未允许时，申请权限
		      if (wnf.checkPermission() !== 0) {
		        wnf.requestPermission(_show);
					// 已允许时直接通知
		      } else {
		      	_show();
		      }
		    // 标准接口
		    } else if("Notification" in window){
		    	_show = function() {
		    		var notification = new Notification(title, {
		  		    "icon": icon,
		  		    "body": content,
		    		});
		    		notification.onclick = function() {
		    			window.focus();
		    			window.open(Ibos.app.url("message/notify/index"));
		    		};
		    		notification.onshow = function() {
		    			setTimeout(function(){
		    				notification.close();
		    			}, 1e4);
		    		};
		    		return notification;
		    	};

					// 已允许时直接通知
		    	if(Notification.permission === "granted") {
						_show();
		    	} else if(Notification.permission === "default") {
		    		Notification.requestPermission(function(permission) {
				    	// 判断是否有权限
				    	if (Notification.permission === "granted") {
				  	    _show();
				    	}
		    		});
		    	}
		    }
			}
		};

		window.Ui = $.extend(window.Ui, Ui);
	})();

	$.extend(Ibos, {
		/**
		 * 日期处理函数集
		 * @class Ibos.date
		 * @static
		 */
		date: {
			/**
			 * 日期格式化..
			 * @method format
			 * @param  {Date||number} [date]                   日期对象或时间戳，默认为当前时间
			 * @param  {String} [format='yyyy-mm-dd'] 		   日期格式，有效值 yyyy, yy, m, mm, d, dd, h, hh, H, HH, i, ii, s, ss
			 * @param  {Boolean} [utc]                         是否使用utc时间
			 * @return {String}        						   格式化后的时间
			 */
			format: function(date, format, utc){
				date = date == null ? new Date() : typeof date === "number" ? new Date(date) : date;
				format = format || "yyyy-mm-dd",
				getFullYear = utc ? "getUTCFullYear" : "getFullYear";
				getMonth = utc ? "getUTCMonth" : "getMonth";
				getDate = utc ? "getUTCDate": "getDate";
				getHours = utc ? "getUTCHours": "getHours";
				getMinutes = utc ? "getUTCMinutes": "getMinutes";
				getSeconds = utc ? "getUTCSeconds": "getSeconds";

				var val = {
					yy:  ("" + date[getFullYear]()).substring(2),
					yyyy: date[getFullYear](),
					m: date[getMonth]() + 1,
					d: date[getDate](),
					h: date[getHours](),
					i: date[getMinutes](),
					s: date[getSeconds]()
				};
	            val.H  = (val.h%12==0? 12 : val.h%12);
	            val.HH = (val.H < 10 ? '0' : '') + val.H;
				val.hh = (val.h < 10 ? '0' : '') + val.h;
				val.ii = (val.i < 10 ? '0' : '') + val.i;
				val.ss = (val.s < 10 ? '0' : '') + val.s;
				val.dd = (val.d < 10 ? '0' : '') + val.d;
				val.mm = (val.m < 10 ? '0' : '') + val.m;

				for(var i in val){
					if(val.hasOwnProperty(i)) {
						var reg = new RegExp('\\b' + i + '\\b', 'g');
						format = format.replace(reg, val[i])
					}
				}
				return format
			},
			/**
			 * 日期计算
			 * @method calc
			 * @param  {Date} date   基本时间对象
			 * @param  {Number} days 日期数
			 * @example
			 * 		var d = new Date;
			 * 		Ibos.date.calc(d, 2); // 两天后的时间对象
			 * @return {Date}
			 */
			calc: function(date, days) {
				var DAY_MICROSECOND = 86400000;
				var date = date || new Date;
				days = days || 0;
				return new Date(date.setTime(date.getTime() + days * DAY_MICROSECOND))
			},
			/**
			 * 数字转为时间格式
			 * @method numberToTime
			 * @param  {Number} num 数字
			 * @example
			 * 		Ibos.date.numberToTime(3.5);
			 * 		// ==> 3:30
			 * @return {String}     时间格式的字符串
			 */
			numberToTime: function(num){
				num = parseFloat(num, 10);
				// if(num < 0 || num > 24) {
				// 	return false;
				// }
				var intBit = parseInt(num, 10),
					decBit = Math.abs(num - intBit);

				var hour = intBit > 9 || intBit < 0 ? intBit : "0" + intBit;
				var minute = parseInt(decBit * 60, 10);
				minute = minute > 9 || minute < 0 ? minute : "0" + minute;
				return hour + ":" + minute;
			}
		},

		/**
		 * 字符串处理函数集
		 * @class Ibos.string
		 * @static
		 */
		string: {
			/**
			 * 移除字符串中的html标签
			 * @method removeHtmlTag
			 * @param  {String} str 目标字符串
			 * @return {String}     结果字符串
			 */
			removeHtmlTag: function(str){
				return str.replace(/<\/?[^>]*>/g,'')
			},
			/**
			 * 从utf16编码转化为utf8编码
			 * @method utf16to8
			 * @param  {String} str utf16编码格式的字符串
			 * @return {String}     utf8编码的字符串
			 */
			utf16to8: function(str) {
			    var out, i, len, c;  
			    out = "";  
			    len = str.length;  
			    for(i = 0; i < len; i++) {  
			    c = str.charCodeAt(i);  
			    if ((c >= 0x0001) && (c <= 0x007F)) {  
			        out += str.charAt(i);  
			    } else if (c > 0x07FF) {  
			        out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));  
			        out += String.fromCharCode(0x80 | ((c >>  6) & 0x3F));  
			        out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));  
			    } else {  
			        out += String.fromCharCode(0xC0 | ((c >>  6) & 0x1F));  
			        out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));  
			    }  
			    }  
			    return out;  
			},

			/**
			 * 当字符串过长时，使用省略号（或其它字符）替换部分文字
			 * @method ellipsis
			 * @param  {String} str           目标字符串
			 * @param  {Number} [limit=20]    最大长度，字串超过此长度时执行替换
			 * @param  {Number} [pos]         替换的起始位置，默认从字符串末替换
			 * @param  {String} [ell="..."]   用于替换的字符串
			 * @return {String}       
			 */
			ellipsis: function(str, limit, pos, ell){
				ell = ell || "...";
				str = "" + str;
				limit = limit || 20;

				if(str.length > limit) {
					if(typeof pos === "undefined"){
						str = str.substr(0, limit) + ell;
					} else{
						pos = +pos > limit ? limit : +pos;
						str = str.substr(0, pos) + ell + str.substr(str.length - limit + pos);
					}
				}

				return str;
			},

			/**
			 * 将字符串转化为整数字符串
			 * @method toPositiveInt 
			 * @param  {String} string 源字符串
			 * @return {String}
			 */
			toPositiveInt: function(string){
				string = "" + string;
				return string.replace(/\D+/g, "").replace(/^0+/g, "") || "0";
			},

			/**
			 * 转化为小数字符串
			 * @method toPositiveDecimals
			 * @param  {String|Number}  value
			 * @param  {Number}         [digits]   小数点后保留几位
			 * @return {String}
			 */
			toPositiveDecimals: function(value, digits){
				var ret = 0;

				if(value) {
					if(U.regex(value, "positiveDecimals")) {
						ret = +value;
					} else {
						ret = Math.abs(parseFloat(value));
						if(isNaN(ret)){
							ret = 0;
						}
					}
				}

				return digits == null ? ret : ret.toFixed(digits);
			},

			/**
			 * 增加千分逗号
			 * @method addCommas
			 * @param {String|Number} value
			 * @return {String}
			 */
			addCommas: function(value){
				value = value + "";

				if(U.regex(value, "currency")) {
					return value;
				}

				if(U.regex(value, "int") || U.regex(value, "decimals")) {
					value = value.replace(/(\d{1,3})(?=(?:\d{3})+(?!\d))/g, '$1,');
					return value;
				}

				return value;
			}
		},

		number: {
			addCommas: function(num) {
				if (isNaN(num)) {
					return '-';
				}
				num = (num + '').split('.');
				return num[0].replace(/(\d{1,3})(?=(?:\d{3})+(?!\d))/g, '$1,') + (num.length > 1 ? ('.' + num[1]) : '');
			}
		},

		/**
		 * 本地存储函数
		 * 由于不考虑IE8 以下的浏览器，所以直接使用localStorage
		 * 其实JSON也可以直接用原生的。。
		 * @class Ibos.local
		 * @static
		 */
		local: {
			/**
			 * 设置本地存储
			 * @method set
			 * @param {String} key   索引名
			 * @param {Any} value    值
			 */
			set: function(key, value){
				return window.localStorage.setItem(key, $.toJSON(value));
			},
			/**
			 * 获取本地存储
			 * @method get
			 * @param  {String} key   索引名
			 * @return {Any} value    值
			 */
			get: function(key){
				var value = window.localStorage.getItem(key);
				return value === null ? null : $.parseJSON(value);
			},
			/**
			 * 移除本地存储
			 * @method remove
			 * @param  {String} key   索引名
			 * @return {}
			 */
			remove: function(key){
				return window.localStorage.removeItem(key);
			},
			/**
			 * 清空本地存储
			 * @method clear
			 * @return {}
			 */
			clear: function(){
				return window.localStorage.clear();
			}
		},

		/**
		 * 拼音匹配汉字，正确时返回true，支持首字母
		 * @method matchSpell
		 * @for Ibos
		 * @param  {String} term 要匹配的拼音
		 * @param  {String} text 与之匹配的汉字
		 * @example
		 * matchSpell("bsxc", "博思协创") // => true;
		 * matchSpell("bosi", "博思协创") // => true;
		 * matchSpell("ink", "博思协创") // => false;
		 * @return {Boolean}      true为匹配，false反之
		 */
		matchSpell: function(term, text){
			var textChars,
				termChars,
				match = false,
				matchStart = 0,
				pinyinText

			if(!term || !text) {
				return false;
			}

			/* 从字面上直接匹配 */
			if(text.toLowerCase().indexOf(term.toLowerCase()) !== -1 ) {
				return true;
			} 

			/* 全拼匹配 */
			pinyinText = pinyinEngine.toPinyin(text, false, ",")
			pinyinText = typeof pinyinText === 'string' ? pinyinText : pinyinText[0];

			if(pinyinText.toLowerCase().indexOf(term.toLowerCase()) !== -1){
				return true;
			}

			/* 首字母匹配 */
			
			// 匹配单文字首字符
			var matchFirstLetter = function(char, text){
				var py = pinyinEngine.toPinyin(text);
				for(var n = 0; n < py.length; n++) {
					if(py[n].charAt(0) === char){
						return true;
					}
				}
				return false;
			}

			// 从指定索引开始逐字符匹配
			var matchFrom = function(index){
				var usText = text.substr(index).split(""),
					usTerms = term.substr(1).split("");

				for(var cr = 0; cr < usTerms.length; cr++) {
					if(!usText[cr] || !matchFirstLetter(usTerms[cr], usText[cr])) {
						return false;
					}
				}

				return true;
			}


			var chars = text.split("");
			for(var i = 0; i < chars.length; i++) {
				// 在目标字符中找到开始位置后，进入逐字符匹配，成功则返回true
				// 失败则尝试找下一个开端，直到完全失败
				if(matchFirstLetter(term.charAt(0), chars[i])) {
					if(matchFrom(i + 1)){
						return true;
					};
				}
			}

			return false;
		},

		/**
		 * 消息@功能
		 * @method atwho
		 * @for Ibos
		 * @param  {[type]} $ctx    [description]
		 * @param  {[type]} options [description]
		 * @return {[type]}         [description]
		 */
		atwho: function($ctx, options){
			options = options||{};
			opts = $.extend({
				at: "@",
				data: Ibos.data && Ibos.data.get("user") || [],
				tpl: "<li data-value='${name}'><img src='${imgUrl}' height='20' width='20'/> ${name} </li>",
				callbacks: {
					filter: function(query, data, search_key){
						var item, _i, _len, _results;
						_results = [];
						if(query === ""){
							return false
						}
						for (_i = 0, _len = data.length; _i < _len; _i++) {
							item = data[_i];
							if (~item[search_key].toLowerCase().indexOf(query)) {
								_results.push(item);
							} 
							else if(Ibos.matchSpell(query, item[search_key])){
								_results.push(item);
							}
						}
						return _results
					},
					remote_filter: function(query, callback){
						var item, _i, _len, _results;
						_results = [];
						if(query === "" && options.url){
							$.ajax({
								url: options.url,
								type: "get",
								dataType: "json",
								success: function(res){
									callback(res);
								}
							})
						}
					},
					sorter: function(query, items, search_key) {
						var item, _i, _len, _results;
						if (!query) {
							return items;
						}
						_results = [];
						for (_i = 0, _len = items.length; _i < _len; _i++) {
							item = items[_i];
							item.atwho_order = item[search_key].toLowerCase().indexOf(query);
							if (item.atwho_order > -1) {
								_results.push(item);
							}
							else if(Ibos.matchSpell(query, item[search_key])){
								item.atwho_order = 10000;
								_results.push(item);
							}
						}
						return _results.sort(function(a, b) {
							return a.atwho_order - b.atwho_order;
						});
					}
				}
			}, options);
			try {
				$ctx.atwho(opts);
			} catch(e){
				// 未引入 jquery.atwho.js
			}
		},

		/**
		 * 功能引导，intro.js的入口
		 * 直接退出引导当作完成引导处理
		 * @method intro
		 * @for Ibos
		 * @param  {Array} steps       引导步骤
		 * @param  {Function} complete 引导完成时的回调
		 * @return {IntroJs}          引导类IntroJs实例
		 */
		intro: function(steps, complete){

			var ito;
			if(typeof introJs === "undefined") {
				$.error("Ibos.intro: 未引入intro.js");
			}
			ito = introJs();
			ito.setOptions({
				steps: steps,
				showStepNumbers: false,
				tooltipClass: 'ibosintro',
				prevLabel: "",
				nextLabel: "",
				skipLabel: "",
				doneLabel: ""
			});
			if(complete) {
				ito.oncomplete(complete)
				ito.onexit(complete);
			}
			ito.start();
			return ito;
		}
	});
	
	/**
	 * 用于检查某个区域内的表单内容是否有修改
	 * 当有修改未保存离开页面时给予提示
	 * 当前思路是通过检查该区域内的表单控件是否有发生 change 事件
	 * 但这种做法有不少局限性，期望有更好的解决方案
	 * @method checkFormChange
	 * @param  {String|Element|Jquery} context   修改的上下文	
	 * @param  {String} tip                      离开页面的提示
	 * @param  {String} selectors                选择器，用于规定作用的节点
	 * @return
	 */
	Ibos.checkFormChange = function(context, tip, selectors){
		$context = context ? $(context) : $(document.body);
		tip = tip || "离开页面后，未保存的数据将丢失";
		selectors = selectors || "input, textarea, select";

		$context.on("change", selectors, function(){
			$context.data("formchange", true);
		})
		.on("formchange", function(){
			$context.data("formchange", true);
		})
		.on("submit ignoreformchange", function(){
			$context.data("formchange", false);
		});
		// Fix: IE 8 下点击 a 标签会先触发 beforeupload
		$(document).on("click", "a[href='javascript:;'], a[href='javascript:void(0);']", function(e){
			e.preventDefault();
		});
		$(window).on("beforeunload", function(e){
			if($context.data("formchange")){
				return tip;
			}
		})
	}
})(window, window.jQuery);


(function(window, $) {
	/**
	 * 搜索功能配置
	 * 为搜索框绑定事件
	 * @method search
	 * @for  jQuery 
	 * @chainable
	 * @param  {Function} onsearch 搜索时回调函数
	 * @param  {Function} onconf   配置时回调函数
	 * @return {Jquery}            
	 */
	 $.fn.search = function(onsearch, onconf){
	 	return this.each(function(){
		 	var $input = $(this),
		 		$searchBtn = $input.next(),
		 		$cont = $input.closest(".search"),
		 		toconf = false;
		 	// 如果配置了高级设置事件，则出现设置按钮
		 	if(onconf) {
		 		toconf = true;
		 		$cont.addClass("search-config");
		 	}
		 	var search = function(){
		 		var val = $input.val();
		 		// if(val) {
		 			// 如果定义了搜索事件，则执行
		 			// 否则，获取到最近的一个表单并提交表单
		 			if(onsearch) {
		 				onsearch(val, $input);
		 			} else {
		 				$input[0].form.submit();
		 			}
		 		// }
		 	}
		 	var config = function() {
		 		var val = $input.val();
		 		onconf && onconf(val, $input);
		 	}

		 	// 点击事件
		 	$input.on({
		 		"keydown": function(evt){
		 			if(evt.which === 13) {
		 				search();
		 			}
		 		},
		 		"focus": function(){
		 			toconf = false;
		 			$cont.addClass("has-focus");
		 		},
		 		"blur": function(){
		 			var val = $(this).val();
		 			if($.trim(val) === "") {
		 				toconf = true;
		 				setTimeout(function(){
		 					$cont.removeClass("has-focus");
		 				}, 200)
		 			}
		 		}
		 	});
		 	 
		 	// 按键事件
		 	$searchBtn.on("click", function(evt){
		 		if(toconf) {
		 			config();
		 		} else {
		 			search();
		 		}
		 	})
	 	});
	 }
})(window, window.jQuery);

// $.fn.blink 闪动提示
(function(window, $){
	/**
	 * 闪动提示
	 * @method  blink
	 * @for  jQuery
	 * @param {Number} times 闪动次数
	 * @param {Number} speed 闪动相隔毫秒数
	 * @return {Jquery} jq对象
	 */

	$.blink = function(el, times, speed){
		times = times||3;
		speed = speed||100;
		var	count = 0,
			isBlink = false,
			blinkBackground = '#FCC',
			blinkBorder = '#F99',
			timer;
		timer = setInterval(function(){
			if(isBlink){
				$.style(el, 'background-color', '');
				$.style(el, 'border-color', '');
				isBlink = false;
			}else{
				$.style(el, 'background-color', blinkBackground);
				$.style(el, 'border-color', blinkBorder);
				isBlink = true;
			}
			count++;
			count >= times*2 && clearInterval(timer);
		}, speed);
		el.focus();
	}
	$.fn.blink = function(times, speed){
		return this.each(function(){
			$.blink(this, times, speed);
		})
	}

})(window, window.jQuery);

/**
 * 显示模态窗口
 * @method showModal
 * @for jQuery
 * @param  {Object} [options] 配置项，一组css属性
 * @return {Jquery}         
 */
$.fn.showModal = function(options){
	var opt = $.extend($.fn.showModal.defaults, options);
	return this.each(function(){
		var $elem = $(this),
			$modal = $elem.data("modal"),
			posVal;
		if(!$modal){
			posVal = $elem.css("position");
			if(posVal !== "fixed" && posVal !== "absolute" && posVal !== "relative") {
				$elem.css("position", "relative");
			}
			$modal = $('<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #FFF; opacity: .7; filter: Alpha(opacity=70)"></div>').hide();;
			$modal.css(opt).appendTo($elem).fadeIn(200).on("mousedown", function(e){
				e.stopPropagation();
				e.preventDefault();
			});
			$elem.data("modal", $modal);
		}
	});
};
$.fn.showModal.defaults = {
	backgroundColor: "#FFF",
	opacity: ".7",
	zIndex: 2000
}
/**
 * 隐藏模态窗口
 * @method hideModal
 * @for jQuery
 * @return {Jquery}         
 */
$.fn.hideModal = function(){
	return this.each(function(){
		var $elem = $(this),
			$modal = $elem.data("modal");
		if($modal) {
			$modal.fadeOut(200, function(){
				$modal.remove();
			});
			$elem.removeData("modal");
		}
	});
};

/**
 * 显示或关闭等待中提示
 * @method waiting
 * @for  jQuery
 * @param  {String|False} [content]        提示文字或HTML，值为false时关闭等待提示
 * @param  {String}       [mode="normal"]  提示类型， normal、small、mini分别对应三种大小的图标
 * @param  {Boolean}      [lock=false] 	   是否锁定
 * @return {Jquery}         
 */
$.fn.waiting = function(content, mode, lock){
	if(content === false) {
		return this.stopWaiting();
	}
	content = content || "";
	mode = mode || "normal";
	var tpl = '<div><img src="<%= Ibos.app.getStaticUrl(path) %>" width="<%= size %>" height="<%= size %>" />' +
		'<span style="margin-left: 10px; font-size: <%= fontSize %>px;"><%= content %></span>' + 
		'</div>';

	var modeSet = {
		normal: $.template(tpl, { path: "/image/loading.gif", size: 60, fontSize: 16, content: content }),
		small: $.template(tpl, { path: "/image/loading_small.gif", size: 24, fontSize: 14, content: content }),
		mini: $.template(tpl, { path: "/image/loading_mini.gif", size: 16, fontSize: 12, content: content })
	}
	content = modeSet[mode] || content || "Loading...";

	return this.each(function(){
		var $ctx = $(this),
			$wait = $ctx.data("waiting");

		if(lock) {
			$ctx.showModal();
		}

		if($wait) {
			$wait.html(content).position({ of: $ctx });
		} else {
			$wait = $("<div class='waiting' style='position: absolute; z-index: 2001; width: 300px; text-align: center; overflow: hidden;'></div>")
			.html(content)
			.appendTo($ctx.parent())
			.position({ of: $ctx });

			$ctx.data("waiting", $wait);
		}
	});
}

// @deprecated 统一使用 $.fn.waiting 入口
$.fn.waitingC = function(content, lock){
	return this.waiting(content, 'normal', lock);
}

// @deprecated 统一使用 $.fn.waiting 入口
$.fn.stopWaiting = function(){
	return this.each(function(){
		var $ctx = $(this),
			$wait = $ctx.data("waiting");

		$ctx.hideModal();

		if($wait) {
			$wait.remove();
			$ctx.removeData("waiting");
		}
	});
}

/**
 * 阻止选择html文本
 * 此函数用于阻止浏览器默认的文本选择状态，调用的对象将不能划选文字
 * @method	noSelect
 * @for  jQuery
 * @param   {Boolean}	[g=true] true为阻止选择文本，false为允许选择文本
 * @return  {Jquery}
 */
$.fn.noSelect = function(g) {   //阻止选择html文本
	return (g == null ? true : g) ? this.each(function() {
		//for IE及safari，支持onselectstart方法
		if ($.browser.msie || $.browser.safari) {
			$(this).bind("selectstart", function() {
				return false
			});
		}
		else if ($.browser.mozilla) {  //for FF，支持user-select样式
			$(this).css("MozUserSelect", "none");
			$("body").trigger("focus")
		}else if($.browser.chrome){ //for chrome
			$(this).css("WebkitUserSelect", "none");
			$("body").trigger("focus")
		}else {
			$.browser.opera ? $(this).bind("mousedown", function() {
				return false
			}) : $(this).attr("unselectable", "on");
		}
	}) : this.each(function() {
		if ($.browser.msie || $.browser.safari) {
			$(this).unbind("selectstart")
		}else if ($.browser.mozilla) {
			$(this).css("MozUserSelect", "inherit");
		}else if ($.browser.chrome) {
			$(this).css("WebkitUserSelect", "inherit");
		}else {
			$.browser.opera ? $(this).unbind("mousedown") : $(this).removeAttr("unselectable", "on");
		}
	})
};

// Ajax Popover
$.fn.ajaxPopover = function(url, reload, options){
	return this.each(function(){
		var $current = $(this),
			defaultContent = "loading...",
			isLoad = false,
			opts = $.extend({
				content: defaultContent,
				placement: "bottom",
				html: true
			}, options);
			
		$current.popover(opts)
		.on("show", function(){
			var popoverData = $current.data("popover");
			if(!isLoad){
				$.when(
					$.get(url, function(result){
						popoverData.options.content = result
						isLoad = true;
						// show函数执行时，会回调本函数体
						// 当reload为true时，AJAX完成后会重置已读状态 isLoad = false;
						// 使内容每次都从服务器上获取
						popoverData.show();
					})
				).then(function(){
					if(reload){
						popoverData.options.content = defaultContent;
						isLoad = false;
					}
				});
			}
		})

	})
};

$.fn.bindEvents = function(events, delegate){
	events = events || {};
	delegate = delegate === false ? false : true;
	return this.each(function(){
		var evt, 
			arr,
			evtType,
			selector;

		for(evt in events) {
			var spaceIndex = evt.indexOf(" ");
			evtType = evt.substr(0, spaceIndex);
			selector = $.trim(evt.substr(spaceIndex));

			if(delegate) {
				$(this).on(evtType, selector, events[evt]);
			} else {
				$(selector, this).on(evtType, events[evt]);
			}
		}
	});
}

// AffixTo
$.fn.affixTo = function(of) {
	var $win = $(window), $doc = $(document),
		$of = $(of);

	return this.each(function() {
		var $elem = $(this);
		$elem.affix({
			offset: {
				top: function() {
					var winHeight = $win.height(),
							ofHeight = $of.outerHeight(),
							ofTop = $of.offset().top,
							elemHeight = $elem.outerHeight();
					// 如果元素的高大于相对元素的高或元素没有超出屏幕，则不定位
					return (elemHeight > ofHeight || elemHeight < winHeight - ofTop) ? $doc.outerHeight() : (elemHeight - winHeight + ofTop);

				},
				bottom: function() {
					var ofHeight = $of.outerHeight(),
							elemHeight = $elem.outerHeight(),
							docHeight = $doc.height(),
							ofTop = $of.offset().top,
							ofBottom = ofTop + ofHeight;

					// 如果元素的高大于相对元素的高或元素没有超出屏幕，则不定位
					return (elemHeight >= ofHeight || elemHeight < $win.height() - ofTop) ? null : docHeight - ofBottom;
				}
			}
		});
		$(window).on("scroll", function() {
			if ($elem.hasClass("affix")) {
				$elem.css("top", $win.height() - $elem.outerHeight());
			}
		});
	});
};

/**
 * 插入文本至 input 或 textarea 框光标处
 * @method insertText
 * @for  jQuery
 * @param {String} text
 * @return Jquery
 */
$.fn.insertText = function(text) {
	this.each(function() {
		if (this.tagName !== 'INPUT' && this.tagName !== 'TEXTAREA') {
			return;
		}
		if (document.selection) {
			this.focus();
			setTimeout(function(){
				var cr = document.selection.createRange();
				cr.text = text;
				cr.collapse();
				cr.select();
			}, 0)
		} else if (this.selectionStart || this.selectionStart == '0') {
			var start = this.selectionStart, end = this.selectionEnd;
			this.value = this.value.substring(0, start) + text + this.value.substring(end, this.value.length);
			this.selectionStart = this.selectionEnd = start + text.length;
		} else {
			this.value += text;
		}
	});
	return this;
};

// @deprecated 使用 Ibos.cmList 或 dataTable 替换
Ibos.List = (function(){
	var getItemId = (function(){
		var itemid = 0;
		return function() {
			return itemid++;
		}
	})();

	var List = function($elem, tpl, options){
		var that = this;
		this.$elem = Dom.getElem($elem, true);
		this.tpl = tpl;
		this.options = $.extend({}, List.defaults, options);
		this._dataCache = [];
		this._itemCache = {};
	}
	List.defaults = {
		idField: "id" // 定义作为id的字段，默认为'id'
	}

	List.prototype = {
		constructor: List,
		// 判断某Id是否在cache中，返回在数组中的下标，不在时返回-1
		indexOf: function(id){
			for(var i = 0; i < this._dataCache.length; i++) {
				if(this._dataCache[i][this.options.idField] == id) {
					return i;
				}
			}
			return -1;
		},
		hasId: function(id){
			return this.indexOf(id) !== -1;
		},
		// 用于补全数据和验证数据，给没有idField值的数据添加唯一符，对有idField的数据做验证
		_assignId: function(data){
			var idField = this.options.idField,
				id;
			// 当设置了options.idField时， 该字段会作为数据的唯一标识符
			if(!data[idField]){
				id = getItemId();
				if(this.hasId(id)) {
					return this._assignId(data)
				}
				data[idField] = id;
			} else {
				if(this.hasId(data[idField])) {
					$.error("Ibos.List._assignId: " + id + "重复");
				}
			}
			return data;
		},
		// 合并时，不能修改idField，所以清理掉对应idField属性
		_cleanData: function(data){
			delete data[this.options.idField];
			return data;
		},
		//
		_createItem: function(data){
			return $.tmpl(this.tpl, data);
		},
		// 传入一组数据以添加节点，prepend为true时，插入在容器的首个子节点
		addItem: function(data, prepend){
			var ret = $(),
				handler = prepend ? "prependTo" : "appendTo",
				$item,
				d;
			data = $.isArray(data) ? data : [data];
			for(var i = 0; i < data.length; i++){

				d = this._assignId(data[i]);
				$item = this._createItem(d);
				
				if($item && $item.length) {
					this._itemCache[d[this.options.idField]] = $item;
					this._addItemData(d);
					ret = ret.add($item[handler](this.$elem));
					this.$elem.trigger("list.add", {item: $item, data: d});
				}
			}
			return ret;
		},
		// 更新节点
		updateItem: function(id, data){
			var ret,
				newData, 
				$oldItem, 
				$newItem;
			if(id == null) {
				return null;
			}
			if(typeof id === "string" || typeof id === "number") {
				// 更新数据，成功时返回新的数据，失败时返回false;
				newData = this._updateItemData(id, data);
				if(newData) {
					$oldItem = this.getItem(id);
					$newItem = this._createItem(newData);
					$oldItem.replaceWith($newItem);
					// 更新节点存储的指向
					this._itemCache[id] = $newItem
				}
				this.$elem.trigger("list.update", { item: $newItem, data: newData })
				ret = $newItem;
			} else {
				data = $.isArray(id) ? id : [id];
				ret = $();
				for(var i = 0; i < data.length; i++) {
					ret = ret.add(this.updateItem(data[i][this.options.idField], data[i]));
				}
			}
			return ret;
		},

		removeItem: function(id){
			var ids = ("" + id).split(",");
			for(var i = 0; i < ids.length; i++) {
				// 成功删除数据时会返回true
				if(this._removeItemData(ids[i])) {
					this._itemCache[ids[i]].remove();
					delete this._itemCache[ids[i]];
					this.$elem.trigger("list.remove");
				}
			}
		},
		// 获取所有列表节点, 参数为空时，返回所有节点组成的jq对象
		getItem: function(id){
			var ret;
			if(U.isUnd(id)) {
				ret = $();
				for(var i in this._itemCache) {
					if(this._itemCache.hasOwnProperty(i)) {
						ret = ret.add(this._itemCache[i])
					}
				}
			} else {
				ret = this._itemCache[id];
			}
			return ret;
		},

		_addItemData: function(data){
			this._dataCache.push(data);
		},

		_updateItemData: function(id, newData){
			// 获取到旧的数据
			var oldData = this.getItemData(id);
			if(oldData) {
				// 将新数据清理后，合并至旧数据
				return $.extend(oldData, this._cleanData(newData));
			}
			return false;
		},

		_removeItemData: function(id){
			var index = this.indexOf(id);
			if(index !== -1) {
				this._dataCache.splice(index, 1);
				return true;
			}
			return false;
		},

		// 获取节点对应的数据，参数为空时返回全部数据
		getItemData: function(id){
			var index;
			if(U.isUnd(id)) {
				return this._dataCache;
			} else {
				index = this.indexOf(id);
				if(index !== -1){
					return this._dataCache[index];
				}
				return null;
			}
		},

		clear: function(){
			for(var i = 0; i < this._dataCache.length; i++) {
				this.removeItem(this._dataCache[i--][this.options.idField]);
			}
		}
	}

	return List;
})();

// @deprecated 使用 Ibos.cmList 或 dataTable 替换
Ibos.OrderList = function($elem, tpl, options){
	options = {} || options;
	options.reorder = options.reorder === false ? false : true;
	this._super.call(this, $elem, tpl, options);
	this.index = this.options.startIndex = (this.options.startIndex || 1);
}
Ibos.core.inherits(Ibos.OrderList, Ibos.List);

$.extend(Ibos.OrderList.prototype, {
	addItem: function(data, prepend) {
		data = $.extend({
			index: this.index
		}, data);
		this.index++;
		return this._super.prototype.addItem.call(this, data, prepend);
	},

	removeItem: function(ids){
		var ret = this._super.prototype.removeItem.apply(this, arguments);
		if(this.options.reorder) {
			this.reorder();
		}
		return ret;
	},

	reorder: function(){
		var datas = this.getItemData();
		this.index = this.options.startIndex;
		for(var i = 0; i < datas.length; i++) {
			this.getItem(datas[i][this.options.idField]).find("[data-item-index]").attr("data-item-index", this.index).text(this.index++);
		}
	}
})


// 通用列表类，提供列表的增删除改功能
Ibos.CmList = function(container, opts){
	this.$container = $(container);
	this.opts = $.extend({}, this.constructor.defaults, opts);
}
Ibos.CmList.prototype = {
	constructor: Ibos.CmList,

	getItems: function(){
		return this.$container.children();
	},

	getItem: function(id){
		return this.getItems().filter("["  + this.opts.idAttr + "='" + id + "']");
	},

	getItemCount: function(){
		return this.getItems().length;
	},

	addItem: function(data, prependTo){
		var _this = this;
		var $item = $.tmpl(this.opts.tpl, data);

		if(this.opts.animate) {
			$item.hide()[prependTo ? "prependTo" : "appendTo"](this.$container).fadeIn(200, function(){
				$(_this).trigger("itemadd", { item: $item });
			});
		} else {
			$item[prependTo ? "prependTo" : "appendTo"](this.$container);
			$(_this).trigger("itemadd", { item: $item });
		}

		return $item;
	},

	updateItem: function(id, data){
		var _this = this;
		var $item = $.tmpl(this.opts.tpl, data).replaceAll(this.getItem(id));
		
		if(this.opts.animate) {
			$item.fadeOut(0, function() {
				$item.fadeIn(200);
			});
		}

		$(_this).trigger("itemupdate", { item: $item });		

		return $item;
	},

	removeItem: function(id){
		var _this = this;
		var $item = this.getItem(id);
		var _remove = function(){
			$(_this).trigger("beforeitemremove", { item: $item });	
			$item.remove();
			$(_this).trigger("itemremove", { item: $item });
		}
		if(this.opts.animate) {
			$item.fadeOut(200, function() {
				_remove();
			});
		} else {
			_remove();
		}

		return $item;
	}
}
Ibos.CmList.defaults = {
	tpl: "",
	idAttr: "data-id",
	animate: false
}
