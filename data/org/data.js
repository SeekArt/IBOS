(function(win, $, Ibos) {
    'use strict';
    // if Ibos.data is not exists
    // throw the error
    try {
        if (Ibos.data.user && Ibos.data.department && Ibos.data.role && Ibos.data.position && Ibos.data.positioncategory) {
            var allData = $.extend({}, Ibos.data);
            // release memory
            delete Ibos.data.user;
            delete Ibos.data.department;
            delete Ibos.data.role;
            delete Ibos.data.position;
            delete Ibos.data.positioncategory;
        }

        // eg: Ibos.data.User = { 'u_1': { id: 'u_1', name: 'admin' } }
        var getRelatedUids, dataFetch, getType, makeName, getValues, getKeys, getText, _filter;

        getRelatedUids = function(type, id, callback) {
            var url, param = {};
            switch (type) {
                case 'department':
                    url = 'main/api/orgRelatedDept';
                    param.deptids = id;
                    break;
                case 'role':
                    url = 'main/api/orgRelatedRole';
                    param.roleids = id;
                    break;
                case 'position':
                    url = 'main/api/orgRelatedPosition';
                    param.positionids = id;
                    break;
                default:
                    break;
            }

            $.ajax({
                url: Ibos.app.url(url),
                type: 'POST',
                data: param,
                dataType: 'json',
                async: false,
                success: callback
            });
        };

        dataFetch = function(ids) {
            var i = 0,
                len = ids.length,
                type, _id,
                ret = {};

            while (i < len) {
                _id = ids[i++];
                type = getType(_id);
                !(type in ret) && (ret[type] = []);
                ret[type].push(_id);
            }

            return ret;
        };

        getType = function(id) {
            var _first = id && id.charAt(0),
                type;

            switch (_first) {
                case 'u':
                    type = 'user';
                    break;
                case 'c':
                case 'd':
                    type = 'department';
                    break;
                case 'r':
                    type = 'role';
                    break;
                case 'p':
                case 'f':
                    type = 'position';
                    break;
                default:
                    break;
            }

            return type || 'user';
        };

        makeName = function(type, id) {
            return "Ibos.related." + type + '.' + id;
        };

        getValues = function(type, ids) {
            var ret = {},
                key;

            if (ids) {
                if (!$.isArray(ids)) {
                    ids = [ids];
                }
                var i = 0,
                    id,
                    len = ids.length;

                while (i < len) {
                    id = ids[i++];
                    ret[id] = $.extend({}, allData[type][id]);
                }
            } else {
                ret = $.extend(true, {}, allData[type]);
            }

            return ret;
        };

        getKeys = function(obj) {
            if (Object.keys) {
                return Object.keys(obj);
            }

            var key, ret = [];
            for (key in obj) {
                obj.hasOwnProperty(key) && ret.push(key);
            }
            return ret;
        };

        getText = function(id) {
            if (!id) {
                return false;
            }
            var type = getType(id);

            return (allData[type][id] && allData[type][id]['text']) || '';
        };

        _filter = function(datas, matcher, limit) {
            if (!matcher || !$.isFunction(matcher)) {
                return datas;
            }

            var i, key, data, item, counter = 0,
                ret = {};

            for (key in datas) {
                data = datas[key];
                ret[key] = {};
                for (i in data) {
                    item = data[i];
                    matcher.call(datas, item) && (ret[key][i] = item) && (counter += 1);

                    if (limit && limit < counter + 1) {
                        return ret;
                    }
                }
            }

            return ret;
        };

        Ibos.data = {
            filter: _filter,

            get: function() {
                var arg = arguments,
                    len = arg.length,
                    matcher, key, i,
                    ret = {};

                if (!arg.length || arg[0] == null) {
                    return allData;
                }
                if ($.isFunction(arg[len - 1])) {
                    matcher = Array.prototype.pop.call(arg);
                    len--;
                }

                if (!len && matcher) {
                    return _filter(allData, matcher);
                } else {
                    for (i = 0; i < len; i++) {
                        key = arg[i];
                        if (key in allData) {
                            ret[key] = $.extend(true, {}, allData[key]);
                        }
                    }
                    return _filter(ret, matcher);
                }
            },

            // getItem(/* ids */)
            getItem: function( /*id1, id2...*/ ) {
                var ret = {},
                    arg = Array.prototype.slice.call(arguments, 0),
                    data, key;

                if (arg.length === 0) {
                    return {
                        user: {}
                    };
                }
                data = dataFetch(arg);
                for (key in data) {
                    ret[key] = getValues(key, data[key]);
                }

                return ret;
            },

            getUser: function(id) {
                if (!id) {
                    return false;
                }
                var type = getType(id),
                    user = getValues('user', id);

                return user[id];
            },

            getUserInfo: function(ids, callback) {
                var data, deptInfo, posInfo,
                    url = Ibos.app.url('main/api/orguser');

                $.post(url, {
                    uids: ids
                }, function(res) {
                    if (res.isSuccess) {
                        data = res.data;
                        callback && callback.call(null, data);
                        return true;
                    } else {
                        Ui && Ui.tip('无法获取成员信息', 'warning');
                        return false;
                    }
                }, 'json');
            },

            // 同步加载关联数据
            // 尽量只请求一个id
            // 在sessionStorage上缓存，以避免重复请求减小服务器压力
            getRelated: function(id /*ids*/ ) {
                if (!id || typeof id !== 'string') {
                    return false;
                }

                // for contact
                var idsArray = id.split(','),
                    type, nameSpace, data, key, ret = {};
                if (idsArray.length > 1) {
                    type = getType(id);
                    getRelatedUids(type, id, function(res) {
                        if (res.isSuccess) {
                            data = res.data;
                            for (key in data) {
                                type = getType(key);
                                nameSpace = makeName(type, key);
                                win.sessionStorage.setItem(nameSpace, JSON.stringify(data[key]));
                            }
                        } else {
                            throw new Error("(Ibos.related." + e + "): 无法获取关联数据");
                        }
                    });

                    return true;
                }

                // for userSelect
                type = getType(id);
                nameSpace = makeName(type, id);

                if (win.sessionStorage) {
                    data = JSON.parse(win.sessionStorage.getItem(nameSpace));
                }

                if (data) {
                    ret['user'] = getValues('user', data);
                } else {
                    getRelatedUids(type, id, function(res) {
                        if (res.isSuccess) {
                            data = res.data[id];
                            ret['user'] = getValues('user', data);
                            try {
                                // 防止数据量过大超出浏览器配额
                                win.sessionStorage.setItem(nameSpace, JSON.stringify(data));
                            } catch (e) {}
                        } else {
                            throw new Error("(Ibos.related." + e + "): 无法获取关联数据");
                        }
                    });
                }

                return ret;
            },

            converToArray: function(obj) {
                var ret = [],
                    key, i, data, item;

                for (key in obj) {
                    data = obj[key];
                    for (i in data) {
                        item = data[i];
                        item && ret.push(item);
                    }
                }

                return ret;
            },

            getType: getType,

            getText: getText,

            includes: function(ids) {
                if (!$.isArray(ids)) {
                    return console.error("param must be an Array");
                }
                return this.getItem.apply(this, ids);
            },

            clear: function() {
                win.sessionStorage.clear();
            }
        };
    } catch (error) {
        var allData = {};
        $.error("(Ibos.data): 数据解析错误");
    }
})(window, jQuery, Ibos);