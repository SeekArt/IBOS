<?php

use application\core\utils\DateTime;
use application\core\utils\Ibos;
use application\modules\user\model\User;

?>
<div class="pdhs" id="work_handle_form_content">
    <ul class="list inset">
        <li>
            <div><?php echo $run['name']; ?></div>
            <div class="content">
                <p class="fss">[<?php echo $run['runid'] ?>] <?php echo $flow['name']; ?></p>
                <p class="fss"><?php echo Ibos::lang("Begin user"); ?><?php echo User::model()->fetchRealnameByUid($run['beginuser']); ?></p>
                <p class="fss"><?php echo Ibos::lang("Begin time"); ?><?php echo DateTime::getlunarCalendar($run['begintime']); ?></p>
            </div>
            <button class="button small wf-vsf-btn" -data-evt="viewSourceForm" data-param='{"runId": 1}'>
                <a href="javascript:void(0);"
                   onclick="window.open('<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?r=workflow/preview/print&key=' . $_REQUEST['key']; ?>', '_blank', 'location=yes')"><i
                        class="moicon mo-search-gray"></i>
                    <?php echo Ibos::lang("Viewform"); ?></a>
            </button>
        </li>
        <?php if (is_array($valueArr)) {
            foreach ($valueArr as $key => $value): ?>
                <li class="control-group bggrl xca">
                    <label><?php echo $value['data-title']; ?></label>
                    <div class="controls"><?php echo $value['value']; ?></div>
                </li>
            <?php endforeach;
        } ?>
    </ul>
    <ul class="list inset">
        <?php if (is_array($enableArr)) {
            foreach ($enableArr as $key => $value):?>
                <li class="control-group">
                    <label class="xcp"><?php echo $value['data-title']; ?></label>
                    <div class="controls">
                        <?php echo $value['value']; ?>
                    </div>
                </li>
            <?php endforeach;
        } ?>
    </ul>
    <button class="button large button-block" -data-evt="showFormNullTerm" data-param='{"runId": 1}'
            onclick="$('#hidden_field').toggle()">显示表单空项
    </button>
    <ul class="list inset hide" id="hidden_field">
        <?php if (is_array($emptyArr)) {
            foreach ($emptyArr as $key => $value): ?>
                <li class="control-group bggrl xca">
                    <label><?php echo $value['data-title']; ?></label>
                    <div class="controls">
                        <?php //echo $value['value']; ?>
                    </div>
                </li>
            <?php endforeach;
        } ?>
    </ul>
</div>