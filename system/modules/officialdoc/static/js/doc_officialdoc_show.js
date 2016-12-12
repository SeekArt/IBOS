/**
 * Officialdoc/officialdoc/show
 * @version $Id$
 */
var OfficialShow = {
    op: {
        /**
         * 发送未签收的人员
         * @method sendNoSign
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        sendNoSign: function(param) {
            var url = Ibos.app.url("officialdoc/officialdoc/index");
            param = $.extend({}, param, {
                op: "remind"
            });
            return $.post(url, param, $.noop);
        },
        /**
         * 回退通知
         * @method sendNoSign
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        backDocs: function(param) {
            var url = Ibos.app.url("officialdoc/officialdoc/edit");
            param = $.extend({}, param, {
                op: "back"
            });
            return $.post(url, param, $.noop, 'json');
        },
        /**
         * 审核通知
         * @method approvalDoc
         * @param  {Object} param 传入JSON格式数据
         * @return {Object}       返回deffered对象
         */
        approvalDoc: function(param) {
            var url = Ibos.app.url('officialdoc/officialdoc/edit');
            param = $.extend({}, param, {
                op: "verify"
            });
            return $.post(url, param, $.noop, 'json');
        }
    },

    /**
     * 加载签收人员数据
     * @method loadSign
     * @param  {String}   id         传入签收人员id
     * @param  {Object}   $elem      传入Jquery节点对象
     * @param  {Function} [callback] 回调函数
     */
    loadSign: function(id, $elem, callback) {
        if (!$elem.data("loaded")) {
            Official.op.getSign(id).done(function(res) {
                $elem.html(res.signView).data("loaded", 1);
                callback && callback(res);
            });
        }
    },
    /**
     * 加载未签收数据
     * @method loadNoSign
     * @param  {String}   id         传入未签收人员id
     * @param  {Object}   $elem      传入Jquery节点对象
     * @param  {Function} [callback] 回调函数
     */
    loadNoSign: function(id, $elem, callback) {
        if (!$elem.data("loaded")) {
            Official.op.getNoSign(id).done(function(res) {
                $elem.html(res.unsignView).data("loaded", 1);
                callback && callback(res);
            });
        }
    },
    /**
     * 加载历史版本数据
     * @method loadVersion
     * @param  {String}   id         传入版本id
     * @param  {Object}   $elem      传入Jquery节点对象
     * @param  {Function} [callback] 回调函数
     */
    loadVersion: function(id, $elem, callback) {
        if (!$elem.data("loaded")) {
            Official.op.getVersion(id).done(function(res) {
                $elem.html($.template("tpl_version_table", {
                        versions: res
                    }))
                    .data("loaded", 1);
                callback && callback(res);
            });
        }
    },
    /**
     * 签收通知
     * @method signdoc
     * @param  {String} id    传入id
     * @param  {Object} $elem 传入Jquery节点对象
     */
    signdoc: function(id, $elem) {
        Official.op.sign(id).done(function(res) {
            Ui.tip(res.msg, res.isSuccess ? "" : "warning");
            res.isSuccess && (window.location.href = document.referrer);
        });
    },
    /**
     * 初始化显示页面
     * @method initShowPage
     */
    initShowPage: function() {
        //替换百度编辑器换页标识符操作
        var content = $("#art_content").html();
        var replaceCont = content.replace(/_baidu_page_break_tag_/g, "</div><div class='officialdoc-content'>");
        $("#art_content").html(replaceCont);
        //设置页码数
        var $offContents = $("#art_content .officialdoc-content");
        $offContents.each(function(key, val) {
            $("<span class='page-num'>" + (key + 1) + " / " + $offContents.length + "</span>").appendTo(this);
        });

        //初始化表情功能
        $('#comment_emotion').ibosEmotion({
            target: $('#commentBox')
        });

        //加载更多签收人数据
        $("#issign").delegate("#load_more_sign", "click", function() {
            $("#art_sing_table").addClass("art-sign-auto").removeClass("art-sign-limit");
            $("#load_more_sign").hide();
        });

        //加载更多未签收人数据
        $("#isnosign").delegate("#load_more_no_sign", "click", function() {
            $("#art_no_sing_table").addClass("art-sign-auto").removeClass("art-sign-limit");
            $("#load_more_no_sign").hide();
        });

        // 禁用评论时，默认显示阅读人员
        if (!Ibos.app.g("commentEnable") || !Ibos.app.g("commentStatus")) {
            $("#sign_tab").tab("show");
        }
    }
};




