<?php

namespace application\modules\weibo\controllers;

use application\core\controllers\Controller;
use application\core\utils\Ibos;

class BaseController extends Controller
{

    protected $_extraAttributes = array();

    /**
     * 默认的页面属性
     * @var array
     */
    private $_attributes = array('uid' => 0);

    /**
     * 设置相对应属性值
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        if (isset($this->_attributes[$name])) {
            $this->_attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * 获取对应属性值
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_attributes[$name])) {
            return $this->_attributes[$name];
        } else {
            parent::__get($name);
        }
    }

    /**
     * 检测$_attributes数组里的值是否存在
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this->_attributes[$name])) {
            return true;
        } else {
            parent::__isset($name);
        }
    }

    /**
     * 执行action前初始化方法，为一些通用参数赋值
     * @return void
     */
    public function init()
    {
        $this->_attributes = array_merge($this->_attributes, $this->_extraAttributes);
        $this->uid = intval(Ibos::app()->user->uid);
        parent::init();
    }

}
