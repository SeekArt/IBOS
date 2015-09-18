<?php

namespace application\modules\email\controllers;

use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\DateTime;
use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Module;
use application\core\utils\String;
use application\modules\article\model\Article;
use application\modules\email\model\Email;
use application\modules\email\model\EmailBody;
use application\modules\email\model\EmailFolder;
use application\modules\email\model\EmailWeb;
use application\modules\email\utils\Email as EmailUtil;
use application\modules\email\utils\WebMail as WebMailUtil;
use application\modules\officialdoc\model\Officialdoc;
use application\modules\thread\model\Thread;
use application\modules\user\model\User;
use application\modules\user\utils\User as UserUtil;

class ContentController extends BaseController {

    public function init() {
        parent::init();
        $this->archiveId = intval( Env::getRequest( 'archiveid' ) );
    }

    /**
     * 列表页
     * @return void 
     */
    public function actionIndex() {
        //  不做任何事，只跳转收件箱。因为内容控制器不需要索引页
        $this->redirect( 'list/index' );
    }

    /**
     * 写邮件
     * @return void
     */
    public function actionAdd() {
        // bodyId
        $id = intval( Env::getRequest( 'id' ) );
        $this->checkUserSize();
        $op = Env::getRequest( 'op' );
        if ( !in_array( $op, array( 'new', 'quickReply', 'reply', 'replyall', 'fw', 'forwardNew', 'forwardDoc' ) ) ) {
            $op = 'new';
        }
        if ( Env::submitCheck( 'formhash' ) ) {
            // 快捷回复提交处理
            if ( $op == 'quickReply' ) {
                $data = EmailBody::model()->fetchByPk( $id );
                $content = String::filterCleanHtml( Env::getRequest( 'content' ) );
                $bodyData = EmailBody::model()->getAttributes();
                $bodyData['issend'] = 1;
                $bodyData['fromid'] = $this->uid;
                $bodyData['toids'] = $data['fromid'];
                $bodyData['sendtime'] = TIMESTAMP;
                $bodyData['content'] = $content;
                $bodyData['subject'] = IBOS::lang( 'Reply subject', '', array( '{subject}' => $data['subject'] ) );
            } else {
                // 表单提交
                $bodyData = $this->beforeSaveBody();
                $bodyData['subject'] = htmlspecialchars($bodyData['subject']);
            }
            $bodyId = EmailBody::model()->add( $bodyData, true );
            $this->save( $bodyId, $bodyData );
        } else {
            // 外部邮箱快捷发送接口
            $webmail = Env::getRequest( 'webmail' );
            $in = array();
            $web = !empty( $webmail ) ? explode( ';', $webmail ) : array();
            // 回复/转发/回复全部处理
            if ( $op == 'new' ) {
                $toid = Env::getRequest( 'toid' );
                $toWebid = Env::getRequest( 'webid' );
                if ( $toid ) {
                    $in[] = String::wrapId( $toid );
                }
                if ( $toWebid ) {
                    $web = $toWebid;
                }
                $subject = $content = '';
            } elseif ( $op == 'forwardNew' || $op == 'forwardDoc' ) {
                $method = 'get' . ucfirst( $op );
                $bodyData = $this->$method();
                $subject = $bodyData['subject'];
                $content = $bodyData['content'];
            } else {
                if ( $id ) {
                    $bodyData = EmailBody::model()->fetchByPk( $id );
                    $content = $this->handleEmailContentData( $bodyData );
                    if ( $bodyData ) {
                        switch ( $op ) {
                            case 'reply':
                            case 'replyall':
                                if ( $op == 'reply' ) {
                                    if ( empty( $bodyData['fromid'] ) && !empty( $bodyData['fromwebmail'] ) ) {
                                        $web[] = $bodyData['fromwebmail'];
                                    } else {
                                        $in[] = String::wrapId( $bodyData['fromid'] );
                                    }
                                } else {
                                    if ( empty( $bodyData['fromid'] ) ) {
                                        $allIds = String::filterStr( $bodyData['toids'] . ',' . $bodyData['copytoids'] );
                                        foreach ( explode( ',', $allIds ) as $key => $uid ) {
                                            if ( !empty( $uid ) ) {
                                                $tempUid = strpos( $uid, '@' );
                                                if ( !$tempUid ) {
                                                    $in[$key] = String::wrapId( $uid );
                                                } else {
                                                    $web[$key] = $uid;
                                                }
                                            }
                                        }
                                    } else {
                                        $toid = explode( ',', $bodyData['toids'] );
                                        $copytoid = explode( ',', $bodyData['copytoids'] );
                                        $toidAll = array_merge( $toid, $copytoid ); // 合并抄送人
                                        $toidAll = array_filter( $toidAll ); // 去除空值
                                        $toidAll = array_unique( $toidAll ); // 去除重复

                                        $uid = IBOS::app()->user->uid;
                                        if ( $uid != $bodyData['fromid'] ) {
                                            $in[] = String::wrapId( $bodyData['fromid'] );
                                        }

                                        // 回复全部中，如果收件人和抄送人有自己的，去除掉。
                                        $selfInitTOid = array_search( $this->uid, $toidAll );
                                        if ( $selfInitTOid !== false ) {
                                            unset( $toidAll[$selfInitTOid] );
                                        }
                                        if ( !empty( $toidAll ) ) {
                                            $in[] = String::wrapId( $toidAll );
                                        }
                                    }
                                }
                                $subject = IBOS::lang( 'Reply subject', '', array( '{subject}' => $bodyData['subject'] ) );
                                break;
                            case 'fw':
                                $subject = IBOS::lang( 'Fw subject', '', array( '{subject}' => $bodyData['subject'] ) );
                                if ( !empty( $bodyData['attachmentid'] ) ) {
                                    $attach = Attach::getAttach( $bodyData['attachmentid'] );
                                }
                                break;
                            default:
                                break;
                        }
                    }
                }
            }


            $data = array(
                'op' => $op,
                'subject' => $subject,
                'in' => $in,
                'web' => $web,
                'content' => $content,
                'allowWebMail' => $this->allowWebMail,
                'webMails' => $this->webMails,
                'systemRemind' => IBOS::app()->setting->get( 'setting/emailsystemremind' ),
                'uploadConfig' => Attach::getUploadConfig(),
                'isInstallThread' => Module::getIsEnabled( 'thread' )
            );
            if ( isset( $attach ) ) {
                $data['attach'] = $attach;
            }
            if ( $data['isInstallThread'] ) {
                $data['threadList'] = Thread::model()->getThreadList( IBOS::app()->user->uid );
            }
            $this->setPageTitle( IBOS::lang( 'Fill in email' ) );
            $this->setPageState( 'breadCrumbs', array(
                array( 'name' => IBOS::lang( 'Personal Office' ) ),
                array( 'name' => IBOS::lang( 'Email center' ), 'url' => $this->createUrl( 'list/index' ) ),
                array( 'name' => IBOS::lang( 'Fill in email' ) )
            ) );
            $this->render( 'add', $data );
        }
    }

