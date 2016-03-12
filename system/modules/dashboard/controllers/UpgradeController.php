<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Cache as CacheUtil;
use application\core\utils\Env;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\Org;
use application\core\utils\String;
use application\core\utils\Upgrade;
use application\modules\dashboard\controllers\BaseController;
use application\modules\dashboard\model\Cache;
use application\modules\main\model\Setting;

class UpgradeController extends BaseController {

	/**
	 * 必须是本地引擎才能进行此操作
	 */
	public function init() {
		parent::init();
		if ( !LOCAL ) {
			die( IBOS::lang( 'Not compatible service', 'message' ) );
		}
	}

	public function actionIndex() {
		if ( Env::getRequest( 'op' ) ) {
			$operation = Env::getRequest( 'op' );
			$operations = array( 'checking', 'patch', 'showupgrade' );
			if ( !in_array( $operation, $operations ) ) {
				exit();
			}
			switch ( $operation ) {
				case 'checking': // 第一步：检查更新
					$upgradeStep = Cache::model()->fetchByPk( 'upgrade_step' );
                    $upgradeStep['cachevalue'] = String::utf8Unserialize( $upgradeStep['cachevalue'] );
					$isExistStep = !empty( $upgradeStep['cachevalue'] ) && !empty( $upgradeStep['cachevalue']['step'] );
					// 查找步骤缓存
					if ( !Env::getRequest( 'rechecking' ) && $isExistStep ) {
						// 步骤缓存的URL参数
						$param = array(
							'op' => $upgradeStep['cachevalue']['operation'],
							'version' => $upgradeStep['cachevalue']['version'],
							'locale' => $upgradeStep['cachevalue']['locale'],
							'charset' => $upgradeStep['cachevalue']['charset'],
							'release' => $upgradeStep['cachevalue']['release'],
							'step' => $upgradeStep['cachevalue']['step']
						);
						$data = array(
							'url' => $this->createUrl( 'upgrade/index', $param ),
							'stepName' => Upgrade::getStepName( $upgradeStep['cachevalue']['step'] )
						);
						$this->render( 'upgradeContinue', array( 'data' => $data ) );
					} else { // 否则如果是重新请求更新或者步骤缓存为空，都做重新检查
						Cache::model()->deleteByPk( 'upgrade_step' );
						Upgrade::checkUpgrade();
						$url = $this->createUrl( 'upgrade/index', array( 'op' => 'showupgrade' ) );
						$this->redirect( $url );
					}
					break;
				case 'showupgrade': // 如有更新，进入第二步：显示更新列表
					$result = $this->processingUpgradeList();
					if ( $result['isHaveUpgrade'] ) {
						$this->render( 'upgradeShow', $result );
					} else {
						$this->render( 'upgradeNewest' );
					}
					break;
				case 'patch': // 升级补丁
					$step = Env::getRequest( 'step' );
					$step = intval( $step ) ? $step : 1;
					$version = trim( $_GET['version'] );
					$release = trim( $_GET['release'] );
					$locale = trim( $_GET['locale'] );
					$charset = trim( $_GET['charset'] );
					$upgradeInfo = $upgradeStep = array();
					$upgradeStepRecord = Cache::model()->fetchByPk( 'upgrade_step' );
                    $upgradeStep = String::utf8Unserialize( $upgradeStepRecord['cachevalue'] );

					// 初始化更新步骤
					$upgradeStep['step'] = isset( $upgradeStep['step'] ) ? intval( $upgradeStep['step'] ) : $step;
					$upgradeStep['operation'] = $operation;
					$upgradeStep['version'] = $version;
					$upgradeStep['release'] = $release;
					$upgradeStep['charset'] = $charset;
					$upgradeStep['locale'] = $locale;
					$data = array(
						'cachekey' => 'upgrade_step',
						'cachevalue' => serialize( $upgradeStep ),
						'dateline' => TIMESTAMP,
					);
					Cache::model()->add( $data, false, true ); //有则更新，无则插入
					// 初始化更新所需信息
					$upgradeRun = Cache::model()->fetchByPk( 'upgrade_run' );
					if ( !$upgradeRun ) {
						$upgrade = IBOS::app()->setting->get( 'setting/upgrade' );
						$data = array(
							'cachekey' => 'upgrade_run',
							'cachevalue' => serialize( $upgrade ),
							'dateline' => TIMESTAMP
						);
						Cache::model()->add( $data, false, true );
						$upgradeRun = $upgrade;
					} else {
                        $upgradeRun = String::utf8Unserialize( $upgradeRun['cachevalue'] );
					}
					// 下一步所需URL参数
					$param = array(
						'op' => $operation,
						'version' => $version,
						'locale' => $locale,
						'charset' => $charset,
						'release' => $release
					);
					// 开始处理升级步骤前的预处理
					if ( $step != 5 ) {
						$upgradeInfo = $this->filterRun( $param, $upgradeRun );
						// 如果上次更新在第4步中断，可能会导致version文件版本与数据库缓存保存的数据对应不上
						if ( empty( $upgradeInfo ) ) {
							Cache::model()->deleteByPk( 'upgrade_step' );
							Cache::model()->deleteByPk( 'upgrade_run' );
							$msg = IBOS::lang( 'upgrade_unknow_error', '', array(
										'{url}' => $this->createUrl( 'upgrade/index', array( 'op' => 'checking', 'rechecking' => 1 ) ) )
							);
							$this->render( 'upgradeError', array( 'msg' => $msg ) );
							exit;
						}
						$savePath = '/data/update/IBOS' . $upgradeInfo['latestversion'] . ' Release[' . $upgradeInfo['latestrelease'] . ']';
						$fileList = Upgrade::fetchUpdateFileList( $upgradeInfo );
						$updateMd5FileList = $fileList['md5'];
						$updateFileList = $fileList['file'];
						$preStatus = $this->preProcessingStep( $upgradeInfo, $actionUrl = $this->createUrl( 'upgrade/index', $param ), !empty( $updateFileList ) ? true : false  );
						if ( $preStatus['status'] < 0 ) {
							$this->ajaxReturn( $preStatus, 'json' );
						}
					}
					// 开始步骤处理
					switch ( $step ) {
						case 1: // 第一步：显示更新文件
							return $this->processingShowUpgrade( $updateFileList, $param, $savePath );
							break;
						case 2: // 第二步：下载文件
							return $this->processingDownloadFile( $upgradeInfo, $updateMd5FileList, $updateFileList, $param );
							break;
						case 3: // 第三步：对比文件
							return $this->processingCompareFile( $updateFileList, $param, $savePath );
							break;
						case 4: // 第四步：应用更新文件
							return $this->processingUpdateFile( $upgradeInfo, $updateFileList, $upgradeStep, $param );
							break;
						case 5:// 第五步：删除临时下载文件，更新完成
							return $this->processingTempFile( $param );
							break;
						default:
							break;
					}
					break;
				default:
					break;
			}
		} else {
			$this->render( 'upgradeCheckVersion' );
		}
	}

