<?php

use application\core\utils\Ibos;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo Ibos::app()->setting->get('title'); ?></title>
    <link rel="shortcut icon" href="<?php echo STATICURL; ?>/image/favicon.ico">
    <meta name="generator" content="IBOS <?php echo VERSION; ?>"/>
    <meta name="author" content="IBOS Team"/>
    <meta name="copyright" content="2013 IBOS Inc."/>
    <!-- load css -->
    <link rel="stylesheet" type="text/css" rev="stylesheet"
          href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>"/>
    <link rel="stylesheet" type="text/css" rev="stylesheet"
          href="<?php echo STATICURL; ?>/css/common.css?<?php echo VERHASH; ?>">
    <!-- load css end -->
    <!-- IE8 fixed -->
    <!--[if lt IE 9]>
    <link rel="stylesheet" href="<?php echo STATICURL; ?>/css/iefix.css?<?php echo VERHASH; ?>">
    <![endif]-->
</head>
<body>
<div class="wrap clearfix">
    <div>
        <div class="fill">
            <form onsubmit="return checkpass()" action="<?php echo $this->createUrl('default/reset'); ?>" method="post"
                  class="form-horizontal form-narrow">
                <div class="data-title mb">
                    <i class="o-change-password"></i><span class="fsl vam">你好，<?php echo $user; ?>
                        。系统已设置密码过期保护。您的密码已过期，请先修改密码</span>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php echo $lang['Original password']; ?><span
                            class="xcr">*</span></label>
                    <div class="controls">
                        <input type="password" name="originalpass" length="32" id="origpass" class="span8"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php echo $lang['New password']; ?><span class="xcr">*</span></label>
                    <div class="controls">
                        <input type="password" name="newpass" length="32" id="newpass" class="span8"/>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php echo $lang['Confirm new password']; ?><span class="xcr">*</span></label>
                    <div class="controls">
                        <input type="password" name="newpass_confirm" length="32" class="span8"/>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
                        <input type="submit" value="<?php echo $lang['Submit']; ?>"
                               class="btn btn-primary btn-large btn-great"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function checkpass() {
        var length = '<?php echo $account['minlength']; ?>';
        var newpass = document.getElementById('newpass').value, orig = document.getElementById('origpass').value;
        if (newpass === orig) {
            alert('修改密码不能与原密码一致');
            return false;
        }
        if (newpass.length < length) {
            alert('密码长度不能小于' + length + '位');
            return false;
        }
        <?php if ($account['mixed'] == '1'): ?>
        var reg = /^(?!\D+$)(?![^a-zA-Z]+$)\S{5,32}$/;
        if (!reg.test(newpass)) {
            alert('密码必须同时包含英文与数字');
            return false;
        }
        <?php endif; ?>
        return true;
    }
</script>
</body>
</html>