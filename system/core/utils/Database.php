<?php

/**
 * 数据库工具类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 数据库工具类库，处理一切与数据库相关操作。包括统计，导出，备份，优化等等
 * @package application.core.utils
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: database.php -1   $
 */

namespace application\core\utils;

use application\extensions\Zip;
use application\modules\main\model\Setting;

class Database {

    /**
     * 备份目录
     */
    const BACKUP_DIR = 'data/backup';

    /**
     * 查询备份数据偏移量
     */
    const OFFSET = 300;

    /**
     * 备份时要排除的表
     * @var array 
     */
    private static $exceptTables = array( 'session' );

    /**
     * 开始处理行数。这个值在备份时会被重复赋值
     * @var integer 
     */
    private static $startRow = 0;

    /**
     * 完成状态标识
     * @var boolean 
     */
    private static $complete = true;

    /**
     * 获取表前缀
     * @return string
     */
    public static function getdbPrefix() {
        return IBOS::app()->setting->get( 'config/db/tableprefix' );
    }

    /**
     * 获取数组里$key的值到另外一个数组
     * @param array $array 源数组
     * @param array $key 要获取的值
     * @return array 过滤后的数组
     */
    public static function arrayKeysTo( $array, $key ) {
        $return = array();
        foreach ( $array as $val ) {
            $return[] = $val[$key];
        }
        return $return;
    }

    /**
     * 获取数据库大小
     * @return string
     */
    public static function getDatabaseSize() {
        $tableList = self::getTablelist( (string) self::getdbPrefix() );
        $count = 0;
        foreach ( $tableList as $table ) {
            $count += $table['Data_length'];
        }
        $size = Convert::sizeCount( $count );
        return $size;
    }

    /**
     * 获取全部ibos数据表的列表，过滤表前缀
     * @param string $tablePrefix 表前缀
     * @return array 过滤后的数据列表数组
     */
    public static function getTablelist( $tablePrefix = '' ) {
        $arr = explode( '.', $tablePrefix );
        $dbName = isset( $arr[1] ) ? $arr[0] : '';
        $prefix = str_replace( '_', '\_', $tablePrefix );
        $sqlAdd = $dbName ? " FROM {$dbName} LIKE '$arr[1]%'" : "LIKE '{$prefix}%'";
        $tables = $table = array();
        $command = IBOS::app()->db->createCommand( "SHOW TABLE STATUS {$sqlAdd}" );
        $command->execute();
        $query = $command->query();
        foreach ( $query as $table ) {
            $table['Name'] = ($dbName ? "{$dbName}." : '') . $table['Name'];
            $tables[] = $table;
        }
        return $tables;
    }

    /**
     * 获取一个表的状态
     * @param string $tableName 表名
     * @param boolean $formatSize 格式化表数据大小与索引大小
     * @return array
     */
    public static function getTableStatus( $tableName, $formatSize = true ) {
        $status = IBOS::app()->db->createCommand()
                ->setText( "SHOW TABLE STATUS LIKE '{{" . str_replace( '_', '\_', $tableName ) . "}}'" )
                ->queryRow();

        if ( $formatSize ) {
            $status['Data_length'] = Convert::sizeCount( $status['Data_length'] );
            $status['Index_length'] = Convert::sizeCount( $status['Index_length'] );
        }
        return $status;
    }

    /**
     * 删除某一个表
     * @param string $tableName 要删除的表名
     * @param boolean $force 是否强制删除（非强制情况只要表存在数据将不进行删除）
     * @return integer -1 表存在数据 1 删除成功
     * @author denglh
     */
    public static function dropTable( $tableName, $force = false ) {
        $quoteTableName = "{{{$tableName}}}";
        if ( $force ) {
            IBOS::app()->db->createCommand()->dropTable( $quoteTableName );
            return 1;
        } else {
            $tableInfo = self::getTableStatus( $tableName );
            if ( $tableInfo['Rows'] == 0 ) {
                IBOS::app()->db->createCommand()->dropTable( $quoteTableName );
                return 1;
            } else {
                return -1;
            }
        }
    }

