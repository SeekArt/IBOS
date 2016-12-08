<?php

/**
 *
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2012-2013 IBOS Inc
 */
/**
 *
 *
 * @package application.core.utils
 * @author banyanCheung <banyan@ibos.com.cn>
 * @version $Id: attach.php -1   $
 */

namespace application\core\utils;

use application\extensions\Zip;
use application\modules\main\model\Attachment;
use application\modules\main\model\AttachmentN;
use application\modules\main\model\AttachmentUnused;

class Attach
{

    const ICON_PATH = 'static/image/filetype/'; // 附件图片地址

    private static $_imgext = array('jpg', 'jpeg', 'gif', 'png');

    /**
     * 附件类型图标
     * @var array
     */
    private static $attachIcons = array(
        0 => 'unknown',
        1 => 'unknown',
        2 => 'psd',
        3 => 'word',
        4 => 'excel',
        5 => 'ppt',
        6 => 'pdf',
        7 => 'txt',
        8 => 'photo',
        9 => 'code',
        10 => 'swf',
        11 => 'html',
        12 => 'exe',
        13 => 'rar',
        14 => 'zip',
        15 => '7z',
        16 => 'music',
        17 => 'video',
        18 => 'fla'
    );

    /**
     *
     * @var type
     */
    private static $dangerTags = array(
        'php',
        'php4',
        'asp',
        'aspx',
        'jsp',
        'exe',
    );

    public static function getCommonImgExt()
    {
        return self::$_imgext;
    }

    /**
     * 获取上传配置数组
     * @param integer $uid 指定用户ID
     * @return array
     */
    public static function getUploadConfig($uid = 0)
    {
        $currentUid = Ibos::app()->user->isGuest ? 0 : Ibos::app()->user->uid;
        $config = array();
        $imageexts = self::getCommonImgExt();
        $config['limit'] = 0;
        $uid = !empty($uid) ? intval($uid) : $currentUid;
        $authKey = Ibos::app()->setting->get('config/security/authkey');
        // 身份验证
        $config['hash'] = md5(substr(md5($authKey), 8) . $uid);
        // 上传限制
        $config['max'] = 0;
        $max = Ibos::app()->setting->get('setting/attachsize');
        if ($max) {
            $max = $max * 1024 * 1024;
        }
        $config['max'] = $max / 1024;
        $config['imageexts'] = array('ext' => '', 'depict' => 'Image File');
        $config['imageexts']['ext'] = !empty($imageexts) ? '*.' . implode(';*.', $imageexts) : '';
        // 文件格式
        $config['attachexts'] = array('ext' => '*.*', 'depict' => 'All Support Formats');
        $extensions = Ibos::app()->setting->get('setting/filetype');
        if (!empty($extensions)) {
            $extension = str_replace(' ', '', $extensions);
            $exts = explode(',', $extension);
            foreach ($exts as $index => $ext) {
                if (in_array(strtolower($ext), self::$dangerTags)) {
                    unset($exts[$index]);
                }
            }
            $config['attachexts']['ext'] = '*.' . implode(';*.', $exts);
        }
        return $config;
    }

    /**
     * 根据当前的关联id自动分配附件所属表id,只能为attachment0-9
     * @param integer $relatedId 关联内容id
     * @return integer 生成的附件所属表id
     */
    public static function getTableId($relatedId)
    {
        $id = (string)$relatedId;
        $tableId = StringUtil::iIntval($id{strlen($id) - 1});
        return $tableId;
    }

