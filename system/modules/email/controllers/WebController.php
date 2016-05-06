<?php

namespace application\modules\email\controllers;

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Page;
use application\core\utils\StringUtil;
use application\modules\email\core\WebEmail;
use application\modules\email\core\WebMailImap;
use application\modules\email\core\WebMailPop;
use application\modules\email\model\EmailFolder;
use application\modules\email\model\EmailWeb;
use application\modules\email\utils\EmailMime;
use application\modules\email\utils\RyosImap;
use application\modules\email\utils\WebMail;
use application\modules\user\model\User;

class WebController extends BaseController {

    /**
     * 外部邮件附件下载
     * GET参数：id,i,都是数字
     * id = id -5;
     * i = i - 9;
     */
    public function actionDownload() {
        $id = isset($_GET['id']) ? intval($_GET['id']) - 5 : 0;
        if ($id <= 0)
            $this->error('抱歉，参数错误', $this->createUrl('web/index'));
        $i = isset($_GET['i']) ? intval($_GET['i']) - 9 : 0;
        if ($i <= 0)
            $this->error('抱歉，参数错误了', $this->createUrl('web/index'));
        $query = IBOS::app()->db->createCommand()
                ->select('qeb.remoteattachment,qeb.sendtime,qew.*')
                ->from('{{email_body}} qeb')
                ->leftJoin('{{email}} qe', 'qe.bodyid = qeb.bodyid')
                ->leftJoin('{{email_folder}} qef', 'qef.fid = qe.fid')
                ->leftJoin('{{email_web}} qew', 'qew.webid = qef.webid')
                ->where('qeb.bodyid = ' . $id)
                ->queryRow();
        if ($query && !empty($query)) {
            //根据邮箱获取邮件
            $user = User::model()->fetchByUid($query['uid']);
            $pwd = StringUtil::authCode($query['password'], 'DECODE', $user['salt']);
            list($prefix,, ) = explode('.', $query['server']);
            $host = $query['server'];
            $port = $query['port'];
            $user = $query['address'];
            $ssl = $query['ssl'] == '1' ? true : false;
            set_time_limit(0);
            $webEmail = new WebEmail($host, $port, $user, $pwd, $ssl, $prefix);
            if ($webEmail->isConnected()) {
                //这里效率有点慢，因为需要获取全部的邮件
                //当邮件比较多时，会很慢
                //TODO 速度优化
                $emails = $webEmail->getMessages();
                foreach ($emails as $email) {
                    //根据时间戳来判断是那一封邮件的附件
                    if ($query['sendtime'] == strtotime($email['date'])) {
                        $remote = StringUtil::utf8Unserialize($query['remoteattachment']);
                        if ($remote[$i - 1]['name'] == $email['attachments'][$i - 1]['name']) {
                            //下载
                            $attach = $webEmail->getAttachment($email['uid'], $i - 1);
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename=' . basename($attach['name']));
                            header('Content-Transfer-Encoding: binary');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate');
                            header('Pragma: public');
                            header('Content-Length: ' . $attach['size']);
                            ob_clean();
                            flush();
                            echo $attach['content'];
                        }
                    }
                }
            }
        }
        $this->error('抱歉，读取数据错误', $this->createUrl('web/index'));
    }

    /**
     * 外部邮箱索引页
     */
    public function actionIndex() {
        $count = EmailWeb::model()->countByAttributes(array('uid' => $this->uid));
        $pages = Page::create($count, $this->getListPageSize());
        $list = EmailWeb::model()->fetchByList($this->uid, $pages->getOffset(), $pages->getLimit());
        $data = array(
            'pages' => $pages,
            'list' => $list
        );
        $this->setPageTitle(IBOS::lang('Web email'));
        $this->setPageState('breadCrumbs', array(
            array('name' => IBOS::lang('Personal Office')),
            array('name' => IBOS::lang('Email center'), 'url' => $this->createUrl('list/index')),
            array('name' => IBOS::lang('Web email'))
        ));
        $this->render('index', $data);
    }

