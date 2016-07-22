<?php

use application\core\utils\IBOS;
?>
<!-- 移动目录 -->
<div id="dialog_doc_move" style="width: 400px;">
    <div class="form-horizontal form-compact">
        <div class="control-group">
            <label class="control-label"><?php echo IBOS::lang( 'Directory' ); ?></label>
            <div class="controls">
                <select name="articleCategory"  id="articleCategory">
					<?php echo $move; ?>
                </select>               
            </div>
        </div>
    </div>
</div>