    /**
     * 全局更新附件函数,主要是把未使用的附件表的数据更新到分配好的附件表里去
     * @param mixed $aid 单个附件ID字符串或数组
     * @param integer $relateId 关联的内容ID
     * @return boolean
     * @since IBOS1.0
     */
    public static function updateAttach($aid, $relateId = 0)
    {
        $aid = is_array($aid) ? $aid : explode(',', $aid);
        $relateId = $relateId > 0 ? $relateId : mt_rand(0, 9);
        $uid = Ibos::app()->user->uid;
        $records = Attachment::model()->findAllByPk($aid);
        $count = 0;
        $tableArray = array();
        foreach ($records as $record) {
            $id = $record['aid'];
            //如果不是未使用的附件，跳过
            if (strcasecmp($record['uid'], $uid) !== 0 || strcasecmp($record['tableid'], 127) !== 0) {
                $tableArray[$id]['tableid'] = $record['tableid'];
                continue;
            } else {
                $unused = AttachmentUnused::model()->fetchByPk($id);
                $tableId = self::getTableId($relateId);
                Attachment::model()->modify($id, array('tableid' => $tableId));
                AttachmentN::model()->add($tableId, $unused);
                AttachmentUnused::model()->deleteByPk($id);
                $count++;
                $tableArray[$id]['tableid'] = $tableId;
            }
        }
        return $tableArray;
    }

    /**
     * 删除指定的附件
     * @param mixed $aid 附件id
     * @return int $count 删除的个数
     * @author wwb
     */
    public static function delAttach($aid)
    {
        $count = 0;
        $aid = is_array($aid) ? implode(',', $aid) : trim($aid, ',');
        $attachUrl = File::getAttachUrl() . '/';
        $records = Attachment::model()->fetchAll(array(
            'select' => array('aid', 'tableid'),
            'condition' => "FIND_IN_SET(aid,'$aid')"
        ));
        foreach ($records as $value) {
            $record = AttachmentN::model()->fetch($value['tableid'], $value['aid']);
            if (!empty($record)) {
                if (File::fileExists($attachUrl . $record['attachment'])) {
                    File::deleteFile($attachUrl . $record['attachment']);
                    $count++;
                }
                AttachmentN::model()->deleteByPk($value['tableid'], $value['aid']);
            }
        }
        if ($count) {
            Attachment::model()->deleteAll("FIND_IN_SET(aid,'{$aid}')");
        }
        return $count;
    }

    /**
     * 输出下载字符串
     * @param integer $aid 附件id
     * @param integer $tableId 附件所属表id
     * @param array $param 额外参数，这些是可以提交到下载处理页处理的数据
     * @return string
     * @since IBOS1.0
     */
    public static function getAttachStr($aid, $tableId = '', $param = array())
    {
        if (!is_numeric($tableId)) {
            $tableId = Ibos::app()->db->createCommand()->select('tableid')->from('{{attachment}}')->where("aid = {$aid}")->queryScalar();
        }
        //下载字符串分割样式： 附件id|所属表id|当前时间戳|$param,采用加密方式：authcode
        //密钥为当前用户的salt + base64_encode + url硬转码，这样加密后就只能保证只有当前的用户能下载
        $str = $aid . '|' . $tableId . '|' . TIMESTAMP;
        if (!empty($param)) {
            $str .= '|' . serialize($param);
        }
        $encode = rawurlencode(base64_encode(StringUtil::authCode($str, 'ENCODE', Ibos::app()->user->salt)));
        return $encode;
    }

    /**
     * 获取已转存的附件数据
     * @param mixed $aid 字符串或数组
     * @param boolean $filterUnused 是否过滤未使用附件
     * @return array
     */
    public static function getAttachData($aid, $filterUnused = true)
    {
        $attach = array();
        $aid = is_array($aid) ? $aid : explode(',', trim($aid, ','));
        $records = Attachment::model()->fetchAllByPk($aid, $filterUnused ? 'tableid != 127' : '');
        foreach ($records as $record) {
            if (!empty($record)) {
                $data = AttachmentN::model()->fetch($record['tableid'], $record['aid']);
                $data['tableid'] = $record['tableid'];
                $attach[$record['aid']] = $data;
            }
        }
        return $attach;
    }

