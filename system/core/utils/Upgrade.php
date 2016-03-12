<?php

/**
 * IBOS升级处理文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * IBOS升级处理文件类,提供所有升级相关方法
 * 
 * @package application.core.utils
 * @version $Id: Upgrade.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\utils;

use application\core\utils\Cache as CacheUtil;
use application\modules\main\model\Setting;
use application\modules\dashboard\model\Cache as CacheModel;


class Upgrade {

    /**
     * 更新地址
     */
    const UPGRADE_URL = 'http://ibosupgrade.oss-cn-hangzhou.aliyuncs.com/';

    /**
     * 本地代号
     * @var string 
     */
    public static $locale = 'SC';

    /**
     * 升级编码
     * @var string 
     */
    public static $charset = 'UTF8';

    /**
     * 获取更新文件列表
     * @param array $upgradeInfo 版本信息
     * @return array array('file'=>文件名列表, 'md5'=>每个文件对应的md5码) 
     */
    public static function fetchUpdateFileList( $upgradeInfo ) {
        // 包含更新信息的文件的路径
        $file = PATH_ROOT . '/data/update/IBOS' . $upgradeInfo['latestversion'] . ' Release[' . $upgradeInfo['latestrelease'] . ']/updatelist.tmp';
        // 更新数据的标记（如果为真表示上面这个文件存在）
        // 假设这个文件存在
        $upgradeDataFlag = true;
        // 获取文件内容
        $upgradeData = @trim( file_get_contents( $file ) );
        // 不存在则根据当前版本下载更新信息
        if ( !$upgradeData ) {
            $url = self::UPGRADE_URL . substr( $upgradeInfo['upgradelist'], 0, -4 ) . strtolower( '_' . self::$locale ) . '.txt';
            $upgradeData = File::fileSockOpen( $url );
            $upgradeDataFlag = false;
        }

        $return = array();
        // 分割文件，每行一个更新文件（包含文件名和MD5码）
        $upgradeData = str_replace( array( "\r\n", "\n" ), array( ",,", ",," ), $upgradeData );
        $upgradeDataArr = explode( ",,", $upgradeData );
        foreach ( $upgradeDataArr as $key => $value ) {
            if ( !$value ) {
                continue;
            }
            //每行的格式是这样： 32位md5码 + 两个字符（可能有特殊意义） + 文件名，由此我们可截取得到md5和文件名
            $return['file'][$key] = trim( substr( $value, 34 ) );
            $return['md5'][$key] = substr( $value, 0, 32 );
            //md5和文件名之间必需包含*号
            if ( trim( substr( $value, 32, 2 ) ) != '*' ) {
                @unlink( $file );
                return array();
            }
        }
        //如果包含更新信息的文件原本不存在，则写入刚刚下载过来的更新信息
        if ( !$upgradeDataFlag ) {
            File::makeDirs( dirname( $file ) );
            $fp = fopen( $file, 'w' );
            if ( !$fp ) {
                return array();
            }
            fwrite( $fp, $upgradeData );
        }

        return $return;
    }

    /**
     * 对比文件，筛选出需要更新的文件列表
     * @param array $upgradeInfo 更新信息
     * @param array $upgradeFileList 需要更新的文件列表
     * @return array array(需要更新的文件列表, 不需要更新的文件列表)(仅仅返回要修改的文件，不在返回数组里面的是新增的文件)
     */
    public static function compareBasefile( $upgradeFileList ) {
        $ibosFiles = @file( IBOS::getPathOfAlias( 'application.ibosfiles' ) . '.md5' );
        /**
         * 如果没有md5文件也更新，毕竟只是为了显示差异文件
         */
//		if ( !$ibosFiles ) {
//			return array();
//		}
        $newUpgradeFileList = array();
        foreach ( $upgradeFileList as $hashFile ) {
            if ( file_exists( PATH_ROOT . '/' . $hashFile ) ) {
                $newUpgradeFileList[$hashFile] = md5_file( PATH_ROOT . '/' . $hashFile );
            }
        }
        $modifyList = $showList = $searchList = array();
		if ( !empty( $ibosFiles ) ) {
        foreach ( $ibosFiles as $line ) {
            $file = trim( substr( $line, 34 ) );
            $md5DataNew[$file] = substr( $line, 0, 32 );
            if ( isset( $newUpgradeFileList[$file] ) ) {
                // md5不相等，则需要升级
                if ( $md5DataNew[$file] != $newUpgradeFileList[$file] ) {
                    $modifyList[$file] = $file;
                } else {
                    $showList[$file] = $file;
                }
            }
        }
		}
        return array( $modifyList, $showList );
    }

    /**
     * 检查更新信息，这些信息将被记录在setting中的upgrade中
     * @return boolean 
     */
    public static function checkUpgrade() {
        $return = false;
        $ibosRelease = VERSION_DATE;
        $upgradeFile = self::UPGRADE_URL . self::getVersionPath() . '/' . $ibosRelease . '/upgrade.xml';
        $remoteResponse = File::fileSockOpen( $upgradeFile );
        $response = Xml::xmlToArray( $remoteResponse );
        if ( isset( $response['cross'] ) || isset( $response['patch'] ) ) {
            Setting::model()->updateSettingValueByKey( 'upgrade', $response );
            CacheUtil::update( 'setting' );
            $return = true;
        } else {
            Setting::model()->updateSettingValueByKey( 'upgrade', '' );
            $return = false;
        }
        return $return;
    }

    /**
     * 获取更新版本路径
     * @return type
     */
    public static function getVersionPath() {
        list($version, ) = explode( ' ', VERSION );
        return $version;
    }

    /**
     * 对比文件内容
     * @param string $file
     * @param string $remoteFile
     * @return boolean
     */
    public static function compareFileContent( $file, $remoteFile ) {
        if ( !preg_match( '/\.php$/i', $file ) ) {
            return false;
        }
        $content = preg_replace( '/\s/', '', file_get_contents( $file ) );
        $ctx = stream_context_create( array( 'http' => array( 'timeout' => 60 ) ) );
        $remotecontent = preg_replace( '/\s/', '', file_get_contents( $remoteFile, false, $ctx ) );
        if ( strcmp( $content, $remotecontent ) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 下载文件
     * @param array $upgradeInfo 更新信息
     * @param string $file 文件名(包括目录名)
     * @param string $folder 目录名，此目录名并不是指本地，而是官方服务器的目录
     * @param string $md5 md5码
     * @param int 可选 $position 文件指针，指定文件指针后，下载器将从这个指针开始下载
     * @param int 可选 $offset 指定本文内容长度，仅仅检验下载文件的长度是否和$offset相等，如果相等将不执行md5检验
     * @return boolean|int 0 md5检验失败	1 断点文件下载成功	2 md5检验成功（文件完全下载完成才会进行）
     */
    public static function downloadFile( $upgradeInfo, $file, $folder = 'upload', $md5 = '', $position = 0, $offset = 0 ) {
        $dir = PATH_ROOT . '/data/update/IBOS' . $upgradeInfo['latestversion'] . ' Release[' . $upgradeInfo['latestrelease'] . ']/';
        File::makeDirs( dirname( $dir . $file ) );
        $downloadFileFlag = true;
        if ( !$position ) {
            $mode = 'wb';
        } else {
            $mode = 'ab';
        }
        $fp = fopen( $dir . $file, $mode );
        if ( !$fp ) {
            return false;
        }
        //下载这个文件
        $tempUploadFileUrl = self::UPGRADE_URL . $upgradeInfo['latestversion'] . '/' . $upgradeInfo['latestrelease'] . '/' . self::$locale . '/' . $folder . '/' . $file . '.sc';
		$response = File::fileSockOpen( $tempUploadFileUrl, $offset, '', '', false, '', 15, true, 'URLENCODE', true, $position );
        if ( $response ) {
            if ( $offset && strlen( $response ) == $offset ) {
                $downloadFileFlag = false;
            }
            //写入
            fwrite( $fp, $response );
        }
        fclose( $fp );

        if ( $downloadFileFlag ) {
            $compare = md5_file( $dir . $file );
            //校验md5
            if ( $compare == $md5 ) {
                return 2;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    /**
     * 复制文件
     * @param string $srcFile 来源文件
     * @param string $desFile 目标文件
     * @param string $type 复制的类型，是直接复制还是使用ftp
     * @return boolean 复制成功与否
     */
    public static function copyFile( $srcFile, $desFile, $type ) {
        if ( !is_file( $srcFile ) ) {
            return false;
        }
        if ( $type == 'file' ) {
            File::makeDirs( dirname( $desFile ) );
            copy( $srcFile, $desFile );
        } elseif ( $type == 'ftp' ) {
            $ftpConf = Env::getRequest( 'ftp' );
            $ftpConf['on'] = 1;
            $ftpConf['password'] = String::authcode( $ftpConf['password'], 'ENCODE', md5( IBOS::app()->setting->get( 'config/security/authkey' ) ) );
            $ftp = Ftp::getInstance( $ftpConf );
            $ftp->connect();
            $ftp->upload( $srcFile, $desFile );
            if ( $ftp->error() ) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获得更新步骤名称，用于断点更新
     * @param integer $step 步骤 1：获取更新文件 2：下载更新 3：本地文件对比 4：正在更新
     * @return string 返回步骤名
     */
    public static function getStepName( $step ) {
        $stepNameArr = array(
            '1' => IBOS::lang( 'Upgrade get file' ),
            '2' => IBOS::lang( 'Upgrade download' ),
            '3' => IBOS::lang( 'Upgrade compare' ),
            '4' => IBOS::lang( 'Upgradeing' ),
            'dbupdate' => IBOS::lang( 'Upgrade db' )
        );
        return $stepNameArr[$step];
    }

    /**
     * 记录更新步骤
     * @param integer 第几步
     */
    public static function recordStep( $step ) {
        $upgradeStep = CacheModel::model()->fetchByPk( 'upgrade_step' );
        if ( !empty( $upgradeStep['cachevalue'] ) && !empty( $upgradeStep['cachevalue']['step'] ) ) {
            $upgradeStep['cachevalue'] = String::utf8Unserialize( $upgradeStep['cachevalue'] );
            $upgradeStep['cachevalue']['step'] = $step;
            CacheModel::model()->add( array(
                'cachekey' => 'upgrade_step',
                'cachevalue' => serialize( $upgradeStep['cachevalue'] ),
                'dateline' => TIMESTAMP,
                    ), false, true );
        }
    }

}
