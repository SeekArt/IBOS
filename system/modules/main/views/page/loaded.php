<?php

use application\core\utils\Attach;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\modules\user\utils\User;
?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/base.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/common.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/zTree/css/ibos/ibos.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/Select2/select2.css?<?php echo VERHASH; ?>" />
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/page.css" />

<!-- IE8 fixed -->
<!--[if lt IE 9]>
    <link rel="stylesheet" href="<?php echo STATICURL; ?>/css/iefix.css?<?php echo VERHASH; ?>" />
<![endif]-->
<!-- load css end -->

<!-- JS全局变量-->
<script>
<?php $gUploadConfig = Attach::getUploadConfig(); ?>
<?php $gAccount = User::getAccountSetting(); ?>
    var G = {
        VERHASH: '<?php echo VERHASH; ?>',
        SITE_URL: '<?php echo IBOS::app()->setting->get( 'siteurl' ); ?>',
        STATIC_URL: '<?php echo STATICURL; ?>',
        uid: '<?php echo IBOS::app()->user->uid; ?>',
        cookiePre: '<?php echo IBOS::app()->setting->get( 'config/cookie/cookiepre' ); ?>',
        cookiePath: '<?php echo IBOS::app()->setting->get( 'config/cookie/cookiepath' ); ?>',
        cookieDomain: '<?php echo IBOS::app()->setting->get( 'config/cookie/cookiedomain' ); ?>',
        creditRemind: '<?php echo IBOS::app()->setting->get( 'setting/creditnames' ); ?>',
        formHash: '<?php echo FORMHASH ?>',
        settings: {notifyInterval: 320},
        contact: '<?php echo User::getJsConstantUids( IBOS::app()->user->uid ); ?>',
        loginTimeout: '<?php echo $gAccount['timeout'] ?>',
        upload: {
            attachexts: {
                depict: "<?php echo $gUploadConfig['attachexts']['depict']; ?>",
                ext: "<?php echo $gUploadConfig['attachexts']['ext']; ?>"
            },
            imageexts: {
                scriptdepict: "<?php echo $gUploadConfig['imageexts']['depict']; ?>",
                ext: "<?php echo $gUploadConfig['imageexts']['ext']; ?>"
            },
            hash: "<?php echo $gUploadConfig['hash'] ?>",
            limit: "<?php echo $gUploadConfig['limit'] ?>",
            max: "<?php echo $gUploadConfig['max']; ?>"
        },
        password: {
            minLength: "<?php echo $gAccount['minlength']; ?>",
            maxLength: 32,
            regex: "<?php echo $gAccount['preg'] ?>"
        }
    };
</script>
<!-- 核心库类 -->
<script src='<?php echo STATICURL; ?>/js/src/core.js?<?php echo VERHASH; ?>'></script>
<!-- 语言包 -->
<script src='<?php echo STATICURL; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>


<script src='<?php echo STATICURL; ?>/js/src/base.js?<?php echo VERHASH; ?>'></script>
<!-- @Todo: 放到 mainer 加载之后 -->
<script src='<?php echo STATICURL; ?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/zTree/jquery.ztree.all-3.5.min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/Select2/select2.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo File::fileName( 'data/org.js' ); ?>?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/src/common.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.userSelect.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/src/application.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js"></script>
<script src="<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js"></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>  
<script src='<?php echo $assetUrl; ?>/js/main_page_loaded.js?<?php echo VERHASH; ?>'></script>				
<script>
    Ibos.app.setPageParam({
        "assetUrl": "<?php echo $assetUrl; ?>"
    });
</script>