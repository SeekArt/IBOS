<!DOCTYPE HTML>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $lang['Install guide']; ?></title>
    <meta name="keywords" content="IBOS" />
    <meta name="generator" content="IBOS 2.1 (Revolution!)" />
    <meta name="author" content="IBOS Team" />
    <meta name="coryright" content="2013 IBOS Inc." />
    <link href="<?php echo IBOS_STATIC; ?>css/base.css" type="text/css" rel="stylesheet" />
    <link href="<?php echo IBOS_STATIC; ?>css/common.css" type="text/css" rel="stylesheet" />
    <link href="<?php echo IBOS_STATIC; ?>js/lib/artDialog/skins/ibos.css" type="text/css" rel="stylesheet" />
    <link href="static/installation_guide.css" type="text/css" rel="stylesheet" />
    <!-- IE8 fixed -->
    <!--[if lt IE 9]>
        <link rel="stylesheet" href="<?php echo IBOS_STATIC; ?>/css/iefix.css">
    <![endif]-->
    <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
    <script>
        (function(){
            var href = location.href,
                SITE_URL = href.slice(0, href.indexOf("/install"));
            var url = SITE_URL + "/api/authlogin.php",
                authLogin = function(){
                    $.post(url, {uid: 1}, function(res){
                        if(res.code){
                            var iframe = document.getElementById('binding');
                            iframe.src = SITE_URL + "/?r=dashboard/cobinding/index&isInstall=1";
                        }else{
                            authLogin();
                        }
                    }, 'json');
                };
            authLogin();
        })();
    </script>
</head>
<body>
    <div class="main">
        <div class="main-content">
            <div class="main-top posr">
                <i class="o-top-bg"></i>
                <div class="version-info"><?php echo IBOS_VERSION_FULL; ?></div>
            </div>
            <div>
                <div class="content-header">
                    <div class="install-success clearfix">
                        <i class="o-install-success"></i>
                        <span class="">恭喜，IBOS安装成功！</span>
                    </div>
                    <a class="btn btn-large pull-right install-login" href="javascript:location.href=SITE_URL;">进入IBOS</a>
                </div>
                <div class="binding-mc">
                    <iframe src="" name="binding" id="binding" width="100%" height="100%" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
    <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
    <script>
        var _ib = _ib || [];
        _ib.push(['authkey', '<?php //echo $config['security']['authkey']; ?>']);
        _ib.push(['domain', '<?php //echo $_SERVER['SERVER_NAME']; ?>']);
        _ib.push(['ip', '<?php //echo $_SERVER['REMOTE_ADDR']; ?>']);
        _ib.push(['type', 'install']);
        (function() {
            var ib = document.createElement('script');
            ib.type = 'text/javascript';
            ib.async = true;
            ib.src = 'http://www.ibos.com.cn/Public/static/ib.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ib, s);
        })();

        var href = location.href,
            SITE_URL = href.slice(0, href.indexOf("/install"));

        Ibos.app.s("SITE_URL", SITE_URL);
    </script>
    <script src="static/create_data.js"></script>
</body>
</html>