    /**
     * 新增操作
     * @return void
     */
    public function actionAdd() {
        $inAjax = intval(Env::getRequest('inajax'));
        if ($inAjax) {
            return $this->ajaxAdd();
        }
        if (Env::submitCheck('emailSubmit')) {
            $this->processAddWebMail(false);
            $this->success(IBOS::lang('Save succeed', 'message'), $this->createUrl('web/index'));
        } else {
            $this->setPageTitle(IBOS::lang('Add web email'));
            $this->setPageState('breadCrumbs', array(
                array('name' => IBOS::lang('Personal Office')),
                array('name' => IBOS::lang('Email center'), 'url' => $this->createUrl('list/index')),
                array('name' => IBOS::lang('Add web email'))
            ));
            $this->render('add', array('more' => false));
        }
    }

    /**
     * 编辑操作
     * @return void
     */
    public function actionEdit() {
        // 设置默认邮箱
        if (Env::getRequest('op') == 'setDefault') {
            $webId = Env::getRequest('webid');
            return $this->setDefault($webId);
        }

        $id = Env::getRequest('id');
        if (Env::submitCheck('emailSubmit')) {
            $data = $_POST['web'];
            $this->submitCheck($data, false);
            $web = $this->beforeSave($data);
            $web['ssl'] = isset($web['ssl']) ? 1 : 0;
            $web['smtpssl'] = isset($web['smtpssl']) ? 1 : 0;
            EmailWeb::model()->modify($id, $web);
            // 更新文件夹名称
            if (!empty($web['foldername'])) {
                EmailFolder::model()->updateAll(array('name' => StringUtil::filterCleanHtml($web['foldername'])), 'webid = ' . $id . ' AND uid = ' . $this->uid);
            }
            $this->success(IBOS::lang('Save succeed', 'message'), $this->createUrl('web/index'));
        } else {
            $web = EmailWeb::model()->fetch("webid = {$id} AND uid = " . $this->uid);
            if ($web) {
                $web['foldername'] = EmailFolder::model()->fetchFolderNameByWebId($id);
                $web['password'] = StringUtil::authCode($web['password'], 'DECODE', IBOS::app()->user->salt);
                $this->setPageTitle(IBOS::lang('Edit web email'));
                $this->setPageState('breadCrumbs', array(
                    array('name' => IBOS::lang(IBOS::lang('Personal Office'))),
                    array('name' => IBOS::lang(IBOS::lang('Email center')), 'url' => $this->createUrl('list/index')),
                    array('name' => IBOS::lang(IBOS::lang('Edit web email')))
                ));
                $this->render('edit', array('web' => $web));
            } else {
                $this->error(IBOS::lang('Parameters error', 'error'), $this->createUrl('web/index'));
            }
        }
    }

    /**
     *  收取邮件
     */
    public function actionReceive() {
        $webId = intval(Env::getRequest('webid'));
        $webList = $this->webMails;
        if ( empty( $webList ) ) {
            $this->ajaxReturn( array( 'isSuccess' => FALSE, 'msg' => IBOS::lang( 'Empty web mail box' ) ) );
        }
        if ($webId === 0) {
            $web = $webList;
        } else {
            $web = isset($webList[$webId]) ? array($webList[$webId]) : array();
        }
        if (empty($web)) {
            exit();
        }
        $msg = array();
        foreach ($web as $webMail) {
            WebMail::receiveMail($webMail);
        }
        $this->ajaxReturn(array('isSuccess' => true));
    }

    /**
     * 删除操作
     * @return void
     */
    public function actionDel() {
        $id = Env::getRequest('webids');
        if ($id) {
            $id = StringUtil::filterStr($id);
            $delStatus = EmailWeb::model()->delClear($id, $this->uid);
            if ($delStatus) {
                if (IBOS::app()->request->getIsAjaxRequest()) {
                    $this->ajaxReturn(array('isSuccess' => true));
                } else {
                    $this->success(IBOS::lang('Del succeed', 'message'), $this->createUrl('web/index'));
                }
            }
        }
    }

