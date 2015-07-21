<?php

use application\core\utils\Env;
use application\core\utils\IBOS;
?>

<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/upgrade.css">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Online upgrade']; ?></h1>
    </div>
    <div>
        <form action="" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Online upgrade']; ?></h2>
                <div class="">
                    <div class="brc mb clearfix">
                        <a class="finish-step" href="javascript:;">
                            <i class="o-finish-step"></i>
                            <span class="ml"><?php echo $lang['Upgrade get file'] ?></span>
                        </a>
                        <a class="finish-step" href="javascript:;">
                            <i class="o-finish-step"></i>
                            <span class="ml"><?php echo $lang['Upgrade download'] ?></span>
                        </a>
                        <a class="finish-step" href="javascript:;">
                            <i class="o-finish-step"></i>
                            <span class="ml xcg"><?php echo $lang['Upgrade compare'] ?></span>
                        </a>
                        <a class="active" href="javascript:;">
                            <span class="circle">4</span>
                            <span class="ml xcg"><?php echo $lang['Upgradeing'] ?></span>
                        </a>
                        <a href="javascript:;">
                            <span class="circle">5</span>
                            <span class="ml xcg"><?php echo $lang['Upgrade complete'] ?></span>
                        </a>
                    </div>
                    <div class="main-content mtg">
                        <div>
                            <i class="o-chicking-image"></i>
                        </div>
                        <p class="version-title mbl mtl" id="upgrade_info"><?php echo $lang['Updateing'] ?></p>
                        <div class="progress progress-striped active">
                            <div class="progress-bar" role="progressbar" style="width: 100%">
                                <span class="sr-only"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--错误重新设置模板-->
<script type="text/ibos-template" id="update_confirm">
    <div>
    <div class="alert alert-error>"><%=msg%></div>
    <button type="button" data-target="<%=retryUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>" autocomplete="off" data-act="processStep" class="btn btn-large">重试</button>
    <button type="button" data-target="<%=ftpUrl%>" data-loading-text="<?php echo $lang['Action processing']; ?>" autocomplete="off" data-act="processStep" class="btn btn-large mls" id="ftp_setup_btn">设置ftp</button>
    </div>
</script>
<!--设置ftp-->
<script type="text/ibos-template" id="ftp_setup">
    <form id="sys_ftp_form" method="post" class="form-horizontal">
    <div class="control-group">
    <label class="control-label"><?php echo $lang['Enabled ssl']; ?></label>
    <div class="controls">
    <input type="checkbox" name="ftp[ssl]" value="1" data-toggle="switch" class="visi-hidden" />
    </div>
    </div>
    <div class="control-group">
    <label class="control-label"><?php echo $lang['Ftp host']; ?></label>
    <div class="controls">
    <input type="text" name="ftp[host]" />
    </div>
    </div>
    <div class="control-group">
    <label class="control-label"><?php echo $lang['Ftp port']; ?></label>
    <div class="controls">
    <input type="text" name="ftp[port]" value="25" />
    </div>
    </div>
    <div class="control-group">
    <label class="control-label"><?php echo $lang['Ftp user']; ?></label>
    <div class="controls">
    <input type="text" name="ftp[username]" />
    </div>
    </div>
    <div class="control-group">
    <label class="control-label"><?php echo $lang['Ftp pass']; ?></label>
    <div class="controls">
    <input type="text" name="ftp[password]" />
    </div>
    </div>
    <div class="control-group">
    <label class="control-label"><?php echo $lang['Ftp pasv']; ?></label>
    <div class="controls">
    <input type="checkbox" name="ftp[pasv]" value="1" data-toggle="switch" class="visi-hidden" />
    </div>
    </div>
    </form>
</script>
<script type="text/javascript">
    var _ib = _ib || [];
    _ib.push(['authkey', '<?php echo IBOS::app()->setting->get( 'config/security/authkey' ); ?>']);
    _ib.push(['ip', '<?php echo Env::getClientIp(); ?>']);
    _ib.push(['from', '<?php echo $from; ?>']);
    _ib.push(['to', '<?php echo $to; ?>']);
    _ib.push(['fullname', '<?php echo IBOS::app()->setting->get( 'setting/unit/fullname' ); ?>']);
    _ib.push(['type', 'upgrade']);
    (function () {
        var ib = document.createElement('script');
        ib.type = 'text/javascript';
        ib.async = true;
        ib.src = 'http://www.ibos.com.cn/Public/static/ib.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ib, s);
    })();
</script>
<script>
    (function () {
        // 这里的用法是为了阻止用户在升级过程中误操作离开页面
        Ibos.checkFormChange();
        $(document.body).trigger("formchange");

        var $info = $('#upgrade_info'),
                coverUrl = "<?php echo $coverUrl; ?>",
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
                            title: "<?php echo $lang['Ftp setting']; ?>",
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
</script>