<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/email.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="ct fill">
            <form id="edit_form" action="<?php echo $this->createUrl('web/edit'); ?>" method="post"
                  class="form-horizontal">
                <fieldset>
                    <legend><?php echo $lang['Edit web mail']; ?></legend>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Email address']; ?>：</label>
                        <div class="controls">
                            <input type="text" id="mal" disabled name="address" value="<?php echo $web['address']; ?>"
                                   class="span6"/><a href="javascript:;" class="ilsep" data-click="showRow"
                                                     data-param="{ &quot;targetId&quot;: &quot;mal_reset_row&quot;}">[<?php echo $lang['Reset password']; ?>
                                ]</a><a
                                href="<?php echo $this->createUrl('web/del', array('webids' => $web['webid'])); ?>"
                                class="ilsep">[<?php echo $lang['Delete']; ?>]</a>
                        </div>
                    </div>
                    <div class="control-group" id="mal_reset_row" style="display: none;">
                        <label class="control-label"><?php echo $lang['Password']; ?>：</label>
                        <div class="controls">
                            <input type="password" name="web[password]" value="<?php echo $web['password']; ?>"
                                   class="span6"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Folders']; ?>：</label>
                        <div class="controls">
                            <input type="text" id="mal_dir" name="web[foldername]"
                                   value="<?php echo $web['foldername']; ?>" class="span6"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Web mail nickname']; ?>：</label>
                        <div class="controls">
                            <input type="text" name="web[nickname]" class="span6"
                                   value="<?php echo $web['nickname']; ?>"/>
                            <span class="ilsep tcm">(<?php echo $lang['Optional']; ?>)</span>
                        </div>
                    </div>
                    <div class="control-group mbm">
                        <label class="control-label"><?php echo $lang['Receive mail server']; ?>（POP 或 IMAP）：</label>
                        <div class="controls">
                            <input type="text" id="mal_pop_server" name="web[server]"
                                   value="<?php echo $web['server']; ?>" class="span6"/>
                            <a href="javascript:;" class="ilsep" data-click="showRow"
                               data-param="{ &quot;targetId&quot;: &quot;mal_pop_row&quot;}">[<?php echo $lang['Set port']; ?>
                                ]</a>
                        </div>
                    </div>
                    <div class="control-group mbm" id="mal_pop_row" style="display:none;">
                        <label class="control-label"><?php echo $lang['Port']; ?>：</label>
                        <div class="controls">
                            <input type="text" id="mal_pop_port" name="web[port]" value="<?php echo $web['port']; ?>"
                                   class="span6"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <label class="checkbox">
                                <input type="checkbox" name="web[ssl]" <?php if ($web['ssl']): ?>checked<?php endif; ?>
                                       value="1"/><?php echo $lang['Use ssl connect']; ?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group mbm">
                        <label class="control-label"><?php echo $lang['Send server']; ?>（SMTP）：</label>
                        <div class="controls">
                            <input type="text" id="mal_smtp_server" name="web[smtpserver]"
                                   value="<?php echo $web['smtpserver']; ?>" class="span6"/>
                            <a href="javascript:;" class="ilsep" data-click="showRow"
                               data-param="{&quot;targetId&quot;: &quot;mal_smtp_row&quot;}">[<?php echo $lang['Set smtp port']; ?>
                                ]</a>
                        </div>
                    </div>
                    <div class="control-group mbm" id="mal_smtp_row" style="display:none;">
                        <label class="control-label"><?php echo $lang['Smtp port']; ?>：</label>
                        <div class="controls">
                            <input type="text" id="mal_smtp_port" name="web[smtpport]" class="span6"
                                   value="<?php echo $web['smtpport']; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <label class="checkbox">
                                <input type="checkbox" name="web[smtpssl]"
                                       <?php if ($web['smtpssl']): ?>checked<?php endif; ?>
                                       value="1"/><?php echo $lang['Use ssl connect']; ?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <button type="submit" name="emailSubmit"
                                    class="btn btn-large btn-submit btn-primary"><?php echo $lang['Submit']; ?></button>
                            &nbsp;
                            <button type="button" onclick="javascript:history.go(-1);"
                                    class="btn btn-large btn-submit"><?php echo $lang['Return']; ?></button>
                        </div>
                    </div>
                    <input type="hidden" name="id" value="<?php echo $web['webid']; ?>">
                    <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
                </fieldset>
            </form>
        </div>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email_web_common.js?<?php echo VERHASH; ?>'></script>