    /**
     * 显示外部邮件的部分内容（如附件，图片等）
     */
    public function actionShow() {
        $webId = intval(Env::getRequest('webid'));
        $id = intval(Env::getRequest('id'));
        $folder = Env::getRequest('folder');
        $part = Env::getRequest('part');
        $cid = Env::getRequest('cid');
        $web = EmailWeb::model()->fetchByPk($webId);
        if (intval($web['uid']) !== $this->uid) {
            exit();
        }
        list($prefix,, ) = explode('.', $web['server']);
        $user = User::model()->fetchByUid($web['uid']);
        $pwd = StringUtil::authCode($web['password'], 'DECODE', $user['salt']); //解密
        //按类型加载所用的函数库
        if ($prefix == 'imap') {
            $obj = new WebMailImap();
        } else {
            $obj = new WebMailPop();
        }
        $conn = $obj->connect($web['server'], $web['username'], $pwd, $web['ssl'], $web['port'], 'plain');
        if (!$conn) {
            exit("Login failed");
        } else {
            // Let's look for MSIE as it needs special treatment
            if (strpos(getenv('HTTP_USER_AGENT'), "MSIE")) {
                $dispositionMode = "inline";
            } else {
                $dispositionMode = "attachment";
            }
            $header = $obj->fetchHeader($conn, $folder, $id);
            if (!$header) {
                exit();
            }
            $structure_str = $obj->fetchStructureString($conn, $folder, $id);
            $structure = EmailMime::getRawStructureArray($structure_str);
            //if part id not specified but content-id is,
            //find corresponding part id
            if (!$part && $cid) {
//				if ( !preg_match( "/^</", $cid ) ) {
//					$cid = "<" . $cid;
//				}
//				if ( !preg_match( "/>$/", $cid ) ) {
//					$cid.= ">";
//				}
                //fetch parts list
                $parts_list = EmailMime::getPartList($structure, "");
                //search for cid
                if (is_array($parts_list)) {
                    reset($parts_list);
                    while (list($part_id, $part_a) = each($parts_list)) {
                        if ($part_a["id"] == $cid) {
                            $part = $part_id;
                        }
                    }
                }
                //we couldn't find part with cid, die
                if (!isset($part)) {
                    exit();
                }
            }
            // DEBUG：：以下这些分支判断暂时没有用，因为我们已经把邮件正文收回本地了。并没有如此多的操作，
            // 只有图片与附件没有收回本地
            if (isset($source)) {

            } else if (isset($show_header)) {

            } else if (isset($printer_friendly)) {

            } else if (isset($tneffid)) {

            } else {
                $header_obj = $header;
                $type = EmailMime::getPartTypeCode($structure, $part);
                if (empty($part) || $part == 0) {
                    $typestr = $header_obj->ctype;
                } else {
                    $typestr = EmailMime::getPartTypeString($structure, $part);
                }
                list($majortype, $subtype) = explode("/", $typestr);
                // format and send HTTP header
                if ($type == EmailMime::MIME_APPLICATION) {
                    $name = str_replace("/", ".", EmailMime::getPartName($structure, $part));
                    header("Content-type: $typestr; name=\"" . $name . "\"");
                    header("Content-Disposition: " . $dispositionMode . "; filename=\"" . $name . "\"");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Pragma: public");
                } else if ($type == EmailMime::MIME_MESSAGE) {
                    $name = str_replace("/", ".", EmailMime::getPartName($structure, $part));
                    header("Content-Type: text/plain; name=\"" . $name . "\"");
                } else if ($type != EmailMime::MIME_INVALID) {
                    $charset = EmailMime::getPartCharset($structure, $part);
                    $name = str_replace("/", ".", EmailMime::getPartName($structure, $part));
                    $header = "Content-type: $typestr";
                    if (!empty($charset)) {
                        $header.="; charset=\"" . $charset . "\"";
                    }
                    if (!empty($name)) {
                        $header.="; name=\"" . $name . "\"";
                    }
                    header($header);
                    if ($type != EmailMime::MIME_TEXT && $type != EmailMime::MIME_IMAGE) {
                        header("Content-Disposition: " . $dispositionMode . "; filename=\"" . $name . "\"");
                        header("Expires: 0");
                        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                        header("Pragma: public");
                    } else if (!empty($name)) {
                        header("Content-Disposition: inline; filename=\"" . $name . "\"");
                    }
                }
                //check if text/html
                if ($type == EmailMime::MIME_TEXT && strcasecmp($subtype, "html") == 0) {
                    $is_html = true;
                    $img_url = IBOS::app()->urlManager->createUrl('email/web/show', array(
                        'webid' => $webId,
                        'folder' => $folder,
                        'id' => $id,
                        'cid' => '')
                    );
                } else {
                    $is_html = false;
                    $img_url = '';
                }
                // send actual output
                if (isset($print)) {
                    // straight output, no processing
                    $obj->printPartBody($conn, $folder, $id, $part);
                } else {
                    // process as necessary, based on encoding
                    $encoding = EmailMime::getPartEncodingCode($structure, $part);
                    if (isset($raw) && $raw) {
                        $obj->printPartBody($conn, $folder, $id, $part);
                    } else if ($encoding == 3) {
                        // base 64
                        if ($is_html) {
                            $body = $obj->fetchPartBody($conn, $folder, $id, $part);
                            $body = preg_replace("/[^a-zA-Z0-9\/\+]/", "", $body);
                            $body = base64_decode($body);
                            $body = preg_replace("/src=\"cid:/", "src=\"" . $img_url, $body);
                            RyosImap::sanitizeHTML($body);
                            echo $body;
                        } else {
                            $obj->printBase64Body($conn, $folder, $id, $part);
                        }
                    } else if ($encoding == 4) {
                        // quoted printable
                        $body = $obj->fetchPartBody($conn, $folder, $id, $part);
                        $body = quoted_printable_decode(str_replace("=\r\n", "", $body));
//					$charset = EmailMime::getPartCharset( $structure, $part );
//					if ( strcasecmp( $charset, "utf-8" ) == 0 ) {
//						include_once("../include/utf8.inc");
//						$body = utf8ToUnicodeEntities( $body );
//					}
                        if ($is_html) {
                            RyosImap::sanitizeHTML($body);
                            $body = preg_replace("/src=\"cid:/", "src=\"" . $img_url, $body);
                        }
                        echo $body;
                    } else {
                        // otherwise, just dump it out
                        if ($is_html) {
                            $body = $obj->fetchPartBody($conn, $folder, $id, $part);
                            RyosImap::sanitizeHTML($body);
                            $body = preg_replace("/src=\"cid:/", "src=\"" . $img_url, $body);
                            echo $body;
                        } else {
                            $obj->printPartBody($conn, $folder, $id, $part);
                        }
                    }
                }
                $obj->close($conn);
            }
        }
    }

