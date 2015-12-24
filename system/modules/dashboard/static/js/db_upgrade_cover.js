(function () {
    // 这里的用法是为了阻止用户在升级过程中误操作离开页面
    Ibos.checkFormChange();
    $(document.body).trigger("formchange");

    var $info = $('#upgrade_info'),
            coverUrl = Ibos.app.g("coverUrl"),
            upgrade = {
                /**
                 * 通用步骤执行方法
                 * @returns {mixed}
                 */
                getAndPass: function (target) {
                    $.get(target, {coverStart: true}, function (data) {
                        return upgrade.processingStep(data.step, data.data);
                    }, 'json');
                },
                /**
                 * 步骤分发
                 * @param {integer} step 当前步骤
                 * @param {object} data 服务器返回操作数据
                 * @returns {@exp;upgrade@call;processingShowUpgrade|@exp;upgrade@call;processingCompareFile|@exp;upgrade@call;processingDownloadFile}
                 */
                processingStep: function (step, data) {
                    switch (step) {
                        case 4:
                            upgrade.processingUpdateFile(data);
                            break;
                        case 5:
                            $(document.body).trigger("ignoreformchange");
                            window.location.href = data.url;
                            break;
                        default:
                            break;
                    }
                },
                /**
                 * 第四步：更新文件
                 * @param {object} data
                 * @returns {@exp;upgrade@call;ftpSetup}
                 */
                processingUpdateFile: function (data) {
                    switch (data.status) {
                        // 错误信息，提供重试与设置选项
                        case 'no_access':
                        case 'upgrade_ftp_upload_error':
                        case 'upgrade_copy_error':
                            $info.html($.template('update_confirm', {
                                msg: data.msg,
                                retryUrl: data.retryUrl,
                                ftpUrl: data.ftpUrl
                            }));
                            break;
                            // 提示信息，显示并执行下一步
                        case 'upgrade_backuping':
                        case 'upgrade_backup_complete':
                            $info.text(data.msg);
                            upgrade.getAndPass(data.url);
                            break;
                            // 更新数据库，跳转到另外的处理文件
                        case 'upgrade_database':
                            $(document.body).trigger("ignoreformchange");
                            alert(data.msg);
                            window.location.href = data.url;
                            break;
                            // 更新文件完成，直接跳转到最后一步
                        case 'upgrade_file_successful':
                            upgrade.getAndPass(data.url);
                            break;
                            // 设置ftp
                        case 'ftpsetup':
                            return upgrade.ftpSetup(data.url);
                            break;
                            // 备份错误
                        case 'upgrade_backup_error':
                            $info.text(data.msg);
                            break;
                        default:
                            break;
                    }
                },
                /**
                 * 设置FTP
                 * @param {string} target 提交的地址
                 * @returns {void}
                 */
                ftpSetup: function (target) {
                    $.artDialog({
                        title: Ibos.l("UPGRADE.FTP_SETTING"),
                        content: $.template('ftp_setup'),
                        id: 'sys_ftp_setup',
                        cancel: true,
                        init: function () {
                            var api = this;
                            api.DOM.content.find('[data-toggle="switch"]').iSwitch();
                        },
                        ok: function () {
                            $.post(target, $('#sys_ftp_form').serializeArray(), function (data) {
                                return upgrade.processingStep(data.step, data.data);
                            }, 'json');
                        },
                        close: function () {
                            $("#ftp_setup_btn").button('reset');
                        }
                    });
                }
            };

    // 步骤分发绑定操作
    $('[data-act="processStep"]').live('click', function () {
        $(this).button('loading');
        var target = $(this).data('target');
        return upgrade.getAndPass(target);
    });

    // 初始化加载页面就开始开始更新文件
    $.get(coverUrl, {coverStart: true}, function (data) {
        upgrade.processingUpdateFile(data.data);
    });

})();
