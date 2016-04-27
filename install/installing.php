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
        <link href="static/installation_guide.css" type="text/css" rel="stylesheet" />
        <!-- IE8 fixed -->
        <!--[if lt IE 9]>
            <link rel="stylesheet" href="<?php echo IBOS_STATIC; ?>/css/iefix.css">
        <![endif]-->
    </head>
    <body>
        <div class="main">
            <div class="main-content">
                <div class="main-top posr">
                    <i class="o-top-bg"></i>
                    <div class="version-info"><?php echo IBOS_VERSION_FULL; ?></div>
                </div>
                <div class="specific-content">
                    <div>
                        <div class="mlst">
                            <div class="dib vam">
                                <p class="mb"><i class="o-install-tip"></i></p>
                                <p class="mbs fsm">全新IBOS V3支持绑定微信企业号，提供多项办</p>
                                <p class="fsm">公应用，充分满足企业移动办公需求。</p>
                            </div>
                            <div class="dib">
                                <i class="o-installing-tip"></i>
                            </div>
                        </div>
                        <div class="clearfix mlg">
                            <div class="progress progress-striped span11 pull-left progress-area">
                                <div id="progressbar" class="progress-bar" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                </div>
                            </div>
                            <div class="pull-right rate-of-progress">
                                <span class="xcbu" id="show_process">0%</span>
                            </div>
                        </div>
                        <div class="project-tip" id="install_info"><?php echo $lang['Installing info']; ?></div>
                    </div>
                    <div class="content-foot clearfix">
                        <button type="button" class="btn btn-large pull-right disabled" disabled><?php echo $lang['Installing']; ?></button>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
        <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
        <script>
            (function() {
                var installModules = '<?php echo json_encode( $installModules ); ?>',
                        installModulesObj = JSON.parse(installModules),
                        installUrl = "index.php?op=installing&init=1&installBegin=1",
                        $progressbar = $("#progressbar"),
                        $show_process = $("#show_process"),
                        $mod_name = $("#mod_name"),
                        $install_info = $("#install_info");
                // 安装模块方法
                function install(module) {
                    $.post(installUrl, {installModules: installModules, installingModule: module}, function(res) {
                        if (res.complete) {
                            $progressbar .css("width", res.process);
                            $show_process.text(res.process);
                            $install_info.text("<?php echo $lang['Install complete'] ?>");
                            window.location.href = "index.php?op=installResult&init=1&res=1";
                        } else {
                            if (res.isSuccess) {
                                $progressbar .css("width", res.process);
                                $show_process.text(res.process);
                                $mod_name.text(res.nextModuleName);
                                install(res.nextModule);
                            } else {
                                window.location.href = "index.php?op=installResult&init=1&res=0&msg=" + res.msg;
                            }
                        }
                    }, 'json');
                }
                // 初始化页面开始安装模块
                var firstModuleName = "<?php echo getModuleName( $installModules['0'] ); ?>";
                $mod_name.text(firstModuleName);
                install(installModulesObj[0]);
            })();
        </script>
    </body>
</html>