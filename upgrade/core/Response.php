<?php
/**
 * @namespace ibos\upgrade\core
 * @filename Response.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2017/1/4 19:14
 */

namespace ibos\upgrade\core;

/**
 * Class Response
 *
 * @package ibos\upgrade\core
 */
class Response extends Singleton
{
    /**
     * 默认Jsonp回调函数
     */
    const DEFAULT_JSONP_HANDLER = 'jsonpReturn';

    /**
     * Ajax方式返回数据到客户端
     *
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return boolean
     */
    public function ajaxReturn($data, $type = '')
    {
        if (empty($type)) {
            $type = 'json';
        }

        switch (strtoupper($type)) {
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=' . CHARSET);
                exit(json_encode($data));
                break;
            case 'XML' :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=' . CHARSET);
                exit($this->xml_encode($data));
                break;
            case 'JSONP':
                // 返回JSONP数据格式到客户端 包含状态信息
                header('Content-Type:text/html; charset=' . CHARSET);
                $handler = isset($_GET['callback']) ? $_GET['callback'] : self::DEFAULT_JSONP_HANDLER;
                exit($handler . '(' . (!empty($data) ? json_encode($data) : '') . ');');
                break;
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=' . CHARSET);
                exit($data);
                break;
            default :
                exit($data);
                break;
        }
    }

    /**
     * 定义 ajax 返回格式
     * Example: ['isSuccess' => true, 'data' => array(), 'msg' => 'Call Success']
     *
     * @param boolean $isSuccess 请求是否成功
     * @param array $data
     * @param string $msg
     * @param array $extraArgs
     * @param string $type
     * @return void|bool
     */
    public function ajaxBaseReturn($isSuccess, array $data, $msg = '', array $extraArgs = array(), $type = '')
    {
        if (empty($msg)) {
            if ($isSuccess === true) {
                $msg = t('Call Success');
            } else {
                $msg = t('Call Failed');
            }
        }

        if (empty($type)) {
            $type = 'json';
        }

        $retData = array(
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        );

        if (!empty($extraArgs)) {
            foreach ($extraArgs as $k => $v) {
                $retData[$k] = $v;
            }
        }

        return $this->ajaxReturn($retData, $type);
    }

    /**
     * XML编码
     *
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    protected function xml_encode($data, $root='think', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml   .= "<{$root}{$attr}>";
        $xml   .= $this->data_to_xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * 数据XML编码
     *
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     * @return string
     */
    protected function data_to_xml($data, $item='item', $id='id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if(is_numeric($key)){
                $id && $attr = " {$id}=\"{$key}\"";
                $key  = $item;
            }
            $xml    .=  "<{$key}{$attr}>";
            $xml    .=  (is_array($val) || is_object($val)) ? $this->data_to_xml($val, $item, $id) : $val;
            $xml    .=  "</{$key}>";
        }
        return $xml;
    }
}