    /**
     * 编辑
     * @return void
     */
    public function actionEdit() {
        $id = intval( Env::getRequest( 'id' ) );
        if ( empty( $id ) ) {
            $this->error( IBOS::lang( 'Parameters error', 'error' ), $this->createUrl( 'list/index' ) );
        }
        $emailBody = EmailBody::model()->fetchByPk( $id );
        if ( empty( $emailBody ) ) {
            $this->error( IBOS::lang( 'Email not exists' ), $this->createUrl( 'list/index' ) );
        }
        // 权限判定
        if ( intval( $emailBody['fromid'] ) !== $this->uid ) {
            $this->error( IBOS::lang( 'Request tainting', 'error' ), $this->createUrl( 'list/index' ) );
        }
        if ( Env::submitCheck( 'formhash' ) ) {
            $bodyData = $this->beforeSaveBody();
            EmailBody::model()->modify( $id, $bodyData );
            $this->save( $id, $bodyData );
        } else {
            // 处理用户ID
            $emailBody['toids'] = String::wrapId( $emailBody['toids'] );
            $emailBody['copytoids'] = String::wrapId( $emailBody['copytoids'] );
            $emailBody['secrettoids'] = String::wrapId( $emailBody['secrettoids'] );
            // 附件生成信息
            if ( !empty( $emailBody['attachmentid'] ) ) {
                $emailBody['attach'] = Attach::getAttach( $emailBody['attachmentid'] );
            }
            $data = array(
                'email' => $emailBody,
                'allowWebMail' => $this->allowWebMail,
                'webMails' => $this->webMails,
                'systemRemind' => IBOS::app()->setting->get( 'setting/emailsystemremind' ),
                'uploadConfig' => Attach::getUploadConfig()
            );
            $this->setPageTitle( IBOS::lang( 'Edit email' ) );
            $this->setPageState( 'breadCrumbs', array(
                array( 'name' => IBOS::lang( 'Personal Office' ) ),
                array( 'name' => IBOS::lang( 'Email center' ), 'url' => $this->createUrl( 'list/index' ) ),
                array( 'name' => IBOS::lang( 'Edit email' ) )
            ) );
            $this->render( 'edit', $data );
        }
    }