    /**
     * 获取处理过后的附件信息，一般用于展示
     * @param mixed $aid 附件ID,字符串或数组
     * @param boolean $down 是否允许下载
     * @param boolean $officeDown 是否允许文档附件下载
     * @param boolean $edit 若文档允许下载，是否允许编辑
     * @param boolean $delete 是否允许删除
     * @param boolean $getRealAddress 是否返回真实附件地址
     * @return array
     * @since IBOS1.0
     */
    public static function getAttach($aid, $down = true, $officeDown = true, $edit = true, $delete = false, $getRealAddress = false)
    {
        $data = array();
        if (!empty($aid)) {
            $data = self::getAttachData($aid);
        }
        $urlManager = Ibos::app()->urlManager;
        foreach ($data as $id => &$val) {
            $val['date'] = Convert::formatDate($val['dateline'], 'u');
            $val['filetype'] = StringUtil::getFileExt($val['filename']);
            if ($val['filetype'] == "rar") {
                $val['read'] = false;
            }
            $val['origsize'] = $val['filesize'];
            $val['filesize'] = Convert::sizeCount($val['filesize']);
//			if ( $getRealAddress ) {
//				$val['attachment'] = File::getAttachUrl() . '/' . $val['attachment'];
//			}
            $val['filename'] = trim($val['filename']);
            $val['delete'] = $delete;
            $val['down'] = $down;
            $val['down_office'] = $officeDown;
            $val['edit'] = $edit;
            $val['iconsmall'] = self::attachType($val['filetype'], 'smallicon');
            $val['iconbig'] = self::attachType($val['filetype'], 'bigicon');

            $idString = self::getAttachStr($id, $val['tableid']);
            $val['openurl'] = $urlManager->createUrl('main/attach/open', array('id' => $idString));
            if ($val['down']) {
                $val['downurl'] = $urlManager->createUrl('main/attach/download', array('id' => $idString));
            }
            //if ( in_array( self::attachType( $val['filetype'], 'id' ), range( 7, 8 ) ) ) {
            $val['officereadurl'] = File::fileName(File::getAttachUrl() . '/' . $val['attachment']);
            //}

            $readOfficeRange = in_array(self::attachType($val['filetype'], 'id'), range(3, 5));
            if ($readOfficeRange && $val['down_office']) {
                // $val['officereadurl'] = $urlManager->createUrl( 'main/attach/office', array( 'id' => self::getAttachStr( $aid, $val['tableid'], array( 'filetype' => $val['filetype'], 'op' => 'read' ) ) ) );
                // $val['officereadurl'] = "http://o.ibos.cn/op/view.aspx?src=" . urlencode( Ibos::app()->setting->get( 'siteurl' ) . File::getAttachUrl() . '/' . $val['attachment'] );
                $val['officereadurl'] = $urlManager->createUrl('main/attach/office', array('id' => $idString, 'op' => 'read'));
            }

            $editOfficeRange = in_array(self::attachType($val['filetype'], 'id'), range(3, 5));
            if ($editOfficeRange) {
//              $val['officeediturl'] = $urlManager->createUrl( 'main/attach/office', array( 'id' => self::getAttachStr( $aid, $val['tableid'], array( 'filetype' => $val['filetype'], 'op' => 'edit' ) ) ) );
                $val['officeediturl'] = $urlManager->createUrl('main/attach/office', array('id' => $idString, 'op' => 'edit'));
            }
        }
        return $data;
    }

