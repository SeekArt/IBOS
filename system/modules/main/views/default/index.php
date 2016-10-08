<?php

use application\core\utils\Ibos;
use application\core\utils\Module;
use application\core\utils\Url;
use application\modules\main\utils\Main;
?>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/introjs/introjs.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/index.css?<?php echo VERHASH; ?>">
<div class="mtw">
    <div class="mtw-portal-nav-wrap">
        <ul class="portal-nav clearfix">
            <li class="active">
                <a href="<?php echo Ibos::app()->urlManager->createUrl( 'main/default/index' ); ?>">
                    <i class="o-portal-office"></i>
                    办公门户
                </a>
            </li>
            <li>
                <a href="<?php echo Ibos::app()->urlManager->createUrl( 'weibo/home/index' ); ?>">
                    <i class="o-portal-personal"></i>
                    个人门户
                </a>
            </li>
            <?php if ( Module::getIsEnabled( 'app' ) ): ?>
                <li >
                    <a href="<?php echo Ibos::app()->urlManager->createUrl( 'app/default/index' ); ?>">
                        <i class="o-portal-app"></i>
                        常用工具
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <span class="pull-right"><?php echo Ibos::app()->setting->get( 'lunar' ); ?></span>
</div>
<div>
    <!-- 常用菜单 -->
    <div class="cm-menu mbs" id="cm_menu">
        <ul class="cm-menu-list clearfix">
            <?php if ( !empty( $menus['commonMenu'] ) ): ?>
                <?php foreach ( $menus['commonMenu'] as $index => $menu ): ?>
                    <?php if ( !$menu['iscustom'] ): ?>
                        <li data-module="<?php echo $menu['module']; ?>">
                            <a href="<?php echo Ibos::app()->urlManager->createUrl( $menu['url'] ); ?>" title="<?php echo $menu['description']; ?>" <?php if ( $menu['openway'] == 0 ): ?>target="_blank"<?php endif; ?>>
                                <div class="posr">
                                    <img width="64" height="64" class="mbs" src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                                    <span class="bubble" data-bubble="<?php echo $menu['module']; ?>"></span>
                                </div>
                                <div class="cm-menu-title"><?php echo $menu['name']; ?></div>
                            </a>
                        </li>
                    <?php else: ?>
                        <li data-module="<?php echo $menu['module']; ?>">
                            <a href="<?php echo Url::getUrl( $menu['url'] ); ?>" title="<?php echo $menu['description']; ?>" <?php if ( $menu['openway'] == 0 ): ?>target="_blank"<?php endif; ?>>
                                <div class="posr">
                                    <img width="64" height="64" class="mbs" src="<?php echo 'data/icon/' . $menu['icon']; ?>">
                                    <span class="bubble" data-bubble="<?php echo $menu['module']; ?>"></span>
                                </div>
                                <div class="cm-menu-title"><?php echo $menu['name']; ?></div>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <a href="javascript:;" class="menu-opt-area" data-action="setupMenu" title="设置常用菜单">
            <i class="menu-opt-btn"></i>
        </a>
        <!-- <i class="o-menu-new-tip" id="menu_new_tip"></i> -->
    </div>
    <div id="module_panel" class="in-mod-wrap clearfix">
        <div class="mbox add-mbox" id="add_mbox_block" data-action="openManager" style="display:none;">
            <div class="mbox-header add-mbox-header"></div>
            <div class="mbox-body">
                <div class="fill-hn xac">
                    <a href="javascript:;" id="manager_ctrl">
                        <i class="o-mudule-add mudule-opt-tip"></i>                   
                        <p class="mudule-add-tip">添加小部件</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="in_operate" class="in-operate">
    <a href="javascript:;" class="o-in-totop" data-action="totop" title="<?php echo $lang['Back to top']; ?>"></a>
