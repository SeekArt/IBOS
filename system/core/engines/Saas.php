<?php

namespace application\core\engines;

use application\core\components\Engine;
use application\core\engines\saas\SaasIo;
use application\core\utils\IBOS;
use CDbConnection;
use CJSON;

class Saas extends Engine {

	private $saasConfig = NULL;
	private $corpRow = NULL;
	private $corpcode = "";

	protected function defineConst() {
		define( 'LOCAL', false );

		//以下参数为了不暴露账号密码什么的，你懂的，会使用本地的文件配置
		//当然你也可以有自己的配置
		$filePath = PATH_ROOT . '/saas_config.php';

		if ( file_exists( $filePath ) ) {
			$this->saasConfig = include $filePath;
		} else {
			echo '需要配置SAAS环境';
			die();
		}
		ini_set( "session.save_handler", "redis" );
		$redis = $this->saasConfig['redis'];
		$auth = !empty( $redis['password'] ) ? '?auth=' . $redis['password'] : '';
		$redisString = 'tcp://' . $redis['host'] . ':' . $redis['port'] . $auth;
		ini_set( "session.save_path", $redisString );
		define( 'ALIOSS_HOSTNAME', $this->saasConfig['ALIOSS_HOSTNAME'] ); //bucket存储区域
		define( 'ALIOSS_IMAGE_HOSTNAME', $this->saasConfig['ALIOSS_IMAGE_HOSTNAME'] ); //图片服务地址
		define( 'ALIOSS_BUCKET', $this->saasConfig['ALIOSS_BUCKET'] ); //bucket名称
		define( 'ALIOSS_ACCESS_ID', $this->saasConfig['ALIOSS_ACCESS_ID'] ); //阿里云账号ID
		define( 'ALIOSS_ACCESS_KEY', $this->saasConfig['ALIOSS_ACCESS_KEY'] ); //阿里云账号密匙
		define( 'ALIOSS_URL_TIMEOUT', 3600 ); //不是公开读写的object访问url有效时间，单位：秒
		require PATH_ROOT . '/system/extensions/alioss2/autoload.php';
	}

	public function getMainConfig() {
		$mainConfig = CJSON::decode( $this->corpRow['config'] );
		return $mainConfig;
	}

	/**
	 * 配置saas
	 * @param type $mainConfig
	 * @return type
	 */
	protected function initConfig( $mainConfig ) {
		// 本地环境使用安装时配置的数据库信息
		$connectionString = "mysql:host={$mainConfig['db']['host']};port={$mainConfig['db']['port']};dbname={$mainConfig['db']['dbname']}";
		$mainConfig = CJSON::decode( $this->corpRow['config'] );
		//注意！！！！
		//这个配置其实是不符合saas的原则的，因为saas是不应该把文件直接输出的
		//但是由于这个只是错误日志记录，这边也不需要做什么分布式部署
		$runtimePath = PATH_ROOT . DIRECTORY_SEPARATOR . 'data/runtime/' . CORP_CODE;
		if ( !file_exists( $runtimePath ) ) {
			mkdir( $runtimePath, 0777, true );
		}

		$config = array(
			'runtimePath' => $runtimePath,
			'language' => $mainConfig['env']['language'],
			'theme' => $mainConfig['env']['theme'],
			'components' => array(
				'db' => array(
					'connectionString' => $connectionString,
					'username' => $mainConfig['db']['username'],
					'password' => $mainConfig['db']['password'],
					'tablePrefix' => $mainConfig['db']['tableprefix'],
					'charset' => $mainConfig['db']['charset']
				),
				'cache' => array(
					'keyPrefix' => CORP_CODE,
					'hostname' => $this->saasConfig['redis']['host'],
					'port' => $this->saasConfig['redis']['port'],
					'password' => !empty( $this->saasConfig['redis']['password'] ) ? $this->saasConfig['redis']['password'] : NULL,
					'database' => 1, //session和缓存的不要放在一起，session默认放在0了
				),
			)
		);
		return $config;
	}

	/**
	 * 获取IO接口
	 * @staticvar type $io
	 * @return SaasIo
	 */
	public function io() {
		static $io = null;
		if ( $io == null ) {
			$io = new SaasIo();
		}
		return $io;
	}

	/**
	 * 设置别名，加载驱动路径
	 * @return void
	 */
	protected function init() {
		// 设置data别名
		IBOS::setPathOfAlias( 'data', PATH_ROOT . DIRECTORY_SEPARATOR . 'data' );
		// 设置引擎驱动别名
		IBOS::setPathOfAlias( 'engineDriver', IBOS::getPathOfAlias( 'application.core.engines.saas' ) );
	}

	protected function preinit() {
		$this->setcorp();

		$dsn = "mysql:"
				. "host={$this->saasConfig['db']['host']};"
				. "port={$this->saasConfig['db']['port']};"
				. "dbname={$this->saasConfig['db']['dbname']}";
		$connection = new CDbConnection( $dsn
				, $this->saasConfig['db']['username']
				, $this->saasConfig['db']['password'] );
		$this->corpRow = $connection->createCommand()
				->select()
				->from( 'config' )
				->where( " `corpcode` = :corpcode " )
				->bindValue( ':corpcode', $this->corpcode )
				->queryRow();
		defined( 'CORP_CODE' ) || define( 'CORP_CODE', $this->corpRow['corpcode'] );
		if ( empty( $this->corpRow ) ) {
			setcookie( 'corp_code', NULL, -1 );
			include PATH_ROOT . '/login.php';
			exit();
		}
	}

	private function setcorp(){
		$domain = $_SERVER['HTTP_HOST']; //取得用户所访问的域名全称
		$domaincorp = str_replace('.saas.ibos.cn', '', $domain); //域名取前缀来代表企业code
		//如果是通过子域名访问进来的,优先为登录这个域名的OA , 否则看是post传递了他要登录的OA还是cookie传递了他要登录的OA
		if( !empty($domaincorp) && $domaincorp != $domain ) {
			$this->corpcode = $domaincorp;			
			setcookie("corp_code", $domaincorp, time()+86400,"/");
		} else if ( !empty( $_POST['corp_code'] ) ) {
			$this->corpcode = $_POST['corp_code'];
		} else if ( !empty( $_COOKIE['corp_code'] ) ) {
			$this->corpcode = $_COOKIE['corp_code'];
		} else {
			include PATH_ROOT . '/login.php';
			exit();
		}
		
	}

}
