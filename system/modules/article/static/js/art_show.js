(function(win, Ibos) {
    'use strict';

    var ARTICLEID = Ibos.app.g('articleId'),
        SWITCH_OFF = 0,
        SWITCH_ON = 1,
        TYPE_TEXT = 0,
        TYPE_PIC = 1,
        TYPE_HREF = 2;
    var initShow, initPlugin, loadReader, loadVote;
    /**
     * 初始化页面信息
     * @method initShow
     */
    initShow = function() {
        // 显示页拉取数据加载
        if (/show/.test(U.getUrlParam().r)) {
            Article.ajaxApi.getFormShow({
                'articleid': ARTICLEID
            }).done(function(res) {
                if (res.isSuccess) {
                    $(document).trigger('initShow.art', res.data);
                } else {
                    Ui.tip(res.msg, 'warning');
                    setTimeout(function() {
                        window.location.href = Ibos.app.url('article/default/index');
                    }, 1000);
                }
            });
        }
    };

    /**
     * 加载页面插件
     * @return {[type]} [description]
     */
    initPlugin = function() {
        // 如果图片类型的新闻，需要加载 Gallery 组件
        if (Ibos.app.g("articleType") == TYPE_PIC) {
            var STATIC_URL = Ibos.app.getStaticUrl();

            U.loadCss(STATIC_URL + "/js/lib/gallery/jquery.gallery.css?" + Ibos.app.g("VERHASH"), function() {
                $.getScript(STATIC_URL + "/js/lib/gallery/jquery.gallery.js", function() {
                    $('#gallery').adGallery({
                        loader_image: STATIC_URL + "/image/loading_mini.gif"
                    });
                });
            });
        }
    };
    /**
     * 加载阅读情况数据
     * @method loadReaderInfo
     */
    loadReader = function() {
        var $comment, $isread, $verifylog, $comment_tab, $isread_tab, $verifylog_tab, _more;

        // 加载更多阅读情况数据
        _more = function(res) {
            var readerTabHeight = $("#art_reader_table").height(),
                moreHtml = "<div class='art-reader-more fill-hn xac'>" +
                "   <a href='javascript:;' class='link-more' id='load_more_reader'>" +
                "       <i class='cbtn o-more'></i>" +
                "       <span class='ilsep'>查看更多查阅人员</span>" +
                "   </a>" +
                "</div>";

            if (readerTabHeight > 300) {
                $("#art_reader_table").addClass("reader-tab-h").removeClass("h-auto");
                $("#isread").append(moreHtml);
            }
        };

        $comment = $('#comment');
        $isread = $('#isread');
        $verifylog = $('#verifylog');
        $comment_tab = $('#comment_tab');
        $isread_tab = $('#isread_tab');
        $verifylog_tab = $('#verifylog_tab');

        $comment_tab.on('shown', function() {
            // 避免重复加载
            if (!$comment.attr('data-loaded')) {
                Article.ajaxApi.getCommentView({
                    'articleid': ARTICLEID,
                    'inajax': 1
                }).done(function(res) {
                    if (res.isSuccess) {
                        $comment.html(res.data)
                            .attr('data-loaded', '1');

                        //初始化表情功能
                        $('#comment_emotion').ibosEmotion({
                            target: $('#commentBox')
                        });
                    } else {
                        Ui.tip('无法有效拉取评论，请重试', 'warning');
                        return false;
                    }
                });
            }
        });

        // 加载查阅人员情况
        $isread_tab.on("shown", function() {
            var $tmpl;

            if (!$isread.attr('data-loaded')) {
                Article.ajaxApi.getReader({
                    'articleid': ARTICLEID
                }).done(function(res) {
                    if (res.isSuccess) {
                        $tmpl = $.tmpl("tpl_reader_table", {
                            readerData: res.data
                        });
                        $isread.html($tmpl)
                            .attr('data-loaded', '1');
                        _more.call(res);
                    } else {
                        Ui.tip(res.msg, 'warning');
                        return false;
                    }
                });
            }
        });

        $verifylog_tab.on("shown", function() {
            var $tmpl;

            if (!$verifylog.attr('data-loaded')) {
                Article.ajaxApi.getFlowLog({
                    'articleid': ARTICLEID
                }).done(function(res) {
                    if (res.isSuccess) {
                        $tmpl = $.tmpl('tpl_verify_log', {
                            datas: res.data
                        });
                        $verifylog.html($tmpl)
                            .attr('data-loaded', '1');
                    } else {
                        Ui.tip(res.msg, 'warning');
                        return false;
                    }
                });
            }
        });

        $isread.delegate("#load_more_reader", "click", function() {
            $("#art_reader_table").addClass("h-auto").removeClass("reader-tab-h");
            $("#load_more_reader").parent().hide();
        });

        // 展开所有阅读人员
        $(document).on('click', '.reader-all', function() {
            $(this).hide().parent().prev().html($.attr(this, "data-fullList"));
        });
    };

    loadVote = function() {
        Article.ajaxApi.getVoteView({
            'articleid': ARTICLEID,
            'view': 'view'
        }).done(function(res) {
            if (res.isSuccess) {
                $(document).trigger('loadVote.art', res.data);
            } else {
                Ui.tip(res.msg, 'warning');
                return false;
            }
        });
    };

    $(document).on('initShow.art', function(evt, data) {
        var $tmpl = $.tmpl('tpl_art_content', data.data);
        $('.ctview-art .art').append($tmpl);

        Ibos.app.s({
            'commentStatus': data.data.commentstatus,
            'articleType': data.data.type
        });

        initPlugin();

        // 允许投票且存在投票时拉取投票数据
        if (Ibos.app.g('voteEnable') && data.data.votestatus == SWITCH_ON) {
            loadVote();
        }

        //加载阅读情况数据
        loadReader();

        $("#verifylog_tab").tab('show');
        // 禁用评论或新闻不允许评论时，直接显示查阅人员
        if (Ibos.app.g('commentEnable') && data.data.commentstatus == SWITCH_ON) {
            $("#comment_tab").tab('show');
        }
    });

    $(document).on('loadVote.art', function(evt, data) {
        var $vote = $('.art-show-vote'),
            htmlArr, len, elem, newscript;

        if ($vote.length > 0) {
            htmlArr = jQuery.parseHTML(data, $vote, true);
            // 重新构造script标签使他执行
            for (len = htmlArr.length; len--;) {
                elem = htmlArr[len];
                if (elem.tagName.toLowerCase() != 'script') {
                    $vote.append(elem);
                } else {
                    newscript = document.createElement('script');
                    elem.src && (newscript.src = elem.src); // 外联js文件
                    elem.innerHTML && (newscript.innerHTML = elem.innerHTML); // 内联js文本
                    $vote.get(0).appendChild(newscript);
                }
            }
        }
    });

    $(function() {
        //初始化页面
        initShow();

        Ibos.evt.add({
            'editArticle': function(param, elem) {
                Ui.confirm(U.lang("ART.EDIT_AT_SURE"), function() {
                    var id = $(elem).data('id');
                    location.href = Ibos.app.url('article/default/edit', {
                        'articleid': id
                    });
                });
            },
            'removeArticle': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Ui.confirm(Ibos.l('ART.SURE_DEL_ARTICLE'), function() {
                    Article.ajaxApi.removeArticles({
                        'articleids': id
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');
                        // 跳转到被退回列表
                        if (res.isSuccess) {
                            win.sessionStorage.setItem('view.article', 'reback_to');
                            window.location.href = Ibos.app.url('article/publish/index');
                        }
                    });
                });
            },
            'pushBack': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Ui.confirm(Ibos.l('ART.APPROVING_MAKE_SURE_REBACK'), function() {
                    Article.ajaxApi.pushBack({
                        'articleid': id
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');

                        // 跳转到审核中列表
                        if (res.isSuccess) {
                            win.sessionStorage.setItem('view.article', 'approval');
                            window.location.href = Ibos.app.url('article/publish/index');
                        }
                    });
                });
            },
            'remindApprover': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Article.ajaxApi.remindApprover({
                    'articleids': id
                }).done(function(res) {
                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');

                    // 跳转到审核中列表
                    if (res.isSuccess) {
                        win.sessionStorage.setItem('view.article', 'approval');
                        window.location.href = Ibos.app.url('article/publish/index');
                    }
                });
            },
            'reback': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id'),
                    value;

                Article.getBackD(function() {
                    value = $('textarea[name="backreason"]').val();

                    Article.ajaxApi.reback({
                        'articleids': id,
                        'reason': value
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');

                        // 跳转到待我列表
                        if (res.isSuccess) {
                            win.sessionStorage.setItem('view.article', 'wait');
                            window.location.href = Ibos.app.url('article/verify/index');
                        }
                    });
                });
            },
            'passArticle': function(param, elem) {
                var $this = $(this),
                    id = $this.data('id');

                Article.ajaxApi.verifyArticles({
                    'articleids': id
                }).done(function(res) {
                    Ui.tip(res.msg, res.isSuccess ? '' : 'warning');

                    // 跳转到待我列表
                    if (res.isSuccess) {
                        win.sessionStorage.setItem('view.article', 'wait');
                        window.location.href = Ibos.app.url('article/verify/index');
                    }
                });
            },
            'getBack': function(param, elem) {
                var id = $(elem).data('id');

                Ui.confirm(U.lang("ART.BACK_AND_VERIFY_AGAIN"), function() {
                    Article.ajaxApi.getBack({
                        'articleids': id
                    }).done(function(res) {
                        Ui.tip(res.msg, res.isSuccess ? '' : 'warning');

                        // 跳转到我已通过列表
                        if (res.isSuccess) {
                            win.sessionStorage.setItem('view.article', 'passed');
                            window.location.href = Ibos.app.url('article/verify/index');
                        }
                    });
                });
            }
        });
    });

})(window, Ibos, undefined);