	/**
	 * 筛选当前更新的步骤信息
	 * @param array $param 用于筛选对比的参数数组
	 * @param array $upgradeRun 当前步骤信息
	 * @return array
	 */
	private function filterRun( $param, $upgradeRun ) {
		$upgradeInfo = array();
		if ( !empty( $upgradeRun ) ) {
			foreach ( $upgradeRun as $type => $list ) {
				if ( $type == $param['op'] && $param['version'] == $list['latestversion'] && $param['release'] == $list['latestrelease'] ) {
					Upgrade::$locale = $param['locale'];
					Upgrade::$charset = $param['charset'];
					$upgradeInfo = $list;
					break;
				}
			}
		}
		return $upgradeInfo;
	}

	/**
	 * 开始更新步骤处理前的预处理
	 * @param type $upgradeInfo
	 * @param type $actionUrl
	 * @param type $fileListExists
	 * @return type
	 */
	private function preProcessingStep( $upgradeInfo, $actionUrl, $fileListExists ) {
		// 没有文件更新
		if ( !$upgradeInfo ) {
			return array( 'status' => -1, 'msg' => IBOS::lang( 'Upgrade none' ) );
		}
		// 无法找到更新列表
		if ( !$fileListExists ) {
			return array( 'status' => -2, 'msg' => IBOS::lang( 'Upgrade download upgradelist error' ), 'actionUrl' => $actionUrl );
		}
		return array( 'status' => 1 );
	}

