<div id="add_form_wrap">
    <form id="add_form" action="<?php echo $this->createUrl('web/add'); ?>" method="post" class="form-horizontal">
        <?php if (!empty($errMsg)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errMsg as $msg): ?>
                    <span><?php echo $msg; ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <table class="table table-condensed">
            <tr>
                <th><?php echo $lang['Email address']; ?>：</th>
                <td><input type="text" id="mal" name="web[address]"
                           value="<?php echo isset($web['address']) ? $web['address'] : ''; ?>"/></td>
            </tr>
            <tr>
                <th><?php echo $lang['Password']; ?>：</th>
                <td><input type="password" id="pwd" name="web[password]"
                           value="<?php echo isset($web['password']) ? $web['password'] : ''; ?>"/></td>
            </tr>
            <?php if ($more): ?>
                <tr>
                    <th><?php echo $lang['Receive mail server']; ?>（POP）：</th>
                    <td>
                        <input type="text" id="mal_pop_server" name="web[server]"/>
                        <a href="javascript:;" class="ilsep" data-click="showRow"
                           data-param="{ &quot;targetId&quot;: &quot;mal_pop_row&quot;}">[<?php echo $lang['Set port']; ?>
                            ]</a>
                    </td>
                </tr>
                <tr id="mal_pop_row" style="display:none;">
                    <th><?php echo $lang['Port']; ?>：</th>
                    <td>
                        <input type="text" id="mal_pop_port" name="web[port]" class="span6" value="110"/>
                        <label><input type="checkbox" name="web[ssl]" value="1"/><?php echo $lang['Use ssl connect']; ?>
                        </label>
                        <input type="hidden" name="web[agreement]" value="1">
                    </td>
                </tr>
                <tr>
                    <th><?php echo $lang['Send server']; ?>（SMTP）：</th>
                    <td>
                        <input type="text" id="mal_smtp_server" name="web[smtpserver]" class="span6"/>
                        <a href="javascript:;" class="ilsep" data-click="showRow"
                           data-param="{&quot;targetId&quot;: &quot;mal_smtp_row&quot;}">[<?php echo $lang['Set smtp port']; ?>
                            ]</a>
                    </td>
                </tr>
                <tr id="mal_smtp_row" style="display:none;">
                    <th><?php echo $lang['Smtp port']; ?>：</th>
                    <td>
                        <input type="text" id="mal_smtp_port" name="web[smtpport]" class="span6" value="25"/>
                        <label><input type="checkbox" name="web[smtpssl]" id=""
                                      value="1"/><?php echo $lang['Use ssl connect']; ?></label>
                    </td>
                </tr>
                <input type="hidden" name="moreinfo" value="1">
            <?php endif; ?>
        </table>
        <input type="hidden" name="inajax" value="1">
    </form>
</div>
