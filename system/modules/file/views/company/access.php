<!-- 设置权限 -->
<div id="set_access_dialog" style="width: 500px;">
    <div class="alert alert-main mbz"><?php echo $lang['Blank means allow all']; ?></div>
    <form id="access_dialog_form" method="post" class="fill">
        <div class="form-horizontal form-narrow">
            <div class="control-group">
                <label class="control-label">
                    <?php echo $lang['Read only']; ?>
                    <p class="fss tcm"><?php echo $lang['Read only operation']; ?></p>
                </label>
                <div class="controls">
                    <input type="text" name="rScope" data-toggle="userSelect" id="rScope"
                           value="<?php echo $rScope; ?>">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">
                    <?php echo $lang['Read and write']; ?>
                </label>
                <div class="controls">
                    <input type="text" name="wScope" data-toggle="userSelect" id="wScope"
                           value="<?php echo $wScope; ?>">
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // 权限设置选人框
    $("#rScope, #wScope").userSelect({
        data: Ibos.data.get()
    });
</script>
