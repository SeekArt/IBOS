<?php 

use application\core\utils\IBOS;
use application\core\utils\File;
use application\modules\user\utils\User;

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
		<link rel="stylesheet" href="<?php echo $this->getAssetUrl(); ?>/css/common.css?<?php echo VERHASH; ?>">
		<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/zTree/css/ibos/ibos.css?<?php echo VERHASH; ?>" />
		<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/Select2/select2.css?<?php echo VERHASH; ?>" />
		<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>" />
		<script>
			<?php $gAccount = User::getAccountSetting(); ?>
			var G = {
				VERHASH: '<?php echo VERHASH; ?>',
				SITE_URL: '<?php echo IBOS::app()->setting->get( 'siteurl' ); ?>',
				STATIC_URL: '<?php echo STATICURL; ?>',
				cookiePre: '<?php echo IBOS::app()->setting->get( 'config/cookie/cookiepre' ); ?>',
				cookiePath: '<?php echo IBOS::app()->setting->get( 'config/cookie/cookiepath' ); ?>',
				cookieDomain: '<?php echo IBOS::app()->setting->get( 'config/cookie/cookiedomain' ); ?>',
				creditRemind: '<?php echo IBOS::app()->setting->get( 'setting/creditnames' ); ?>',
				formHash: '<?php echo FORMHASH ?>',
				<?php if(!IBOS::app()->user->isGuest): ?>
				uid: '<?php echo IBOS::app()->user->uid; ?>',
				contact: '<?php echo User::getJsConstantUids( IBOS::app()->user->uid ); ?>',
				<?php endif; ?>
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
		<script src='<?php echo STATICURL; ?>/js/lib/artDialog/artDialog.min.js?<?php echo VERHASH; ?>'></script>
		<script src='<?php echo STATICURL; ?>/js/lib/zTree/jquery.ztree.all-3.5.min.js?<?php echo VERHASH; ?>'></script>
		<script src='<?php echo STATICURL; ?>/js/lib/Select2/select2.js?<?php echo VERHASH; ?>'></script>
		<script src='<?php echo File::fileName( 'data/org.js' ); ?>?<?php echo VERHASH; ?>'></script>
		<script src='<?php echo STATICURL; ?>/js/src/common.js?<?php echo VERHASH; ?>'></script>
		<script src='<?php echo STATICURL; ?>/js/app/ibos.userSelect.js?<?php echo VERHASH; ?>'></script>
		<script src='<?php echo STATICURL; ?>/js/src/application.js?<?php echo VERHASH; ?>'></script>
		<script src="<?php echo $this->getAssetUrl(); ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
		<script src="<?php echo $this->getAssetUrl(); ?>/js/dashboard.js?<?php echo VERHASH; ?>"></script>
	</head>
	<body>
		<?php echo $content; ?>
	</body>
</html>