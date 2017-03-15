<link href="<?php echo $this->getAssetUrl(); ?>/css/ibosco.css" type="text/css" rel="stylesheet">
<div class="<?php if ($isInstall) : ?>binding-install-wrap<?php endif; ?>">
    <div class="ct">
        <?php if (!$isInstall) : ?>
            <div class="clearfix">
                <h1 class="mt">连接酷办公，体验真正的移动办公！</h1>
            </div>
        <?php endif; ?>
        <div>
            <div <?php if (!$isInstall) : ?>class="ctb"<?php endif; ?>>
                <?php if (!$isInstall) : ?>
                    <h2 class="st">酷办公连接</h2>
                <?php endif; ?>
                <div class="co-banding-wrap text-center">
                    <h1 class="binding-title">请选择要绑定的酷办公企业</h1>
                    <p>不是酷办公超级管理员？你还可以添加新企业进行绑定哦！</p>
                    <div class="co-list-wrap">
                        <ul class="clearfix">
                            <?php foreach ($corpList as $corp): ?>
                                <li>
                                    <div
                                        class="co-list-box <?php if ($corp['isSuperAdmin']) : ?>active-box<?php endif; ?>"
                                        <?php if ($corp['isSuperAdmin']) : ?>
                                            data-action="coBindingAct" data-param='{
													"corpid": "<?php echo $corp['corpid']; ?>",
													"corptoken": "<?php echo $corp['corptoken']; ?>",
													"corpname": "<?php echo $corp['corpname']; ?>",
													"isSuperAdmin": <?php echo $corp['isSuperAdmin']; ?>,
													"systemUrl": "<?php echo $corp['systemUrl']; ?>",
													"corpname": "<?php echo $corp['corpname']; ?>",
													"corpshortname": "<?php echo $corp['corpshortname']; ?>",
													"corplogo": "<?php echo $corp['corplogo']; ?>",
													"isBindOther": <?php echo $corp['isBindOther']; ?>}'
                                        <?php endif; ?>
                                        alt="<?php echo $corp['corpname'] ?>" title="<?php echo $corp['corpname'] ?>">
                                        <div class="img-box mbs">
                                            <img src="<?php if ($corp['corplogo']) {
                                                echo $corp['corplogo'];
                                            } else {
                                                echo $this->getAssetUrl() . '/image/corp_logo.png';
                                            } ?>"/>
                                            <div class="corp-wrap-lock">
                                                <p class="lock-text">非超管</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="ellipsis mtm co-name"><?php echo $corp['corpname'] ?></p>
                                            <div>
                                            </div>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <div class="co-list-box" data-action="addBindingAct">
                                    <i class="o-binding-add mbs"></i>
                                    <p class="mtm">添加企业</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <a class="co-login-back"
                           href="/?r=dashboard/cobinding/logout&isInstall=<?php echo $isInstall; ?>">
                            <i class="o-back-arrow pull-left"></i>
                            <span class="mls">退出当前帐号</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    Ibos.app.s({
        "isInstall": "<?php echo $isInstall; ?>",
        "aeskey": "<?php echo $aeskey; ?>",
        "systemUrl": "<?php echo $systemurl; ?>"
    })
</script>
<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/iboscoselect.js"></script>

<div id="ibosco_addcorp_dialog" style="display:none;">
    <div style="width:380px">
        <form class="form-horizontal form-compact" id="ibosco_addcorp_form">
            <div class="control-group">
                <label class="control-label xcl">企业全称</label>
                <div class="controls">
                    <input type="text" id="new_corpname" value="<?php echo $createCorpInfo['corpname']; ?>"
                           placeholder="企业全称"/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label xcl">企业简称</label>
                <div class="controls">
                    <input type="text" id="new_corpshortname" value="<?php echo $createCorpInfo['corpshortname']; ?>"
                           placeholder="企业简称">
                </div>
            </div>
        </form>
    </div>
</div>