<?php

use application\core\utils\Ibos;

?>
<!-- 移动目录 -->
<div id="dialog_doc_move" style="width: 400px;">
    <div class="form-horizontal form-compact">
        <div class="control-group">
            <label class="control-label"><?php echo Ibos::lang('Directory'); ?></label>
            <div class="controls">
                <select name="articleCategory" id="articleCategory">
                    <?php echo $move; ?>
                </select>
            </div>
        </div>
    </div>
</div>
