<?php

/**
 * 验证项目更新缓存类文件
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 验证项目更新缓存类,处理权限项目存入系统缓存表
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: AuthItemCacheProvider.php 939 2013-08-05 03:35:53Z zhangrong $
 * @package ext.cacheProvider
 */

namespace application\core\cache\provider;

use application\modules\dashboard\model\Syscache;
use application\modules\role\model\Node;
use CBehavior;

class AuthItem extends CBehavior
{

    public function attach($owner)
    {
        $owner->attachEventHandler('onUpdateCache', array($this, 'handleAuthItem'));
    }

    /**
     * 处理验证数据缓存
     * 用于视图显示权限认证项
     * @param object $event
     * @return void
     */
    public function handleAuthItem($event)
    {
        $categorys = array();
        // 获取所有根节点，node字段为空表明这是普通节点，不为空表示是数据类型的子节点
        $nodes = Node::model()->fetchAllEmptyNode();
        foreach ($nodes as $node) {
            if (empty($node['category'])) {
                continue;
            }
            // category 一般是中文字符，所以编码一下，以防出现问题
            $category = base64_encode($node['category']);
            $categorys[$category]['category'] = $node['category'];
            if ($node['type'] == 'data' && empty($node['node'])) {
                $node['node'] = Node::model()->fetchAllNotEmptyNodeByModuleKey($node['module'], $node['key']);
            }
            if (!empty($node['group'])) {
                // 同上
                $group = base64_encode($node['group']);
                $categorys[$category]['group'][$group]['groupName'] = $node['group'];
                $categorys[$category]['group'][$group]['node'][] = $node;
            } else {
                $categorys[$category]['node'][] = $node;
            }
        }
        Syscache::model()->modifyCache('authitem', $categorys);
    }

}
