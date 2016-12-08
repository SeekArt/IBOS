<?php

use application\core\utils\Env;
use application\core\utils\Ibos;

?>
<div class="aside">
    <div class="sbbf">
        <ul class="nav nav-strip nav-stacked">
            <!--
            <li <?php if (Env::getRequest('space') == 'top'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('officialdoc/index', array('type' => 'nosign', 'space' => 'top')); ?>">
                    <?php if ($noSignCount > 0): ?>
                        <span class="badge pull-right"><?php echo $noSignCount; ?></span>
                    <?php endif; ?>
                    <i class="o-art-unsign-m"></i>
                    <?php echo Ibos::lang('No sign'); ?>
                </a>
            </li>
            -->
            <li <?php if (Env::getRequest('space') != 'top'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('officialdoc/index'); ?>">
                    <i class="o-art-doc"></i>
                    <?php echo Ibos::lang('Officialdoc'); ?>
                </a>
                <ul id="tree" class="ztree">

                </ul>
            </li>
        </ul>
    </div>
</div>

<!-- Template: 分类编辑 -->
<script type="text/template" id="tpl_category_edit">
    <form action="javascript:;" class="form-horizontal form-compact" style="width: 300px;">
        <div class="control-group">
            <label class="control-label"><?php echo Ibos::lang('Category name', 'category'); ?></label>
            <div class="controls">
                <input type="text" class="input-small" name="name" value="<%=name%>">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?php echo Ibos::lang('Category parent', 'category'); ?></label>
            <div class="controls">
                <select class="input-small" name="pid">
                    <option value="0"><?php echo Ibos::lang('None'); ?></option>
                    <%= optionHtml %>
                </select>
            </div>
        </div>
            <div class="control-group">
                <label class="control-label">审批流程</label>
                <div class="controls">
                    <select class="input-small" name="aid" id="approval_id">
                        <option value="0">无需审核</option>
                        <?php foreach ($approvals as $approval): ?>
                            <option
                                value="<?php echo $approval['id']; ?>" <%= aid == "<?php echo CHtml::encode($approval['id']) ?>" ? "selected" : "" %>><?php echo CHtml::encode($approval['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
    </form>
</script>
<script>
    Ibos.app.setPageParam({
        catId: <?php echo $catid ?>
    })
</script>

