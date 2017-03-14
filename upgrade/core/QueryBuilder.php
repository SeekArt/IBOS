<?php
/**
 * @namespace ibos\upgrade\core
 * @filename QueryBuilder.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2017/1/5 9:39
 */

namespace ibos\upgrade\core;


use Pixie\QueryBuilder\QueryBuilderHandler;

/**
 * Class QueryBuilder
 *
 * @package ibos\upgrade\core
 */
class QueryBuilder extends QueryBuilderHandler
{
    /**
     * @var string
     */
    protected $dbName;

    /**
     * @var string 表名（带表前缀）
     */
    protected $tableNameWithPrefix;

    public function __construct($connection)
    {
        parent::__construct($connection);

        $ibosDbConfig = getConfig();

        $this->dbName = $ibosDbConfig['dbname'];
    }

    /**
     * @param $tables
     *
     * @return static
     */
    public function table($tables)
    {
        $instance = new static($this->connection);
        $tables = $this->addTablePrefix($tables, false);
        $instance->tableNameWithPrefix = $tables;
        $instance->addStatement('tables', $tables);
        return $instance;
    }


    /**
     * 检查 $tableName 表是否存在 $columnName 列
     *
     * <code>
     * $this->table('article')->isColumnExists('content');
     * </code>
     *
     * @param string $columnName
     * @return bool
     */
    protected function isColumnExists($columnName)
    {
        $sql = <<<EOF
SELECT
    `COLUMN_NAME`
FROM
    INFORMATION_SCHEMA. COLUMNS
WHERE
    `TABLE_SCHEMA` = :dbName
AND `TABLE_NAME` = :tableName
AND `COLUMN_NAME` = :columnName
EOF;

        $row = $this->query($sql, array(
            ':dbName' => $this->dbName,
            ':tableName' => $this->tableNameWithPrefix,
            ':columnName' => $columnName,
        ))->first();

        return empty($row) ? false : true;
    }


    /**
     * 在某张表上添加一列
     *
     * <code>
     * $this->table('article')->addColumn('content', 'INT(11) NOT NULL');
     * </code>
     *
     * @param string $columnName
     * @param string $columnType
     * @return $this
     */
    public function addColumn($columnName, $columnType)
    {
        $sql = <<<EOF
ALTER TABLE `$this->tableNameWithPrefix`
ADD COLUMN `$columnName` $columnType
EOF;

        return $this->query($sql);
    }

    /**
     * 在某张表上不存在 $columnName 列，则添加
     *
     * <code>
     * $this->table('article')->addColumnIfNotExists('content', 'TEXT')
     * </code>
     *
     * @param string $columnName
     * @param string $columnType
     * @return bool true 添加成功，false 添加失败或列已存在
     */
    public function addColumnIfNotExists($columnName, $columnType)
    {
        if ($this->isColumnExists($columnName) === true) {
            return false;
        }

        return (boolean)$this->addColumn($columnName, $columnType);
    }

    /**
     * 在某张表上添加多列，如果列已存在，则跳过
     *
     * <code>
     * $this->table('article')->addColumnsIfNotExists(array('content' => 'TEXT'));
     * </code>
     *
     * @param array $columns
     * @return bool
     */
    public function addColumnsIfNotExists(array $columns)
    {
        $successFlag = true;

        foreach ($columns as $column) {
            if (isset($column['columnName']) && isset($column['columnType'])) {
                $successFlag = $successFlag && $this->addColumnIfNotExists($column['columnName'],
                        $column['columnType']);
            }
        }

        return $successFlag;
    }

    /**
     * 设置表引擎类型
     *
     * <code>
     * $this->table('article')->setTableEngine('InnoDb');
     * </code>
     * @param string $engineName
     * @return \CDbDataReader
     */
    public function setTableEngine($engineName)
    {
        $tableName = $this->tableNameWithPrefix;
        $sql = "ALTER TABLE `$tableName` ENGINE = '$engineName'";

        return $this->query($sql);
    }

}