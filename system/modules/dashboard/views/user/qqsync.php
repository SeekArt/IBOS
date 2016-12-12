<?php

use application\core\utils\Ibos;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="<?php echo CHARSET; ?>">
    <title></title>
    <!-- load css -->
    <link rel="stylesheet" href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>">
    <!-- IE8 fixed -->
    <!--[if lt IE 9]>
    <link rel="stylesheet" href="<?php echo STATICURL; ?>/css/iefix.css?<?php echo VERHASH; ?>">
    <![endif]-->
</head>
<body>
<div style="padding: 20px; width: 400px;">
    <form class="form-horizontal" method="post"
          action="<?php echo Ibos::app()->createUrl('dashboard/organizationApi/syncuser'); ?>">
        <div class="control-group">
            <label class="control-label">确认同步</label>
            <div class="controls">
                是否要<?php if ($flag == '1'):echo '增加';
                elseif ($flag == '2'):echo '启用';
                else: echo '禁用';endif; ?>用户【<?php echo implode(',', $usernames); ?>】到企业QQ？
                <button type="submit" class="btn">是</button>
                /
                <button onclick="javascript:parent.Ui.closeDialog();" type="button" class="btn">否</button>
            </div>
        </div>
        <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
        <input type="hidden" name="uid" value="<?php echo $uid; ?>"/>
        <input type="hidden" name="type" value="qq"/>
        <input type="hidden" name="op" value="sync"/>
        <input type="hidden" name="flag" value="<?php echo $flag; ?>"/>
    </form>
</div>
</body>
</html>