    /**
     * 邮件显示
     * @return void 
     */
    public function actionShow() {
        $id = is_null( $_GET['id'] ) ? 0 : intval( $_GET['id'] );
        if ( $id ) {
            $data = array();
            $email = Email::model()->fetchById( $id, $this->archiveId );
            if ( !$email ) {
                $this->error( IBOS::lang( 'Parameters error', 'error' ), $this->createUrl( 'list/index' ) );
            }
            // 阅读权限判定
            $isReceiver = $email['toid'] == $this->uid ||
                    $email['fromid'] == $this->uid ||
                    String::findIn( $email['copytoids'], $this->uid ) ||
                    String::findIn( $email['toids'], $this->uid );
            if ( !$isReceiver ) {
                $this->error( IBOS::lang( 'View access invalid' ), $this->createUrl( 'list/index' ) );
            }
            // 显示外部邮件框架内容
            if ( Env::getRequest( 'op' ) == 'showframe' ) {
                echo $email['content'];
                exit();
            }
            // 阅读状态更改权限判定
            if ( ($email['toid'] == $this->uid || String::findIn( $email['toids'], $this->uid )) && $email['isread'] == 0 ) {
                Email::model()->setRead( $id, $this->uid );
            }
            $email['dateTime'] = Convert::formatDate( $email['sendtime'] );
            // 处理人员信息
            if ( $this->uid == $email['fromid'] ) {
                $email['fromName'] = IBOS::lang( 'Me' );
            } else {
                if ( !empty( $email['fromid'] ) ) {
                    $email['fromName'] = User::model()->fetchRealnameByUid( $email['fromid'] );
                } else {
                    $email['fromName'] = $email['fromwebmail'];
                }
            }
            //外部邮箱的
            if (!is_numeric($email['toids'][0])) {//内部邮件toids第一个字符一定是数字
                $ids = @unserialize($email['toids']);
                if ($ids) {
                    //去掉第一个收件人，会和下面的towebmail重复
                    $email['toids'] = array_pop($ids);
                    $email['toids'] = implode(',', $ids);
                }
            }
            //外部邮箱的
            if (isset($email['copytoids'][0]) && !is_numeric($email['copytoids'][0])) {
                $copys = @unserialize($email['copytoids']);
                if ($copys)
                    $email['copytoids'] = implode(',', $copys);
            }
            $allIds = String::filterStr( $email['toids'] . ',' . $email['copytoids'] );
            $copyToId = explode( ',', $email['copytoids'] );
            $toId = explode( ',', $email['toids'] );
            $allUsers = $copyToUsers = $toUsers = array();
            // 组合人员信息到以上三个数组，用于页面调用
            foreach ( explode( ',', $allIds ) as $key => $uid ) {
                if ( !empty( $uid ) ) {
                    $tempUid = strpos( $uid, '@' );
                    if ( !$tempUid ) {
                        if ( $this->uid == $uid ) {
                            $name = IBOS::lang( 'Self' );
                        } else {
                            $name = User::model()->fetchRealnameByUid( $uid );
                        }
                    } else {
                        $name = $uid;
                    }
                    if ( in_array( $uid, $copyToId ) ) {
                        $copyToUsers[$key] = $allUsers[$key] = $name;
                    } else if ( in_array( $uid, $toId ) ) {
                        $allUsers[$key] = $toUsers[$uid] = $name;
                    } else {
                        $allUsers[$key] = $name;
                    }
                }
            }
            if ( !empty( $email['towebmail'] ) ) {
                $towebmails = explode( ';', $email['towebmail'] );
                while ( !empty( $towebmails ) ) {
                    $toUsers[] = $allUsers[] = array_pop( $towebmails );
                }
                $toUsers = array_unique( $toUsers );
                $allUsers = array_unique( $allUsers );
            }
            $data['allUsers'] = $allUsers;
            $data['toUsers'] = $toUsers;
            $data['copyToUsers'] = $copyToUsers;
            // 是否密送者，密送者在回复全部的时候会有提示
            $data['isSecretUser'] = String::findIn( $this->uid, $email['secrettoids'] );
            //外部邮件的
            if (!empty($email['remoteattachment'])) {
                $data['atts'] = unserialize($email['remoteattachment']);
                $data['webid'] = $email['bodyid'];
            }
            !empty( $email['attachmentid'] ) && $data['attach'] = Attach::getAttach( $email['attachmentid'] );
            // 获取上/下一封邮件
            $data['next'] = Email::model()->fetchNext( $id, $this->uid, $email['fid'], $this->archiveId );
            $data['prev'] = Email::model()->fetchPrev( $id, $this->uid, $email['fid'], $this->archiveId );
            $data['email'] = $email;
            $data['weekDay'] = DateTime::getWeekDay( $email['sendtime'] );
            $this->setPageTitle( IBOS::lang( 'Show email' ) );
            $this->setPageState( 'breadCrumbs', array(
                array( 'name' => IBOS::lang( 'Personal Office' ) ),
                array( 'name' => IBOS::lang( 'Email center' ), 'url' => $this->createUrl( 'list/index' ) ),
                array( 'name' => IBOS::lang( 'Show email' ) )
            ) );
            $this->render( 'show', $data );
        } else {
            $this->error( IBOS::lang( 'Parameters error' ), $this->createUrl( 'list/index' ) );
        }
    }