    /**
     * 参照某一个表的数据结构，创建一个与其一样的表
     * @param string $prototype 原型表的表名
     * @param string $target 目标表名
     * @return boolean 
     */
    public static function cloneTable( $prototype, $target ) {
        $db = IBOS::app()->db->createCommand();
        $prefix = IBOS::app()->db->tablePrefix;
        $prototype = $prefix . $prototype;
        $target = $prefix . $target;
        $db->setText( 'SET SQL_QUOTE_SHOW_CREATE = 0' )->execute();
        $create = $db->setText( "SHOW CREATE TABLE {$prototype}" )->queryRow();
        $createSql = $create['Create Table'];
        $createSql = preg_replace( '/^([^\(]*)' . $prototype . '/', '$1' . $target, $createSql );
        return $db->setText( $createSql )->execute();
    }

    /**
     * 导出数据表结构
     * @param string $table 表名
     * @param string $compat 建表语句格式
     * @param string $dumpCharset 输出编码
     * @param string $charset 数据库编码
     * @return string 数据库表结构字符串
     */
    public static function getSqlDumpTableStruct( $table, $compat, $dumpCharset, $charset = '' ) {
        $command = IBOS::app()->db->createCommand();
        $rows = $command->setText( "SHOW CREATE TABLE {$table}" )->queryRow();
        if ( $rows ) {
            $tableDump = "DROP TABLE IF EXISTS {$table};\n";
        } else {
            return '';
        }
        if ( strpos( $table, '.' ) !== false ) {
            $tableName = substr( $table, strpos( $table, '.' ) + 1 );
            $rows['Create Table'] = str_replace( "CREATE TABLE {$tableName}", 'CREATE TABLE ' . $table, $rows['Create Table'] );
        }
        $tableDump .= $rows['Create Table'];
        $dbVersion = IBOS::app()->db->getServerVersion();
        if ( $compat == 'MYSQL41' && $dbVersion < '4.1' ) {
            $tableDump = preg_replace( '/TYPE\=(.+)/', 'ENGINE=\\1 DEFAULT CHARSET=' . $dumpCharset, $tableDump );
        }
        if ( $dbVersion > '4.1' && $charset ) {
            $tableDump = preg_replace( '/(DEFAULT)*\s*CHARSET=.+/', 'DEFAULT CHARSET=' . $charset, $tableDump );
        }

        $tableStatus = $command->setText( "SHOW TABLE STATUS LIKE '{$table}'" )->queryRow();
        $tableDump .= ($tableStatus['Auto_increment'] ? " AUTO_INCREMENT={$tableStatus['Auto_increment']}" : '') . ";\n\n";
        if ( $compat == 'MYSQL40' && $dbVersion >= '4.1' && $dbVersion < '5.1' ) {
            if ( $tableStatus['Auto_increment'] <> '' ) {
                $temppos = strpos( $tableDump, ',' );
                $tableDump = substr( $tableDump, 0, $temppos ) . ' auto_increment' . substr( $tableDump, $temppos );
            }
            if ( $tableStatus['Engine'] == 'MEMORY' ) {
                $tableDump = str_replace( 'TYPE=MEMORY', 'TYPE=HEAP', $tableDump );
            }
        }
        return $tableDump;
    }