    /**
     * 附件类型，返回类型图片链接或类型ID
     * @param mixed $type 附件类型
     * @param string $returnVal 返回类型
     * @return mixed
     * @since IBOS1.0
     */
    public static function attachType($type, $returnVal = 'smallicon')
    {
        $type = strtolower($type);
        if (is_numeric($type)) {
            $typeId = $type;
        } else {
            if (in_array($type, array('fla', 'flv'))) {
                $typeId = 18;
            } elseif (in_array($type, array('asf', 'avi', 'wm', 'wmp', 'wmv', 'ram', 'rm', 'rmvb', 'rp', 'rpm', 'rt', 'smil', 'scm', 'dat', 'm1v', 'm2v', 'm2p', 'm2ts', 'mp2v', 'mpe', 'mpeg', 'mpeg1', 'mpeg2', 'mpg', 'mpv2', 'pss', 'pva', 'tp', 'tpr', 'ts', 'm4b', 'm4p', 'mp4', 'mpeg4', '3g2', '3gp', '3gp2', '3gpp', 'mov', 'qt', 'mov', 'qt', 'flv', 'f4v', 'swf', 'hlv', 'ifo', 'vob', 'amv', 'csf', 'divx', 'evo', 'mkv', 'mod', 'pmp', 'vp6', 'bik', 'mts', 'xv', 'xlmv', 'ogm', 'ogv', 'ogx', 'dvd'))) {
                $typeId = 17;
            } elseif (in_array($type, array('aac', 'ac3', 'acc', 'aiff', 'amr', 'ape', 'au', 'cda', 'dts', 'flac', 'm1a', 'm2a', 'm4a', 'mka', 'mp2', 'mp3', 'mpa', 'mpc', 'ra', 'tta', 'wav', 'wma', 'wv', 'mid', 'midi', 'ogg', 'oga'))) {
                $typeId = 16;
            } elseif (in_array($type, array('7z'))) {
                $typeId = 15;
            } elseif (in_array($type, array('zip'))) {
                $typeId = 14;
            } elseif (in_array($type, array('rar'))) {
                $typeId = 13;
            } elseif (in_array($type, array('exe'))) {
                $typeId = 12;
            } elseif (in_array($type, array('html', 'htm'))) {
                $typeId = 11;
            } elseif (in_array($type, array('swf', 'swi'))) {
                $typeId = 10;
            } elseif (in_array($type, array('php', 'js', 'pl', 'cgi', 'asp'))) {
                $typeId = 9;
            } elseif (in_array($type, array('jpg', 'gif', 'png', 'bmp'))) {
                $typeId = 8;
            } elseif (in_array($type, array('txt', 'rtf', 'wri', 'chm'))) {
                $typeId = 7;
            } elseif (in_array($type, array('pdf'))) {
                $typeId = 6;
            } elseif (in_array($type, array('pptx', 'pptm', 'ppt', 'potx', 'potm', 'pot', 'pps', 'ppsx', 'ppsm', 'ppam', 'ppa'))) {
                $typeId = 5;
            } elseif (in_array($type, array('xlsx', 'xlsm', 'xlsb', 'xltx', 'xltm', 'xlt', 'xls', 'xml', 'xlam', 'xla', 'xlw', 'csv'))) {
                $typeId = 4;
            } elseif (in_array($type, array('doc', 'docm', 'docx', 'dot', 'dotm', 'dotx'))) {
                $typeId = 3;
            } elseif (in_array($type, array('psd'))) {
                $typeId = 2;
            } elseif ($type) {
                $typeId = 1;
            } else {
                $typeId = 0;
            }
        }
        if ($returnVal == 'smallicon') {
            return self::ICON_PATH . self::$attachIcons[$typeId] . '_lt.png';
        } elseif ($returnVal == 'bigicon') {
            return self::ICON_PATH . self::$attachIcons[$typeId] . '.png';
        } elseif ($returnVal == 'id') {
            return $typeId;
        }
    }

    /**
     * 本地批量下载 （打包为zip）
     * @param mixed $aIds
     */
    public static function localBatchDownload($aIds, $downloadName = '')
    {
        $attachDir = File::getAttachUrl() . '/';
        $attachName = (empty($downloadName) ? TIMESTAMP : $downloadName) . '.zip';
        $attachData = self::getAttachData($aIds);
        $zip = new Zip();
        foreach ($attachData as $attach) {
            $content = File::readFile($attachDir . $attach['attachment']);
            $zip->addFile($content, Convert::iIconv($attach['filename'], CHARSET, 'gbk'));
        }
        $output = $zip->file();
        if (ob_get_length()) {
            ob_end_clean();
        }
        header("Content-type: text/html; charset=" . CHARSET);
        header("Cache-control: private");
        header("Content-type: application/x-zip");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($output));
        header("Content-Length: " . strlen($output));
        header("Content-Disposition: attachment; filename= " . urlencode($attachName));
        echo $output;
    }

}
