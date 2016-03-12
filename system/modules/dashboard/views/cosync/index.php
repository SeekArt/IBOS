<link href="<?php echo $this->getAssetUrl(); ?>/css/ibosco.css" type="text/css" rel="stylesheet">
<div class="new-binding-wrap <?php if( $isInstall ) : ?>binding-install-wrap<?php endif; ?>">
    <div class="ct">
        <?php if( !$isInstall ) : ?>
            <div class="clearfix">
                <h1 class="mt">绑定酷办公，体验真正的移动办公！</h1>
            </div>
        <?php endif; ?>
        <div>
            <!-- 企业信息 start -->
            <div <?php if( !$isInstall ) : ?>class="ctb"<?php endif; ?>>
                <?php if( !$isInstall ) : ?>
                    <h2 class="st">酷办公绑定</h2>
                <?php endif; ?>
                <div class="co-banding-wrap">
                    <?php if( !$isInstall ) : ?>
                        <div class="co-binding-content clearfix">
                            <div class="co-info-box pull-left co-info-ibos">
                                <div class="co-binding-logo">
                                    <img src="<?php if( $ibos['corplogo'] ) { 
                                                echo $ibos['corplogo']; 
                                                } else { 
                                                    echo $this->getAssetUrl().'/image/corp_logo.png'; 
                                                }?>"/>
                                    <i class="o-binding-ibos"></i>
                                </div>
                                <div class="xac">
                                    <p class="xwb mts"><?php echo $ibos['corpshortname']; ?></p>
                                    <p><?php echo $ibos['systemurl']; ?></p>
                                </div>
                            </div>
                            <div class="co-binding-state">
                                <div class="co-binding-icon">
                                    <i class="o-binding-success"></i>
                                    <span>已绑定</span>
                                </div>
                                <div class="co-unbinding-icon" data-action="unBinding">
                                    <i class="o-unbinding-success"></i>
                                    <span>解除绑定</span>
                                </div>
                            </div>
                            <div class="co-info-box pull-right co-info-co">
                                <div class="co-binding-logo">
                                    <img src="<?php if( $co['corplogo'] ) { 
                                                echo $co['corplogo']; 
                                                } else { 
                                                    echo $this->getAssetUrl().'/image/corp_logo.png'; 
                                                }?>"/>
                                    <i class="o-binding-co"></i>
                                </div>
                                <div class="xac">
                                    <p class="xwb mts"><?php echo $co['corpshortname']; ?></p>
                                    <p>企业ID:<?php echo $co['corpid']; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div id="tmpl_ctn"></div>
                    <?php if( !$isInstall ) : ?>
                        <div class="co-sync-auto clearfix">
                            <div class="pull-left">
                                <p>自动同步</p>
                                <p style="color:#C1CCD9;">开启后将定时双向同步用户</p>
                            </div>
                            <div class="pull-right">
                                <label class="toggle">
                                    <input type="checkbox" name="autoSync" id="auto_sync" data-toggle="switch" <?php if( $autoSync == 1 ): ?> checked <?php endif; ?>>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ibosco_sync_dialog" style="display:none;">
    <div style="width:740px; min-height:400px;">
        <div class="position-mumber-wrap">
            <div class="ibosco-sync-list span12">
                <ul class="ibosco-member-list clearfix">
                </ul>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    Ibos.app.s("isInstall", "<?php echo $isInstall; ?>");
    Ibos.app.s("pageInit", "<?php echo $pageInit; ?>")
</script>
<script type="text/template" id="binding_member_tpl">
    <li id="binding_member_<%=uid%>">
    <div class="ibosco-avatar-box">
    <a href="javascript:;" class="ibosco-avatar-circle"><img src="<%=avatar%>" alt=""></a>
    </div>
    <div class="ibosco-member-item-body">
    <p class="ellipsis xcn xwb" title="<%=realname%>"><%=realname%></p>
    <p class="tcm"><%=detail%></p>
    </div>
    </li>
