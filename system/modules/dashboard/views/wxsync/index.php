<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/home.css?<?php echo VERHASH; ?>">
<link href="<?php echo $this->getAssetUrl(); ?>/css/weixin.css" type="text/css" rel="stylesheet"/>
<div class="ct sp-ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Binding wechat and enjoy it']; ?></h1>
    </div>
    <div>
        <div class="ctb ps-type-title">
            <h2 class="st"><?php echo $lang['Wechat binding'] ?></h2>
            <!-- 同步start -->
            <div class="box-shadow bind-info-wrap">
                <div class="clearfix">
                    <div class="box-shadow ibos-qy">
                        <div class="aes-key" data-toggle="tooltip" data-html="true"
                             title="<div class='aes-key-tip'><p class='xwb'>AES KEY：</p><p><?php echo $aeskey; ?></p></div>">
                            AES KEY
                        </div>
                        <div class="company-logo mbs">
                            <img src="<?php echo $unit['logourl']; ?>" alt="<?php echo $unit['shortname']; ?>">
                            <div class="ibos-logo">
                                <i class="o-binding-ibos"></i>
                            </div>
                        </div>
                        <p class="lhl t"><?php echo $unit['fullname']; ?></p>
                        <p class="lhl"><?php echo $unit['systemurl']; ?></p>
                    </div>
                    <div class="box-shadow weixin-qy">
                        <div class="company-logo mbs">
                            <img src="<?php echo $wxqy['logo']; ?> " alt="<?php echo $wxqy['name']; ?>">
                            <div class="weixin-logo">
                                <i class="o-binding-weixin"></i>
                            </div>
                        </div>
                        <p class="lhl"><?php echo $wxqy['name']; ?></p>
                        <p class="lhl">CorpID : <?php echo $wxqy['corpid']; ?></p>
                    </div>
                    <div class="co-binding-state" data-toggle="tooltip" title="解绑需要到微信企业号后台取消套件托管">
                        <div class="co-binding-icon">
                            <i class="o-binding-success"></i>
                            <span></span>绑定成功
                        </div>
                        <div class="co-unbinding-icon"
                             onclick="window.open('http://doc.ibos.com.cn/article/detail/id/329' ,'_blank');">
                            <i class="o-unbinding-success"></i>
                            <span></span>解除绑定
                        </div>
                    </div>
                </div>
                <div class="clearfix" id="sync_opt_wrap">
                    <div class="row pts pbs">
                        <div class="span6">
                            <p class="mbs fsm">
                                <span><?php echo $lang['IBOS not sync wechat']; ?></span>
                            <div>
                                <span class="xco fsg xwb"><?php echo $localCount; ?></span>
                                <span>人</span>
                            </div>
                            </p>
                        </div>
                        <div class="span6">
                            <p class="mbs fsm">
                                <span><?php echo $lang['Wechat not sync IBOS'] ?></span>
                            <div id="wx_count">
                                <span style="color: #C1CCD9;"><?php echo $lang['Data loading and wait']; ?></span>
                            </div>
                            </p>
                        </div>
                    </div>
                    <div class="wrap-footer" id="wrap_footer">
                        <button class="btn btn-primary btn-large btn-block" type="button"
                                data-action="syncData"><?php echo $lang['Start synchronization'] ?></button>
                    </div>
                </div>
            </div>
            <div class="auto-sync clearfix">
                <div class="pull-left lhf xwb">
                    <i class="o-wxapp-center"></i>
                    <span class="mls fsm"><?php echo $lang['Wechat app list center']; ?></span>
                </div>
                <div class="pull-right">
                    <a href="<?php echo $this->createUrl('wxsync/app'); ?>" class="btn">添加或修改授权应用</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!--同步进程-->
<script type="text/template" id="result_syncing_tpl">
    <div class="row pt">
        <div class="xac syncing-info-wrap span6 offset3">
            <ul class="list-inline mb">
                <li>
                    <i class="o-ibos-tip"></i>
                </li>
                <li class="mlm">
                    <i class="o-transport-right mbm"></i>
                    <p class="mbs">同步IBOS成员</p>
                    <i class="o-transport-left mbm"></i>
                </li>
                <li class="mlm">
                    <i class="o-weixin-tip"></i>
                </li>
            </ul>
            <p class="mbs">
                <span class="fsm"><%= data.msg %></span>
            </p>
            <div class="progress">
                <div class="progress-bar progress-bar-striped active" role="progressbar"
                     aria-valuenow="<%= data.percentage %>" aria-valuemin="0" aria-valuemax="100"
                     style="width: <%= data.percentage %>%">
                </div>
            </div>
        </div>
    </div>
</script>
<!--同步成功-->
<script type="text/template" id="result_success_tpl">
    <div class="xac result-info-wrap">
        <i class="o-opt-success mb"></i>
        <p class="mbm">
            <span class="fsl">恭喜，同步成功！</span>
        </p>
        <p class="mb">
            其中企业号新增&nbsp;<span class="xcbu xwb"><%= data.successCount %></span>&nbsp;人,&nbsp;
            已绑定人数为&nbsp;<span class="xcbu xwb"><%= data.bindCount %></span>&nbsp;人
        </p>
        <a href="javascript:location.reload();" class="btn btn-block btn-primary btn-large">确定</a>
    </div>
</script>
<!--同步失败-->
<script type="text/template" id="result_error_tpl">
    <div class="xac result-info-wrap">
        <i class="o-opt-faliue mb"></i>
        <p class="mbs">
            <span class=" xcr"><%= data.errorCount %></span>
            <span>个联系人无法同步</span>
        </p>
        <p class="mbs">
            <span>请根据错误信息修正并重新同步。</span>
        </p>
        <p>
            <a href="<%= data.downUrl %>" class="btn">下载错误信息</a>
        </p>
    </div>
</script>

<script type="text/javascript" src="<?php echo $this->getAssetUrl(); ?>/js/syncdata.js"></script>