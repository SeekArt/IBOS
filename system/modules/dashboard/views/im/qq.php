<?php

use application\core\utils\DateTime;
use application\core\utils\Ibos;

$rtxIsOpen = Ibos::app()->setting->get('setting/im/rtx/open');
?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Instant messaging binding']; ?></h1>
        <ul class="mn">
            <?php if (LOCAL && $rtxIsOpen): ?>
                <li>
                    <a href="<?php echo $this->createUrl('im/index', array('type' => 'rtx')); ?>"><?php echo $lang['Rtx Setup']; ?></a>
                </li>
            <?php endif; ?>
            <li hidden = "hidden">
                <span><?php echo $lang['QQ setup']; ?></span>
            </li>
        </ul>
    </div>
    <div id="cm_content">
        <div>
            <form action="<?php echo $this->createUrl('im/index'); ?>" method='post' class="form-horizontal">
                <!-- 企业QQ设置 start -->
                <div class="ctb">
                    <h2 class="st"><?php echo $lang['QQ setup']; ?></h2>
                    <div class="alert trick-tip">
                        <div class="trick-tip-title">
                            <i></i>
                            <strong><?php echo $lang['Skills prompt']; ?></strong>
                        </div>
                        <div class="trick-tip-content">
                            <?php echo $lang['QQ setup tip']; ?>
                        </div>
                    </div>
                    <div class="ctbw">
                        <div class="control-group">
                            <label class="control-label"><?php echo $lang['QQ binding']; ?></label>
                            <div class="controls row">
                                <div class="span4">
                                    <input type="checkbox" value='1' name="open" id="QQ_enable" data-toggle="switch"
                                           class="visi-hidden"
                                           <?php if ($im['open'] == '1'): ?>checked<?php endif; ?> />
                                </div>
                                <div class="span8">
                                    <a target="_blank"
                                       href="http://b.qq.com/eim/home.html"><?php echo $lang['about BQQ']; ?></a><br>
                                    <a target="_blank"
                                       href="http://doc.ibos.com.cn/article/detail/id/69"><?php echo $lang['Click to view helper doc']; ?></a>
                                </div>
                            </div>
                        </div>
                        <div id="QQ_setup" class="b-m"
                             <?php if ($im['open'] == '0'): ?>style="display: none;"<?php endif; ?>>
                            <div class="control-group">
                                <label class="control-label">App ID</label>
                                <div class="controls">
                                    <input type="text" name='appid' value="<?php echo $im['appid']; ?>"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">App Secret</label>
                                <div class="controls">
                                    <input type="text" name='appsecret' value="<?php echo $im['appsecret']; ?>"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['QQ id']; ?></label>
                                <div class="controls">
                                    <input type="text" disabled value="<?php echo $im['id']; ?>"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['QQ token']; ?></label>
                                <div class="controls">
                                    <input type="text" disabled value="<?php echo $im['token']; ?>"/>
                                    <?php if (!isset($im['checkpass'])): ?>
                                        <span class="db tcm">如果没有以上两项的信息，请阅读提示</span>
                                    <?php else: ?>
                                        <?php if (empty($im['refresh_token']) && empty($im['time'])): ?>
                                            <span class="db tcm">您没有通过系统自动获取TOKEN，无法自动更新TOKEN，请注意过期时间</span>
                                        <?php else: ?>
                                            <?php
                                            $secs = TIMESTAMP - $im['time'];
                                            $timestr = DateTime::getTime($secs);
                                            ?>
                                            <span
                                                class="db tcm">您通过系统自动获取TOKEN，您的TOKEN有效期一般为30天，现已使用<?php echo $timestr; ?>
                                                。</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['QQ sso']; ?></label>
                                <div class="controls row">
                                    <div class="span4">
                                        <input type="checkbox" name="sso" value="1"
                                               <?php if ($im['sso'] == '1'): ?>checked<?php endif; ?>
                                               data-toggle="switch"/>
                                    </div>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['Push setting']; ?></label>
                                <div class="controls">
                                    <label class="checkbox">
                                        <input name='push[note]' value='1'
                                               <?php if ($im['push']['note'] == '1'): ?>checked<?php endif; ?>
                                               type="checkbox"/>
                                        <span class="mls"><?php echo $lang['Notice push']; ?></span>
                                    </label>
                                    <label class="checkbox" title="因为QQ接口的关系，目前暂不支持消息推送">
                                        <input name='push[msg]' value='1'
                                               <?php if (isset($im['push']['msg']) && $im['push']['msg'] == '1'): ?>checked<?php endif; ?>
                                               type="checkbox"/>
                                        <span class="mls"><?php echo $lang['Message push']; ?></span>
                                    </label>
                                </div>
                            </div>
                            <div class="control-group">
                                <label for="" class="control-label"><?php echo $lang['QQ show unread']; ?></label>
                                <div class="controls">
                                    <input type="checkbox" name="showunread" value="1"
                                           <?php if ($im['showunread'] == '1'): ?>checked<?php endif; ?>
                                           data-toggle="switch"/>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['QQ sync account']; ?></label>
                                <div class="controls">
                                    <input type="checkbox" value="1" name="syncuser"
                                           <?php if ($im['syncuser'] == '1'): ?>checked<?php endif; ?>
                                           data-toggle="switch" class="mlm"/>
                                    <span class="db tcm"><?php echo $lang['QQ sync account tip']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($im['checkpass']) && $im['checkpass'] == '1'): ?>
                        <div class="ctb">
                            <h2 class="st"><?php echo $lang['QQ binding user']; ?></h2>
                            <div class="ctbw">
                                <div class="control-group">
                                    <label class="control-label"><?php echo $lang['Binding method']; ?></label>
                                    <div class="controls">
                                        <div class="clearfix">
                                            <div class="pull-left ml">
                                                <button type="button" data-click="mb"
                                                        class="btn"><?php echo $lang['Manual binding']; ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="controls">
                        <button class="btn btn-primary btn-large btn-submit" name="imSubmit"
                                type="submit"><?php echo $lang['Submit']; ?></button>
                    </div>
                </div>
                <input type='hidden' name='refresh_token'
                       value='<?php echo isset($im['refresh_token']) ? $im['refresh_token'] : ''; ?>'/>
                <input type='hidden' name='expires_in'
                       value='<?php echo isset($im['expires_in']) ? $im['expires_in'] : ''; ?>'/>
                <input type='hidden' name='time' value='<?php echo isset($im['time']) ? $im['time'] : 0; ?>'/>
                <input type='hidden' name='id' value='<?php echo $im['id']; ?>'/>
                <input type='hidden' name='token' value='<?php echo $im['token']; ?>'/>
                <input type='hidden' name='type' value='<?php echo $type; ?>'/>
            </form>
        </div>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_im.js"></script>