	/**
	 * 处理升级内容列表
	 * @return array 结果数组 e.g : array('isHaveUpgrade' => true, list => array(...));
	 */
	private function processingUpgradeList() {
		$upgrades = IBOS::app()->setting->get( 'setting/upgrade' );
		if ( !$upgrades ) {
			return array( 'isHaveUpgrade' => false, 'msg' => IBOS::lang( 'Upgrade latest version' ) );
		} else {
			// 有更新，即存入缓存表备用
			$upgradeStep = array(
				'cachekey' => 'upgrade_step',
				'cachevalue' => serialize( array(
					'curversion' => Upgrade::getVersionPath(),
					'currelease' => VERSION_DATE )
				),
				'dateline' => TIMESTAMP,
			);
			Cache::model()->add( $upgradeStep, false, true );
			// -----------------------
			$upgradeRow = array();
			$charset = str_replace( '-', '', strtoupper( CHARSET ) );
			$dbVersion = IBOS::app()->db->getServerVersion();
			// 确定更新地区目录
			$locale = '';
			if ( $charset == 'BIG5' ) {
				$locale = 'TC';
			} elseif ( $charset == 'GBK' ) {
				$locale = 'SC';
			} elseif ( $charset == 'UTF8' ) {
				$language = IBOS::app()->getLanguage();
				if ( $language == 'zh_cn' ) {
					$locale = 'SC';
				} elseif ( $language == 'zh_tw' ) {
					$locale = 'TC';
				}
			}
			foreach ( $upgrades as $type => $upgrade ) {
				$unUpgrade = 0;
				if ( version_compare( $upgrade['phpversion'], PHP_VERSION ) > 0 ||
						version_compare( $upgrade['mysqlversion'], $dbVersion ) > 0 ) {
					$unUpgrade = 1;
				}
				$baseDesc = 'IBOS ' . $upgrade['latestversion'] . '_' .
						$locale . '_' . $charset .
						' [' . $upgrade['latestrelease'] . ']';
				// 未达到版本要求的提示
				if ( $unUpgrade ) {
					$this->render( 'upgradeError', array( 'msg' => IBOS::lang( 'Upgrade require config', '', array( 'phpVersion' => PHP_VERSION, 'dbVersion' => $dbVersion ) ) ) );
					exit;
				} else {
					$params = array(
						'op' => $type,
						'version' => $upgrade['latestversion'],
						'locale' => $locale,
						'charset' => $charset,
						'release' => $upgrade['latestrelease']
					);
					$linkUrl = $this->createUrl( 'upgrade/index', $params );
					$upgradeRow[] = array(
						'desc' => $baseDesc,
						'upgrade' => true,
						'link' => $linkUrl,
						'upgradeDesc' => $upgrade['upgradeDesc'],
						'official' => $upgrade['official']
					);
				}
			}
			return array( 'isHaveUpgrade' => true, 'list' => $upgradeRow );
		}
	}

	/**
	 * 更新第一步：显示更新列表
	 * @param array $updateFileList 当前可以更新的文件列表
	 * @param array $urlParam url参数数组，用于生成并返回下一步链接
	 * @param string $savePath 显示更新文件保存路径
	 * @return JsonString
	 */
	private function processingShowUpgrade( $updateFileList, $urlParam = array(), $savePath = '' ) {
		$urlParam['step'] = 2;
		$url = $this->createUrl( 'upgrade/index', $urlParam );
		$data = array_merge( array( 'actionUrl' => $url ), array( 'list' => $updateFileList ), array( 'savePath' => $savePath ) );
		$this->render( 'upgradeDownloadList', array( 'step' => 1, 'data' => $data ) );
	}