    /**
     * 数据库备份操作
     * @todo 分离分卷备份与shell备份操作为子函数
     * @return array 返回一个带有消息状态及消息内容的数组
     */
    public static function databaseBackup() {
        $config = IBOS::app()->setting->toArray();
        // 设置备份时关键字不转义
        $command = IBOS::app()->db->createCommand( 'SET SQL_QUOTE_SHOW_CREATE=0' );
        $command->execute();
        // 检查导出名字
        $fileName = Env::getRequest( 'filename' );
        $hasDangerFileName = preg_match( '/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i', $fileName );
        if ( !$fileName || (boolean) $hasDangerFileName ) {
            return array(
                'type' => 'error',
                'msg' => IBOS::lang( 'Database export filename invalid', 'dashboard.default' )
            );
        }

        $tablePrefix = $config['config']['db']['tableprefix'];
        $dbCharset = $config['config']['db']['charset'];
        // --- 备份模式 ---
        $type = Env::getRequest( 'backuptype' );
        // 取得要备份的表
        if ( $type == 'all' ) {
            // 全部
            $tableList = self::getTablelist( $tablePrefix );
            $tables = self::arrayKeysTo( $tableList, 'Name' );
        } elseif ( $type == 'custom' ) {
            // 自定义
            $tables = array();
            // 如果不是第一次备份，取之前存到数据表里的表记录
            if ( is_null( Env::getRequest( 'dbSubmit' ) ) ) {
                $tables = Setting::model()->fetchSettingValueByKey( 'custombackup' );
                $tables = String::utf8Unserialize( $tables );
            } else {
                // 如果是第一次备份，取表单里的提交，存到setting表以用来重复调用此方法
                $customTables = Env::getRequest( 'customtables' );
                Setting::model()->updateSettingValueByKey( 'custombackup', is_null( $customTables ) ? '' : $customTables  );
                $tables = &$customTables;
            }
            if ( !is_array( $tables ) || empty( $tables ) ) {
                return array(
                    'type' => 'error',
                    'msg' => IBOS::lang( 'Database export custom invalid', 'dashboard.default' )
                );
            }
        }
        // -----------------

        $time = date( 'Y-m-d H:i:s', TIMESTAMP );
        $volume = intval( Env::getRequest( 'volume' ) ) + 1;
        $method = Env::getRequest( 'method' );
        $encode = base64_encode( "{$config['timestamp']}," . VERSION . ",{$type},{$method},{$volume},{$tablePrefix},{$dbCharset}" );
        $idString = '# Identify: ' . $encode . "\n";
        // 导出编码
        $sqlCharset = Env::getRequest( 'sqlcharset' );
        $sqlCompat = Env::getRequest( 'sqlcompat' );
        $dbVersion = IBOS::app()->db->getServerVersion();
        $useZip = Env::getRequest( 'usezip' );
        $useHex = Env::getRequest( 'usehex' );
        $extendIns = Env::getRequest( 'extendins' );
        $sizeLimit = Env::getRequest( 'sizelimit' );
        $dumpCharset = !empty( $sqlCharset ) ? $sqlCharset : str_replace( '-', '', CHARSET );
        $isNewSqlVersion = $dbVersion > '4.1' && (!is_null( $sqlCompat ) || $sqlCompat == 'MYSQL41');
        $setNames = (!empty( $sqlCharset ) && $isNewSqlVersion) ? "SET NAMES '{$dumpCharset}';\n\n" : '';
        if ( $dbVersion > '4.1' ) {
            if ( $sqlCharset ) {
                $command->setText( "SET NAMES `{$sqlCharset}`" )->execute();
            }
            if ( $sqlCompat == 'MYSQL40' ) {
                $command->setText( "SET SQL_MODE='MYSQL40'" )->execute();
            } elseif ( $sqlCompat == 'MYSQL41' ) {
                $command->setText( "SET SQL_MODE=''" )->execute();
            }
        }
        // --- 备份文件夹及备份文件名 ---
        if ( !is_dir( self::BACKUP_DIR ) ) {
            File::makeDir( self::BACKUP_DIR, 0777 );
        }
        $backupFileName = self::BACKUP_DIR . '/' . str_replace( array( '/', '\\', '.', "'" ), '', $fileName );
        // --------------------------
        // ibos 分卷备份
        if ( $method == 'multivol' ) {
            $sqlDump = '';
            $tableId = intval( Env::getRequest( 'tableid' ) );
            $startFrom = intval( Env::getRequest( 'startfrom' ) );
            if ( !$tableId && $volume == 1 ) {
                foreach ( $tables as $table ) {
                    $sqlDump .= self::getSqlDumpTableStruct( $table, $sqlCompat, $sqlCharset, $dumpCharset );
                }
            }

            self::$complete = true;
            for (; self::$complete && $tableId < count( $tables ) && strlen( $sqlDump ) + 500 < $sizeLimit * 1000; $tableId++ ) {
                $sqlDump .= self::sqlDumpTable( $tables[$tableId], $extendIns, $sizeLimit, $useHex, $startFrom, strlen( $sqlDump ) );
                if ( self::$complete ) {
                    $startFrom = 0;
                }
            }

            $dumpFile = $backupFileName . "-%s" . '.sql';
            !self::$complete && $tableId--;
            if ( trim( $sqlDump ) ) {
                $sqlDump = "{$idString}" .
                        "# <?php exit();?>\n" .
                        "# IBOS Multi-Volume Data Dump Vol.{$volume}\n" .
                        "# Version: IBOS {$config['version']}\n" .
                        "# Time: {$time}\n" .
                        "# Type: {$type}\n" .
                        "# Table Prefix: {$tablePrefix}\n" .
                        "#\n" .
                        "# IBOS Home: http://www.ibos.com.cn\n" .
                        "# Please visit our website for newest infomation about IBOS\n" .
                        "# --------------------------------------------------------\n\n\n" .
                        "{$setNames}" .
                        $sqlDump;
                $dumpFileName = sprintf( $dumpFile, $volume );
                @$fp = fopen( $dumpFileName, 'wb' );
                @flock( $fp, 2 );
                if ( @!fwrite( $fp, $sqlDump ) ) {
                    @fclose( $fp );
                    return array(
                        'type' => 'error',
                        'msg' => IBOS::lang( 'Database export file invalid', 'dashboard.default' ),
                        'url' => ''
                    );
                } else {
                    fclose( $fp );
                    if ( $useZip == 2 ) {
                        $fp = fopen( $dumpFileName, "r" );
                        $content = @fread( $fp, filesize( $dumpFileName ) );
                        fclose( $fp );
                        $zip = new Zip();
                        $zip->addFile( $content, basename( $dumpFileName ) );
                        $fp = fopen( sprintf( $backupFileName . "-%s" . '.zip', $volume ), 'w' );
                        if ( @fwrite( $fp, $zip->file() ) !== false ) {
                            @unlink( $dumpFileName );
                        }
                        fclose( $fp );
                    }
                    unset( $sqlDump, $zip, $content );
                    $param = array(
                        'setup' => 1,
                        'backuptype' => rawurlencode( $type ),
                        'filename' => rawurlencode( $fileName ),
                        'method' => 'multivol',
                        'sizelimit' => rawurlencode( $sizeLimit ),
                        'volume' => rawurlencode( $volume ),
                        'tableid' => rawurlencode( $tableId ),
                        'startfrom' => rawurlencode( self::$startRow ),
                        'extendins' => rawurlencode( $fileName ),
                        'sqlcharset' => rawurlencode( $sqlCharset ),
                        'sqlcompat' => rawurlencode( $sqlCompat ),
                        'usehex' => $useHex,
                        'usezip' => $useZip
                    );
                    $url = IBOS::app()->urlManager->createUrl( 'dashboard/database/backup', $param );
                    return array(
                        'type' => 'success',
                        'msg' => IBOS::lang( 'Database export multivol redirect', 'dashboard.default', array( 'volume' => $volume ) ),
                        'url' => $url
                    );
                }
            } else {
                $volume--;
                if ( $useZip == 1 ) {
                    $zip = new Zip();
                    $zipFileName = $backupFileName . '.zip';
                    $unlinks = array();
                    for ( $i = 1; $i <= $volume; $i++ ) {
                        $filename = sprintf( $dumpFile, $i );
                        $fp = fopen( $filename, "r" );
                        $content = @fread( $fp, filesize( $filename ) );
                        fclose( $fp );
                        $zip->addFile( $content, basename( $filename ) );
                        $unlinks[] = $filename;
                    }
                    $fp = fopen( $zipFileName, 'w' );
                    if ( @fwrite( $fp, $zip->file() ) !== false ) {
                        foreach ( $unlinks as $link ) {
                            @unlink( $link );
                        }
                    } else {
                        return array(
                            'type' => 'success',
                            'msg' => IBOS::lang( 'Database export multivol succeed', 'dashboard.default', array( 'volume' => $volume ) ),
                            'url' => IBOS::app()->urlManager->createUrl( 'dashboard/database/restore' )
                        );
                    }
                    unset( $sqlDump, $zip, $content );
                    fclose( $fp );
                    $filename = $zipFileName;
                    return array(
                        'type' => 'success',
                        'msg' => IBOS::lang( 'Database export zip succeed', 'dashboard.default' ),
                        'param' => array( 'autoJump' => false )
                    );
                } else {
                    return array(
                        'type' => 'success',
                        'msg' => IBOS::lang( 'Database export multivol succeed', 'dashboard.default', array( 'volume' => $volume ) ),
                        'url' => IBOS::app()->urlManager->createUrl( 'dashboard/database/restore' )
                    );
                }
            }
        } else {
            // shell 备份
            $tablesstr = '';
            foreach ( $tables as $table ) {
                $tablesstr .= '"' . $table . '" ';
            }
            $db = $config['config']['db'];
            $query = $command->setText( "SHOW VARIABLES LIKE 'basedir'" )->queryRow();
            $mysqlBase = $query['Value'];

            $dumpFile = addslashes( dirname( dirname( __FILE__ ) ) ) . '/' . $backupFileName . '.sql';
            @unlink( $dumpFile );
            $mysqlBin = $mysqlBase == '/' ? '' : addslashes( $mysqlBase ) . 'bin/';

            shell_exec( $mysqlBin . 'mysqldump --force --quick ' . ($dbVersion > '4.1' ? '--skip-opt --create-options' : '-all') . ' --add-drop-table' . (Env::getRequest( 'extendins' ) == 1 ? ' --extended-insert' : '') . '' . ($dbVersion > '4.1' && $sqlCompat == 'MYSQL40' ? ' --compatible=mysql40' : '') . ' --host="' . $db['host'] . ($db['port'] ? (is_numeric( $db['port'] ) ? ' --port=' . $db['port'] : ' --socket="' . $db['port'] . '"') : '') . '" --user="' . $db['username'] . '" --password="' . $db['password'] . '" "' . $db['dbname'] . '" ' . $tablesstr . ' > ' . $dumpFile );
            if ( @file_exists( $dumpFile ) ) {
                if ( $useZip ) {
                    $zip = new Zip();
                    $zipfilename = $backupFileName . '.zip';
                    $fp = fopen( $dumpFile, "r" );
                    $content = @fread( $fp, filesize( $dumpFile ) );
                    fclose( $fp );
                    $zip->addFile( $idString . "# <?php exit();?>\n " . $setNames . "\n #" . $content, basename( $dumpFile ) );
                    $fp = fopen( $zipfilename, 'w' );
                    @fwrite( $fp, $zip->file() );
                    fclose( $fp );
                    @unlink( $dumpFile );
                    $filename = $backupFileName . '.zip';
                    unset( $sqlDump, $zip, $content );
                    return array(
                        'type' => 'success',
                        'msg' => IBOS::lang( 'Database export zip succeed', 'dashboard.default' ),
                        'url' => IBOS::app()->urlManager->createUrl( 'dashboard/database/restore' )
                    );
                } else {
                    if ( @is_writeable( $dumpFile ) ) {
                        $fp = fopen( $dumpFile, 'rb+' );
                        @fwrite( $fp, $idString . "# <?php exit();?>\n " . $setNames . "\n #" );
                        fclose( $fp );
                    }
                    $filename = $backupFileName . '.sql';
                    return array(
                        'type' => 'success',
                        'msg' => IBOS::lang( 'Database export succeed', 'dashboard.default' ),
                        'param' => IBOS::app()->urlManager->createUrl( 'dashboard/database/restore' )
                    );
                }
            } else {
                return array(
                    'type' => 'error',
                    'msg' => IBOS::lang( 'Database shell fail', 'dashboard.default' )
                );
            }
        }// end else
    }