</script>
<script type="text/template" id="binding_progress">
    <div class="co-binding-content">
    <div id="co_sync_progress">
    <div>
    <i class="o-sync-progress"></i>
    </div>
    <div class="co-binding-progress">
    <p class="mbs" id="show_process">准备同步，请稍候...</p>
    <div class="progress progress-striped span12 pull-left progress-area">
    <div id="progressbar" class="progress-bar" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
    </div>
    </div>
    </div>
    </div>
    </div>
</script>
<script type="text/template" id="binding_success">
    <div class="co-binding-content">
    <div class="xac" id="co_sync_success">
    <div class="mb">
    <i class="o-sync-success"></i>
    <p class="fsl">恭喜，同步成功！</p>
    </div>
    <div class="mts mbs co-sync-success">
    <div class="clearfix">
    <label class="pull-left">IBOS成员</label> 
    <span class="pull-right">新增<b class="xcbu mlm mrm"><%=ibosCreateNum%></b>，禁用<b class="xcbu mlm mrm"><%=ibosRemoveNum%></b></span>
    </div>
    <div class="clearfix">
    <label class="pull-left">酷办公成员</label>
    <span class="pull-right">新增<b class="xcbu mlm mrm"><%=coCreateNum%></b>，移除<b class="xcbu mlm mrm"><%=coRemoveNum%></b></span>
    </div>
    <div class="clearfix">
    <label class="pull-left">已绑定人数</label>
    <span class="pull-right"><b class="xcbu mlm mrm"><%=syncCountNum%></b></span>
    </div>
    </div>
    <?php if( !$isInstall ) : ?>
    <button class="btn btn-primary span6 mts" data-action="showSyncDetail">确定</button>
    <?php endif; ?>
    </div>
    </div>
</script>
<script type="text/template" id="binding_update">
    <div class="clearfix">
    <div class="co-sync-box pull-left">
    <p>IBOS成员<b class="mlm mrm"><%=ibos['count']%></b>人</p>
    <div class="co-sync-tip <%=ibos['ibosAddAct']%>">
    <i class="o-binding-new mrs"></i>
    <span>新增</span><span class="sync-tip-number"><%=ibos['ibosAddNum']%></span><span>人</span>
    <span class="sync-tip-detail pull-right" data-action="bindingDetail" data-param='{"list": "ibosAddList"}'>详情</span>
    </div>
    <div class="co-sync-tip <%=ibos['ibosDelAct']%>">
    <i class="o-binding-forbidden mrs"></i>
    <span>禁用</span><span class="sync-tip-number"><%=ibos['ibosDelNum']%></span><span>人</span>
    <span class="sync-tip-detail pull-right" data-action="bindingDetail" data-param='{"list": "ibosDelList"}'>详情</span>
    </div>
    </div>
    <div class="co-sync-box pull-right">
    <p>酷办公成员<b class="mlm mrm"><%=co['count']%></b>人</p>
    <div class="co-sync-tip <%=co['coAddAct']%>">
    <i class="o-binding-new mrs"></i>
    <span>加入</span><span class="sync-tip-number"><%=co['coAddNum']%></span><span>人</span>
    <span class="sync-tip-detail pull-right" data-action="bindingDetail" data-param='{"list": "coAddList"}'>详情</span>
    </div>
    <div class="co-sync-tip <%=co['coDelAct']%>">
    <i class="o-binding-remove mrs"></i>
    <span>移除</span><span class="sync-tip-number"><%=co['coDelNum']%></span><span>人</span>
    <span class="sync-tip-detail pull-right" data-action="bindingDetail" data-param='{"list": "coDelList"}'>详情</span>
    </div>
    </div>
    </div>
    <div class="co-binding-content">
    <button type="button" class="btn btn-primary sync-btn span12" data-action="startedSync">开始同步</button>
    </div>
</script>
<script src="<?php echo $assetUrl; ?>/js/db_cobinding_banding.js"></script>