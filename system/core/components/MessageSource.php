<?php

/**
 * 全局消息类型处理文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 继承CPhpMessageSource的消息来源类。
 * @package application.core.components
 * @version $Id: messageSource.php -1   $
 * @author banyanCheung <banyan@ibos.com.cn>
 */

namespace application\core\components;

use CPhpMessageSource;

class MessageSource extends CPhpMessageSource
{

    /**
     * 为指定的语言和分类加载信息的翻译。
     * <code>
     *    $data['lang'] = Yii::app()->getMessages()->loadMessages( 'dashboard.frameworkMenu','zh_cn' );
     * </code>
     * @param string $category 指定目录
     * @param string $language 指定语言
     * @return array
     */
    public function loadMessages($category, $language)
    {
        return parent::loadMessages($category, $language);
    }

}