</div>
<div class="mbox in-mu" id="in_mu">
    <div class="in-inmenu rdt">
        <ul class="in-inmenu-list clearfix">
            <?php if ( !empty( $menus['commonMenu'] ) ): ?>
                <?php foreach ( $menus['commonMenu'] as $index => $menu ): ?>
                    <?php if ( !$menu['iscustom'] ): ?>
                        <li>
                            <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo Ibos::app()->urlManager->createUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>" 
                                 data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                                <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                                <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                                <div title="<?php echo $menu['description']; ?>">
                                    <img width="64" height="64" src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                                </div>
                                <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                            </div>
                        </li>
                    <?php else: ?>
                        <li>
                            <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo Url::getUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>" 
                                 data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo 'data/icon/' . $menu['icon']; ?>">
                                <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                                <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                                <div title="<?php echo $menu['description']; ?>">
                                    <img width="64" height="64" src="
                                         <?php echo 'data/icon/' . $menu['icon']; ?>">
                                </div>
                                <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php $emptyLi = 8 - count( $menus['commonMenu'] ); ?>
            <?php for ( $i = 1; $i <= $emptyLi; $i++ ): ?>
                <li></li>
            <?php endfor; ?>
        </ul>
    </div>
    <div class="in-outmenu">
        <h5><?php echo $lang['Without adding modules']; ?></h5>
        <div class="in-outmenu-list clearfix">
            <?php if ( !empty( $menus['notUsedMenu'] ) ): ?>
                <?php foreach ( $menus['notUsedMenu'] as $index => $menu ): ?>
                    <?php if ( !$menu['iscustom'] ): ?>
                        <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo Ibos::app()->urlManager->createUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>"
                             data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                            <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                            <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                            <div title="<?php echo $menu['description']; ?>">
                                <img width="64" height="64" src="<?php echo Ibos::app()->assetManager->getAssetsUrl( $menu['module'] ) . '/image/icon.png'; ?>">
                            </div>
                            <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                        </div>
                    <?php else: ?>
                        <div class="in-menu-item" data-mod="<?php echo $menu['id']; ?>" data-href="<?php echo Url::getUrl( $menu['url'] ); ?>" data-title="<?php echo $menu['name']; ?>"
                             data-desc="<?php echo $menu['description']; ?>" data-src="<?php echo 'data/icon/' . $menu['icon']; ?>">
                            <a href="javascript:;" class="o-mu-plus" data-action="addToCommonMenu"></a>
                            <a href="javascript:;" class="o-mu-minus" data-action="removeFromCommonMenu"></a>
                            <div title="<?php echo $menu['description']; ?>">
                                <img width="64" height="64" src="<?php echo 'data/icon/' . $menu['icon']; ?>">
                            </div>
                            <p class="in-mu-title xac fss"><?php echo $menu['name']; ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="fill-sn clearfix">
        <div class="pull-right">
            <!--管理员，设置通用菜单按钮-->
            <?php if ( Ibos::app()->user->uid == 1 ): ?>
                <a href="javascript:;" class="btn" style="" data-action="setDefaultMent"><?php echo $lang['Set as default menu']; ?></a>
            <?php endif; ?>
            <a href="javascript:;" class="btn" data-action="restoreDefaultMenu"><?php echo $lang['Restore default settings']; ?></a>
            <a href="javascript:;" class="btn btn-primary" data-action="saveCommonMenu"><?php echo $lang['Save']; ?></a>
        </div>
    </div>
</div>
<!-- Template: 模块管理器模板 -->
<script type="text/template" id="tpl_manager">
    <div class="mbox mod-manager">
    <div class="mbox-header">
    <div class="fill-hn">
    <strong><%= U.lang("MAIN.MANAGE_MY_MODULE") %></strong>
    </div>
    </div>
    <div>
    <ul class="slist mod-list">
    <li>
    <% for(var i = 0; i < data.length; i++) { %>
    <% if(i !== 0 && i % 2 === 0) { %>
    </li><li>
    <% } %>
    <label class="checkbox">
    <input type="checkbox" value="<%= data[i].name %>" data-title="<%= data[i].title %>">
    <%= data[i].title%>
    </label>
    <% } %>
    </li>
    </ul>
    </div>
    <div class="mod-manager-footer">
    <a href="javascript:;" data-act="reset">
    <i class="o-cancel"></i>
    <%= U.lang("MAIN.RESET_SETTINGS") %>
    </a>
    </div>
    </div>