    /**
     * 导出表数据
     * @param string $table 表名
     * @param integer $extendIns 是否使用扩展插入方式
     * @param integer $sizeLimit 导出的条数
     * @param boolean $useHex 是否使用16进制
     * @param integer $startFrom 开始导出位置
     * @param integer $currentSize 当前数据大小
     * @return string
     */
    public static function sqlDumpTable( $table, $extendIns, $sizeLimit, $useHex = true, $startFrom = 0, $currentSize = 0 ) {
        $offset = self::OFFSET;
        $tableDump = '';
        $command = IBOS::app()->db->createCommand();
        $tableFields = $command->setText( "SHOW FULL COLUMNS FROM `{$table}`" )->queryAll();
        if ( !$tableFields ) {
            $useHex = false;
        }
        if ( !in_array( $table, self::getExceptTables() ) ) {
            $tableDumped = 0;
            $numRows = $offset;
            $firstField = $tableFields [0];
            //不使用扩展插入模式
            if ( $extendIns == '0' ) {
                while ( ($currentSize + strlen( $tableDump ) + 500 < $sizeLimit * 1000) && ($numRows == $offset) ) {
                    if ( $firstField['Extra'] == 'auto_increment' ) {
                        $selectSql = "SELECT * FROM `{$table}` WHERE `{$firstField['Field']}` > {$startFrom} ORDER BY `{$firstField['Field']}` LIMIT {$offset}";
                    } else {
                        $selectSql = "SELECT * FROM `{$table}` LIMIT {$startFrom}, {$offset}";
                    }
                    $tableDumped = 1;
                    $numRows = $command->setText( $selectSql )->execute();
                    $rows = $command->queryAll();
                    foreach ( $rows as $row ) {
                        $comma = $t = '';
                        $index = 0;
                        foreach ( $row as $value ) {
                            $t .= $comma . ($useHex && !empty( $value ) && ( String::strExists( $tableFields[$index]['Type'], 'char' ) || String::strExists( $tableFields[$index]['Type'], 'text' )) ? '0x' . bin2hex( $value ) : '\'' . addslashes( $value ) . '\'' );
                            $comma = ',';
                            $index++;
                        }
                        if ( strlen( $t ) + $currentSize + strlen( $tableDump ) + 500 < $sizeLimit * 1000 ) {
                            if ( $firstField['Extra'] == 'auto_increment' ) {
                                $startFrom = array_shift( $row );
                            } else {
                                $startFrom++;
                            }
                            $tableDump .= "INSERT INTO `{$table}` VALUES ( {$t});\n";
                        } else {
                            self::$complete = false;
                            break 2;
                        }
                    }
                }
            } else {
                while ( ($currentSize + strlen( $tableDump ) + 500 < $sizeLimit * 1000) && ($numRows == $offset) ) {
                    if ( $firstField['Extra'] == 'auto_increment' ) {
                        $selectSql = "SELECT * FROM `{$table}` WHERE {$firstField['Field']} > {$startFrom} LIMIT {$offset}";
                    } else {
                        $selectSql = "SELECT * FROM `{$table}` LIMIT {$startFrom}, {$offset}";
                    }
                    $tableDumped = 1;
                    $numRows = $command->setText( $selectSql )->execute();
                    $rows = $command->queryAll();
                    if ( $numRows ) {
                        $t1 = $comma1 = '';
                        foreach ( $rows as $row ) {
                            $t2 = $comma2 = '';
                            $index = 0;
                            foreach ( $row as $value ) {
                                $t2 .= $comma2 . ($useHex && !empty( $value ) && ( String::strExists( $tableFields[$index]['Type'], 'char' ) || String::strExists( $tableFields[$index]['Type'], 'text' )) ? '0x' . bin2hex( $value ) : '\'' . addslashes( $value ) . '\'');
                                $comma2 = ',';
                                $index++;
                            }
                            if ( strlen( $t1 ) + $currentSize + strlen( $tableDump ) + 500 < $sizeLimit * 1000 ) {
                                if ( $firstField['Extra'] == 'auto_increment' ) {
                                    $startFrom = array_shift( $row );
                                } else {
                                    $startFrom++;
                                }
                                $t1 .= "$comma1 ($t2)";
                                $comma1 = ',';
                            } else {
                                $tableDump .= "INSERT INTO `{$table}` VALUES {$t1};\n";
                                self::$complete = false;
                                break 2;
                            }
                        }
                        $tableDump .= "INSERT INTO `{$table}` VALUES {$t1};\n";
                    }
                }
            } // end else
            self::$startRow = $startFrom;
            $tableDump .= "\n";
        }
        return $tableDump;
    }