    /**
     * 处理外部邮件箱新增，兼容表单与ajax提交
     * @param boolean $inAjax
     * @return type
     */
    protected function processAddWebMail($inAjax = false) {
        $web = $_POST['web'];
        $errMsg = '';
        $this->submitCheck($web, $inAjax);
        if (isset($_POST['moreinfo'])) {
            // 已经是自定义配置模式，再次检查账户
            if (empty($web['server'])) {
                $this->error(IBOS::lang('Empty server address'), '', array(), $inAjax);
            }
            if ((!empty($web['ssl']) || !empty($web['smtpssl'])) && !extension_loaded('openssl')) {
                $passCheck = false;
                $errMsg = IBOS::lang('OpenSSL needed');
            } else {
                // 但这一次，优先处理用户提交的服务器配置值 $web
                $passCheck = WebMail::checkAccount($web['address'], $web['password'], $web);
                if ($passCheck) {
                    // 如果检查通过，合并提交的值并返回该邮箱的配置值
                    $web = WebMail::mergePostConfig($web['address'], $web['password'], $web);
                } else {
                    $errMsg = IBOS::lang('Error server info');
                }
            }
        } else {
            /**
             * 测试时直接跳过这一步
              // 第一次提交，查看默认配置里有没有适合的服务器配置
              $passCheck = WebMail::checkAccount($web['address'], $web['password']);
              if ( $passCheck ) {
              // 如果检查通过，返回该邮件的配置值
              $web = WebMail::getEmailConfig($web['address'], $web['password']);
              } else {
              // 没有的话，需要配置更详细的服务器信息，返回错误提示
              $errMsg = Ibos::lang('More server info');
              }
             */
            $errMsg = IBOS::lang('More server info');
            $passCheck = false;
        }
        if (!$passCheck) {
            if (!$inAjax) {
                $this->setPageTitle(IBOS::lang('Add web email'));
                $this->setPageState('breadCrumbs', array(
                    array('name' => IBOS::lang('Personal Office')),
                    array('name' => IBOS::lang('Email center'), 'url' => $this->createUrl('list/index')),
                    array('name' => IBOS::lang('Add web email'))
                ));
                $this->render('add', array('more' => true, 'errMsg' => $errMsg, 'web' => $web));
            } else {
                $data = array(
                    'lang' => IBOS::getLangSources(),
                    'more' => true,
                    'errMsg' => $errMsg,
                    'web' => $web,
                );
                $content = $this->renderPartial('ajaxAdd', $data, true);
                $this->ajaxReturn(array('moreinfo' => true, 'content' => $content));
            }
            exit();
        }
        // 如果检查通过，保存邮箱配置
        $web = $this->beforeSave($web);
        $newId = EmailWeb::model()->add($web, true);
        // 与文件夹表创建一条关联数据
        $folder = array(
            'sort' => 0,
            'name' => isset($_POST['web']['name']) ? StringUtil::filterCleanHtml($_POST['web']['name']) : $web['address'],
            'uid' => $this->uid,
            'webid' => $newId
        );
        $fid = EmailFolder::model()->add($folder, true);
        EmailWeb::model()->modify($newId, array('fid' => $fid));
        return $newId;
    }

