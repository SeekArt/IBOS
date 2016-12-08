(function(win, Ibos) {
    'use strict';

    var View = U.getUrlParam().r,
        SWITCH_OFF = 0,
        SWITCH_ON = 1,
        PUBLISH = 1,
        APPROVAL = 2,
        DRAFT = 3;
    var initEditor, initUpload, picAction, initOthers, formVerify, initView, setAttachData, pluginInit, setCateConfig,
        createApprovalStepTmpl, openPostWindow, getPicsList;

    initEditor = function() {
        // 编辑器
        var viewAdd = /add/.test(View),
            ue = UE.getEditor('article_editor', {
                initialFrameWidth: 738,
                minFrameWidth: 738,
                autoHeightEnabled: true,
                toolbars: UEDITOR_CONFIG.mode.simple
            });

        ue.ready(function() {
            if (viewAdd) {
                (new Ibos.EditorCache(ue, null, "article_editor")).restore();
            }

            ue.addListener("contentchange", function() {
                $("#article_form").trigger("formchange");
            });
        });

        // 新手引导
        // if (viewAdd) {
        //     setTimeout(function() {
        //         Ibos.guide("art_def_add", [{
        //             element: "#article_status",
        //             intro: U.lang("ART.INTRO.STATUS"),
        //             position: "top"
        //         }]);
        //     }, 1000);
        // }

        return ue;
    };

    initUpload = function() {
        //上传
        var attachUpload = Ibos.upload.attach({
            "module": "article",
            custom_settings: {
                containerId: "file_target",
                inputId: "attachmentid"
            }
        });

        // 图片上传配置
        var picUpload = Ibos.upload.attach({
            "module": "article",
            file_types: Ibos.settings.imageTypes,
            button_placeholder_id: "pic_upload",
            custom_settings: {
                containerId: "pic_list",
                inputId: "picids",
                success: function(file, data, item) {
                    Article.picAction.initPicItem(item, data);
                }
            }
        });
    };

    picAction = function() {
        var $picRemove = $("#pic_remove"),
            $picMoveUp = $("#pic_moveup"),
            $picMoveDown = $("#pic_movedown"),
            picSelected = [];

        function resetBtns() {
            var $checked = U.getChecked("pic");
            var count = $checked.length,
                enableRemove = count >= 1,
                enableMove = count === 1;

            // 根据选中条目数决定，删除按钮和移动按钮的显隐
            $picRemove.toggle(enableRemove);
            $picMoveUp.toggle(enableMove);
            $picMoveDown.toggle(enableMove);
        }

        $(document).on("change", "[name='pic']", resetBtns);

        // 删除选中图片项
        $picRemove.on("click", function() {
            Article.picAction.removeSelect(U.getCheckedValue("pic").split(","));
            resetBtns();
        });
        // 上移选中图片项
        $picMoveUp.on("click", function() {
            Article.picAction.moveUp(U.getCheckedValue("pic").split(",")[0]);
        });
        // 下移选中图片项
        $picMoveDown.on("click", function() {
            Article.picAction.moveDown(U.getCheckedValue("pic").split(",")[0]);
        });
    };

    initOthers = function() {
        // tab 事件
        $("#content_type [data-toggle=tab]").on("show", function(evt) {
            $("#content_type_value").val($.attr(evt.target, "data-value"));
        });

        // 投票
        $('#voteStatus').on('change', function() {
            $('#vote').toggle($.prop(this, 'checked'));
        });

        // 默认分类
        $("#articleCategory").on("change", function() {
            setCateConfig({ catid: this.value });
        });

        if (Ibos.app.g('voteInstall') == SWITCH_ON && Ibos.app.g('articlevoteenable') == SWITCH_ON) {
            Article.ajaxApi.getVoteView({ 'articleid': Ibos.app.g('articleid'), 'view': 'topicsform' })
                .done(function(res) {
                    var $vote = $('.art-show-vote');

                    if (res.isSuccess) {
                        $vote.length > 0 && $vote.html(res.data) && $('#vote').hide();
                    } else {
                        Ui.tip(res.msg, 'warning');
                        return false;
                    }
                });
        }
    };

    formVerify = function() {
        var formID = 'article_form';

        $.formValidator.initConfig({ formID: formID, errorFocus: true });
        $("#subject").formValidator({ onFocus: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY") })
            .regexValidator({
                regExp: "notempty",
                dataType: "enum",
                onError: U.lang("RULE.SUBJECT_CANNOT_BE_EMPTY")
            });

        $('#publishscope').formValidator()
            .functionValidator({
                fun: function() {
                    if (!!$('#publishscope').val()) {
                        return true;
                    }

                    Ui.tip(Ibos.l("ART.PUBLISH_RANGE_CANNOT_BE_EMPTY"), 'warning');
                    return false;
                },
                validateType: "functionValidator"
            });

        Ibos.checkFormChange("#" + formID);
        $('#' + formID).on('form.submit', function(evt) {
            if (!$.formValidator.pageIsValid()) {
                return false;
            }

            var data, type, $this = $(this);

            data = U.serializedToObject($this.serializeArray());
            data.articleid = Ibos.app.g('articleid') || '';
            // save or submit
            data.status = Ibos.app.g('status.form') || data.status;

            data.votestatus = $('input[name="votestatus"]').prop('checked') ? 1 : 0;
            data.commentstatus = $('input[name="commentstatus"]').prop('checked') ? 1 : 0;

            if (data.votestatus == SWITCH_OFF) {
                delete data.vote;
            }

            Article.ajaxApi.submitForm(data).done(function(res) {
                if (res.isSuccess) {
                    Ui.tip('保存成功');
                    win.sessionStorage.setItem('view.article',
                        data.status == DRAFT ? 'draft' : res.data.status == APPROVAL ? 'approval' : 'publish');
                    win.location.href = Ibos.app.url('article/publish/index');
                } else {
                    Ui.tip(res.msg, 'warning');
                    return false;
                }
            });
        });
    };

    initView = function() {
        // 表单验证
        formVerify();
        // 初始化页面其余插件
        initOthers();
        // 获取页面配置
        // 新闻分类、投票、评论
        Article.ajaxApi.getOption().done(function(res) {
            var data = res.data,
                $cate = $('select[name="catid"]');
            if (res.isSuccess) {
                // 新闻分类
                $cate.html(data);
                setCateConfig({ catid: $cate.val() });
                // 上传配置
                // $('#file_limit_tip').text(Ibos.l('ART.FILE_SIZE_LIMIT') + data.uploadConfig.max / 1024 + 'MB');
                // 投票
                // (!data.dashboardConfig.articlevoteenable || !data.isVoteInstall) && $('input[name="votestatus"]')
                //     .prop('disabled', true)
                //     .attr('title', Ibos.l('ART.VOTES_MODULE_IS_NOT_INSTALLED_OR_ENABLED'));
                // 评论
                // !data.dashboardConfig.articlecommentenable ? $('input[name="commentstatus"]')
                //     .prop('disabled', true)
                //     .attr('title', Ibos.l('ART.COMMENTS_MODULE_IS_NOT_INSTALLED_OR_ENABLED')) :
                //     $('input[name="commentstatus"]').iSwitch('turnOn');
            } else {
                Ui.tip(res.msg, 'warning');
                win.location.reload();
            }
        });

        if (/edit/.test(View)) {
            Article.ajaxApi.getFormEdit({ articleid: Ibos.app.g('articleid') }).done(function(res) {
                if (res.isSuccess) {
                    $(document).trigger('initView.art', res.data);
                } else {
                    Ui.tip(res.msg, 'warning');
                    return false;
                }
            });
        } else {
            pluginInit();
        }
    };

    setAttachData = function(data) {
        var attach = data.attach,
            pics = data.pictureData;

        attach && $('#file_target').append($.tmpl('tmpl_file_container', { fileArr: attach }));
        pics && $('#pic_list').append($.tmpl('tmpl_pics_container', { picsArr: pics }))
            .find('input[type="checkbox"]').label();
    };

    pluginInit = function() {
        // 初始化编辑器
        initEditor();
        // 初始化上传插件
        initUpload();
        // 初始化图片操作
        picAction();
        // 初始化用户选择器
        $("#publishscope").userSelect({
            data: Ibos.data.get(),
            type: 'all'
        });

        return true;
    };

    setCateConfig = function(param) {
        var tmpl = '<span class="xcbu lhf">不需要审批</span>';
        Article.ajaxApi.cateApproval(param).done(function(res) {
            if (res.isSuccess) {
                // 审核步骤
                $('.art-cate-approval').html(res.data.level ? createApprovalStepTmpl(res.data) : tmpl);
                $('input[name="status"]').val(res.data.level ? APPROVAL : PUBLISH);
            } else {
                Ui.tip(res.msg, 'warning');
                return false;
            }
        });
    };

    createApprovalStepTmpl = function(data) {
        return $.tmpl('approval_step', data);
    };

    openPostWindow = function(url, data) {
        !data && win.open(url, '_blank');

        win.sessionStorage.setItem('preview.article', JSON.stringify(data));
        win.open(url, '_blank');
    };

    getPicsList = function() {
        var info, PICS = [],
            $items = $('#pic_list').find('.attl-item');

        $.each($items, function(i, v) {
            info = $(v).data('fileInfo');

            PICS.push({
                aid: info.aid,
                name: info.name,
                url: info.url
            });
        });

        return PICS;
    };

    $(document).on('initView.art', function(evt, data) {
        Article.setFormInfo(data.data);
        setAttachData(data);
        pluginInit();
        // 编辑页tab切换
        $("#content_type [data-toggle='tab'][data-value='" + data.data.type + "']").tab("show");
    });

    $(function() {
        initView();

        Ibos.evt.add({
            'toTop': function() {
                var checked, time, $this = $(this),
                    $istop = $('input[name="istop"]'),
                    $topendtime = $('input[name="topendtime"]');

                $this.addClass('active'); // 先按钮高亮显示，以表示操作框弹出
                Article.getTopD(function() {
                    checked = $('input[name="totop"]').prop('checked');
                    time = $('input[name="topEndTime"]').val();

                    checked ? $this.addClass('active') : $this.removeClass('active');
                    $istop.val(checked ? 1 : 0);
                    $topendtime.val(checked ? time : '');
                });
            },
            'toHighLight': function() {
                var $this = $(this),
                    checked, hlstyle, time,
                    $ishighlight = $('input[name="ishighlight"]'),
                    $highlightstyle = $('input[name="highlightstyle"]'),
                    $highlightendtime = $('input[name="highlightendtime"]');

                $this.addClass('active');
                Article.getHighLightD(function() {
                    checked = $('input[name="tohighlight"]').prop('checked');
                    time = $('input[name="highlightEndTime"]').val();
                    hlstyle = [
                        $('input[name="highlight_bold"]').val(),
                        $('input[name="highlight_color"]').val(),
                        $('input[name="highlight_italic"]').val(),
                        $('input[name="highlight_underline"]').val()
                    ].join();

                    checked ? $this.addClass('active') : $this.removeClass('active');
                    $ishighlight.val(checked ? 1 : 0);
                    $highlightstyle.val(checked ? hlstyle : '');
                    $highlightendtime.val(checked ? time : '');
                });
            },
            'preview': function() {
                var type = parseInt($('#content_type_value').val(), 10),
                    TYPE_ARTICLE = 0,
                    TYPE_PIC = 1,
                    TYPE_URL = 2,
                    url, setting;

                switch (type) {
                    case TYPE_ARTICLE: // 文章
                        url = Ibos.app.url("article/default/preview");
                        setting = {
                            type: type,
                            subject: $('#subject').val(),
                            content: UE.getEditor('article_editor').getContent()
                        };
                        break;
                    case TYPE_PIC: // 超链接
                        url = Ibos.app.url("article/default/preview");
                        setting = {
                            type: type,
                            subject: $('#subject').val(),
                            pics: getPicsList()
                        };
                        break;
                    case TYPE_URL: // 图片
                        url = $('#article_link_url').val();
                        setting = U.reg.url.exec(url);
                        if (!setting) {
                            Ui.tip(U.lang("RULE.URL_INVALID_FORMAT"), "warning");
                        } else {
                            // 没有协议前缀，自动补全
                            url = setting[1] ? url : 'http://' + url;
                            setting = null;
                        }
                        break;
                }

                openPostWindow(url, setting);
            },
            'saveForm': function() {
                Ibos.app.s('status.form', DRAFT);
                $('#article_form').trigger('submit');
            },
            'submitForm': function() {
                $('#article_form').trigger('submit');
            }
        });
    });

})(window, Ibos, undefined);