    /**
     * 获取备份文件列表，返回数组格式
     * @return array
     */
    public static function getBackupList() {
        $exportLog = $exportSize = $exportZipLog = array();
        if ( is_dir( self::BACKUP_DIR ) ) {
            $dir = dir( self::BACKUP_DIR );
            while ( $entry = $dir->read() ) {
                $entry = self::BACKUP_DIR . '/' . $entry;
                if ( is_file( $entry ) ) {
                    if ( preg_match( "/\.sql$/i", $entry ) ) {
                        $fileSize = filesize( $entry );
                        $fp = fopen( $entry, 'rb' );
                        $identify = explode( ',', base64_decode( preg_replace( "/^# Identify:\s*(\w+).*/s", "\\1", fgets( $fp, 256 ) ) ) );
                        fclose( $fp );
                        $key = preg_replace( '/^(.+?)(\-\d+)\.sql$/i', '\\1', basename( $entry ) );
                        $exportLog[$key][$identify[4]] = array(
                            'version' => $identify[1],
                            'type' => $identify[2],
                            'method' => $identify[3],
                            'volume' => $identify[4],
                            'filename' => $entry,
                            'dateline' => filemtime( $entry ),
                            'size' => $fileSize
                        );
                        if ( isset( $exportSize[$key] ) ) {
                            $exportSize[$key] += $fileSize;
                        } else {
                            $exportSize[$key] = $fileSize;
                        }
                    } elseif ( preg_match( "/\.zip$/i", $entry ) ) {
                        $fileSize = filesize( $entry );
                        $exportZipLog[] = array(
                            'type' => 'zip',
                            'filename' => $entry,
                            'size' => filesize( $entry ),
                            'dateline' => filemtime( $entry )
                        );
                    }
                }
            }
            $dir->close();
        }
        return array( 'exportLog' => $exportLog, 'exportSize' => $exportSize, 'exportZipLog' => $exportZipLog );
    }

