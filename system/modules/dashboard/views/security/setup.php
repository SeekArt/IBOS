<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Security setting']; ?></h1>
        <!-- @Todo: PHP -->
        <ul class="mn">
            <li>
                <span><?php echo $lang['Account security setup']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('security/log'); ?>"><?php echo $lang['Run log']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('security/ip'); ?>"><?php echo $lang['Disabled ip']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('security/setup'); ?>" method="post" class="form-horizontal">
            <!-- IP设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Account security setup']; ?></h2>
                <div class="control-group">
                    <label class="control-label"><?php echo $lang['Password expiration']; ?></label>
                    <div class="controls">
                        <select name='expiration' class='span2'>
                            <option value='0'
                                    <?php if ($account['expiration'] == '0'): ?>selected<?php endif; ?>><?php echo $lang['Expiration never']; ?></option>
                            <option value='1'
                                    <?php if ($account['expiration'] == '1'): ?>selected<?php endif; ?>><?php echo $lang['Expiration one month']; ?></option>
                            <option value='2'
                                    <?php if ($account['expiration'] == '2'): ?>selected<?php endif; ?>><?php echo $lang['Expiration three month']; ?></option>
                            <option value='3'
                                    <?php if ($account['expiration'] == '3'): ?>selected<?php endif; ?>><?php echo $lang['Expiration six month']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php echo $lang['Password strenth']; ?></label>
                    <div class="controls ctbw">
                        <div class="b-m">
                            <div id="psw_strength" data-target="minlength"></div>
                            <input type="hidden" id="minlength" name="minlength"
                                   value="<?php echo $account['minlength']; ?>"/>
                        </div>
                        <div>
                            <label class="checkbox">
                                <input type="checkbox" name="mixed" value="1"
                                       <?php if ($account['mixed'] == 1): ?>checked<?php endif; ?> />
                                <?php echo $lang['Password must contain both letters and Numbers']; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php echo $lang['Login error limit']; ?></label>
                    <div class="controls">
                        <input type="checkbox" value="1" data-toggle="switch" name="errorlimit"
                               <?php if ($account['errorlimit'] == 1): ?>checked<?php endif; ?> />
                        <div>
                            <?php echo $lang['Login error retry 1']; ?>
                            <input type="text" name="errorrepeat" class="input-small" style="width: 40px;"
                                   value="<?php echo $account['errorrepeat']; ?>"/>
                            <?php echo $lang['Login error retry 2']; ?>
                            <input type="text" name="errortime" class="input-small" style="width: 40px;"
                                   value="<?php echo $account['errortime']; ?>"/>
                            <?php echo $lang['Login error retry 3']; ?>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php echo $lang['Auto login time']; ?></label>
                    <div class="controls">
                        <select name='autologin' class='span2'>
                            <option value='-1'
                                    <?php if ($account['autologin'] == '-1'): ?>selected<?php endif; ?>><?php echo $lang['Autologin never']; ?></option>
                            <option value='0'
                                    <?php if ($account['autologin'] == '0'): ?>selected<?php endif; ?>><?php echo $lang['Autologin one day']; ?></option>
                            <option value='1'
                                    <?php if ($account['autologin'] == '1'): ?>selected<?php endif; ?>><?php echo $lang['Autologin one week']; ?></option>
                            <option value='2'
                                    <?php if ($account['autologin'] == '2'): ?>selected<?php endif; ?>><?php echo $lang['Autologin one month']; ?></option>
                            <option value='3'
                                    <?php if ($account['autologin'] == '3'): ?>selected<?php endif; ?>><?php echo $lang['Autologin three month']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">超时时间</label>
                    <div class="controls">
                        <input type="text" name="timeout" class="w40 input-small"
                               value="<?php echo $account['timeout']; ?>"/>
                        分钟内无动作自动退出
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">
                        <?php echo $lang['Allowed use the same account login at the same time']; ?>
                    </label>
                    <div class="controls">
                        <input type="checkbox" data-toggle="switch" name="allowshare" value="1"
                               <?php if ($account['allowshare'] == 1): ?>checked<?php endif; ?> />
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" name="securitySubmit" class="btn btn-primary btn-large btn-submit">
                            <?php echo $lang['Submit']; ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_security.js"></script>