<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/calendar.css?<?php echo VERHASH; ?>">
<!-- Task start -->
<div class="mc clearfix">
    <!-- Sidebar start -->
    <?php echo $this->getSidebar(); ?>
    <!-- Sidebar end -->
    <!-- Task right start -->
    <div class="mcr">
        <div class="mc-header">
            <div class="mc-header-info clearfix">
                <div class="usi-terse">
                    <a href="" class="avatar-box">
                        <span class="avatar-circle">
                            <img class="mbm" src="<?php echo $user['avatar_middle']; ?>" alt="">
                        </span>
                    </a>
                    <span class="usi-terse-user"><?php echo $user['realname']; ?></span>
                    <span class="usi-terse-group"><?php echo $user['deptname']; ?></span>
                </div>
            </div>
        </div>

        <div class="page-list">
            <div class="page-list-header">
                <form
                    action="<?php echo $this->createUrl('task/index', array('param' => 'search', 'uid' => $user['uid'], 'complete' => $complete)) ?>"
                    method="post" id="form_serch">
                    <div class="search span3 pull-right">
                        <input type="text" name="keyword" placeholder="Search" id="mn_search" nofocus>
                        <a href="javascript:;"></a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
                <div class="btn-toolbar">
                    <div class="btn-group ">
                        <a href="<?php echo $this->createUrl('task/index', array('complete' => 0)); ?>"
                           class="btn <?php echo $complete == 1 ? "" : "active" ?>"><?php echo $lang['Uncompleted']; ?></a>
                        <a href="<?php echo $this->createUrl('task/index', array('complete' => 1)); ?>"
                           class="btn <?php echo $complete == 0 ? "" : "active" ?>"><?php echo $lang['Completed']; ?></a>
                    </div>
                </div>
            </div>
            <div class="page-list-mainer" id="uncomp-list">
                <?php if ($complete == 0): ?>
                    <div class="todo-header" id="todo-header">
                        <input type="text" placeholder="<?php echo $lang['Click to add a new task']; ?>" id="todo_add">
                    </div>
                <?php endif; ?>
                <div class="no-data-tip" style="display:none" id="no_data_tip"></div>
                <div class="todo-list" id="todo_list">
                </div>
            </div>
            <div class="page-list-footer">
                <div class="pull-right">
                    <?php
                    if (isset($complete) && $complete == 1 && isset($pages)) {
                        $this->widget('application\core\widgets\Page', array('pages' => $pages));
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
    <!-- Task right end -->
</div>
<!-- Task end -->
<script>
    Ibos.app.s({
        taskData: <?php echo $todolist; ?>
    })
</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/todolist.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/calendar_task_index.js?<?php echo VERHASH; ?>'></script>
