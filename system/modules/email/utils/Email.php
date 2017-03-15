<?php

/**
 * 邮件中心模块静态函数库类文件。
 *
 * @author Ring <Ring@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 * 邮件中心模块函数库类，提供全局静态方法调用
 * @package application.modules.email.utils
 * @version $Id$
 * @author Ring <Ring@ibos.com.cn>
 */

namespace application\modules\email\utils;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\File;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;
use application\modules\email\model\Email as EmailModel;
use application\modules\user\model\User;
use application\core\utils\Env;
use CHtml;

class Email
{

    /**
     * 返回用户的邮箱大小
     * @param integer $uid 用户ID
     * @return integer 单位为M
     */
    public static function getUserSize($uid)
    {
        $user = User::model()->fetchByUid($uid);
        $userSize = Ibos::app()->setting->get('setting/emaildefsize');
        if (!empty($user['allposid'])) {
            $role = Ibos::app()->setting->get('setting/emailroleallocation');
            if (!empty($role)) {
                $sizes = array();
                foreach (explode(',', $user['allposid']) as $posId) {
                    if (isset($role[$posId])) {
                        $sizes[] = $role[$posId];
                    }
                }
                if (!empty($sizes)) {
                    rsort($sizes, SORT_NUMERIC);
                    isset($sizes[0]) && $userSize = $sizes[0];
                }
            }
        }
        return (int)$userSize;
    }

    /**
     * 组合搜索条件,返回查询必须之条件
     * @param array $search 提交的查询条件数据
     * @param integer $uid 查询用户ID
     * @return array
     */
    public static function mergeSearchCondition($search, $uid)
    {
        if (Env::getRequest('type') === 'normal_search') {
            switch (Env::getRequest('op')) {
                case 'inbox':
                    $condition = "e.toid = {$uid} AND eb.subject LIKE '%{$search['keyword']}%'";
                    break;
                case 'todo':
                    $condition = "e.toid = {$uid} AND eb.subject LIKE '%{$search['keyword']}%' AND e.ismark = 1";
                    break;
                case 'draft':
                    $condition = "eb.fromid = {$uid} AND eb.subject LIKE '%{$search['keyword']}%' AND eb.issend = 0";
                    break;
                case 'send':
                    $condition = "eb.fromid = {$uid} AND eb.subject LIKE '%{$search['keyword']}%' AND eb.issend = 1";
                    break;
                case 'del':
                    $condition = "e.toid = {$uid} AND eb.subject LIKE '%{$search['keyword']}%' AND e.isdel = 1";
                    break;
                default:
                    $condition = "( e.toid = 0 )";
                    break;
            }
            return array('condition' => $condition, 'archiveId' => 0);
        }
        // 上面是新增专门针对普通搜索的查询条件处理
        // 下面是原来的搜索处理代码
        $condition = "(e.toid = {$uid})";
        // 关键字
        //添加对keyword的转义，防止SQL错误
        $keyword = CHtml::encode(stripcslashes($search['keyword']));
        // 查询位置
        $pos = isset($search['pos']) ? $search['pos'] : 'all';
        // 文件夹
        $folder = isset($search['folder']) ? $search['folder'] : 0;
        $setAttach = isset($search['attachment']) && $search['attachment'] !== '-1';
        if ($folder == 'allbynoarchive') {//全部邮件（不含归档）
            $queryArchiveId = 0;
            $folder = 0;
        } elseif ($folder == 'all') {//全部邮件（包含归档）
            $ids = Ibos::app()->setting->get('setting/emailtableids');
            $queryArchiveId = $ids;
            $folder = 0;
        } elseif (strpos($folder, 'archive_') !== false) { //某一个归档
            $queryArchiveId = intval(preg_replace('/^archive_(\d+)/', '\1', $folder));
            //重置文件夹
            $folder = 0;
        } else { //某一个文件夹（不含归档）
            $queryArchiveId = 0;
            $folder = intval($folder);
        }
        if (!empty($keyword)) {
            //搜索的时候也应该转义然后搜索，不然找不到
            // StringUtil::ihtmlSpecialCharsUseReference( $keyword );
            // 搜索位置条件
            $allPos = ($pos == 'all');
            $posWhereJoin = $allPos ? ' OR ' : ' AND ';
            $posWhere = '';
            if ($pos == 'content' || !empty($pos)) {
                if ($pos == 'subject' || $allPos) {  //标题
                    $posWhere .= $posWhereJoin . "eb.subject LIKE '%{$keyword}%'";
                }
                if ($pos == 'content' || $allPos) {  //邮件正文
                    $posWhere .= $posWhereJoin . "eb.content LIKE '%{$keyword}%'";
                }
                if ($pos == 'attachment' || $allPos) {  //附件名
                    $containAttach = isset($search['attachment']) && $search['attachment'] !== '0'; // 是否设置包含附件
                    $kwBodyIds = EmailModel::model()->fetchAllBodyIdByKeywordFromAttach($keyword, $condition, $queryArchiveId);
                    if (!$allPos && (!$containAttach || count($kwBodyIds) == 0)) { // 高级模式下，不包含附件或者没找到数据就直接返回
                        return array('condition' => "1=0", 'archiveId' => $queryArchiveId); // 找不到直接返回
                    } else {
                        $posWhere .= $posWhereJoin . 'FIND_IN_SET(eb.bodyid,\'' . implode(',', $kwBodyIds) . '\')';
                    }
                } //end 搜索附件名
                if ($allPos) {
                    $condition .= ' AND (' . preg_replace('/^' . $posWhereJoin . '/', '', $posWhere) . ')';
                } else {
                    $condition .= $posWhere;
                }
            }
            // 文件夹条件
            if ($folder) {
                if ($folder == 1) {
                    $condition .= " AND (e.fid = 1 AND e.isdel = 0)";
                } elseif ($folder == 3) {
                    $condition .= " AND (eb.issend = 1 AND eb.issenderdel != 1 AND e.isweb=0)";
                } else {
                    $condition .= " AND (e.fid = {$folder} AND e.isdel = 0)";
                }
            }
            // 时间范围条件
            if (isset($search['dateRange']) && $search['dateRange'] !== '-1') {
                $dateRange = intval($search['dateRange']);
                $endTime = TIMESTAMP;
                $startTime = strtotime("- {$dateRange}day", $endTime);
                $condition .= " AND (eb.sendtime BETWEEN {$startTime} AND {$endTime})";
            }
            // 邮件读取状态
            if (isset($search['readStatus']) && $search['readStatus'] !== '-1') {
                $readStatus = intval($search['readStatus']);
                $condition .= " AND e.isread = {$readStatus}";
            }
            // 设置附件
            if ($setAttach) {
                if ($search['attachment'] == '0') {
                    $condition .= " AND eb.attachmentid = ''";
                } else if ($search['attachment'] == '1') {
                    $condition .= " AND eb.attachmentid != ''";
                }
            }
            // 发件人与收件人
            if (isset($search['sender']) && !empty($search['sender'])) {
                $sender = StringUtil::getUid($search['sender']);
                $condition .= " AND eb.fromid = " . implode(',', $sender);
            }
            if (isset($search['recipient']) && !empty($search['recipient'])) {
                $recipient = StringUtil::getUid($search['recipient']);
                $condition .= " AND e.toid = " . implode(',', $recipient);
            }
        }
        return array('condition' => $condition, 'archiveId' => $queryArchiveId);
    }

