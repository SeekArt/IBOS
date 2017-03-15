(function() {
    // 这里的用法是为了阻止用户在升级过程中误操作离开页面
    Ibos.checkFormChange();
    $(document.body).trigger("formchange");

    var coverUrl = Ibos.app.g("coverUrl"),
        upgrade = {
            /**
             * 第四步：更新文件
             * @param {object} data
             * @returns {@exp;upgrade@call;ftpSetup}
             */
            processingUpdateFile: function(data) {
                switch (data.status) {
                    // 更新数据库，跳转到另外的处理文件
                    case 'upgrade_database':
                        $(document.body).trigger("ignoreformchange");
                        Ui.openFrame(data.url, {
                            title: Ibos.l("UPGRADE.EXCUTE_DATA"),
                            width: '570px',
                            ok: function() {
                                var $inputs = $(this.iframe.contentDocument).find('input'),
                                    params = {};

                                // 锁定面板
                                this.DOM.content.waiting(null, 'normal', true);
                                this.DOM.buttons.find('.btn').prop('disabled', true);

                                $inputs.each(function(i, v) {
                                    params[v.name] = v.value;
                                });

                                upgrade.executeSql(params).done(function(res) {
                                    if (res.isSuccess) {
                                        window.location.href = Ibos.app.url('dashboard/upgrade/index', {
                                            op: 'patch',
                                            step: 4
                                        });
                                    } else {
                                        window.location.href = Ibos.app.url('dashboard/upgrade/showUpgradeErrorMsg', {
                                            msg: res.msg
                                        });
                                    }
                                });

                                return false;
                            },
                            cancel: false
                        });
                        break;
                        // 更新文件完成，直接跳转到最后一步
                    case 'upgrade_file_successful':
                        $(document.body).trigger("ignoreformchange");
                        window.location.href = data.url;
                        break;
                    default:
                        break;
                }
            },
            /*
             * 请求执行数据库
             */
            executeSql: function(param) {
                var url = '/upgrade/index.php?op=execute';

                return $.post(url, param, $.noop, 'json');
            }
        };

    // 初始化加载页面就开始开始更新文件
    $.get(coverUrl, {
        coverStart: true
    }, function(data) {
        upgrade.processingUpdateFile(data.data);
    });

})();