<?php

use application\core\utils\StringUtil;

?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Email setting']; ?></h1>
        <ul class="mn">
            <li>
                <span><?php echo $lang['Setup']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('email/check'); ?>"><?php echo $lang['Check']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('email/setup'); ?>" method='post' class="form-horizontal">
            <!-- 邮件发送设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Email sent setting']; ?></h2>
                <div class="">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Email sent method']; ?></label>
                        <div class="controls" id="sent_method">
                            <label class="radio" data-target="#method_socket">
                                <input type="radio" value="1" name="mailsend"
                                       <?php if ($mail['mailsend'] == 1): ?>checked<?php endif; ?> />
                                <?php echo $lang['Email sent method socket']; ?>
                            </label>
                            <label class="radio" data-target="#method_smtp">
                                <input type="radio" value="2" name="mailsend"
                                       <?php if ($mail['mailsend'] == 2): ?>checked<?php endif; ?> />
                                <?php echo $lang['Email sent method smtp']; ?>
                            </label>
                        </div>
                    </div>
                    <div id="mail_setup_box">
                        <div id="method_socket"
                             <?php if ($mail['mailsend'] == 2): ?>style="display: none;"<?php endif; ?>>
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>SMTP <?php echo $lang['Server']; ?></th>
                                    <th width="60"><?php echo $lang['Port']; ?></th>
                                    <th width="60"><?php echo $lang['Validation']; ?></th>
                                    <th><?php echo $lang['Send address']; ?></th>
                                    <th>SMTP <?php echo $lang['Auth username']; ?></th>
                                    <th>SMTP <?php echo $lang['Auth password']; ?></th>
                                    <th width="60"></th>
                                </tr>
                                </thead>
                                <tbody id="socket_setup_tbody">
                                <!-- 显示行 查改删-->
                                <?php if ($mail['mailsend'] == 1): ?>
                                    <?php foreach ($mail['server'] as $key => $value): ?>
                                        <tr>
                                            <td>
                                                <input type="text" name='socket[<?php echo $key; ?>][server]'
                                                       value='<?php echo $value['server']; ?>' class="input-small">
                                            </td>
                                            <td>
                                                <input type="text" name='socket[<?php echo $key; ?>][port]'
                                                       value='<?php echo $value['port']; ?>' class="input-small">
                                            </td>
                                            <td>
                                                <label class="checkbox">
                                                    <input type="checkbox" name='socket[<?php echo $key; ?>][auth]'
                                                           value="1"
                                                           <?php if ($value['auth'] == 1): ?>checked<?php endif; ?> />
                                                </label>
                                            </td>
                                            <td>
                                                <input type="text" name='socket[<?php echo $key; ?>][from]'
                                                       class="input-small" value='<?php echo $value['from']; ?>'/>
                                            </td>
                                            <td>
                                                <input type="text" name='socket[<?php echo $key; ?>][username]'
                                                       class="input-small" value='<?php echo $value['username']; ?>'/>
                                            </td>
                                            <td>
                                                <input type="text" name='socket[<?php echo $key; ?>][password]'
                                                       class="input-small"
                                                       value='<?php echo StringUtil::passwordMask($value['password']); ?>'/>
                                            </td>
                                            <td>
                                                <a href="javascript:;" title="<?php echo $lang['Del']; ?>"
                                                   class="cbtn o-trash"></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <!-- 增加行 -->
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <a href="javascript:;" data-id='socket' id="add_socket_item"
                                           class="cbtn o-plus"></a>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="method_smtp"
                             <?php if ($mail['mailsend'] == 1): ?>style="display: none;"<?php endif; ?>>
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>SMTP <?php echo $lang['Server']; ?></th>
                                    <th width="60"><?php echo $lang['Port']; ?></th>
                                    <th width="60"></th>
                                </tr>
                                </thead>
                                <tbody id="smtp_setup_tbody">
                                <?php if ($mail['mailsend'] == 2): ?>
                                    <?php foreach ($mail['server'] as $key => $value): ?>
                                        <!-- 显示行 查改删-->
                                        <tr>
                                            <td>
                                                <input type="text" name='smtp[<?php echo $key; ?>][server]'
                                                       value='<?php echo $value['server'] ?>' class="input-small">
                                            </td>
                                            <td>
                                                <input type="text" name='smtp[<?php echo $key; ?>][port]'
                                                       value='<?php echo $value['port'] ?>' class="input-small">
                                            </td>
                                            <td>
                                                <a href="javascript:;" title="<?php echo $lang['Del']; ?>"
                                                   class="cbtn o-trash"></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <!-- 增加行 -->
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3">
                                        <a href="javascript:;" data-id='smtp' id="add_smtp_item"
                                           class="cbtn o-plus"></a>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Email delimiter']; ?></label>
                        <div class="controls">
                            <label class="radio">
                                <input type="radio" value='1' name="maildelimiter"
                                       <?php if ($mail['maildelimiter'] == 1): ?>checked<?php endif; ?> />
                                <?php echo $lang['Email delimiter windows']; ?>
                            </label>
                            <label class="radio">
                                <input type="radio" value='2' name="maildelimiter"
                                       <?php if ($mail['maildelimiter'] == 2): ?>checked<?php endif; ?> />
                                <?php echo $lang['Email delimiter linux']; ?>
                            </label>
                            <label class="radio">
                                <input type="radio" value='3' name="maildelimiter"
                                       <?php if ($mail['maildelimiter'] == 3): ?>checked<?php endif; ?> />
                                <?php echo $lang['Email delimiter mac']; ?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo $lang['Email include username']; ?>
                        </label>
                        <div class="controls">
                            <input type="checkbox" name="mailusername"
                                   <?php if ($mail['mailusername'] == 1): ?>checked<?php endif; ?> value='1'
                                   data-toggle="switch" class="visi-hidden"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo $lang['Email sent silent']; ?>
                        </label>
                        <div class="controls">
                            <input type="checkbox" name="sendmailsilent"
                                   <?php if ($mail['sendmailsilent'] == 1): ?>checked<?php endif; ?> value='1'
                                   data-toggle="switch" class="visi-hidden"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"></label>
                        <div class="controls">
                            <button name='emailSubmit' class="btn btn-primary btn-large btn-submit"
                                    type="submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/template" id="mail_socket_template">
    <tr>
        <td>
            <input type="text" class="input-small" name="newsocket[<%=id%>][server]"/>
        </td>
        <td>
            <input type="text" class="input-small" value="25" name="newsocket[<%=id%>][port]">
        </td>
        <td>
            <label class="checkbox">
                <input type="checkbox" value="1" name="newsocket[<%=id%>][auth]"/>
            </label>
        </td>
        <td>
            <input type="text" class="input-small" name="newsocket[<%=id%>][from]"/>
        </td>
        <td>
            <input type="text" class="input-small" name="newsocket[<%=id%>][username]"/>
        </td>
        <td>
            <input type="text" class="input-small" name="newsocket[<%=id%>][password]">
        </td>
        <td>
            <a href="javascript:;" title="<?php echo $lang['Del']; ?>" class="cbtn o-trash"/>
        </td>
    </tr>
</script>
<script type="text/template" id="mail_smtp_template">
    <tr>
        <td>
            <input type="text" class="input-small" name="newsmtp[<%=id%>][server]"/>
        </td>
        <td>
            <input type="text" class="input-small" value="25" name="newsmtp[<%=id%>][port]">
        </td>
        <td>
            <a href="javascript:;" title="<?php echo $lang['Del']; ?>" class="cbtn o-trash"></a>
        </td>
    </tr>
</script>
<script src="<?php echo $assetUrl; ?>/js/db_email.js"></script>