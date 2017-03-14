<?php
use ibos\upgrade\core\QueryBuilder;
use Pixie\Connection;

/**
 * 从数据库中获取当前 IBOS 的版本信息，包括版本号+版本类型
 *
 * @return string Example: 4.0.0 pro
 */
function getVersion()
{
    $db = getPdo();
    $cfg = getConfig();
    $query = $db->prepare(" SELECT `svalue` FROM `{$cfg['tableprefix']}setting` WHERE `skey` = 'version'");
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    return !empty($row) ? $row['svalue'] : '';
}

/**
 *
 * @param string $version
 * @return type
 */
function updateVersion($version)
{
    $db = getPdo();
    $cfg = getConfig();
    $query = $db->prepare(" UPDATE `{$cfg['tableprefix']}setting` SET `svalue` = '{$version}' WHERE `skey` = 'version'");
    return $query->execute();
}

/**
 * Get mysql version.
 *
 * @return string
 */
function getMysqlVersion()
{
    $db = getPdo();
    $sql = "SELECT VERSION() AS version";
    $result = $db->query($sql)->fetch();
    return substr($result['version'], 0, 3);
}

/**
 *
 * @staticvar mixed $db
 * @return \PDO
 */
function getPdo()
{
    static $db = null;
    if (null === $db) {
        $config = getConfig();
        $host = $config['host'];
        $port = $config['port'];
        $dbname = $config['dbname'];
        $user = $config['username'];
        $password = $config['password'];
        $charset = $config['charset'];
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname}";
        $options = array();
        if (version_compare(PHP_VERSION, '5.3.6', '<')) {
            if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $charset;
            }
        } else {
            $dsn .= ';charset=' . $charset;
        }
        try {
            $db = new PDO($dsn, $user, $password, $options);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            ErrorLogger::log('pdo', $e->getMessage());
            return null;
        }
    }
    return $db;
}

/**
 * 获取数据库连接实例
 *
 * @return Connection
 */
function getDbConn()
{
    static $dbConnection = null;

    if (is_null($dbConnection)) {
        $ibosDbConfig = getConfig();
        $config = array(
            'driver' => 'mysql',
            'host' => $ibosDbConfig['host'],
            'database' => $ibosDbConfig['dbname'],
            'username' => $ibosDbConfig['username'],
            'password' => $ibosDbConfig['password'],
            'charset' => $ibosDbConfig['charset'],
            'collation' => 'utf8_unicode_ci',
            'prefix' => $ibosDbConfig['tableprefix'],
        );
        $dbConnection = new Connection('mysql', $config, 'QB');
    }

    return $dbConnection;
}

/**
 * 获取 QueryBuilder 实例
 * 
 * @return QueryBuilder
 */
function getQB()
{
    $queryBuilder = null;

    if (is_null($queryBuilder)) {
        $conn = getDbConn();
        $queryBuilder = new QueryBuilder($conn);
    }

    return $queryBuilder;
}


/**
 * 返回系统配置信息
 *
 * @return array
 */
function getConfig()
{
    global $config;
    return $config['db'];
}

