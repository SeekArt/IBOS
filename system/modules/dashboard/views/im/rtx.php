<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Instant messaging binding']; ?></h1>
        <ul class="mn" id="communication_type">
            <li>
                <span><?php echo $lang['Rtx Setup']; ?></span>
            </li>
            <li hidden = "hidden">
                <a href="<?php echo $this->createUrl('im/index', array('type' => 'qq')); ?>"><?php echo $lang['QQ setup']; ?></a>
            </li>
        </ul>
    </div>
    <div id="cm_content">
        <div>
            <form action="<?php echo $this->createUrl('im/index'); ?>" method='post' class="form-horizontal">
                <!-- RTX设置 start -->
                <div class="ctb">
                    <h2 class="st"><?php echo $lang['Rtx Setup']; ?></h2>
                    <div class="alert trick-tip">
                        <div class="trick-tip-title">
                            <i></i>
                            <strong><?php echo $lang['Skills prompt']; ?></strong>
                        </div>
                        <div class="trick-tip-content">
                            <?php echo $lang['Rtx setup tip']; ?>
                        </div>
                    </div>
                    <div class="ctbw">
                        <div class="control-group">
                            <label class="control-label"><?php echo $lang['Rtx binding']; ?></label>
                            <div class="controls row">
                                <div class="span4">
                                    <input type="checkbox" value='1' name="open" id="rtx_enable" data-toggle="switch"
                                           class="visi-hidden"
                                           <?php if ($im['open'] == '1'): ?>checked<?php endif; ?> />
                                </div>
                                <div class="span8">
                                    <a target="_blank"
                                       href="http://bqq.tencent.com/rtx/index.shtml"><?php echo $lang['about RTX']; ?></a><br>
                                    <a target="_blank"
                                       href="http://doc.ibos.com.cn/article/detail/id/69"><?php echo $lang['Click to view helper doc']; ?></a>
                                </div>
                            </div>
                        </div>
                        <div id="rtx_setup" <?php if ($im['open'] == '0'): ?>style="display: none;"<?php endif; ?>>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['Server address']; ?></label>
                                <div class="controls">
                                    <input type="text" name='server' value='<?php echo $im['server']; ?>'/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['App server port']; ?></label>
                                <div class="controls">
                                    <input type="text" name='appport' value='<?php echo $im['appport']; ?>'>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['Sdk server port']; ?></label>
                                <div class="controls">
                                    <input type="text" name='sdkport' value='<?php echo $im['sdkport']; ?>'>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['Push setting']; ?></label>
                                <div class="controls">
                                    <label class="checkbox">
                                        <input type="checkbox" name='push[note]' value='1'
                                               <?php if ($im['push']['note'] == '1'): ?>checked<?php endif; ?> />
                                        <?php echo $lang['Notice push']; ?>
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="push[msg]" value='1'
                                               <?php if ($im['push']['msg'] == '1'): ?>checked<?php endif; ?> />
                                        <?php echo $lang['Message push']; ?>
                                    </label>
                                </div>
                            </div>
                            <!--<div class="control-group">
                                <label for="" class="control-label">
							<?php echo $lang['Rtx sso']; ?>
                                    <span class="help-block">(<?php echo $lang['Only for ie']; ?>)</span>
                                </label>
                                <div class="controls">
                                    <input type="checkbox" name="sso" value='1' data-toggle="switch" class="visi-hidden">
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">
							<?php echo $lang['Rtx reverse landing']; ?>
                                </label>
                                <div class="controls">
                                    <input type="checkbox" name="reverselanding" value='1' data-toggle="switch" class="visi-hidden">
                                </div>
                            </div>-->
                            <div class="control-group">
                                <label class="control-label">
                                    <?php echo $lang['Rtx synchronize']; ?>
                                </label>
                                <div class="controls">
                                    <input type="checkbox" name="syncuser" value='1' data-toggle="switch"
                                           class="visi-hidden"
                                           <?php if ($im['syncuser'] == '1'): ?>checked<?php endif; ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 同步组织架构到RTX start -->
                <div class="ctb" id="synctortx" <?php if ($im['open'] == '0'): ?>style="display: none;"<?php endif; ?>>
                    <h2 class="st"><?php echo $lang['Synchronize rtx']; ?></h2>
                    <div class="alert trick-tip">
                        <div class="trick-tip-title">
                            <i></i>
                            <strong><?php echo $lang['Skills prompt']; ?></strong>
                        </div>
                        <div class="trick-tip-content">
                            <?php echo $lang['Synchronize rtx tip']; ?>
                        </div>
                    </div>
                    <div class="ctbw">
                        <div class="control-group">
                            <label class="control-label">
                                <?php echo $lang['Enter rtx init password']; ?>
                            </label>
                            <div class="controls">
                                <div class="row">
                                    <div class="span7">
                                        <input type="password" id='rtx_init_pwd'>
                                    </div>
                                    <div class="span5">
                                        <a href="javascript:void(0);" data-act='syncrtx'
                                           class="btn"><?php echo $lang['Synchronization Immediate']; ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 同步RTX组织架构到OA start -->
                <div class="ctb" id="synctooa" <?php if ($im['open'] == '0'): ?>style="display: none;"<?php endif; ?>>
                    <h2 class="st"><?php echo $lang['Synchronize oa']; ?></h2>
                    <div class="alert trick-tip">
                        <div class="trick-tip-title">
                            <i></i>
                            <strong><?php echo $lang['Skills prompt']; ?></strong>
                        </div>
                        <div class="trick-tip-content">
                            <?php echo $lang['Synchronize oa tip']; ?>
                        </div>
                    </div>
                    <div class="ctbw">
                        <div class="control-group">
                            <label class="control-label">
                                &nbsp;
                            </label>
                            <div class="controls">
                                <div class="row">
                                    <a href="javascript:void(0);" data-act='syncoa'
                                       class="btn"><?php echo $lang['Synchronization Immediate']; ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="controls">
                        <button name='imSubmit' class="btn btn-primary btn-large btn-submit"
                                type="submit"><?php echo $lang['Submit']; ?></button>
                    </div>
                </div>
                <input type='hidden' name='type' value='<?php echo $type; ?>'/>
            </form>
        </div>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_im.js"></script>