    /**
     * 取出源数据中$field的值，用$join分割合并成字符串
     * @param string $str 逗号分割的字符串
     * @param array $data 源数据
     * @param type $field 要取出的字段
     */
    public static function joinStringByArray($str, $data, $field, $join)
    {
        if (empty($str)) {
            return '';
        }
        $result = array();
        $strArr = explode(',', $str);

        foreach ($strArr as $value) {
            if (array_key_exists($value, $data)) {
                $result[] = $data[$value][$field];
            }
        }
        $resultStr = implode($join, $result);
        return $resultStr;
    }

    /**
     * 导出邮件为eml格式
     * @param integer $id 邮件ID
     */
    public static function exportEml($id)
    {
        $data = EmailModel::model()->fetchById($id);
        if ($data) {
            $select = 'uid,realname';
            User::model()->setSelect($select);
            $users = User::model()->findUserIndexByUid();
            $data['copytoname'] = self::joinStringByArray($data['copytoids'], $users, 'realname', ';');
            $filecontent = "Date: " . Convert::formatDate($data['sendtime']) . "\n";

            $data['fromname'] = isset($users[$data['fromid']]) ? $users[$data['fromid']]['realname'] : '';
            $filecontent .= "From: \"" . $data['fromname'] . "\"\n";
            $filecontent .= "MIME-Version: 1.0\n";

            $data['toname'] = self::joinStringByArray($data['toids'], $users, 'realname', ';');
            $filecontent .= "To: \"" . $data['toname'] . "\"\n";
            if ($data['copytoids'] != "") {
                $filecontent .= "Cc: \"" . $data['copytoname'] . "\" <" . $data['copytoids'] . ">\n";
            }
            $filecontent .= "subject: " . $data['subject'] . "\n";
            $filecontent .= "Content-Type: multipart/mixed; boundary=\"==========myOA==========\"\n\n";

            $filecontent .= "This is a multi-part message in MIME format.\n";
            $filecontent .= "--==========myOA==========\n";
            $filecontent .= "Content-Type: text/html;	charset=\"utf-8\"\n";
            $filecontent .= "Content-Transfer-Encoding: base64\n\n";
            $filecontent .= chunk_split(base64_encode($data['content'])) . "\n";

            if ($data['attachmentid'] !== '') {
                $tempdata = Attach::getAttach($data['attachmentid'], true, true, false, false, true);
                foreach ($tempdata as $value) {
                    $filecontent .= "--==========myOA==========\n";
                    $filecontent .= "Content-Type: application/octet-stream; name=\"" . $value['filename'] . "\"\n";
                    $filecontent .= "Content-Transfer-Encoding: base64\n";
                    $filecontent .= "Content-Disposition: attachment; filename=\"" . $value['filename'] . "\"\n\n";
                    $filecontent .= chunk_split(base64_encode(File::readFile($value['attachment']))) . "\n";
                }
            }
            $filecontent .= "--==========myOA==========--";
            if (ob_get_length()) {
                ob_end_clean();
            }
            header("Cache-control: private");
            header("Content-type: message/rfc822");
            header("Accept-Ranges: bytes");
            header("Content-Disposition: attachment; filename=" . Convert::iIconv(StringUtil::filterCleanHtml($data['subject']), CHARSET, 'GBK') . "(" . date("Y-m-d") . ").eml");
            echo $filecontent;
        }
    }

