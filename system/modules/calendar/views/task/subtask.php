<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/calendar.css?<?php echo VERHASH; ?>">
<!-- Task start -->
<div class="mc clearfix">
    <!-- Sidebar start -->
    <?php echo $this->getSubSidebar(); ?>
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
                    action="<?php echo $this->createUrl('task/subtask', array('param' => 'search', 'uid' => $user['uid'], 'complete' => $complete)) ?>"
                    method="post" id="form_serch">
                    <div class="search span3  pull-right ml">
                        <input type="text" name="keyword" placeholder="Search" id="mn_search" nofocus>
                        <a href="javascript:;"></a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
                <div class="btn-toolbar">
                    <div class="btn-group ">
                        <a href="<?php echo $this->createUrl('task/subtask', array('complete' => 0, 'uid' => $user['uid'])); ?>"
                           class="btn <?php echo $complete == 1 ? "" : "active" ?>"><?php echo $lang['Uncompleted']; ?></a>
                        <a href="<?php echo $this->createUrl('task/subtask', array('complete' => 1, 'uid' => $user['uid'])); ?>"
                           class="btn <?php echo $complete == 0 ? "" : "active" ?>"><?php echo $lang['Completed']; ?></a>
                    </div>
                </div>
            </div>
            <div class="page-list-mainer" id="uncomp-list">
                <?php if ($allowEditTask == "1" && $complete == 0): ?>
                    <div class="todo-header" id="todo-header">
                        <input type="text" placeholder="<?php echo $lang['Click to add a new task']; ?>" id="todo_add">
                    </div>
                <?php endif; ?>
                <div class="no-data-tip" style="display:none" id="no_data_tip"></div>
                <div class="todo-list" id="todo_list"></div>
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
    Ibos.app.setPageParam({
        // 当前下属 Uid
        subUid: "<?php echo $user['uid'] ?>",
        // 当前下属上司 Uid
        supUid: "<?php echo isset($supUid) ? $supUid : 0; ?>",
        allowEditTask: "<?php echo $allowEditTask?>",
        taskData: <?php echo $todolist; ?>
    })
</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/todolist.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/calendar_task_subtask.js?<?php echo VERHASH; ?>'></script>
