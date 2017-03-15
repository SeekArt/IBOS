<?php

use application\core\utils\Ibos;

?>
<div class="mc mcf clearfix">
    <?php echo $this->getHeader($lang); ?>
    <div>
        <div>
            <ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
                <li>
                    <a href="<?php echo $this->createUrl('home/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Home page']; ?></a>
                </li>
                <?php if ($this->getIsWeiboEnabled()): ?>
                    <li><a
                        href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Weibo']; ?></a>
                    </li><?php endif; ?>
                <?php if ($this->getIsMe()): ?>
                    <li>
                        <a href="<?php echo $this->createUrl('home/credit', array('uid' => $this->getUid())); ?>"><?php echo $lang['Credit']; ?></a>
                    </li>
                <?php endif; ?>
                <li class="active">
                    <a href="<?php echo $this->createUrl('home/personal', array('uid' => $this->getUid())); ?>"><?php echo $lang['Profile']; ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="pc-header clearfix">
    <ul class="nav nav-skid">
        <li>
            <a href="<?php echo $this->createUrl('home/personal', array('op' => 'profile', 'uid' => $this->getUid())); ?>">
                <?php echo $lang['My profile']; ?>
            </a>
        </li>
        <?php if ($this->getIsMe()): ?>
            <li>
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'avatar', 'uid' => $this->getUid())); ?>"><?php echo $lang['Upload avatar']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'password', 'uid' => $this->getUid())); ?>"><?php echo $lang['Change password']; ?></a>
            </li>
            <!-- 因管理后台-云服务功能暂时屏蔽，所以前台对应功能也屏蔽 ->