    /**
     * 导出邮件为excel格式
     * @param integer $id 邮件ID
     */
    public static function exportExcel($id)
    {
        $data = EmailModel::model()->fetchById($id);
        if ($data) {
            $select = 'uid,realname';
            User::model()->setSelect($select);
            $users = User::model()->findUserIndexByUid();
            header("Cache-control: private");
            header("Content-type: application/vnd.ms-excel");
            //下面的这个标题使用了标签过滤，而不是直接输出，因为我还不知道怎么样才可以直接输出
            //如果标题带有js代码，那么会有问题，是不是头部不能写特殊字符呢？
            header("Content-Disposition: attachment; filename=" . Convert::iIconv(StringUtil::filterCleanHtml($data['subject']), CHARSET, 'GBK') . "(" . date("Y-m-d") . ").xls");
            $html = <<<EOT
            <html xmlns:o="urn:schemas-microsoft-com:office:office"
		xmlns:x="urn:schemas-microsoft-com:office:excel"
		xmlns="http://www.w3.org/TR/REC-html40">
		<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		</head>
		<body topmargin="5">
		 <table border="1" cellspacing="1" width="95%" class="small" cellpadding="3">
			<tr style="BACKGROUND: #D3E5FA; color: #000000; font-weight: bold;">
			  <td align="center">收件人：</td>
			  <td align="center">发件人：</td>
			  <td align="center">抄送：</td>
			  <td align="center">重要性：</td>
			  <td align="center">标题：</td>
			  <td align="center">发送时间：</td>
			  <td align="center">内容：</td>
			  <td align="center">附件名称：</td>
			</tr>
EOT;
            $data['toname'] = self::joinStringByArray($data['toids'], $users, 'realname', ';');
            $data['content'] = str_replace("  ", "&nbsp;&nbsp;", $data['content']);
            $data['content'] = str_replace("\n", "<br>", $data['content']);
            $data['fromname'] = isset($users[$data['fromid']]) ? $users[$data['fromid']]['realname'] : '';
            $data['copytoname'] = self::joinStringByArray($data['copytoids'], $users, 'realname', ';');
            $important_desc = '';
            if ($data['important'] == '0') {
                $important_desc = "";
            } else if ($data['important'] == '1') {
                $important_desc = "<font color=\"#ff6600\">一般邮件</font>";
            } else if ($data['important'] == '2') {
                $important_desc = "<font color=\"#FF0000\">重要邮件</font>";
            }
            $attachmentname = '';
            if ($data['attachmentid'] !== '') {
                $tempdata = Attach::getAttach($data['attachmentid']);
                foreach ($tempdata as $value) {
                    $attachmentname .= $value['filename'] . '; ';
                }
            }
            $data['sendtime'] = Convert::formatDate($data['sendtime']);
            $html .= '
                <tr>
                    <td nowrap align="center">' . $data['toname'] . '</td>
                    <td nowrap align="center">' . $data['fromname'] . '</td>
                    <td>' . $data['copytoname'] . '</td>
                    <td nowrap align="center">' . $important_desc . '</td>
                    <td nowrap>' . $data['subject'] . '</td>
                    <td nowrap align="center" x:str="' . $data['sendtime'] . '">' . $data['sendtime'] . '</td>
                    <td>' . $data['content'] . '</td>
                    <td>' . $attachmentname . '</td>
                </tr>
            </table>';
            echo $html;
        }
    }

    /**
     * 取得邮件大小
     * @param string $content 内容
     * @param string $attachmentId 附件ID
     * @return integer
     */
    public static function getEmailSize($content, $attachmentId = '')
    {
        $tmpfile = PATH_ROOT . '/data/emailsize.temp';
        // 统计单封邮件的大小
        File::createFile($tmpfile, $content);
        $emailContentSize = File::fileSize($tmpfile);
        File::deleteFile($tmpfile);
        // 附件大小
        $attFileSize = 0;
        if (!empty($attachmentId)) {
            $attach = Attach::getAttachData($attachmentId, false);
            foreach ($attach as $value) {
                $attFileSize += intval($value['filesize']);
            }
        }
        return intval($emailContentSize + $attFileSize);
    }

}