	/**
	 * 更新第二步：下载文件
	 * @param array $upgradeInfo 更新所需信息
	 * @param array $updateMd5FileList 可更新文件的md5列表
	 * @param array $updateFileList 可更新文件列表
	 * @param array $urlParam url参数数组，用于生成并返回下一步链接
	 * @return JsonString
	 */
	private function processingDownloadFile( $upgradeInfo, $updateMd5FileList, $updateFileList, $urlParam ) {
		if ( Env::getRequest( 'downloadStart' ) ) {
			// 文件列表索引
			$fileSeq = intval( Env::getRequest( 'fileseq' ) );
			// 默认第1个（实际数组从0开始，所以下载时需要减1）
			$fileSeq = $fileSeq ? $fileSeq : 1;
			// 文件指针，用于断点下载
			$position = intval( Env::getRequest( 'position' ) );
			$position = $position ? $position : 0;
			// 文件最大长度，如果下载的文件超过这个长度，则自动使用断点下载
			$offset = 100 * 1024;
			$data['step'] = 2;
			// 所有文件更新完成后，更新固定的特殊文件
			if ( $fileSeq > count( $updateFileList ) ) {
				// 如果有更新数据库
				if ( $upgradeInfo['isupdatedb'] ) {
//					Upgrade::downloadFile( $upgradeInfo, 'install/data/install.sql' );
//					Upgrade::downloadFile( $upgradeInfo, 'install/data/installData.sql' );
					// 执行数据库表升级的文件
					Upgrade::downloadFile( $upgradeInfo, 'update.php', 'utils' );
				}
				$data['data'] = array(
					'IsSuccess' => true,
					'msg' => IBOS::lang( 'Upgrade download complete to compare' ),
					'url' => $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 3 ), $urlParam ) )
				);
				$data['step'] = 3;
				return $this->ajaxReturn( $data, 'json' );
			} else {
				// 当前文件
				$curFile = $updateFileList[$fileSeq - 1];
				// 当前MD5
				$curMd5File = $updateMd5FileList[$fileSeq - 1];
				// 当前进度百分比
				$percent = sprintf( "%2d", 100 * $fileSeq / count( $updateFileList ) ) . '%';
				$percent2 = $fileSeq . "/" . count( $updateFileList );
				// 开始下载并返回下载状态
				$downloadStatus = Upgrade::downloadFile( $upgradeInfo, $curFile, 'upload', $curMd5File, $position, $offset );
				if ( $downloadStatus == 1 ) { // 断点下载，继续进行下载
					$data['data'] = array(
						'IsSuccess' => true,
						'msg' => IBOS::lang( 'Upgrade downloading file', '', array( '{file}' => $curFile, '{percent}' => $percent, '{percent2}' => $percent2 ) ),
						'url' => $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 2, 'fileseq' => $fileSeq, 'position' => ($position + $offset) ), $urlParam ) )
					);
				} elseif ( $downloadStatus == 2 ) { // 下载完成,继续下一个
					$data['data'] = array(
						'IsSuccess' => true,
						'msg' => IBOS::lang( 'Upgrade downloading file', '', array( '{file}' => $curFile, '{percent}' => $percent, '{percent2}' => $percent2 ) ),
						'url' => $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 2, 'fileseq' => ($fileSeq + 1) ), $urlParam ) )
					);
				} else {
					// 尝试重新下载
					$data['data'] = array(
						'IsSuccess' => false,
						'msg' => IBOS::lang( 'Upgrade redownload', '', array( '{file}' => $curFile ) ),
						'url' => $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 2, 'fileseq' => $fileSeq ), $urlParam ) )
					);
				}
				return $this->ajaxReturn( $data, 'json' );
			}
		} else {
			// 更新步骤缓存
			Upgrade::recordStep( 2 );
			$downloadUrl = $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 2 ), $urlParam ) );
			$this->render( 'upgradeDownload', array( 'downloadUrl' => $downloadUrl ) );
		}
	}

	/**
	 * 更新第三步：对比及显示差异文件
	 * @param array $updateFileList 更新列表文件
	 * @param array $urlParam url参数数组，用于生成并返回下一步链接
	 * @param string $savePath 更新文件保存路劲
	 * @return JsonString
	 */
	private function processingCompareFile( $updateFileList, $urlParam, $savePath = '' ) {
		//筛选更新文件
		list($modifyList, $showList) = Upgrade::compareBasefile( $updateFileList );
		$data['step'] = 3;
//		if ( empty( $modifyList ) && empty( $showList ) ) {
//			$msg = IBOS::lang( 'Filecheck nofound md5file' );
//			$this->render( 'upgradeError', array( 'msg' => $msg ) );
//			exit();
//		} else {
			$list = array();
			foreach ( $updateFileList as $file ) {
				if ( isset( $modifyList[$file] ) ) {
					// 差异文件
					$list['diff'][] = $file;
				} elseif ( isset( $showList[$file] ) ) {
					// 普通文件
					$list['normal'][] = $file;
				} else {
					// 新文件
					$list['newfile'][] = $file;
				}
			}
			$backPath = './data/back/IBOS' . VERSION . ' Release[' . VERSION_DATE . ']';
			$data['data']['param'] = $urlParam;
			$data['data']['list'] = $list;
			$data['data']['url'] = $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 4 ), $urlParam ) );
			$data['data']['forceUpgrade'] = !empty( $modifyList );
			$data['data']['msg'] = IBOS::lang( 'Upgrade comepare', '', array(
						'{savePath}' => $savePath,
						'{backPath}' => $backPath )
			);
