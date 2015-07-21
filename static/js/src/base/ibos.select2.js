/**
 * 基于jquery.select2扩展的select插件，基本使用请参考select2相关文档
 * 默认是多选模式，并提供了input模式下的初始化方法，对应的数据格式是{ id: 1, text: "Hello" } 
 * 这里的参数只对扩展的部分作介绍 
 * filter、includes、excludes、query四个参数是互斥的，理论只能有其一个参数
 * @method ibosSelect
 * @param option.filter
 * @param {Function} option.filter   用于过滤源数据的函数
 * @param {Array} 	 option.includes 用于过滤源数据的数据，有效数据的id组
 * @param {Array} 	 option.excludes 用于过滤源数据的数据，无效数据的id组
 * @param {Boolean}  option.pinyin   启用拼音搜索，需要pinyinEngine组件	
 * @return {jQuery} 
 */
$.fn.ibosSelect = (function(){
	var _process = function(datum, collection, filter){
		var group, attr;
		datum = datum[0];
		if (datum.children) {
			group = {};
			for (attr in datum) {
				if (datum.hasOwnProperty(attr)) group[attr] = datum[attr];
			}
			group.children = [];
			$(datum.children).each2(function(i, childDatum) {
				_process(childDatum, group.children, filter);
			});
			if (group.children.length) {
				collection.push(group);
			}
		} else {
			if(filter && !filter(datum)) {
				return false;
			}
			collection.push(datum);				
		}
	}
	// 使用带有filter过滤源数据的query函数，其实质就是在query函数执行之前，用filter函数先过滤一次数据
	var _queryWithFilter = function(query, filter){
		var t = query.term, filtered = { results: [] }, data = [];

		$(this.data).each2(function(i, datum) {
			_process(datum, data, filter);
		});

		if (t === "") {
			query.callback({ results: data });
			return;
		}

		$(data).each2(function(i, datum) {
			_process(datum, filtered.results, function(d){
				return query.matcher(t, d.text + "");
			})
		});

		query.callback(filtered);
	}
	// 根据ID从data数组中获取对应的文本， 主要用于val设置
	var _getTextById = function(id, data){
		// debugger;
		var ret;
		for(var i = 0; i < data.length; i++){
			if(data[i].children){
				ret = _getTextById(id, data[i].children);
				if(typeof ret !== "undefined"){
					break;
				}
			} else {
				if(data[i].id + "" === id) {
					ret = data[i].text;
					break;
				}
			}
		}
		return ret;
	}

	var defaults = {
		multiple: true,
		pinyin: true,
		formatResultCssClass: function(data){
			return data.cls;
		},
		formatNoMatches: function(){ return U.lang("S2.NO_MATCHES"); },
		formatSelectionTooBig: function (limit) { return U.lang("S2.SELECTION_TO_BIG", { count: limit}); },
        formatSearching: function () { return U.lang("S2.SEARCHING"); },
        formatInputTooShort: function (input, min) { return U.lang("S2.INPUT_TO_SHORT", { count: min - input.length}); },
        formatLoadMore: function (pageNumber) { return U.lang("S2.LOADING_MORE"); },

		initSelection: function(elem, callback){
			var ins = elem.data("select2"),
				data = ins.opts.data,
				results;

			if(ins.opts.multiple) {
				results = [];
				$.each(elem.val().split(','), function(index, val){
		            results.push({id: val, text: _getTextById(val, data)});
				})
			} else {
				results = {
					id: elem.val(),
					text: _getTextById(elem.val(), data)
				}
			}

	        callback(results);
		}
	}
	var select2 = function(option){
		if(typeof option !== "string") {
			option = $.extend({}, defaults, option);

			// 注意: filter | query | includes | excludes 四个属性是互斥的
			// filter基于query, 而includes、excludes基于filter
			// 优先度 includes > excludes > filter > query
			
			// includes是一个数组，指定源数据中有效数据的ID值，将过滤ID不在此数组中的数据
			if(option.includes && $.isArray(option.includes)){

				option.filter = function(datum){
					return $.inArray(datum.id, option.includes) !== -1;
				}

			// includes是一个数组，指定源数据中无效数据的ID值，将过滤ID在此数组中的数据
			} else if(option.excludes && $.isArray(option.excludes)) {

				option.filter = function(datum){
					return $.inArray(datum.id, option.excludes) === -1;
				}

			}

			// 当有filter属性时，将使用自定义的query方法替代原来的query方法，filter用于从源数据层面上过滤不需要出现的数据
			if(option.filter){
				option.query = function(query) {
					_queryWithFilter(query, option.filter);
				}
			}
			// 使用pinyin搜索引擎
			if(option.pinyin) {
				var _customMatcher = option.matcher;
				option.matcher = function(term){
					if(term === ""){
						return true;
					}
					return Ibos.matchSpell.apply(this, arguments) && 
					(_customMatcher ? _customMatcher.apply(this, arguments) : true);
				}
			}
			
			// 使用 select 元素时，要去掉一部分默认项
			if($(this).is("select")) {
				delete option.multiple;
				delete option.initSelection;
			}
			return $.fn.select2.call(this, option)
		}

		return $.fn.select2.apply(this, arguments)
	}

	return select2;
})();