</script>
<!-- Template:常用菜单项模板 -->
<script type="text/template" id="mu_item_tpl">
    <li data-module="<%=mod%>">
    <a href="<%=href%>" title="<%=desc%>">
    <div class="posr">
    <img width="64" height="64" class="mbs" src="<%=src%>">
    <span class="bubble" data-bubble="<%=mod%>"></span>
    </div>
    <div class="cm-menu-title"><%=title%></div>
    </a>
    </li>
</script>
<script>
    Ibos.app.s({
        "guideNextTime": <?php
            if ( Main::getCookie( 'guideNextTime' ) == md5( Ibos::app()->user->uid ) ) {
                echo 1;
            } else {
                echo 0;
            }
            ?>,
        "assetUrl": "<?php echo $assetUrl; ?>",
        "refreshInterval": 10000
    })
</script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.mbox.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/index.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/main_default_index.js?<?php echo VERHASH; ?>'></script>
<script>
    (function() {
        // 默认配置，已安装的模块，由PHP端判断输出
        var moduleInstalled = [
<?php foreach ( $widgetModule as $index => $module ): ?>
                {name: "<?php echo $index; ?>", title: "<?php echo $module['title']; ?>", show: true},
<?php endforeach; ?>
        ];

        // 按上面的输出方式在Ie 8下 数组多出一位null，所以要去掉
        if (moduleInstalled[moduleInstalled.length - 1] == null) {
            moduleInstalled.pop();
        }

        var getModuleSettings = (function() {
            var moduleSettings = $.parseJSON('<?php echo $moduleSetting; ?>');
            return function(name, options) {
                if (!moduleSettings[name]) {
                    $.error("(getModuleSettings): 不存在标识为" + name + "的模块配置")
                }
                return $.extend(true, {}, moduleSettings[name], options);
            };
        })();


        // 获取已安装模块名称组合的字符串
        var getInstalledModuleName = function() {
            return $.map(moduleInstalled, function(d, i) {
                return d.name;
            }).join(",");
        }

        var loadModuleUrl = Ibos.app.url("main/api/loadmodule"),
            loadNewUrl = Ibos.app.url("main/api/loadnew"),
            refreshInterval = Ibos.app.g("refreshInterval");

        // 配置存储管理器
        var storage = moduleStorage(moduleInstalled);
        // 模块面板
        var $modulePanel = $("#module_panel");
        panel = modulePanel($modulePanel, moduleInstalled);



        // 拖拽配置
        var dragSettings = {handle: ".mbox-header", tolerance: "pointer", cancel: "#add_mbox_block"},
        dragUpdateHandler = function() {
            var srg = [];
            $modulePanel.find(".mbox").each(function() {
                var name = $.attr(this, "data-name");
                srg.push({
                    name: name
                });
                storage.set(srg);
            });
        };

        //重置添加小部件添加按钮    
        var resetAddMbox = function(){
            var $mbox = $("#module_panel").children(".mbox"),
                $addMbox = $("#add_mbox_block"),
                mlength = $mbox.length,
                installModuleLength = moduleInstalled.length,
                hasMeetingBox = $mbox.hasClass("meeting-mbox"),
                isEven = mlength % 2;

                if(hasMeetingBox){
                     installModuleLength = installModuleLength + 1; 
                }

                if(mlength <= installModuleLength){
                    if(isEven){
                        $addMbox.addClass("small-add-mbox")
                            .find("i.mudule-opt-tip").removeClass("o-mudule-add").addClass("o-plus");
                    }else{
                        $addMbox.removeClass("small-add-mbox")
                            .find("i.mudule-opt-tip").removeClass("o-plus").addClass("o-mudule-add");
                    }
                    //切换显示条状添加按钮
                    $addMbox.show().appendTo("#module_panel");  
                }else{
                    $addMbox.hide();
                }
         
        }

        // 模块管理器
        var $managerCtrl = $("#manager_ctrl");
        manager = moduleManager($managerCtrl, moduleInstalled, {
            onchange: function(name, isChecked) {
                if (isChecked) {
                    // 插入面板并写入存储，读取内容
                    panel.add(name, getModuleSettings(name, {
                            onremove: function(name) {
                                // 模块移除时，删除对应存储，uncheck对应项
                                storage.remove(name);
                                manager.unCheck(name);
                                resetAddMbox();
                            }
                        }), function(name) {
                        storage.add({'name': name});
                        indexModule.load(loadModuleUrl, {module: name});
                    });
                } else {
                    // 移除面板并写入存储
                    panel.remove(name, function(name) {
                        storage.remove(name); 
                    });
                }
                //重置添加按钮
                resetAddMbox();
            },
            onreset: function() {
                // 清除本地存储的配置，使用默认设置
                storage.clear();
                // 重载页面
                window.location.reload();
            }
        });

        // 模块加载
        $(function() {
            // 从存储器中读取设置为显示状态的模块
            var getModuleNames = function() {
                var mods = storage.get(), modNames = [];
                for (var i = 0, len = mods.length; i < len; i++) {
                    modNames.push(mods[i].name);
                }
                return modNames;
            };
            var modNames = getModuleNames(),
                    modstr;
            var $cmMenu = $("#cm_menu"),
                    bubble = menuBubble($cmMenu); // 消息数目提醒;
            for (var i = 0, len = modNames.length; i < len; i++) {
                // 当存储器中的模块已卸载时，删除对应的存储记录
                if (nameIndexOf(modNames[i], moduleInstalled) === -1) {
                    storage.remove(modNames[i]);
                } else {
                    // 第二个参数false表示不写入存储
                    panel.add(modNames[i], getModuleSettings(modNames[i], {
                        onremove: function(name) {
                            // 模块移除时，删除对应存储，uncheck对应项
                            storage.remove(name);
                            manager.unCheck(name);
                            resetAddMbox();
                        }
                    }), function(name, $container) {
                        // 加载模块后，更新模块管理器的选中项
                        manager.check(modNames[i]);
                        $container.waiting(null, "normal");
                    });
                }
            }

            modstr = modNames.join(",");
            var requestTime = +new Date();
            // 模块读取
            indexModule.load(loadModuleUrl, {module: modstr, random: Math.random()}, function(name, $container) {
                $container.stopWaiting();
            });

            resetAddMbox();

            // 读取后初始化拖拽排序功能
            $modulePanel.sortable(dragSettings).on("sortupdate", dragUpdateHandler);

            // 消息数目提醒
            bubble.load(loadNewUrl, {module: getInstalledModuleName()});
            // 定时刷新未读消息数
            setInterval(function() {
                if (modstr) {
                    // indexModule.load(loadModuleUrl, {module: modstr});
                    bubble.load(loadNewUrl, {module: getInstalledModuleName(), d: requestTime}, function(data) {
                        var modNeedFresh = [];
                        requestTime = data.timestamp;
                        for (var name in data) {
                            if (name !== 'timestamp') {
                                if (parseInt(data[name], 10) > 0) {
                                    modNeedFresh.push(name);
                                }
                            }
                        }
                        ;
                        if (modNeedFresh.length) {
                            indexModule.load(loadModuleUrl, {module: modNeedFresh.join(",")});
                        }
                    });
                }
            }, refreshInterval);
        });
    })();
</script>