$(function() {
    // 初始化显示页面
    OfficialShow.initShowPage();

    $(".o-art-description, .o-allow-circle, .o-noallow-circle").tooltip();

    //点击关闭当前通知时，提示签收通知
    $("#art_close").on("click.artClose", function() {
        Ui.confirm(Ibos.l("DOC.HAS_NOT_SIGN_DOC"), function() {
            var id = Ibos.app.g("docId"),
                $elem = $("#sign_btn");
            OfficialShow.signdoc(id, $elem);
        }, function() {
            window.location.href = document.referrer;
        });
    });


    var moreHtml = function(id, text) {
        return "<div class='fill-hn xac doc-reader-more'>" +
            "<a href='javascript:;' class='link-more' id='" + id + "'>" +
            "<i class='cbtn o-more'></i>" +
            "<span class='ilsep'>" + text + "</span>" +
            "</a>" +
            "</div>";
    };

    // 切换到查阅情况
    var cache = {
        el: $("#isread"),
        tmpl: '<li>'+
                    '<a href="<%= spaceurl %>" class="avatar-circle avatar-circle-small">'+
                        '<img src="<%= avatar %>" data-id="4">'+
                    '</a>'+
                    '<%= text %>'+
                '</li>'
    };
    $("#isread_tab").on("shown", function() {
        if( !cache.el.data("init") ){
            var data = function(){
                var dataUser = Ibos.app.g("readers").split(","),
                    len = dataUser.length,
                    template = "";

                len = dataUser[0] == "" ? 0 : len;

                if( len ){
                    for( var i=0; i<len; i++ ){
                        template += $.template( cache.tmpl, Ibos.data.getUser("u_"+dataUser[i]) );
                    }
                }
                return {
                    num: len,
                    html: template
                };
            }();
            cache.el.data("init", 1);
            cache.el.find("ul").html( data.html );
            cache.el.find(".num").text(data.num);
        }
    });

    // 切换到签收情况
    $("#sign_tab").on("shown", function() {
        OfficialShow.loadSign(Ibos.app.g("docId"), $($.attr(this, "href")), function(res) {
            var contentHeight = $("#art_sing_table").height(),
                html = moreHtml("load_more_sign", Ibos.l("DOC.SEE_MORE_SIGN_PERSON"));
            if (contentHeight > 300) {
                $("#art_sing_table").addClass("art-sign-limit").removeClass("art-sign-auto");
                $("#issign").append(html);
            }
            $("#issign .o-art-pc-phone").tooltip();
        });
    });

    //切换到未签收情况
    $("#no_sign_tab").on("shown", function() {
        var docid = Ibos.app.g("docId");
        OfficialShow.loadNoSign(docid, $($.attr(this, "href")), function(res) {
            var contentHeight = $("#art_no_sing_table").height(),
                html = moreHtml("load_more_no_sign", Ibos.l("DOC.SEE_MORE_NOSIGN_PERSON"));

            if (contentHeight > 300) {
                $("#art_no_sing_table").addClass("art-sign-limit").removeClass("art-sign-auto");
                $("#isnosign").append(html);
            }
            //将未签收人员的id发送给后台
            $("#at_once_remind").on("click", function() {
                var $imgs = $("#art_no_sing_table img"),
                    uids = Official.getImgsID($imgs),
                    docTitle = Ibos.app.g("docTitle"),
                    param = {
                        docid: docid,
                        uids: uids,
                        docTitle: docTitle
                    };

                OfficialShow.op.sendNoSign(param).done(function(res) {
                    if (res.isSuccess) {
                        $("#at_once_remind").html(Ibos.l("DOC.HAS_REMINDED")).addClass("xcn");
                        Ui.tip(Ibos.l("OPERATION_SUCCESS"), "success");
                    }
                });
            });
        });
    });

    // 切换到历史版本
    $("#version_tab").on("shown", function() {
        OfficialShow.loadVersion(Ibos.app.g("docId"), $($.attr(this, "href")), function(res) {
            var liLength = $(".version-list").children("li").length,
                emptyInfo = "<div class='empty-info'></div>";
            if (!liLength) {
                $("#version").append(emptyInfo);
            }
        });
    });


    Ibos.evt.add({
        //点击审核通过
        "approvalDoc": function(param, elem) {
            Ui.confirm(Ibos.l("DOC.CAN_NOT_REVOKE_OPERATE"), function() {
                var docids = Ibos.app.g("docId"),
                    param = {
                        docids: docids
                    };
                OfficialShow.op.approvalDoc(param).done(function(res) {
                    if (res.isSuccess) {
                        Ui.tip(Ibos.l("DOC.APPROVAL_SUCCESS"));
                        window.location.href = document.referrer;
                    } else {
                        Ui.tip(res.info, 'warning');
                    }
                });
            });
        },
        //点击回退，填写回退理由
        rollbackDoc: function(param, elem) {
            Ui.dialog({
                id: "doc_rollback",
                title: Ibos.l("DOC.DOC_ROLLBACK"),
                content: document.getElementById("rollback_reason"),
                cancel: true,
                ok: function() {
                    var docids = Ibos.app.g('docId');
                    var reason = $("#rollback_textarea").val(),
                        param = {
                            docids: docids,
                            reason: reason
                        };

                    OfficialShow.op.backDocs(param).done(function(res) {
                        if (res.isSuccess) {
                            Ui.tip(Ibos.l("OPERATION_SUCCESS"));
                            window.location.href = document.referrer;
                        } else {
                            Ui.tip(Ibos.l("DOC.REASON_IS_EMPTY"), "danger");
                        }
                    });
                }
            });
        },
        // 签收通知
        "signDoc": function(param, elem) {
            Official.op.sign(Ibos.app.g("docId")).done(function(res) {
                if (res.isSuccess) {
                    var btnHtml = "<button type='button' disabled='disabled' class='btn btn-large'> " +
                        "<i class='o-art-handel-sign'></i><span class='dib fsl'>您已签收</span>" +
                        "</button>" +
                        "<span class='dib mls'>签收时间为: " + res.signtime + "</span>",
                        $artClose = $("#art_close");
                    $(elem).parent().html(btnHtml);
                    $artClose.off("click.artClose");
                    $artClose.on("click", function() {
                        window.location.href = document.referrer;
                    });
                    Ui.tip(Ibos.l("OPERATION_SUCCESS"));
                } else {
                    Ui.tip(res.msg, "warning");
                }
            });
        },
        // 下次签收
        "signNextTime": function(param, elem) {
            var $btn = $(elem).parent().find('.btn');
            $btn.removeClass('btn-danger').removeAttr('data-action').attr({
                disabled: "disabled"
            });
            $btn.children('.fsl').html(Ibos.l("DOC.APPROVAL_NEXT_TIME"));
            $btn.children('i').removeClass('o-art-immediately-sign').addClass('o-art-next-sign');
            $(elem).hide();
        },
        // 转发到邮件
        "forwardDocByMail": function() {
            var param = {
                "op": "forwardDoc",
                "relatedid": Ibos.app.g("docId")
            };
            window.location = Ibos.app.url("email/content/add", param);
        },
    });
});