    /**
     * 设置默认邮箱
     * @param integer $webId 外部邮箱ID
     * @return void
     */
    protected function setDefault($webId) {
        if ($webId) {
            EmailWeb::model()->updateAll(array('isdefault' => 0), 'uid = ' . $this->uid);
            EmailWeb::model()->modify($webId, array('uid' => $this->uid, 'isdefault' => 1));
            $isSuccess = true;
        } else {
            $isSuccess = false;
        }
        $this->ajaxReturn(array('isSuccess' => $isSuccess));
    }

    /**
     * 快捷添加操作
     * @return void
     */
    protected function ajaxAdd() {
        // if (Ibos::app()->request->getIsPostRequest()) {
        if ( Env::submitCheck( 'formhash' ) ) {
            $newId = $this->processAddWebMail(true);
            $this->success(IBOS::lang('Save succeed', 'message'), '', array(), array('webId' => $newId));
        } else {
            $data = array(
                'lang' => IBOS::getLangSources(),
                'more' => false,
            );
            $this->renderPartial('ajaxAdd', $data);
        }
    }

    /**
     * 保存前的预处理
     * @param array $web
     * @return array 返回部分处理后的数据
     */
    private function beforeSave($web) {
        $web['nickname'] = isset($_POST['web']['nickname']) ? trim(htmlspecialchars($_POST['web']['nickname'])) : ''; //发信昵称
        if (empty($web['nickname'])) {  //如果为空默认设置为真实姓名
            $web['nickname'] = IBOS::app()->user->realname;
        }
        $web['uid'] = $this->uid;
        $web['password'] = StringUtil::authCode($web['password'], 'ENCODE', IBOS::app()->user->salt);
        return $web;
    }

    /**
     * 提交前检查
     * @param array $data
     * @param boolean $inajax
     */
    private function submitCheck($data, $inAjax) {
        if (isset($data['address']) && empty($data['address'])) {
            $this->error(IBOS::lang('Empty email address'), '', array(), $inAjax);
        }
        if (empty($data['password'])) {
            $this->error(IBOS::lang('Empty email password'), '', array(), $inAjax);
        }
    }

}