//		}
		// 更新步骤缓存
		Upgrade::recordStep( 3 );
		$this->render( 'upgradeCompare', $data );
	}

	/**
	 * 更新第四步：更新覆盖文件
	 * @param array $upgradeInfo
	 * @param array $updateFileList
	 * @param string $upgradeStep
	 * @param array $urlParam
	 */
	private function processingUpdateFile( $upgradeInfo, $updateFileList, $upgradeStep, $urlParam ) {
		if ( Env::getRequest( 'coverStart' ) ) {
			$data['step'] = 4;
			$confirm = Env::getRequest( 'confirm' );
			$startUpgrade = Env::getRequest( 'startupgrade' );
			if ( !$confirm ) {
				// 返回设置ftp界面
				if ( Env::getRequest( 'ftpsetting' ) ) {
					$param = array( 'step' => 4, 'confirm' => 'ftp' );
					if ( $startUpgrade ) {
						$param['startupgrade'] = 1;
					}
					$data['data']['status'] = 'ftpsetup';
					$data['data']['url'] = $this->createUrl( 'upgrade/index', array_merge( $param, $urlParam ) );
					$this->ajaxReturn( $data, 'json' );
				}
				// 检查是否有更新数据库  DEBUG:: 
				if ( $upgradeInfo['isupdatedb'] ) {
//					$fileList = array( 'data/update.php', 'install/data/install.sql', 'install/data/installData.sql' );
					$fileList = array( 'data/update.php' );
					$checkUpdateFileList = array_merge( $fileList, $updateFileList );
				} else {
					$checkUpdateFileList = $updateFileList;
				}
				// 检查目录权限
				if ( File::checkFolderPerm( $checkUpdateFileList ) ) {
					$confirm = 'file';
				} else {
					// 没有权限，要设置ftp或重试。
					$data['data']['status'] = 'no_access';
					$data['data']['msg'] = IBOS::lang( 'Upgrade cannot access file' );
					$data['data']['retryUrl'] = $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 4 ), $urlParam ) );
					$data['data']['ftpUrl'] = $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 4, 'ftpsetting' => 1 ), $urlParam ) );
					$this->ajaxReturn( $data, 'json' );
				}
			}
			$ftpParam = array();
			$ftpSetup = Env::getRequest( 'ftpsetup' );
			if ( $ftpSetup ) {
				foreach ( $ftpSetup as $key => $value ) {
					$ftpParam["ftp[{$key}]"] = $value;
				}
			}
			// 还没开始升级的话
			if ( !$startUpgrade ) {
				// 先开始备份
				if ( !Env::getRequest( 'backfile' ) ) {
					$param = array(
						'step' => 4,
						'backfile' => 1,
						'confirm' => $confirm
					);
					$data['data']['status'] = 'upgrade_backuping';
					$data['data']['msg'] = IBOS::lang( 'Upgrade backuping' );
					$data['data']['url'] = $this->createUrl( 'upgrade/index', array_merge( $ftpParam, $param, $urlParam ) );
					$this->ajaxReturn( $data, 'json' );
				}
				foreach ( $updateFileList as $updateFile ) {
					$destFile = PATH_ROOT . '/' . $updateFile;
					$backFile = PATH_ROOT . '/data/back/IBOS' . VERSION . ' Release[' . VERSION_DATE . ']/' . $updateFile;
					if ( is_file( $destFile ) ) {
						if ( !Upgrade::copyFile( $destFile, $backFile, 'file' ) ) {
							$data['data']['status'] = 'upgrade_backup_error';
							$data['data']['msg'] = IBOS::lang( 'Upgrade backup error' );
							$this->ajaxReturn( $data, 'json' );
						}
					}
				}
				$data['data']['status'] = 'upgrade_backup_complete';
				$data['data']['msg'] = IBOS::lang( 'Upgrade backup complete' );
				$data['data']['url'] = $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 4, 'startupgrade' => 1, 'confirm' => $confirm ), $ftpParam, $urlParam ) );
				$this->ajaxReturn( $data, 'json' );
			}
			// 开始升级
			// --- 覆盖文件 ---
			$param = array( 'step' => 4, 'startupgrade' => 1 );
			$url = $this->createUrl( 'upgrade/index', array_merge( $param, $urlParam, $ftpParam, array( 'confirm' => $confirm ) ) );
			$ftpUrl = $this->createUrl( 'upgrade/index', array_merge( $param, $urlParam, array( 'ftpsetting' => 1 ) ) );
			foreach ( $updateFileList as $updateFile ) {
				$srcFile = PATH_ROOT . '/data/update/IBOS' . $urlParam['version'] . ' Release[' . $urlParam['release'] . ']/' . $updateFile;
				if ( $confirm == 'ftp' ) {
					$destFile = $updateFile;
				} else {
					$destFile = PATH_ROOT . '/' . $updateFile;
				}
				// 覆盖旧文件
				if ( !Upgrade::copyFile( $srcFile, $destFile, $confirm ) ) {
					Cache::model()->deleteByPk( 'upgrade_step' );
					Cache::model()->deleteByPk( 'upgrade_run' );
					$data['data']['ftpUrl'] = $ftpUrl;
					$data['data']['retryUrl'] = $url;
					if ( $confirm == 'ftp' ) {
						$data['data']['status'] = 'upgrade_ftp_upload_error';
						$data['data']['msg'] = IBOS::lang( 'Upgrade ftp upload error', '', array( '{file}' => $updateFile ) );
					} else {
						$data['data']['status'] = 'upgrade_copy_error';
						$data['data']['msg'] = IBOS::lang( 'Upgrade copy error', '', array( '{file}' => $updateFile ) );
					}
					$this->ajaxReturn( $data, 'json' );
				}
			}
			// --- 覆盖操作完成 ---
			// -- 是否有数据库升级 -- 
			if ( $upgradeInfo['isupdatedb'] ) {
//				$dbUpdateFileArr = array( 'update.php', 'install/data/install.sql', 'install/data/installData.sql' );
				$dbUpdateFileArr = array( 'update.php' );
				foreach ( $dbUpdateFileArr as $dbUpdateFile ) {
					$srcFile = PATH_ROOT . '/data/update/IBOS' . $urlParam['version'] . ' Release[' . $urlParam['release'] . ']/' . $dbUpdateFile;
					$dbUpdateFile = $dbUpdateFile == 'update.php' ? 'data/update.php' : $dbUpdateFile;
					if ( $confirm == 'ftp' ) {
						$destFile = $dbUpdateFile;
					} else {
						$destFile = PATH_ROOT . '/' . $dbUpdateFile;
					}
					if ( !Upgrade::copyFile( $srcFile, $destFile, $confirm ) ) {
						$data['data']['ftpUrl'] = $ftpUrl;
						$data['data']['retryUrl'] = $url;
						if ( $confirm == 'ftp' ) {
							$data['data']['status'] = 'upgrade_ftp_upload_error';
							$data['data']['msg'] = IBOS::lang( 'Upgrade ftp upload error', '', array( '{file}' => $dbUpdateFile ) );
						} else {
							$data['data']['status'] = 'upgrade_copy_error';
							$data['data']['msg'] = IBOS::lang( 'Upgrade copy error', '', array( '{file}' => $dbUpdateFile ) );
						}
						$this->ajaxReturn( $data, 'json' );
					}
				}
				$upgradeStep['step'] = 4;
				Cache::model()->add( array(
					'cachekey' => 'upgrade_step',
					'cachevalue' => serialize( $upgradeStep ),
					'dateline' => TIMESTAMP,
						), false, true );
				// 直接访问数据库升级文件（此文件是data目录下刚生成的upgrade.php文件）
				$dbReturnUrl = $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 5 ), $urlParam ) );
				$param = array(
					'step' => 'prepare',
					'from' => rawurlencode( $dbReturnUrl ),
					'frommd5' => md5( rawurlencode( $dbReturnUrl ) . IBOS::app()->setting->get( 'config/security/authkey' ) )
				);
				$data['data']['status'] = 'upgrade_database';
				$data['data']['url'] = 'data/update.php?' . http_build_query( $param );
				$data['data']['msg'] = IBOS::lang( 'Upgrade file successful' );
				$this->ajaxReturn( $data, 'json' );
			}
			$data['data']['status'] = 'upgrade_file_successful';
			$data['data']['url'] = $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 5 ), $urlParam ) );
			$data['step'] = 5;
			$this->ajaxReturn( $data, 'json' );
		} else {
			// 更新步骤缓存
			Upgrade::recordStep( 4 );
			$data = array(
				'coverUrl' => $this->createUrl( 'upgrade/index', array_merge( array( 'step' => 4 ), $urlParam ) ),
				'to' => $upgradeInfo['latestversion'] . ' ' . $upgradeInfo['latestrelease'],
				'from' => VERSION . ' ' . VERSION_DATE
			);
			$this->render( 'upgradeCover', $data );
		}
	}

	/**
	 * 更新第五步：删除临时文件，返回成功信息
	 * @param array $urlParam url参数数组
	 * @return JsonString
	 */
	private function processingTempFile( $urlParam ) {
		$file = PATH_ROOT . '/data/update/IBOS ' . $urlParam['version'] . ' Release[' . $urlParam['release'] . ']/updatelist.tmp';
		$authKey = IBOS::app()->setting->get( 'config/security/authkey' );
		@unlink( $file );
		@unlink( PATH_ROOT . '/data/update.php' );
		Cache::model()->deleteByPk( 'upgrade_step' );
		Cache::model()->deleteByPk( 'upgrade_run' );
		Setting::model()->updateSettingValueByKey( 'upgrade', '' );
		$randomStr = String::random( 6 );
		$oldUpdateDir = '/data/update/';
		$newUpdateDir = '/data/update-' . $randomStr . '/';
		$oldBackDir = '/data/back/';
		$newBackDir = '/data/back-' . $randomStr . '/';
		File::copyDir( PATH_ROOT . $oldUpdateDir, PATH_ROOT . $newUpdateDir );
		File::copyDir( PATH_ROOT . $oldBackDir, PATH_ROOT . $newBackDir );
		File::clearDirs( PATH_ROOT . $oldUpdateDir );
		File::clearDirs( PATH_ROOT . $oldBackDir );
		$data['step'] = 5;
		$data['data']['url'] = $this->createUrl( 'upgrade/updateCache', array_merge( array( 'op' => "cache" ) ) );
		$data['data']['msg'] = IBOS::lang( 'Upgrade successful', '', array(
					'{version}' => 'IBOS' . VERSION . ' ' . VERSION_DATE,
					'{saveUpdateDir}' => $newUpdateDir,
					'{saveBackDir}' => $newBackDir )
		);
		$this->render( 'upgradeSuccess', $data );
	}

	/**
	 * 输出在线更新中错误信息
	 */
	public function actionShowUpgradeErrorMsg() {
		$msg = Env::getRequest( 'msg' );
		$this->render( 'upgradeError', array( 'msg' => $msg ) );
	}

	public function actionUpdateCache() {
		$op = Env::getRequest( 'op' ) ? Env::getRequest( 'op' ) : "cache";
		switch ( $op ) {
			case "cache":
				CacheUtil::update();
				$op = "org";
				$msg = "正在更新静态文件缓存";
				$isSuccess = true;
				$isContinue = true;
				break;
			case "org":
				Org::update();
				$op = "module";
				$msg = "正在更新模块配置缓存";
				$isSuccess = true;
				$isContinue = true;
				break;
			case "module":
				Module::updateConfig();
				$op = "end";
				$msg = "缓存更新完成,记得要定期检查更新哦~";
				$isSuccess = true;
				$isContinue = false;
				break;
		}
		$param = array(
			'op' => $op,
			'msg' => $msg,
			'isSuccess' => $isSuccess,
			'isContinue' => $isContinue,
		);
		$this->ajaxReturn( $param, "json" );
	}

}
