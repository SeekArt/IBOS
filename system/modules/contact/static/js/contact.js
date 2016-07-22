(function(win, $, Ibos, undefined) {
    'use strict';
    // global
    var Ibos = Ibos || {},
        Contact = {},
        domMap = {
            $win: $(win),
            $doc: $(document),
            $mc: $('.mc'),
            $user_list: $('#user_datalist'),
            $search_list: $('#user_searchlist'),
            $search_area: $('#search_area')
        };
    // const
    var DEPTID = Ibos.app.g('deptid'),
        OP = Ibos.app.g('op') || 'letter',
        SORTED = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
        UIDS, AllDATA = {};
    // function
    var ajaxApi, setDomMap, userInfoBarShown, calLetterPosition, formatUserInfo,
        loadUserList, pidCache, getPreg, getLetterList, getDeptList, cacheRelated,
        toDataTmpl, toDeptData, toCreateTmpl, toLetterTmpl, searchUser, treeObj;
    // flag
    var _do_search = false,
        _has_cache = false;

    ajaxApi = {
        // get userInfo for sidebar showing
        getProfile: function(param) {
            var url = Ibos.app.url('contact/default/ajaxApi');
            param = $.extend({}, param, { op: 'getProfile' });

            return $.post(url, param, $.noop, 'json');
        },
        // page print
        printContact: function(param) {
            var url = Ibos.app.url('contact/default/printContact');

            return $.post(url, param, $.noop);
        }
    };

    setDomMap = function(name, dom) {
        if (!name) {
            return false;
        }

        if (typeof name === 'object') {
            $.extend(domMap, name);
        } else if (typeof name === 'string') {
            domMap[name] = dom;
        }

        return true;
    };

    userInfoBarShown = function(show) {
        !domMap.$cl_rolling_sidebar && setDomMap({
            $cl_rolling_sidebar: $('#cl_rolling_sidebar')
        });
        domMap.$cl_rolling_sidebar.animate(show ? { width: '520px', marginLeft: '261px' } : { width: '0', marginLeft: '780px' }, 200);
    };

    calLetterPosition = function() {
        // cache domlist result
        setDomMap({
            $cl_list_header: $('#cl_list_header'),
            $rollingSidebar: $('#cl_rolling_sidebar'),
            $addWrap: $('#add_contacter_wrap'),
            $letterSidebar: $('#cl_letter_sidebar'),
            $funbar: $('#cl_funbar')
        });

        var mcheight = domMap.$mc.height(),
            slidetop = domMap.$doc.scrollTop() - domMap.$cl_list_header.offset().top,
            linkheight = mcheight - slidetop,
            rollingslideheight = linkheight + 'px',
            mcheightval = mcheight + 'px',
            slidetopval = -slidetop + 'px',
            rletterHeightVal = linkheight - 60 + 'px';

        if (slidetop > 0) {
            domMap.$addWrap.css({ 'top': '60px', 'height': rollingslideheight });
            domMap.$rollingSidebar.css({ 'top': '60px', 'height': rollingslideheight });
            domMap.$letterSidebar
                .css({ 'height': rletterHeightVal })
                .addClass('sidebar-rolling')
                .removeClass('sidebar-normal');
            domMap.$funbar.addClass('funbar-rolling').removeClass('funbar-normal');
        } else {
            domMap.$addWrap.css({ 'top': slidetopval, 'height': mcheightval });
            domMap.$rollingSidebar.css({ 'top': slidetopval, 'height': mcheightval });
            domMap.$letterSidebar
                .addClass('sidebar-normal')
                .removeClass('sidebar-rolling');
            domMap.$funbar.addClass('funbar-normal')
                .removeClass('funbar-rolling');
        }
    };

    formatUserInfo = function(param) {
        if (param.indexOf('u') === 0) {
            var arr = param.split(','),
                data = $.map(arr, function(uid) {
                    var data = Ibos.data.getUser(uid);
                    return { uid: uid.slice(2), name: data.text, avatar: data.avatar, phone: data.phone };
                });
            return data;
        } else {
            var avatar = Ibos.app.g('emptyAvatar');
            return [{ name: '未知', avatar: avatar, phone: param }];
        }
    };
    // load userList
    // divide to types
    loadUserList = function(deptid) {
        var action = {
                letter: getLetterList,
                dept: getDeptList
            },
            fetchDatas;

        fetchDatas = action[OP](deptid);
        toCreateTmpl('tpl_contact_table', domMap.$user_list, { datas: fetchDatas });
        OP === 'letter' && toLetterTmpl(fetchDatas);
        // while creating tmpl, we can record UIDS for print and export
        UIDS = Ibos.app.g('uids');

        return true;
    };
    // make sure related user of department by id has been cache
    pidCache = function(deptid) {
        var deptCache = win.sessionStorage.getItem('contact.dept.cache') || '',
            deptArr = deptCache.split(',');

        if (deptArr.indexOf(deptid) !== -1) {
            _has_cache = true;
        } else {
            deptArr.push(deptid);
            win.sessionStorage.setItem('contact.dept.cache', deptArr.join(','));
        }

        return true;
    };
    // using pinyinEngine
    // support complete and brief and chinese
    getPreg = function(text) {
        if (typeof text !== 'string') {
            return '';
        }

        var toPinyin = pinyinEngine.toPinyin,
            pyArr = toPinyin(text, false),
            allStr = [],
            sinStr = [],
            firstChar, atZero,
            i, j, alen, slen;

        for (i = 0, alen = pyArr.length; i < alen; i += 1) {
            firstChar = pyArr[i];
            allStr.push(firstChar.join(''));
            atZero = [];
            for (j = 0, slen = firstChar.length; j < slen; j += 1) {
                atZero.push(firstChar[j].charAt(0));
            }
            sinStr.push(atZero.join(''));
        }

        allStr = allStr.join(',');
        sinStr = sinStr.join(',');
        return allStr + ',' + sinStr + ',' + text;
    };
    // get list by letter sort
    getLetterList = function(deptid) {
        var ret = {},
            src = Ibos.data.getRelated(deptid)['user'];

        ret = toDataTmpl(src, true);
        // cache search list
        // letter/dept different types
        AllDATA = ret;

        src = null;
        return ret;
    };
    // get list by dept sort
    getDeptList = function(deptid) {
        var ret, i, len, curData, childrenDatas = {},
            // if curNode is null or undefined, it represent to c_0
            curNode = treeObj.getNodeByParam('id', deptid),
            childrenNodes = treeObj.getNodesByParamFuzzy('id', '', curNode);

        // get data by dept
        if (!curNode) {
            // d_0 contain users who do not belong to anyother
            // simulate an ztree obj for toDeptData
            curData = toDeptData({
                id: 'd_0',
                getPath: function() {
                    return [{ id: 'd_0', text: Ibos.data.getText('c_0') }];
                }
            }, true);
            pidCache('c_0');
        } else {
            curData = toDeptData(curNode, true);
            pidCache(curNode.id);
        }

        if (childrenNodes.length > 0) {
            // collect deptids in order to request once for saving time
            // too gruff, need to optimize
            // use sessionStorage to cache, prevent request more
            if (!_has_cache) {
                cacheRelated(childrenNodes);
            }

            // get datas by children depts
            for (i = 0, len = childrenNodes.length; i < len; i += 1) {
                childrenDatas = $.extend(childrenDatas, toDeptData(childrenNodes[i]));
            }
        }

        ret = $.extend({}, curData, childrenDatas);
        curData = childrenDatas = childrenNodes = null;
        return ret;
    };
    // if nodes's too long
    // divide into piece
    cacheRelated = function(nodes) {
        var i, len, deptids = [];
        for (i = 0, len = nodes.length; i < len; i += 1) {
            deptids.push(nodes[i].id);
        }

        while (deptids.length) {
            Ibos.data.getRelated(deptids.splice(0, 100).join(','));
        }

        return true;
    };

    toDataTmpl = function(data, issort) {
        var fetch = {},
            ret = {},
            firstChar, uid, preg, text, sort;

        for (uid in data) {
            text = data[uid]['text'];
            preg = getPreg(text);
            firstChar = preg.charAt(0).toUpperCase();
            if (!fetch[firstChar]) {
                fetch[firstChar] = [];
            }
            fetch[firstChar].push($.extend({}, data[uid], { preg: preg }));
        }

        // sort list
        if (!issort) {
            return fetch;
        }

        for (var i = 0; i < 26; i++) {
            sort = SORTED[i];
            if (sort in fetch) {
                ret[sort] = fetch[sort];
            }
        }

        fetch = null;
        return ret;
    };

    toDeptData = function(node, isSave) {
        var deptid = node.id,
            ret = {},
            dept, path, users;

        // waste time for request one by one
        // think a better way to cache children list once
        users = Ibos.data.converToArray(toDataTmpl(Ibos.data.getRelated(deptid)['user']));
        // save the parent node for shown
        if (users.length === 0 && !isSave) {
            return {};
        }

        dept = !ret[deptid] && (ret[deptid] = {});
        path = node.getPath().map(function(val, i) {
            return val.text;
        });

        dept['pDeptids'] = path;
        dept['users'] = users;
        AllDATA[deptid] = dept['users'];

        return ret;
    };

    toCreateTmpl = function(tmplID, $container, data) {
        var tpl;
        tpl = $.tmpl(tmplID, data);
        $container.html(tpl);

        return tpl;
    };

    toLetterTmpl = function(data) {
        var ret = {},
            letter;

        ret['allLetters'] = SORTED;
        ret['existLetters'] = [];

        for (letter in data) {
            ret['existLetters'].push(letter);
        }
        toCreateTmpl('tpl_letter_sidebar', $('#cl_letter_sidebar'), ret);

        return true;
    };

    searchUser = function(val) {
        if (!val) {
            domMap.$search_list.hide().empty();
            domMap.$user_list.show();
            Ibos.app.s('uids', UIDS);
            return false;
        }

        var searchArr = [],
            tmplData = {},
            letter, users,
            i, len, user;

        for (letter in AllDATA) {
            users = AllDATA[letter];
            for (i = 0, len = users.length; i < len; i += 1) {
                if (!_do_search) {
                    return false;
                }
                user = users[i];
                if (user.preg.indexOf(val) !== -1) {
                    if (!tmplData[letter]) {
                        tmplData[letter] = [];
                    }
                    tmplData[letter].push(user);
                }
            }
        }

        domMap.$user_list.hide();
        domMap.$search_list.show();
        toCreateTmpl('tpl_search_table', domMap.$search_list, { datas: tmplData });

        searchArr = null;
        tmplData = null;
        return true;
    };
    // create zTree of department
    treeObj = (function() {
        var ztreeOpt, settings, treeObj;
        setDomMap({
            $tree: $('#utree'),
            $corp_unit: $('#corp_unit')
        });

        ztreeOpt = {
            'nodeOnClick': function(event, treeId, treeNode) {
                domMap.$corp_unit.removeClass('dep-active');
                win.location.href = Ibos.app.url('contact/default/index', {
                    op: OP,
                    deptid: treeNode.id.slice(2)
                });
            }
        };

        settings = {
            data: {
                key: {
                    name: 'text'
                },
                simpleData: {
                    enable: true,
                    pIdKey: 'pid'
                }
            },
            view: {
                showLine: false,
                selectedMulti: false,
                showIcon: false
            },
            callback: {
                onClick: ztreeOpt.nodeOnClick
            }
        };

        treeObj = $.fn.zTree.init(domMap.$tree, settings, Ibos.data.converToArray(Ibos.data.get('department', function(data) {
            return data.id !== 'c_0';
        })));

        return treeObj;
    })();

    // page init to select department by deptid
    treeObj.selectNode(treeObj.getNodeByParam('id', 'd_' + DEPTID));

    $(function() {
        //load user list
        loadUserList(DEPTID ? 'd_' + DEPTID : 'c_0');

        domMap.$win.on('resize scroll', function() {
            calLetterPosition();
        });

        // page init to calculate letter position
        calLetterPosition();
        setDomMap({
            $mark: $('.letter-mark')
        });

        // search
        domMap.$search_area.on('input propertychange', (function() {
            var timer = null,
                startTime;

            return function() {
                var val = $(this).val().toLowerCase(),
                    curTime = +new Date();

                clearTimeout(timer);
                // to prevent the pre search
                _do_search = false;
                if (!startTime) {
                    startTime = curTime;
                }

                if (curTime - startTime >= 400) {
                    // deal with the search request
                    _do_search = true;
                    searchUser(val);
                } else {
                    startTime = curTime;
                    timer = setTimeout(function() {
                        _do_search = true;
                        searchUser(val);
                    }, 250);
                }
            }
        })());

        (function noDataTip() {
            setDomMap({
                $group_item: $(".exist-data .group-item"),
                $noDataTip: $(".inexist-data")
            });

            var nodata = domMap.$group_item.length == 0 || (domMap.$group_item.length == 1 && domMap.$group_item.find('tr').length == 0);
            domMap.$noDataTip.toggle(nodata);
        })();

        Ibos.evt.add({
            // 公司通讯录，点击列表单行，侧栏信息显示，改变选择行背景色
            'getUserInfo': function(param, elem) {
                var $elem = $(elem),
                    id = $elem.attr('data-id'),
                    param = { uid: id };
                // control css style
                domMap.$user_list.find('tr').removeClass('active');
                $elem.addClass('active');
                userInfoBarShown(true);
                // load userInfo async
                setDomMap({
                    $personal_info: $('#personal_info'),
                    $card_pm: $('#card_pm')
                });

                domMap.$personal_info.waitingC();
                ajaxApi.getProfile(param).done(function(res) {
                    if (res.isSuccess) {
                        var user = res.user,
                            tpl = $.tmpl('tpl_rolling_sidebar', { user: user });

                        domMap.$cl_rolling_sidebar.empty().append(tpl);
                        domMap.$personal_info.stopWaiting();

                        // if have chat feature
                        domMap.$card_pm.toggle(res.uid != user.uid);
                        // set formatdata to global
                        Ibos.app.s({ formatdata: formatUserInfo('u_' + user.uid) });
                    }
                });
            },
            // 点击字母导航，滚动条滚动到对应字母位置
            'letterNav': function(param, elem) {
                var $elem = $(elem),
                    id = $elem.attr('data-id');

                setDomMap({
                    $target: $('#target_' + id),
                    $letter_title: $('.cl-letter-title')
                });

                domMap.$mark.removeClass('active');
                $elem.addClass('active');

                if (!id) {
                    return false;
                }
                domMap.$letter_title.removeClass('active');
                Ui.scrollYTo(domMap.$target, -120, function() { domMap.$target.addClass('active'); });
            },
            // 关闭侧栏个人信息
            'closeSidebar': function(param, elem) {
                userInfoBarShown(false);
            },
            // print current table
            'printCont': function(param, elem) {
                ajaxApi.printContact({ uids: Ibos.app.g('uids') }).done(function(res) {
                    if (res.isSuccess) {
                        $('body').find('.main-content').remove().end().append(res.view);
                    }
                    window.print();
                });
            },
            // exports current table
            'educeCont': function(param, elem) {
                var $form = $('#export_contact'),
                    $input = $form.find('input[name="uids"]');

                $input.val(Ibos.app.g('uids'));
                $form.submit();
            }
        });
    })

    // export obj
    Contact = {
        ajaxApi: ajaxApi,
        getPreg: getPreg,
        treeObj: treeObj,
        getLetterListById: getLetterList,
        getDeptListById: getDeptList
    }

    if (typeof define === 'function' && define.amd) {
        define(function() {
            return Contact;
        });
    } else if (typeof module !== "undefined" && module.exports) {
        module.exports = Contact;
    } else {
        win.Contact = Contact;
    }
})(window, jQuery, Ibos);
