<?php

use application\core\utils\Ibos;
?>
<!doctype html>
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"> -->
<html lang="en">
    <head>
        <meta charset="<?php echo CHARSET; ?>">
        <title><?php echo $lang['Home page']; ?></title>
        <!-- load css -->
        <link rel="stylesheet" href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>">
        <!-- IE8 fixed -->
        <!--[if lt IE 9]>
            <link rel="stylesheet" href="<?php echo STATICURL; ?>/css/iefix.css?<?php echo VERHASH; ?>">
        <![endif]-->
        <!-- private css -->
        <link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/index.css?<?php echo VERHASH; ?>">
        <link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>">
    </head>
    <body>
        <script>
            var adjustSidebarWidth = function() {
                document.body.className = (window.innerWidth || document.documentElement.clientWidth) > 1150 ? "db-widen" : "";
            }
            adjustSidebarWidth();
            window.onresize = adjustSidebarWidth;
        </script>
        <!-- <div style="height: 100%"> -->
        <div class="db-map" id="db_map" style="display:none;">
            <ul class="dbm-main-list">
                <?php foreach ( $routes as $cate => $routeA ): ?>
                    <li class="dbm-main-item clearfix">
                        <div class="dbm-main-item-name"><?php echo $lang[$cateConfig[$cate]['lang']]; ?></div>
                        <ul class="dbm-sub-list">
                            <?php foreach ( $routeA as $route => $config ): ?>
                                <?php if ( $config['config']['isShow'] ): ?>
                                    <li>
                                        <a href="<?php echo $config['url']; ?>" target="main" title="<?php echo $lang[$config['config']['lang']]; ?>">
                                            <?php echo $lang[$config['config']['lang']]; ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>



        <div class="header">
            <div class="logo" id="logo">
                <h2 class="logo-bg">IBOS</h2>
                <a href="javascript:;" id="db_map_ctrl" class="cbtn db-map-ctrl"></a>
            </div>
            <div class="hdbar clearfix" id="bar">
                <form method="post" autocomplete="off" target="main" action="<?php echo $this->createUrl( 'default/search' ); ?>">
                    <div class="dbsearch">
                        <input type="text" name="keyword" placeholder="<?php echo $lang['Search']; ?>" x-webkit-speech="" speech="" class="input-small">
                        <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>" />
                    </div>
                </form>
                <div class="user-info pull-right">
                    <span class="user-name">
                        <a href="<?php echo Ibos::app()->user->space_url; ?>" target="_blank"><img width="30" height="30" class="radius msep" src="<?php echo Ibos::app()->user->avatar_middle; ?>" title="<?php echo Ibos::app()->user->realname; ?>"></a>
                        <strong><?php echo Ibos::app()->user->realname; ?></strong>
                    </span>
                    <a href="<?php echo Ibos::app()->urlManager->createUrl( '/' ); ?>" target="_blank" class="msep cbtn o-homepage" title="<?php echo Ibos::lang( 'Return to home page' ); ?>"></a>
                    <a href="<?php echo $this->createUrl( 'default/logout', array( 'formhash' => FORMHASH ) ); ?>" class="cbtn o-logout" title="<?php echo $lang['Logout']; ?>"></a>
                </div>
            </div>
        </div>

        <div class="mainer" id="mainer">
            <div class="aside" id="aside">
                <div class="main-nav">
                    <ul id="main_nav">
                        <?php $i = 0; ?>
                        <?php foreach ( $cateConfig as $cate => $config ): ?>
                            <li <?php if ( empty( $i ) ): ?>class="active"<?php
                            endif;
                            $i++;
                            ?>>
                                <a href="<?php echo $config['url']; ?>" target="main" data-href="#db_<?php echo $config['id']; ?>_list" id="db_<?php echo $config['id']; ?>" class="db-<?php echo $config['id']; ?>"><?php echo $lang[$config['lang']]; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="sub-nav" id="sub_nav" style="overflow: auto">
                    <?php $i = $j = 1; ?>
                    <?php foreach ( $routes as $cate => $routeA ): ?>
                        <ul id="db_<?php echo $cateConfig[$cate]['id']; ?>_list" <?php if ( $j !== 1 ): ?>style="display:none;"<?php endif; ?>>
                            <?php foreach ( $routeA as $route => $config ): ?>
                                <?php if ( $config['config']['isShow'] ): ?>
                                    <li <?php if ( $i === 1 ): ?>class="active"<?php endif; ?>>
                                        <?php $i++; ?>
                                        <a href="<?php echo $config['url']; ?>" target="main" title="<?php echo $lang[$config['config']['lang']]; ?>">
                                            <?php echo $lang[$config['config']['lang']]; ?>
                                        </a>
                                        <?php if ( $route == 'module/manager' && !empty( $moduleMenu ) ): ?>
                                        <li class="active">
                                            <ul class="sub-sec-nav">
                                                <?php foreach ( $moduleMenu as $id => $menu ): ?>
                                                    <li>
                                                        <a href="<?php echo Ibos::app()->urlManager->createUrl( $menu['m'] . '/' . $menu['c'] . '/' . $menu['a'] ); ?>" target="main" title="<?php echo $menu['name']; ?>">
                                                            <?php echo $menu['name']; ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                        <?php $j++; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mc" id="mc">
                <iframe src="<?php echo $def; ?>" width="100%" height="100%" frameborder="0" name="main" id="main"></iframe>
            </div>
        </div>

        <!-- </div> -->
        <!-- load js -->
        <script src="<?php echo STATICURL; ?>/js/src/core.js?<?php echo VERHASH; ?>"></script>
        <script src="<?php echo STATICURL; ?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH; ?>"></script>
        <script src="<?php echo STATICURL; ?>/js/src/base.js?<?php echo VERHASH; ?>"></script>
        <script src="<?php echo STATICURL; ?>/js/src/common.js?<?php echo VERHASH; ?>"></script>
        <script src="<?php echo $assetUrl; ?>/js/frame.js?<?php echo VERHASH; ?>"></script>
        <script>
            $(function() {
                var refer = U.getUrlParam().refer;
                if (refer !== "") {
                    var $referElem = $('#sub_nav [href="' + unescape(refer) + '"]');
                    var $subMenu = $referElem.closest("ul");
                    var $nav = $('[data-href="#' + $subMenu.attr("id") + '"]');
                    $nav.click();
                    $referElem.click();
                }

                $(document).on("click", "a[target='main']", function() {
                    var title = '<?php echo $lang['Admin control']; ?> -' + $(this).html();
                    document.title = title;
                })
            });
        </script>
    </body>
</html>