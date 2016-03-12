<!DOCTYPE HTML>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $lang['Install guide']; ?></title>
    <meta name="keywords" content="IBOS" />
    <meta name="generator" content="IBOS 3.0" />
    <meta name="author" content="IBOS Team" />
    <meta name="coryright" content="2014 IBOS Inc." />
    <link href="<?php echo IBOS_STATIC; ?>css/base.css" type="text/css" rel="stylesheet" />
    <link href="<?php echo IBOS_STATIC; ?>css/common.css" type="text/css" rel="stylesheet" />
    <link href="<?php echo IBOS_STATIC; ?>js/lib/artDialog/skins/ibos.css" rel="stylesheet" type="text/css" />
    <link href="static/installation_guide.css" type="text/css" rel="stylesheet" />
    <!-- IE8 fixed -->
    <!-- [if lt IE 9]> -->
    <link rel="stylesheet" href="<?php echo IBOS_STATIC; ?>/css/iefix.css">
    <!--[endif] -->
</head>
<body>
    <div class="main">
        <div class="main-content">
            <div class="main-top posr">
                <i class="o-top-bg"></i>
                <div class="version-info"><?php echo IBOS_VERSION_FULL; ?></div>
            </div>
            <div class="specific-content">
                <div class="db-install-wrap">
                    <form action="index.php?op=dbInit" method="post" class="form-horizontal form-narrow" id="user_form">
                        <table class="table table-info" id="table_info">
                            <tbody>
                                <tr>
                                    <th>管理员账号</th>
                                    <td>
                                        <div class="control-group">
                                            <label class="control-label">用户名<span class="xcr">*</span></label>
                                            <div class="controls">
                                                <input type="text" class="span6" data-type="ADname" id="administrator_name" name="adminName" value="admin" placeholder="请输入密码">
                                                <span id="administrator_name_tip" class="ml nomatch-tip">用户名不能为空</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label"><?php echo $lang['Password']; ?><span class="xcr">*</span></label>
                                            <div class="controls">
                                                <input type="text" class="span6" data-type="ADpassword" id="administrator_password" name="adminPassword" placeholder="请输入密码">
                                                <span id="administrator_password_tip" class="ml nomatch-tip"><?php echo $lang['Password tip']; ?></span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label">手机号<span class="xcr">*</span></label>
                                            <div class="controls">
                                                <input type="text" class="span6" data-type="account" id="administrator_account" name="adminAccount" placeholder="请输入手机号码">
                                                <span id="result_account"></span>
                                                <span id="administrator_account_tip" class="ml nomatch-tip">账号不能为空！</span>
                                                <input type ="hidden" name ="extraData" id ="extraData"/>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>企业信息</th>
                                    <td>
                                        <div class="control-group">
                                            <label class="control-label">企业全称<span class="xcr">*</span></label>
                                            <div class="controls">
                                                <input type="text" name="fullname" class="span6" id="full_name" data-type="fullname" placeholder="请使用工商营业执照登记名称" />
                                                <span id="full_name_tip" class="ml nomatch-tip">企业全称不能为空！</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label">企业简称<span class="xcr">*</span></label>
                                            <div class="controls">
                                                <input type="text" name="shortname" class="span6" id="short_name" data-type="shortname" placeholder="企业名称缩写，通常4-8个中文缩写" />
                                                <span id="short_name_tip" class="ml nomatch-tip">企业简称必须为4-8个字！</span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label">企业代码<span class="xcr">*</span></label>
                                            <div class="controls">
                                                <input type="text" name="qycode" class="span6" id="qy_code" data-type="qycode" placeholder="通常为4~16位英文缩写，不可更改 " />
                                                <span id="qy_code_result"></span>
                                                <span id="qy_code_tip" class="ml nomatch-tip">企业代码不能为空！</span>
                                                <input type="hidden" id="qy_code_verify" value="1" data-status="unlink" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php echo $lang['Db info']; ?></th>
                                    <td>
                                        <div class="control-group">
                                            <label class="control-label"><?php echo $lang['Db username']; ?><span class="necessary-write">*</span></label>
                                            <div class="controls">
                                                <input type="text" class="span6" data-type="username" id="database_name" name="dbAccount" value="<?php echo $dbInitData['username']; ?>">
                                                <span id="database_name_tip" class="ml nomatch-tip"><?php echo $lang['Dbaccount not empty']; ?></span>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label"><?php echo $lang['Db password']; ?><span class="necessary-write">*</span></label>
                                            <div class="controls">
                                                <input type="text" class="span6" data-type="DBpassword" id="database_password" name="dbPassword" value='<?php echo $dbInitData['password']; ?>'>
                                                <span id="database_password_tip" class="ml nomatch-tip"><?php echo $lang['Password not empty']; ?></span>
                                            </div>
                                        </div>
                                        <div class="mbs">
                                            <a href="javascript:;" class="dib show-info">
                                                <span class="dib"><?php echo $lang['Show more']; ?></span>
                                                <i class="o-pack-down mlm"></i>
                                            </a>
                                        </div>
                                        <div class="hidden-info">
                                            <div class="control-group">
                                                <label class="control-label"><?php echo $lang['Db host']; ?></label>
                                                <div class="controls">
                                                    <input type="text" class="span6" id="database_server" name="dbHost" value="<?php echo $dbInitData['host']; ?><?php
                                                    if (!empty($dbInitData['port'])) {
                                                        echo ':' . $dbInitData['port'];
                                                    }
                                                    ?>">
                                                    <span class="write-tip">一般为127.0.0.1,比localhost快</span>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label"><?php echo $lang['Db name']; ?></label>
                                                <div class="controls">
                                                    <input type="text" class="span6" id="dbname" name="dbName" value="<?php echo $dbInitData['dbname']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="control-group install-choose" id="tablepre_exist_tip">
                                            <label class="control-label"><span class="constraint-install"><?php echo $lang['Mandatory installation']; ?></span></label>
                                            <div class="controls">
                                                <div class="constraint-label">
                                                    <label class="checkbox constraint-check">
                                                        <input type="checkbox" name="enforce" id="enforce" ><?php echo $lang['Del data']; ?>
                                                    </label>
                                                </div>
                                                <div class="constraint-tip">
                                                    <span id="enforce_info">
                                                        <?php echo $lang['Dbinfo forceinstall invalid']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="content-foot nbt clearfix">
                            <div class="pull-left protocol-check">
                                <span class="fss">
                                    点击“立即安装”即同意
                                    <a href="http://www.ibos.com.cn/" target="_blank">《IBOS用户使用协议》</a>
                                </span>
                            </div>
                            <div class="pull-right">
                                <label class="checkbox checkbox-inline mbz">
                                    <input type="checkbox" id="ext_data" name="extData" value="1" checked="checked" />
                                    <span><?php echo $lang['Suc tip']; ?></span>
                                </label>
                                <label class="checkbox checkbox-inline disabled user-defined">
                                    <input type="checkbox" name="custom" id="user_defined" disabled/>
                                    <span><?php echo $lang['Custom module']; ?></span>
                                </label>
                                <!--1.当未勾选自定义模块时,按钮显示为立即安装,点击后去往安装页面.
                                    2.当勾选自定义模块后,按钮显示为下一步,点击后去往模块设置页面.
                                    js会动态改变a的href值,需要将url写入到js中-->
                                <input type="hidden" name="submitDbInit" value="1" />
                                <button type="button" class="btn btn-large btn-primary btn-install" id="btn_install"><?php echo $lang['Install now']; ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
    <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
    <script src='<?php echo IBOS_STATIC; ?>js/lib/artDialog/artDialog.min.js'></script>
    <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
    <script src="static/create_data.js"></script>
    <script>
        (function() {
            $("#btn_install").click(function() {
                if ($("#enforce").is(":checked")) {
                    $("#user_form").submit();
                } else {
                    var dbInfo = {
                        dbHost: $("#database_server").val(),
                        dbAccount: $("#database_name").val(),
                        dbPassword: $("#database_password").val(),
                        dbName: $("#dbname").val(),
                        tablePre: $("#tableprefix").val()
                    };
                    $.post("index.php?op=tablepreCheck", dbInfo, function(res) {
                        if (res.isSuccess) {
                            $("#user_form").submit();
                        } else {
                            if (res.tableExist === true) {
                                // 显示强制数据库插入信息
                                $("#tablepre_exist_tip").css('display', 'block');
                            } else {
                                window.location.href = "index.php?op=installResult&res=0&msg=" + res.msg;
                            }
                        }
                    }, 'json');
                }
            });

            $("#user_defined").on("change", function() {
                if (this.checked) {
                    $("#ext_data").label("uncheck").label("disable");
                } else {
                    $("#ext_data").label("enable");
                }
            });

            $("#ext_data").on("change", function() {
                if (this.checked) {
                    $("#user_defined").label("uncheck").label("disable");
                } else {
                    $("#user_defined").label("enable");
                }
            });
        })();
    </script>
</body>
</html>