<?php
/**
 * 用户输入验证器
 *
 * @namespace application\modules\vote\utils
 * @filename RequestValidator.php
 * @encoding UTF-8
 * @author zqhong <i@zqhong.com>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2016 IBOS Inc
 * @datetime 2016/10/27 11:39
 */

namespace application\modules\vote\utils;


use application\core\utils\System;

class RequestValidator extends System
{
    /**
     * @param string $className
     * @return RequestValidator
     */
    public static function getInstance($className = __CLASS__)
    {
        return parent::getInstance($className);
    }

    /**
     * 验证投票模块专有字段：subject、publishscope、content
     *
     * @param $postData
     * @return bool
     * @throws \Exception
     */
    public function initAddVoteForVoteModule($postData)
    {
        $rules = array(
            'required' => array(
                array('subject'),
                array('content'),
            ),
            'lengthMax' => array(
                array('subject', 255),
                array('content', 65535),
            ),
        );

        return Validator::validate($postData, $rules);
    }

    /**
     * 检查 addVote 接口的请求数据是否准确
     *
     * @parama array $postData
     * @param $postData
     * @return bool
     * @throws \Exception
     */
    public function initAddVote($postData)
    {
        // 第一次验证
        $rules = array(
            'array' => array(
                array('topics'),
            ),
            'required' => array(
                array('endtime'),
                array('isvisible'),
                array('topics.*.topic_type'),
                array('topics.*.subject'),
                array('topics.*.maxselectnum'),
                array('publishscope'),
            ),
            'regex' => array(
                // 格式：2007/01/02 10:38
                array('endtime', '@^\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}$@'),
                // 取值范围：0 或 1
                array('isvisible', '@^[01]$@'),
                // 取值范围：1 或 2
                array('topics.*.topic_type', '@^[12]$@'),
                // 取值范围：1 或 2 或 3
                array('topics.*.maxselectnum', '@^[123]$@'),
            ),
            'lengthMax' => array(
                array('topics.*.subject', 255),
            ),
        );
        Validator::validate($postData, $rules);

        // 第二次验证
        $validateData = array();
        foreach ($postData['topics'] as $topic) {
            if (!is_array($topic)) {
                continue;
            }
            foreach ($topic as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $validateData[] = $item;
            }
        }
        $rules = array(
            'isset' => array(
                array('*.content'),
            ),
            'lengthMax' => array(
                array('*.content', 20),
            ),
        );

        return Validator::validate($validateData, $rules);
    }

    /**
     * 检查 updateVote 接口的请求数据是否准确
     *
     * @param array $postData
     * @return bool
     * @throws \Exception
     */
    public function initUpdateVote($postData)
    {
        $this->initAddVote($postData);

        $rules = array(
            'required' => 'voteid',
            'integer' => 'voteid',
        );

        return Validator::validate($postData, $rules);
    }

    /**
     * 检查 delVote 接口的请求数据是否正确
     *
     * @param array $postData
     * @return bool
     * @throws \Exception
     */
    public function initDelVotes($postData)
    {
        $rules = array(
            'required' => 'voteid',
            'array' => 'voteid',
            'numeric' => 'voteid.*',
        );

        return Validator::validate($postData, $rules);
    }


    /**
     * 检查 updateEndTime 接口的请求数据是否正确
     *
     * @param array $postData
     * @return bool
     * @throws \Exception
     */
    public function initUpdateEndTime(array $postData)
    {
        $rules = array(
            'required' => array(
                array('voteid'),
                array('endtime'),
            ),
            'integer' => array(
                array('voteid'),
            ),
        );

        return Validator::validate($postData, $rules);
    }

    /**
     * 检查 vote 接口的请求数据是否正确
     *
     * @param array $postData
     * @return bool
     * @throws \Exception
     */
    public function initVote(array $postData)
    {
        $rules = array(
            'required' => array(
                array('voteid'),
                array('topics'),
                array('topics.*.topicid'),
                array('topics.*.itemids'),
            ),
            'integer' => array(
                array('voteid'),
                array('topics.*.topicid'),
                array('topics.*.itemids.*'),
            ),
            'array' => array(
                array('topics'),
                array('topics.*.itemids'),
            ),
        );

        return Validator::validate($postData, $rules);
    }
}