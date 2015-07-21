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
</head>
<body>
    <div class="main">
        <div class="main-content">
            <div class="main-top posr">
                <i class="o-top-bg"></i>
                <div class="version-info"><?php echo IBOS_VERSION_FULL; ?></div>
            </div>
            <div class="specific-content">
				<div class="mlg nht">
					<div class="dib vam">
						<i class="o-install-success"></i>
					</div>
					<div class="dib vam mls">
						<p class="mb"><i class="o-success-tip"></i></p>
					</div>
				</div>
				<div class="content-foot clearfix">
					<a href="../index.php" class="btn btn-large pull-right btn-large btn-warning"><?php echo $lang['Complete']; ?></a>
				</div>
            </div>
        </div>
    </div>
    <script src="<?php echo IBOS_STATIC; ?>js/src/core.js"></script>
    <script src="<?php echo IBOS_STATIC; ?>js/src/base.js"></script>
    <script src="<?php echo IBOS_STATIC; ?>js/src/common.js"></script>
    <script type="text/javascript">
		var _ib = _ib || [];
		_ib.push(['authkey', '<?php echo $config['security']['authkey']; ?>']);
		_ib.push(['domain', '<?php echo $_SERVER['SERVER_NAME']; ?>']);
		_ib.push(['ip', '<?php echo $_SERVER['REMOTE_ADDR']; ?>']);
		_ib.push(['type', 'install']);
		(function() {
			var ib = document.createElement('script');
			ib.type = 'text/javascript';
			ib.async = true;
			ib.src = 'http://www.ibos.com.cn/Public/static/ib.js';
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ib, s);
		})();
    </script>
</body>
</html>