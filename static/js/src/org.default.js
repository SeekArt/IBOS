//@Debug
var Ibos = Ibos || {};
(function() {
	try {
		var allData = [
			// company: [
				{{company}}
			// ],
			// department: [
				{{department}}
			// ],
			// position: [
				{{position}}
			// ],
			// user: [
				{{users}}
			// ],
			// positioncategory:[
				{{positioncategory}}
			// ],
			// role:[
				{{role}}
			// ]
		];

		var _filter = function(data, matcher){
			var results = [];
			if(!matcher || typeof matcher !== "function") {
				return data;
			}
			for (var i = 0, len = data.length; i < len; i++) {
				if( Object.prototype.toString.call(data[i]) === "[object Function]" || data[i] == null){
					continue;
				}
				if(matcher(data[i])){
					results.push(data[i]);
				}
			}
			return results;
		}

		Ibos.data = {
			filter: function(matcher) {
				return _filter(allData, matcher);
			},

			get: function() {
				var argu = arguments,
					matcher,
					ret = [];
				if(!argu.length || argu[0] == null) {
					return allData;
				}
				if(typeof argu[argu.length - 1] == "function") {
					matcher = argu[argu.length - 1];
					Array.prototype.pop.call(argu);
				}
				if(!argu.length && matcher) {
					return this.filter(matcher);
				} else {
					for(var i = 0; i < argu.length; i++) {
						var _ret = this.filter(function(data){
							return data && (data.type === argu[i] || data.iconSkin === argu[i]);
						})
						ret =  ret.concat(_ret);
					}
					return _filter(ret, matcher);
				}
			},

			getItem: function(/*id1, id2...*/) {
				var results = [],
					argu = arguments;

				for(var i = 0, len = argu.length; i < len; i++) {
					results = results.concat(this.filter(function(data){
						return data.id === argu[i];
					}))
				}

				return results;
			},
			getUser: function(id){
				var ret,
					deptInfo,
					posInfo;
				if(id) {
					if(id.charAt(0) !== "u") {
						id = "u_" + id;
					}
					ret = this.getItem(id)[0] || {};
					deptInfo = this.getItem(ret.department)[0];
					posInfo = this.getItem(ret.position)[0];
					ret = $.extend({}, ret, {
						department: deptInfo ? deptInfo.name : "",
						position: posInfo ? posInfo.name: ""
					});
				}
				return ret;
			},
			
			includes: function(ins){
				return this.getItem.apply(this, ins);
			}
		}
	} catch(e) {
		throw new Error("(Ibos.data): 模板解析错误" )
	}
})();