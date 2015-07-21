<?php

/**
 * 系统升级类
 * 
 * @package application.core.components
 * @version $Id: Upgrade.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\core\components;

use application\core\utils\Ftp;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\core\utils\Xml;
use application\modules\file\model\File;
use application\modules\main\model\Setting;

class Upgrade {

    /**
     * 更新xml文件信息来由网址
     * @var string 
     */
    private $upgradeurl = 'http://update.ibos.com.cn/upgrade/';

    /**
     * 扩展名
     * @var string 
     */
    private $locale = 'SC';

    /**
     * 字符编码
     * @var string 
     */
    private $charset = 'UTF8';

    /**
     * 获取更新文件列表 原名fetch_updatefile_list
     * 
     * @param array $upgradeInfo 版本信息
     * @return array array('file'=>文件名列表, 'md5'=>每个文件对应的md5码) 
     * @author Ring 
     */
    public function fetchUpdateFileList( $upgradeInfo ) {
        //包含更新信息的文件的路径
        $version = 'ibos' . $upgradeInfo['latestversion'] . ' Release[' . $upgradeInfo['latestrelease'] . ']';
        $file = PATH_ROOT . './data/update/' . $version . '/updatelist.tmp';
        //更新数据的标记（如果为真表示上面这个文件存在）
        $upgradeDataFlag = true;  //假设这个文件存在
        $upgradeData = @file_get_contents( $file ); //获取文件内容
        if ( !$upgradeData ) { //不存在则根据当前版本下载更新信息
            $_txtFile = $this->upgradeurl . substr( $upgradeInfo['upgradelist'], 0, -4 ) . strtolower( '_' . $this->locale ) . '.txt';
            $upgradeData = File::fSockOpen( $_txtFile );
            $upgradeDataFlag = false;
        }
        $return = array();
        //分割文件，每行一个更新文件（包含文件名和MD5码）
        $upgradeDataArr = explode( "\r\n", $upgradeData );
        foreach ( $upgradeDataArr as $k => $v ) {
            if ( !$v ) {
                continue;
            }
            //每行的格式是这样： 32位md5码 + 两个字符（可能有特殊意义） + 文件名，由此我们可截取得到md5和文件名
            $return['file'][$k] = trim( substr( $v, 34 ) );
            $return['md5'][$k] = substr( $v, 0, 32 );
            //md5和文件名之间必需包含*号
            if ( trim( substr( $v, 32, 2 ) ) != '*' ) {
                @unlink( $file );
                return array();
            }
        }
        //如果包含更新信息的文件原本不存在，则写入刚刚下载过来的更新信息
        if ( !$upgradeDataFlag ) {
            $this->mkdirs( dirname( $file ) );
            $fp = fopen( $file, 'w' );
            if ( !$fp ) {
                return array();
            }
            fwrite( $fp, $upgradeData );
        }
        return $return;
    }

    /**
     * 对比文件，筛选出需要更新的文件列表 原名compare_basefile
     * 
     * @param array $upgradeInfo 更新信息
     * @param array $upgradeFileList 需要更新的文件列表
     * @return array array(需要更新的文件列表, 不需要更新的文件列表, 被忽略的文件列表（不存在或者被忽略的文件）)
     * @author Ring 
     */
    public function compareBaseFile( $upgradeInfo, $upgradeFileList ) {
        //TODO ./source/admincp/ibosfiles.md5文件路径有待后期升级决定
        if ( !$ibosFiles = @file( './source/admincp/ibosfiles.md5' ) ) {
            return array();
        }
        $newUpgradeFileList = array();
        foreach ( $upgradeFileList as $v ) {
            $newUpgradeFileList[$v] = md5_file( PATH_ROOT . './' . $v );
        }
        $modifyList = $showList = $searchList = array();
        foreach ( $ibosFiles as $line ) {
            $file = trim( substr( $line, 34 ) );
            $md5DataNew[$file] = substr( $line, 0, 32 );
            if ( isset( $newUpgradeFileList[$file] ) ) {
                if ( $md5DataNew[$file] != $newUpgradeFileList[$file] ) { //md5不相等，则需要升级
                    if ( !$upgradeInfo['isupdatetemplate'] && preg_match( '/\.htm$/i', $file ) ) { //如果不是更新模板但是又有htm文件需要更新
                        $ignoreList[$file] = $file;
                        $searchList[] = "\r\n" . $file; //添加到搜索列表
                        continue;
                    }
                    $modifyList[$file] = $file;
                } else {
                    $showList[$file] = $file;
                }
            }
        }
        if ( $searchList ) { //在搜索列表中的文件将不被更新
            $version = 'ibos' . $upgradeInfo['latestversion'] . ' Release[' . $upgradeInfo['latestrelease'] . ']';
            $file = PATH_ROOT . './data/update/' . $version . '/updatelist.tmp';
            $upgradeData = file_get_contents( $file );
            $upgradeData = str_replace( $searchList, '', $upgradeData );
            $fp = fopen( $file, 'w' );
            if ( $fp ) {
                fwrite( $fp, $upgradeData );
            }
        }
        return array( $modifyList, $showList, $ignoreList );
    }

    /**
     * 原名compare_file_content
     * 
     * @param string $file
     * @param string $remoteFile
     * @return boolean
     * @author Ring
     */
    public function compareFileContent( $file, $remoteFile ) {
        if ( !preg_match( '/\.php$|\.htm$/i', $file ) ) {
            return false;
        }
        $content = preg_replace( '/\s/', '', file_get_contents( $file ) );
        $ctx = stream_context_create( array( 'http' => array( 'timeout' => 60 ) ) );
        $remoteContent = preg_replace( '/\s/', '', file_get_contents( $remoteFile, false, $ctx ) );
        if ( strcmp( $content, $remoteContent ) ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查更新信息，这些信息将被记录在setting中的upgrade中
     * 
     * @return boolean 
     * @author Ring 
     */
    public function checkUpgrade() {
        $return = false;
        $upgradeFile = $this->upgradeurl . $this->versionPath() . '/' . IBOS_RELEASE . '/upgrade.xml';
        $xmlContents = File::fileSockOpen( $upgradeFile );
        $response = Xml::xmlToArray( $xmlContents );
        if ( isset( $response['cross'] ) || isset( $response['patch'] ) ) {
            Setting::model()->updateByPk( 'upgrade', array( 'value' => serialize( $response ) ) );
            $return = true;
        } else {
            Setting::model()->updateByPk( 'upgrade', array( 'value' => '' ) );
            $return = false;
        }
        $setting = IBOS::app()->setting->get( 'setting' );
        $setting['upgrade'] = ( isset( $response['cross'] ) || isset( $response['patch'] ) ) ? $response : array();
        IBOS::app()->setting->set( 'setting', $setting );
        return $return;
    }

    /**
     * 原名check_folder_perm
     * 
     * @param array $updateFileList
     * @return boolean
     * @author Ring 
     */
    public function checkFolderPerm( $updateFileList ) {
        foreach ( $updateFileList as $file ) {
            if ( !file_exists( PATH_ROOT . '/' . $file ) ) {
                if ( !$this->testWritAble( dirname( PATH_ROOT . '/' . $file ) ) ) {
                    return false;
                }
            } else {
                if ( !is_writable( PATH_ROOT . '/' . $file ) ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 测试是否有写文件的权限，有返回1 原名test_writable
     * 
     * @param string $dir
     * @return int
     * @author Ring 
     */
    public function testWritAble( $dir ) {
        $writeable = 0;
        $this->mkdirs( $dir );
        if ( is_dir( $dir ) ) {
            if ( $fp = @fopen( "$dir/test.txt", 'w' ) ) {
                @fclose( $fp );
                @unlink( "$dir/test.txt" );
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }
        return $writeable;
    }

    /**
     * 下载文件 原名download_file
     * 
     * @param array $upgradeInfo 更新信息
     * @param string $file 文件名(包括目录名)
     * @param string $folder 目录名，此目录名并不是指本地，而是官方服务器的目录
     * @param string $md5 md5码
     * @param integer 可选 $position 文件指针，指定文件指针后，下载器将从这个指针开始下载
     * @param integer 可选 $offset 指定本文内容长度，仅仅检验下载文件的长度是否和$offset相等，如果相等将不执行md5检验
     * @return boolean|int 0 md5检验失败	1 断点文件下载成功	2 md5检验成功（文件完全下载完成才会进行）
     * @author Ring 
     */
    public function downloadFile( $upgradeInfo, $file, $folder = 'upload', $md5 = '', $position = 0, $offset = 0 ) {
        $dir = PATH_ROOT . './data/update/IBOS' . $upgradeInfo['latestversion'] . ' Release[' . $upgradeInfo['latestrelease'] . ']/';
        $this->mkdirs( dirname( $dir . $file ) );
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
        $_uploadFileUrl = $this->upgradeurl . $upgradeInfo['latestversion'] . '/' . $upgradeInfo['latestrelease'] . '/' . $this->locale . '/' . $folder . '/' . $file . '.sc';
        $response = File::fSockOpen( $_uploadFileUrl, $offset, '', '', FALSE, '', 15, TRUE, 'URLENCODE', FALSE, $position );
        if ( $response ) {
            if ( $offset && strlen( $response ) == $offset ) {
                $downloadFileFlag = false;
            }
            fwrite( $fp, $response ); //写入
        }
        fclose( $fp );
        if ( $downloadFileFlag ) {
            if ( md5_file( $dir . $file ) == $md5 ) { //校验md5
                return 2;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    /**
     * 新建目录
     * 
     * @param string $dir
     * @return boolean
     * @author Ring 
     */
    public function mkdirs( $dir ) {
        if ( !is_dir( $dir ) ) {
            if ( !self::mkdirs( dirname( $dir ) ) ) {
                return false;
            }
            if ( !@mkdir( $dir, 0777 ) ) {
                return false;
            }
            @touch( $dir . '/index.htm' );
            @chmod( $dir . '/index.htm', 0777 );
        }
        return true;
    }

    /**
     * 原名copy_file
     * 
     * @param string $srcFile
     * @param string $desFile
     * @param string $type
     * @return boolean
     * @author Ring 
     */
    public function copyFile( $srcFile, $desFile, $type ) {
        $_G = IBOS::app()->setting->toArray();
        if ( !is_file( $srcFile ) ) {
            return false;
        }
        if ( $type == 'file' ) {
            $this->mkdirs( dirname( $desFile ) );
            copy( $srcFile, $desFile );
        } elseif ( $type == 'ftp' ) {
            $siteFtp = $_GET['siteftp'];
            $siteFtp['on'] = 1;
            $autoKey = md5( $_G['config']['security']['authkey'] );
            $siteFtp['password'] = String::authCode( $siteFtp['password'], 'ENCODE', $autoKey );
            $ftp = & Ftp::instance( $siteFtp );
            $ftp->connect();
            $ftp->upload( $srcFile, $desFile );
            if ( $ftp->error() ) {
                return false;
            }
        }
        return true;
    }

    /**
     * versionPath
     * 
     * @return string
     */
    public function versionPath() {
        $versionPath = '';
        foreach ( explode( ' ', IBOS_VERSION ) as $unit ) {
            $versionPath = $unit;
            break;
        }
        return $versionPath;
    }

    /**
     * 复制目录
     * 
     * @param string $srcDir
     * @param string $destDir
     */
    public function copyDir( $srcDir, $destDir ) {
        $dir = @opendir( $srcDir );
        while ( $entry = @readdir( $dir ) ) {
            $file = $srcDir . $entry;
            if ( $entry != '.' && $entry != '..' ) {
                if ( is_dir( $file ) ) {
                    self::copyDir( $file . '/', $destDir . $entry . '/' );
                } else {
                    self::mkdirs( dirname( $destDir . $entry ) );
                    copy( $file, $destDir . $entry );
                }
            }
        }
        closedir( $dir );
    }

    /**
     * 删除目录
     * 
     * @param string $srcDir
     * @return void 
     */
    public function rmdirs( $srcDir ) {
        $dir = @opendir( $srcDir );
        while ( $entry = @readdir( $dir ) ) {
            $file = $srcDir . $entry;
            if ( $entry != '.' && $entry != '..' ) {
                if ( is_dir( $file ) ) {
                    self::rmdirs( $file . '/' );
                } else {
                    @unlink( $file );
                }
            }
        }
        closedir( $dir );
        rmdir( $srcDir );
    }

}
