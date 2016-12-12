<?php
/**
 * 企业工具类
 *
 * @namespace application\modules\contact\utils
 * @filename CorpUtil.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/11/8 18:01
 */

namespace application\modules\contact\utils;


use application\core\utils\Ibos;
use application\core\utils\System;

/**
 * Class CorpUtil
 *
 * @package application\modules\contact\utils
 */
class CorpUtil extends System
{
    /**
     * @param string $className
     * @return CorpUtil
     */
    public static function getInstance($className = __CLASS__)
    {
        static $instance = null;

        if (empty($instance)) {
            $instance = parent::getInstance($className);
        }

        return $instance;
    }
    
    /**
     * 返回当前企业详细数据
     *
     * @return array
     */
    public function fetchCorpDetail()
    {
        static $corpDetail = null;

        if (empty($corpDetail)) {
            $util = Ibos::app()->setting->get('setting/unit');

            $logoUrl = isset($util['logourl']) ? $util['logourl'] : '';
            if (empty($logoUrl)) {
                // 用户没有设置公司 logo，返回默认的 logo url
                $logoUrl = Ibos::app()->assetManager->getAssetsUrl('contact') . '/image/corp_avatar.png?' .VERHASH;
            }

            $corpDetail =  array(
                'corpname' => isset($util['shortname']) ? $util['shortname'] : '',
                'logourl' => $logoUrl,
                'systemurl' => isset($util['systemurl']) ? $util['systemurl'] : '',
                'phone' => isset($util['phone']) ? $util['phone'] : '',
                'fax' => isset($util['fax']) ? $util['fax'] : '',
                'address' => isset($util['address']) ? $util['address'] : '',
                'adminemail' => isset($util['adminemail']) ? $util['adminemail'] : '',
                'corpcode' => isset($util['corpcode']) ? $util['corpcode'] : '',
                'zipcode' => isset($util['zipcode']) ? $util['zipcode'] : '',
                'fullname' => isset($util['fullname']) ? $util['fullname'] : '',
                'bgbig' => DeptUtil::getInstance()->getBgImageUrl(),
            );
        }

        return $corpDetail;
    }

    /**
     * 返回公司简称
     *
     * @return string
     */
    public function fetchCorpShortName()
    {
        static $cropShortName = null;

        if (empty($corpShortName)) {
            $corpDetail = $this->fetchCorpDetail();
            $cropShortName = $corpDetail['corpname'];
        }

        return $cropShortName;
    }

}