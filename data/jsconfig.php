<?php

use application\core\utils\Attach;
use application\core\utils\IBOS;
use application\modules\main\utils\Main;
use application\modules\user\utils\User;

$gUploadConfig = Attach::getUploadConfig();
$gAccount = User::getAccountSetting();
$mods = Main::getModuleParamsForJs();
$qr = IBOS::app()->setting->get( 'setting/qrcode' );
$qrcode = isset( $qr ) ? $qr : '';
$conf = array(
	'VERHASH' => VERHASH,
	'SITE_URL' => IBOS::app()->setting->get( 'siteurl' ),
	'STATIC_URL' => STATICURL,
	'uid' => IBOS::app()->user->uid,
	'cookiePre' => IBOS::app()->setting->get( 'config/cookie/cookiepre' ),
	'cookiePath' => IBOS::app()->setting->get( 'config/cookie/cookiepath' ),
	'cookieDomain' => IBOS::app()->setting->get( 'config/cookie/cookiedomain' ),
	'creditRemind' => IBOS::app()->setting->get( 'setting/creditnames' ),
	'formHash' => FORMHASH,
	'settings' => array( 'notifyInterval' => 320 ),
	'contact' => User::getJsConstantUids( IBOS::app()->user->uid ),
	'loginTimeout' => $gAccount['timeout'],
	'upload' => array(
		'attachexts' => array(
			'depict' => $gUploadConfig['attachexts']['depict'],
			'ext' => $gUploadConfig['attachexts']['ext']
		),
		'imageexts' => array(
			'depict' => $gUploadConfig['imageexts']['depict'],
			'ext' => $gUploadConfig['imageexts']['ext']
		),
		'hash' => $gUploadConfig['hash'],
		'limit' => $gUploadConfig['limit'],
		'max' => $gUploadConfig['max']
	),
	'password' => array(
		'minLength' => $gAccount['minlength'],
		'maxLength' => 32,
		'regex' => $gAccount['preg']
	),
	'mods' => $mods,
	'appDownloadPageUrl' => 'http://www.ibos.com.cn/home/download/mobile',
	'followWxUrl' => 'http://www.ibos.com.cn/api/show/qrcode?qrcode=' . $qrcode,
);

echo "var G = " . json_encode( $conf );