    /**
     * 获取可优化的数据表列表
     * @return array
     */
    public static function getOptimizeTable() {
        $tableType = IBOS::app()->db->getServerVersion() > '4.1' ? 'Engine' : 'Type';
        $lists = self::getTablelist( self::getdbPrefix() );
        $tables = array();
        foreach ( $lists as $list ) {
            if ( $list['Data_free'] && $list[$tableType] == 'MyISAM' ) {
                $list['checked'] = $list[$tableType] == 'MyISAM' ? 'checked' : 'disabled';
                $list['tableType'] = $tableType;
                $tables[] = $list;
            }
        }
        return $tables;
    }

    /**
     * 调用数据库自带的优化命令优化数据表
     * @param array $tables
     * @return boolean
     */
    public static function optimize( $tables ) {
        $command = IBOS::app()->db->createCommand();
        foreach ( $tables as $table ) {
            $command->setText( "OPTIMIZE TABLE {$table}" )->execute();
        }
        return true;
    }

    /**
     * 返回备份文件夹，方便其他程序调用
     * @return string
     */
    public static function getBackupDir() {
        return self::BACKUP_DIR;
    }

    /**
     * getter方法，对排除的表加上表前缀
     * @return string
     */
    private static function getExceptTables() {
        $tables = array();
        $prefix = self::getdbPrefix();
        foreach ( self::$exceptTables as $table ) {
            $tables[] = $prefix . $table;
        }
        return $tables;
    }