<!--            <li class="active"><a-->
<!--                    href="--><?php //echo $this->createUrl('home/personal', array('op' => 'remind', 'uid' => $this->getUid())); ?><!--">--><?php //echo $lang['Remind setup']; ?><!--</a>-->
<!--            </li>-->
            <li>
                <a href="<?php echo $this->createUrl('home/personal', array('op' => 'history', 'uid' => $this->getUid())); ?>"><?php echo $lang['Login history']; ?></a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<div>
    <div class="pc-container clearfix dib left-sidebar">
        <div>
            <?php if (!empty($nodeList)): ?>
                <form class='form-horizontal' method='post' action='<?php echo $this->createUrl('home/personal'); ?>'>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th width="80"><?php echo $lang['Module name']; ?></th>
                            <th><?php echo $lang['Remind desc']; ?></th>
                            <th width="80"><?php echo $lang['Email remind']; ?></th>
                            <th width="80"><?php echo $lang['Sms remind']; ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($nodeList as $id => $node): ?>
                            <tr>
                                <td><?php echo $node['moduleName']; ?></td>
                                <td><?php echo $node['nodeinfo']; ?></td>
                                <td>
                                    <label class="checkbox"
                                           <?php if ($node['maildisabled']): ?>title='<?php echo $lang['Bind email first']; ?>'<?php endif; ?>>
                                        <input type="checkbox" name="email[<?php echo $id; ?>]" value='1'
                                               <?php if ($node['emailcheck'] and !$node['maildisabled']): ?>checked<?php endif; ?>
                                               <?php if ($node['maildisabled']): ?>disabled<?php endif; ?> />
                                    </label>
                                </td>
                                <td>
                                    <label class="checkbox"
                                           <?php if ($node['smsdisabled']): ?>title='<?php echo $lang['Bind mobile first']; ?>'<?php endif; ?>>
                                        <input type="checkbox" name="sms[<?php echo $id; ?>]" value='1'
                                               <?php if ($node['smscheck'] and !$node['smsdisabled']): ?>checked<?php endif; ?>
                                               <?php if ($node['smsdisabled']): ?>disabled<?php endif; ?> />
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="4">
                                <div class="pull-right">
                                    <input type='hidden' name='formhash' value='<?php echo FORMHASH; ?>'/>
                                    <input type='hidden' name='op' value='remind'/>
                                    <input type="submit" name='userSubmit' value="<?php echo $lang['Save']; ?>"
                                           class="btn btn-large btn-primary btn-great">
                                </div>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </form>
            <?php else: ?>
                <div class='no-data-tip'></div>
            <?php endif; ?>
        </div>
    </div>
    <!-- 右栏 绑定信息 -->
    <div class="dib right-sidebar">
        <div class="sidebar-header">
            <span class="header-title"><i class="o-bind-info"></i><?php echo $lang['Bind info']; ?></span>
        </div>
        <div class="bind-item clearfix">
            <i class="o-mail-<?php if ($user['validationemail'] == 1): ?>bind<?php else: ?>unbind<?php endif; ?>"></i>
            <div class="dib vam mls">
                <div><?php echo $lang['Email address']; ?><?php if ($user['validationemail'] == 1): ?><span
                        class="fss tcm">(<?php echo $lang['Already bind']; ?>)</span><?php endif; ?></div>
                <div><?php echo $user['email']; ?></div>
            </div>
            <div class="dib pull-right">
                <?php if ($user['validationemail'] == 1): ?>
                    <a href="javascript:;" data-action="bind" data-param='{"type": "email"}'
                       class="pull-right btn"><?php echo $lang['Modify']; ?></a>
                <?php else: ?>
                    <a href="javascript:;" data-action="bind" data-param='{"type": "email"}'
                       class="pull-right btn"><?php echo $lang['Bind']; ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="bind-item">
            <i class="o-phone-<?php if ($user['validationmobile'] == 1): ?>bind<?php else: ?>unbind<?php endif; ?>"></i>
            <div class="dib vam mls">
                <div><?php echo $lang['Mobile number']; ?><?php if ($user['validationmobile'] == 1): ?><span
                        class="fss tcm">(<?php echo $lang['Already bind']; ?>)</span><?php endif; ?></div>
                <div><?php echo $user['mobile']; ?></div>
            </div>
            <div class="dib pull-right">
                <?php if ($user['validationmobile'] == 1): ?>
                    <a href="javascript:;" data-action="bind" data-param='{"type": "mobile"}'
                       class="pull-right btn"><?php echo $lang['Modify']; ?></a>
                <?php else: ?>
                    <a href="javascript:;" data-action="bind" data-param='{"type": "mobile"}'
                       class="pull-right btn"><?php echo $lang['Bind']; ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($cobinding)): ?>
            <div class="bind-item clearfix">
                <?php if ($co): ?>
                    <i class="o-ibosco-bind"></i>
                    <!-- 解绑酷办公账号 -->
                    <a href="javascript:;" data-action="relieveIbosco" class="pull-right btn">解绑</a>
                <?php else: ?>
                    <i class="o-ibosco-unbind"></i>
                    <!-- 绑定酷办公账号 -->
                    <a href="javascript:;" data-action="bindIbosco" class="pull-right btn">绑定</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($wxqybinding)): ?>
            <div class="bind-item clearfix">
                <?php if ($wxqy): ?>
                    <i class="o-iboswxqy-bind"></i>
                <?php else: ?>
                    <i class="o-iboswxqy-unbind"></i>
                <?php endif; ?>
                <div class="dib vam mls">
                    <div>企业号账号</div>
                    <div><?php echo $value; ?></div>
                </div>
                <div class="dib pull-right">
                    <?php if ($wxqy): ?>
                        <!-- 解绑企业号账号 -->
                        <a href="javascript:;" data-action="relieveIboswxqy" class="pull-right btn">解绑</a>
                    <?php else: ?>
                        <!-- 绑定企业号账号 -->
                        <a href="javascript:;" data-action="bind" data-param='{"type": "wxqy"}' class="pull-right btn">绑定</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    Ibos.app.s({
        "currentUid": "<?php echo $this->getUid(); ?>"
    })
</script>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
