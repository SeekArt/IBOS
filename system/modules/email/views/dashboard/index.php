<?php

use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Email module']; ?></h1>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('dashboard/edit'); ?>" class="form-horizontal" method="post">
            <!-- 基本设置 -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Base setup']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label for="" class="control-label"><?php echo $lang['External mail function']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="emailexternalmail" value='1' id="" data-toggle="switch"
                                   class="visi-hidden"
                                   <?php if ($setting['emailexternalmail']): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label"><?php echo $lang['Allow recall']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="emailrecall" value='1' id="" data-toggle="switch"
                                   class="visi-hidden" <?php if ($setting['emailrecall']): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                    <!--                    <div class="control-group">
                                            <label for="" class="control-label"><?php echo $lang['System remind']; ?></label>
                                            <div class="controls">
                                                <input type="checkbox" name="emailsystemremind" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ($setting['emailsystemremind']): ?>checked<?php endif; ?>>
                                            </div>
                                        </div>-->
                </div>
            </div>
            <!--邮箱容量分配-->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Mailbox capacity allocation']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Default size']; ?></label>
                        <div class="controls">
                            <div class="input-group">
                                <input type="text" name="emaildefsize" value="<?php echo $setting['emaildefsize']; ?>">
                                <span class="input-group-addon">MB</span>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Role allocation']; ?></label>
                        <div class="controls email-controls">
                            <?php if (!empty($setting['emailroleallocation'])): ?>
                                <?php foreach ($setting['emailroleallocation'] as $key => $value): ?>
                                    <div class="mbs">
                                        <input type="text" name="role[<?php echo $key; ?>][positionid]"
                                               data-id="<?php echo $key; ?>" id="roleallocation_<?php echo $key; ?>"
                                               value="<?php echo StringUtil::wrapId($key, 'p'); ?>">
                                        <div id="roleallocation_<?php echo $key; ?>_box"></div>
                                    </div>
                                    <div class="input-group mbs">
                                        <input type="text" name="role[<?php echo $key; ?>][size]"
                                               value="<?php echo $value; ?>">
                                        <span class="input-group-addon">MB</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="mbs">
                                    <input type="text" name="role[0][positionid]" id="roleallocation" value="">
                                    <div id="roleallocation_box"></div>
                                </div>
                                <div class="input-group mbs">
                                    <input type="text" name="role[0][size]" value="">
                                    <span class="input-group-addon">MB</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <a href="javascript:;" class="add-one" id="email_option_add">
                                <i class="circle-btn-small o-plus"></i>
                                <?php echo $lang['Add role allocation']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"></label>
                <div class="controls">
                    <button type="submit" name="emailSubmit"
                            class="btn btn-primary btn-large btn-submit"> <?php echo $lang['Submit']; ?>    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- 新增容量分配模板 -->
<script type="text/ibos-template" id="email_template">
    <div class="mbs">
        <input type="text" name="<%=name%>" id="<%=id%>" value="">
        <div id="<%=boxid%>"></div>
    </div>
    <div class="input-group mbs">
        <input type="text" name="<%=size%>" value="">
        <span class="input-group-addon">MB</span>
    </div>
</script>
<script
    src="<?php echo Ibos::app()->assetManager->getAssetsUrl('email'); ?>/js/email_dashboard_index.js?<?php echo VERHASH; ?>"></script>
