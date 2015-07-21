<?php

/**
 * Office class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 文档控件显示与处理挂件
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.main.widgets
 * $Id$
 */

namespace application\modules\main\widgets;

use application\core\utils\Attach;
use application\core\utils\File;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\core\utils\Xml;
use CWidget;

class Office extends CWidget {

    // 渲染的视图alias
    const VIEW = 'application.modules.main.views.attach.office';
    // office文档组件path
    const OFFICE_PATH = 'system/modules/main/office/';
    // word 类型
    const DOC_WORD = 3;
    // excel 类型
    const DOC_EXCEL = 4;
    // ppt类型
    const DOC_PPT = 5;
    // 请求锁定刷新时间
    const LOCK_SEC = 180;

    /**
     * 待处理的参数
     * @var array 
     */
    private $_param = array();

    /**
     * 该文档指向的附件数组
     * @var array 
     */
    private $_attach = array();

    /**
     * run方法处理的数组
     * @var array 
     */
    private $_var = array();

    /**
     * 设置参数
     * @param array $param
     */
    public function setParam( $param ) {
        $this->_param = $this->formatParam( $param );
    }

    /**
     * 获取参数
     * @return array
     */
    public function getParam() {
        return $this->_param;
    }

    /**
     * 设置附件
     * @param type $attach
     */
    public function setAttach( $attach ) {
        $this->_attach = $attach;
    }

    /**
     * 获取附件
     * @return array
     */
    public function getAttach() {
        return $this->_attach;
    }

    /**
     * 
     */
    public function init() {
        $attach = $this->getAttach();
        $var = array(
            'assetUrl' => $this->getController()->getAssetUrl(),
            'lang' => IBOS::getLangSources(),
            'attach' => $attach,
            'param' => $this->getParam(),
            'isNew' => empty( $attach )
        );
        $var = array_merge( $this->getDocFile( $var ), $var );
        $this->_var = $var;
    }

    /**
     * 实例化widget
     */
    public function run() {
        $var = $this->_var;
        $licence = $this->getLicence();
        $correct = $this->chkLicence( $licence );
        if ( $correct ) {
            $var['licence'] = $licence['officelicence'];
            $var['officePath'] = self::OFFICE_PATH;
            $var['assetUrl'] = $this->getController()->getAssetUrl();
            $this->render( self::VIEW, $var );
        } else {
            $this->getController()->error( IBOS::lang( 'Illegal office license', 'main.default' ), '', array( 'autoJump' => 0 ) );
        }
    }

    /**
     * 
     */
    public function handleRequest() {
        $allowedOps = array( 'lock', 'save' );
        $op = filter_input( INPUT_GET, 'op', FILTER_SANITIZE_STRING );
    }

    /**
     * 
     * @param type $var
     * @return array
     */
    private function getDocFile( $var ) {
        if ( $var['isNew'] ) {
            $typeId = Attach::attachType( $var['param']['filetype'], 'id' );
            $map = array(
                self::DOC_WORD => array(
                    'fileName' => $var['lang']['New doc'] . '.doc',
                    'fileUrl' => self::OFFICE_PATH . 'new.doc',
                    'typeId' => self::DOC_WORD,
                ),
                self::DOC_EXCEL => array(
                    'fileName' => $var['lang']['New excel'] . '.xls',
                    'fileUrl' => self::OFFICE_PATH . 'new.doc',
                    'typeId' => self::DOC_WORD,
                ),
                self::DOC_PPT => array(
                    'fileName' => $var['lang']['New ppt'] . '.ppt',
                    'fileUrl' => self::OFFICE_PATH . 'new.ppt',
                    'typeId' => self::DOC_WORD,
                )
            );
            return $map[$typeId];
        } else {
            return array(
                'typeId' => Attach::attachType( String::getFileExt( $var['attach']['attachment'] ), 'id' ),
                'fileName' => $var['attach']['filename'],
                'fileUrl' => File::fileName( File::getAttachUrl() . '/' . $var['attach']['attachment'] )
            );
        }
    }

    /**
     * 格式化参数数组
     * @param array $param
     * @return array
     */
    private function formatParam( $param ) {
        $return = array();
        if ( isset( $param[0] ) ) {
            $return['aid'] = intval( $param[0] );
        }
        if ( isset( $param[1] ) ) {
            $return['tableid'] = intval( $param[1] );
        }
        if ( isset( $param[2] ) ) {
            $return['timestamp'] = intval( $param[2] );
        }
        if ( isset( $param[3] ) ) {
            $ext = unserialize( $param[3] );
            $return = array_merge( $return, $ext );
        }
        return $return;
    }

    /**
     * 
     * @return boolean
     */
    private function getLicence() {
        $file = self::OFFICE_PATH . 'licence.xml';
        if ( file_exists( $file ) ) {
            $content = file_get_contents( $file );
            if ( is_string( $content ) ) {
                $licence = Xml::xmlToArray( $content );
                return $licence;
            }
        } else {
            return false;
        }
    }

    /**
     * 检查授权文件的正确性
     * @param type $licence
     * @return boolean
     */
    private function chkLicence( $licence ) {
        if ( is_array( $licence ) ) {
            if ( isset( $licence['officelicence'] ) ) {
                $data = $licence['officelicence'];
                return !empty( $data['ProductCaption'] ) && !empty( $data['ProductKey'] );
            }
        }
        return false;
    }

}