    /**
     * 同步数据表结构
     * @param string $sql
     * @param boolean $version 是否更新版本的数据库
     * @param string $dbCharset 数据库编码
     * @return mixed
     */
    public static function syncTableStruct( $sql, $version, $dbCharset ) {
        if ( strpos( trim( substr( $sql, 0, 18 ) ), 'CREATE TABLE' ) === false ) {
            return $sql;
        }
        $sqlVersion = strpos( $sql, 'ENGINE=' ) === false ? false : true;
        if ( $sqlVersion === $version ) {
            $pattern = array( '/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is" );
            $replacement = array( '', '', "DEFAULT CHARSET={$dbCharset}" );
            return $sqlVersion && $dbCharset ? preg_replace( $pattern, $replacement, $sql ) : $sql;
        }

        if ( $version ) {
            $pattern = array( '/TYPE=HEAP/i', '/TYPE=(\w+)/is' );
            $replacement = array(
                "ENGINE=MEMORY DEFAULT CHARSET={$dbCharset}",
                "ENGINE=\\1 DEFAULT CHARSET={$dbCharset}"
            );
            return preg_replace( $pattern, $replacement, $sql );
        } else {
            $pattern = array(
                '/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i',
                '/\s*DEFAULT CHARSET=\w+/is',
                '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'
            );
            $replacement = array( '', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2' );
            return preg_replace( $pattern, $replacement, $sql );
        }
    }

}