    /**
     * 导出操作
     */
    public function actionExport() {
        $id = intval( Env::getRequest( 'id' ) );
        $op = Env::getRequest( 'op' );
        if ( $op == 'eml' ) {
            EmailUtil::exportEml( $id );
        } else if ( $op == 'excel' ) {
            EmailUtil::exportExcel( $id );
        }
    }

    /**
     * 检查用户邮件使用情况
     */
    protected function checkUserSize() {
        $userSize = EmailUtil::getUserSize( $this->uid );
        $usedSize = EmailFolder::model()->getUsedSize( $this->uid );
        if ( $usedSize > Convert::ConvertBytes( $userSize . 'm' ) ) {
            $this->error( IBOS::lang( 'Capacity overflow', '', array( '{size}' => $usedSize ) ), $this->createUrl( 'email/list' ) );
        }
    }

    /**
     * 保存邮件主体前的处理
     * @return array
     */
    private function beforeSaveBody() {
        $data = $_POST['emailbody'];
        // 内部收件人与外部收件人不能同时为空
        if ( empty( $data['towebmail'] ) && empty( $data['toids'] ) ) {
            $this->error( IBOS::lang( 'Empty receiver' ) );
        }
        $data['fromid'] = $this->uid;
        $bodyData = EmailBody::model()->handleEmailBody( $data );
        return $bodyData;
    }

