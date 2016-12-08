<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Sms setting']; ?></h1>
        <ul class="mn">
            <li>
                <span><?php echo $lang['Sms setup']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('sms/manager'); ?>"><?php echo $lang['Sms sent manager']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('sms/access'); ?>"><?php echo $lang['Sms access']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('sms/setup'); ?>" method="post" class="form-horizontal">
            <!-- 开关设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Switch setup']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label for="" class="control-label"><?php echo $lang['Sms function']; ?></label>
                        <div class="controls">
                            <input type="checkbox" value="1" name="enabled" id="sms_enable" data-toggle="switch"
                                   class="visi-hidden" <?php if ($setup['smsenabled'] == '1'): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                </div>
            </div>
            <div id="sms_setup" <?php if ($setup['smsenabled'] == '0'): ?>style="display: none;"<?php endif; ?>>
                <!-- 短信接口设置 start -->
                <div class="ctb">
                    <h2 class="st"><?php echo $lang['Sms interface setup']; ?></h2>
                    <div class="alert trick-tip">
                        <div class="trick-tip-title">
                            <strong></strong>
                        </div>
                        <div class="trick-tip-content">
                            <?php echo $lang['Sms tip']; ?>
                        </div>
                    </div>
                    <div class="ctbw">
                        <div class="control-group">
                            <label for="" class="control-label"><?php echo $lang['Enable sms function']; ?></label>
                            <div class="controls">
                                <label class="radio">
                                    <input type="radio" name="interface" value="1"
                                           <?php if ($setup['smsinterface'] == '1'): ?>checked<?php endif; ?>><?php echo $lang['Interface 1']; ?>
                                </label>
                            </div>
                        </div>
                        <?php if ($setup['smsinterface'] == '1'): ?>
                            <div id="interface1_box">
                                <div class="control-group">
                                    <label class="control-label">accessKey</label>
                                    <div class="controls">
                                        <input type="text" name="interface1[accesskey]"
                                               value="<?php echo $setup['smssetup']['accesskey']; ?>"/>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">secretKey</label>
                                    <div class="controls">
                                        <input type="text" name="interface1[secretkey]"
                                               value="<?php echo $setup['smssetup']['secretkey']; ?>"/>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div id="interface1_box">
                                <div class="control-group">
                                    <label class="control-label">accessKey</label>
                                    <div class="controls">
                                        <input type="text" name="interface1[accesskey]" value=""/>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">secretKey</label>
                                    <div class="controls">
                                        <input type="text" name="interface1[secretkey]" value=""/>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- 短信余额 start -->
                <div class="ctb">
                    <h2 class="st"><?php echo $lang['Sms left']; ?></h2>
                    <div class="ctbw sms-balance">
                        <div class="control-group">
                            <label for="" class="control-label">
                                <?php echo $lang['Sms left']; ?>
                            </label>
                            <div class="controls">
                                <div></div>
                                <div class="card-circle pull-left">
                                    <p><?php echo $lang['Balance']; ?></p>
                                    <p>
                                        <strong id="smsleft"><?php echo $smsLeft; ?></strong>
                                    </p>
                                    <p><?php echo $lang['Item']; ?></p>
                                </div>
                                <a href="http://www.ibos.com.cn/" target="_blank">
                                    <span class="label label-warning"><?php echo $lang['Top-up immediately']; ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
                    <button type="submit" name="smsSubmit"
                            class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_sms.js"></script>