<?php
use application\core\utils\Ibos;

?>

<div class="aside">
    <div class="sbbf sbbl">
        <?php if ($publishAuthority): ?>
            <div class="sbbf-top">
                <a href="<?php echo Ibos::app()->createUrl('article/default/add'); ?>"
                   class="btn btn-warning btn-sbbf-top">
                    <i class="o-bar-write"></i><?php echo Ibos::lang('Publish Article'); ?>
                </a>
            </div>
        <?php endif; ?>
        <div>
            <ul class="nav nav-stacked nav-strip">
                <?php if ($publishAuthority): ?>
                    <li <?php if ($control == 'publish'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo Ibos::app()->createUrl('article/publish/index'); ?>">
                            <i class="o-bar-publish"></i>
                            <?php echo Ibos::lang('My Publish'); ?>
                            <span class="badge pull-right" data-count="new" style="display: none;"></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($verifyAuthority): ?>
                    <li <?php if ($control == 'verify'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo Ibos::app()->createUrl('article/verify/index'); ?>">
                            <i class="o-bar-approval"></i>
                            <?php echo Ibos::lang('My Approval'); ?>
                            <span class="badge pull-right" data-count="new" style="display: none;"></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($showAuthority): ?>
                    <li <?php if ($control == 'default'): ?>class="active"<?php endif; ?>>
                        <a href="<?php echo Ibos::app()->createUrl('article/default/index'); ?>">
                            <i class="o-bar-list"></i>
                            <?php echo Ibos::lang('Information center'); ?>
                            <span class="badge pull-right" data-count="new" style="display: none;"></span>
                        </a>
                        <?php if ($control == 'default'): ?>
                            <ul id="c_tree" class="ztree posr"></ul>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!--移动-->
<script type="text/template" id="dialog_art_move">
    <div style="width: 400px; ">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label class="control-label">目录</label>
                <div class="controls">
                    <select name="articleCategory">
                        <%= optionHtml %>
                    </select>
                </div>
            </div>
        </div>
    </div>
</script>
<!-- 设置置顶 -->
<script type="text/template" id="dialog_art_top">
    <div class="form-horizontal form-compact" style="width: 400px; display:none;">
        <form action="javascript:;">
            <div class="control-group">
                <label class="control-label" id="test"><?php echo Ibos::lang('TurnOn Top'); ?></label>
                <div class="controls">
                    <input type="checkbox" value="1" name="totop" data-toggle="switch" checked>
                </div>
            </div>
            <div class="control-group top_mc">
                <label class="control-label" id="test"><?php echo Ibos::lang('Expired time'); ?></label>
                <div class="controls">
                    <div class="datepicker" id="date_time_top">
                        <a href="javascript:;" class="datepicker-btn"></a>
                        <input type="text" class="datepicker-input" name="topEndTime" value="<%= date %>"/>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>
<!-- 高亮对话框  -->
<script type="text/template" id="dialog_art_highlight">
    <div class="form-horizontal form-compact" style="width: 400px; display:none;">
        <form action="javascript:;">
            <div class="control-group">
                <label class="control-label" id="test"><?php echo Ibos::lang('TurnOn Highlight'); ?></label>
                <div class="controls">
                    <input type="checkbox" value="1" name="tohighlight" data-toggle="switch" checked>
                </div>
            </div>
            <div class="highlight_mc">
                <div class="control-group">
                    <label class="control-label" id="test"><?php echo Ibos::lang('Expired time'); ?></label>
                    <div class="controls">
                        <div class="datepicker" id="date_time_highlight">
                            <a href="javascript:;" class="datepicker-btn"></a>
                            <input type="text" class="datepicker-input" name="highlightEndTime"
                                   value="<%= date %>"/>
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls" id="simple_editor"></div>
                    <input type="hidden" id="highlight_color" name="highlight_color" value="00b050">
                    <input type="hidden" id="highlight_bold" name="highlight_bold" value="0">
                    <input type="hidden" id="highlight_italic" name="highlight_italic" value="0">
                    <input type="hidden" id="highlight_underline" name="highlight_underline" value="0">
                </div>
            </div>
        </form>
    </div>
</script>
<!-- 退回 -->
<script type="text/template" id="dialog_rollback_reason">
    <div style="display:none;">
        <form action="javascript:;" method="post">
            <textarea rows="8" cols="60" name="backreason" placeholder="退回理由...."></textarea>
        </form>
    </div>
</script>
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
                                value="<?php echo $approval['id']; ?>" <%= aid == "<?php echo $approval['id'] ?>" ? "selected" : "" %>><?php echo CHtml::encode($approval['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
    </form>
</script>
<script type="text/javascript">
    Ibos.app.s('treeInit', '<?php echo ($showAuthority && $control == "default") ? "1" : "0"; ?>')
</script>
	