    /**
     * 保存动作。发送邮件会作一系列处理，草稿则不作处理
     * @param integer $bodyId 邮件主体ID
     * @param array $bodyData 邮件主体数据
     */
    private function save( $bodyId, $bodyData ) {
        // 更新附件使用情况
        if ( !empty( $bodyData['attachmentid'] ) && $bodyId ) {
            Attach::updateAttach( $bodyData['attachmentid'], $bodyId );
        }
        if ( $bodyData['issend'] ) {
            // 是否关联主线
            if ( Module::getIsEnabled( 'thread' ) ) {
                $threadId = intval( Env::getRequest( 'threadid' ) );
            } else {
                $threadId = 0;
            }
            Email::model()->send( $bodyId, $bodyData, self::INBOX_ID, $threadId );
            // 外部邮件处理
            if ( !empty( $bodyData['towebmail'] ) ) {
                $toUsers = String::filterStr( $bodyData['towebmail'], ';' );
                if ( !empty( $toUsers ) ) {
                    $webBox = EmailWeb::model()->fetchByPk( $bodyData['fromwebid'] );
                    WebMailUtil::sendWebMail( $toUsers, $bodyData, $webBox );
                }
            }
            // 更新积分
            UserUtil::updateCreditByAction( 'postmail', $this->uid );
            $message = IBOS::lang( 'Send succeed' );
        } else {
            $message = IBOS::lang( 'Save succeed', 'message' );
        }
        // 两种请求的不同返回
        if ( IBOS::app()->request->getIsAjaxRequest() ) {
            $this->ajaxReturn( array( 'isSuccess' => true, 'messsage' => $message ) );
        } else {
            $this->success( $message, $this->createUrl( 'list/index' ) );
        }
    }

    /**
     * 处理邮件正文数据，返回渲染后的视图
     * @param array $bodyData 邮件主体数据
     * @return string
     */
    private function handleEmailContentData( $bodyData ) {
        $lang = IBOS::getLangSources();
        $contentData = array(
            'lang' => $lang,
            'body' => $bodyData
        );
        // 处理视图中要显示的收件人及抄送ID
        $toids = !empty( $bodyData['toids'] ) ? explode( ',', $bodyData['toids'] ) : array();
        $copyToIds = !empty( $bodyData['copytoids'] ) ? explode( ',', $bodyData['copytoids'] ) : array();
        $toid = $copyToId = array();
        if ( !empty( $toids ) ) {
            $toUsers = User::model()->fetchAllByUids( $toids );
            $toid = Convert::getSubByKey( $toUsers, 'realname' );
        }

        if ( !empty( $copyToIds ) ) {
            $copyToUsers = User::model()->fetchAllByUids( $copyToIds );
            $copyToId = Convert::getSubByKey( $copyToUsers, 'realname' );
        }

        if ( !empty( $bodyData['towebmail'] ) ) {
            $webMailAddress = explode( ';', $bodyData['towebmail'] );
            $toid = array_merge( $toid, $webMailAddress );
        }
        $contentData['toid'] = $toid;
        $contentData['copyToId'] = $copyToId;
        $content = $this->renderPartial( 'content', $contentData, true );
        return $content;
    }

    /**
     * 获得要转发的新闻的数据
     * @return array
     */
    private function getForwardNew() {
        $artId = intval( Env::getRequest( 'relatedid' ) );
        $article = Article::model()->fetchByPk( $artId );
        if ( empty( $article ) ) {
            $this->error( IBOS::lang( '转发的新闻不存在或者已删掉' ), IBOS::app()->urlManager->createUrl( 'article/default/index' ) );
        }
        return $article;
    }

    /**
     * 获得要转发的公文的数据
     * @return array
     */
    private function getForwardDoc() {
        $docId = intval( Env::getRequest( 'relatedid' ) );
        $doc = Officialdoc::model()->fetchByPk( $docId );
        if ( empty( $doc ) ) {
            $this->error( IBOS::lang( '转发的公文不存在或者已删掉' ), IBOS::app()->urlManager->createUrl( 'officialdoc/officialdoc/index' ) );
        }
        return $doc;
    }

}
