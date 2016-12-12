<?php

use application\core\utils\Ibos;

?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="<?php echo CHARSET; ?>">
        <title><?php echo Ibos::app()->setting->get('title'); ?></title>
        <link rel="shortcut icon" href="<?php echo STATICURL; ?>/image/favicon.ico?<?php echo VERHASH; ?>">
        <link rel="apple-touch-icon-precomposed" href="<?php echo STATICURL; ?>/image/common/ios_icon.png">
        <meta name="generator" content="IBOS <?php echo VERSION; ?>" />
        <meta name="author" content="IBOS Team" />
        <meta name="copyright" content="2013 IBOS Inc." />
        <!-- IE 8 以下跳转至浏览器升级页 -->
        <!--[if lt IE 8]>
            <script>
                window.location.href = "<?php echo Ibos::app()->urlManager->createUrl("main/default/unsupportedBrowser"); ?>"
            </script>
        <![endif]-->
        <!-- load css -->
        <link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/print.css" />

    </head>
    <body class="ibbody" style="background: transparent;">
        <div class="ibcontainer">
            <!-- load script end -->
            <div class="wrap" id="mainer">
                <div class="mtw">
                    <h2 class="mt pull-left"><?php echo $pageTitle; ?></h2>
                    <span class="pull-right"><?php echo Ibos::app()->setting->get('lunar'); ?></span>
                </div>

                <!-- Mainer -->
                <!-- 这里就是内容模板 -->
                <div class="mpc clearfix" id="page_content">

                </div>
                <!-- 这里就是内容模板 -->

                <!-- Mainer end -->
            </div>
        </div>
    </body>
</html>