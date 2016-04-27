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

		var _filter = function(data, matcher) {
            if (!matcher || typeof matcher !== "function") {
                return data;
            }
            // ECMAScript5
            if(Array.prototype.filter){
                return data.filter(function(d) {
                    if (isFunction(d) || d == null) return false;
                    return matcher(d);
                });
            }
            // for IE8 etc
            var results = [];
            for (var i = 0, len = data.length; i < len; i++) {
                if (isFunction(data[i]) || data[i] == null) continue;
                matcher(data[i]) && results.push(data[i]);
            }
            return results;
        }

        var isFunction = function(fn) {
            return Object.prototype.toString.call(fn) === "[object Function]";
        }

        Ibos.data = {
            filter: function(matcher) {
                return _filter(allData, matcher);
            },

            get: function() {
                var argu = arguments,
                    len = argu.length,
                    matcher,
                    ret = [];

                if (!argu.length || argu[0] == null) return allData;
                if (isFunction(argu[len - 1])) {
                    matcher = Array.prototype.pop.call(argu);
                    len--;
                }

                if (!len && matcher) {
                    return this.filter(matcher);
                } else {
                    for (var i = 0; i < len; i++) {
                        var _ret = this.filter(function(data) {
                            return data && (data.type === argu[i] || data.iconSkin === argu[i]);
                        })
                        ret = ret.concat(_ret);
                    }
                    return _filter(ret, matcher);
                }
            },

            getItem: function( /*id1, id2...*/ ) {
                var results = [],
                    argu = arguments,
                    i, len = argu.length;

                // ECMAScript5
                if(Array.prototype.filter){
                    return allData.filter(function(data) {
                        for (i = 0; i < len; i++) {
                            if( data.id === argu[i] ) return true;
                        }
                    });
                }
                // for IE8 etc
                for(i = 0; i < len; i++) {
                    results = results.concat(this.filter(function(data){
                        return data.id === argu[i];
                    }))
                }

                return results;
            },

            getUser: function(id) {
                var ret,
                    deptInfo,
                    posInfo;
                if (id) {
                    if (id.charAt(0) !== "u") {
                        id = "u_" + id;
                    }
                    ret = this.getItem(id)[0] || {};
                    deptInfo = this.getItem(ret.department)[0];
                    posInfo = this.getItem(ret.position)[0];

                    ret.department = deptInfo ? deptInfo.name : "";
                    ret.position = posInfo ? posInfo.name : "";
                }
                return ret;
            },

            includes: function(ins) {
                return this.getItem.apply(this, ins);
            }
        }
	} catch(e) {
		throw new Error("(Ibos.data): 模板解析错误" )
	}
})();