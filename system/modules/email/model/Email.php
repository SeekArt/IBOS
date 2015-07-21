<?php

/**
 * Email model class file.
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @link http://www.ibos.cn/
 * @copyright 2008-2014 IBOS Inc
 */
/**
 * 邮件主表模型
 *
 * @author banyanCheung <banyan@ibos.com.cn>
 * @package application.modules.email.model
 * @version $Id: Email.php 5175 2015-06-17 13:25:24Z Aeolus $
 */

namespace application\modules\email\model;

use application\core\model\Model;
use application\core\utils\Attach;
use application\core\utils\Convert;
use application\core\utils\Database;
use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\email\controllers\BaseController;
use application\modules\main\model\Attachment;
use application\modules\message\model\Notify;
use application\modules\thread\utils\Thread as ThreadUtil;
use application\modules\user\model\User;

class Email extends Model {

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function tableName() {
		return '{{email}}';
	}

	/**
	 * 获取上一封邮件
	 * @param integer $id 当前邮件ID
	 * @param integer $uid 当前用户ID
	 * @param integer $fid 文件夹ID
	 * @param integer $archiveId 存档表ID
	 * @return array
	 */
	public function fetchPrev( $id, $uid, $fid, $archiveId = 0 ) {
		$condition = sprintf( 'e.fid = %d AND toid = %d AND eb.issend = 1 AND e.isdel = 0 AND e.emailid > %d', $fid, $uid, $id );
		$order = 'emailid ASC';
		return $this->getSiblingsByCondition( $condition, $order, $archiveId );
	}

	/**
	 * 获取下一封邮件
	 * @param integer $id 当前邮件ID
	 * @param integer $uid 当前用户ID
	 * @param integer $fid 文件夹ID
	 * @param integer $archiveId 存档表ID
	 * @return array
	 */
	public function fetchNext( $id, $uid, $fid, $archiveId = 0 ) {
		$condition = sprintf( 'e.fid = %d AND toid = %d AND eb.issend = 1 AND e.isdel = 0 AND e.emailid < %d', $fid, $uid, $id );
		$order = 'emailid DESC';
		return $this->getSiblingsByCondition( $condition, $order, $archiveId );
	}

	/**
	 * 查找一条完整的email数据
	 * @param integer $id 邮件索引ID
	 * @param integer $archiveId 存档表ID
	 * @return array
	 */
	public function fetchById( $id, $archiveId = 0 ) {
		$mainTable = $this->getTableName( $archiveId );
		$bodyTable = EmailBody::model()->getTableName( $archiveId );
		$email = IBOS::app()->db->createCommand()
				->select( '*' )
				->from( '{{' . $mainTable . '}} e' )
				->leftJoin( '{{' . $bodyTable . '}} eb', 'e.bodyid = eb.bodyid' )
				->where( 'emailid = ' . intval( $id ) )
				->queryRow();
		return is_array( $email ) ? $email : array();
	}

	public function fetchAllBodyIdByKeywordFromAttach( $keyword, $whereAdd = '1', $queryArchiveId = 0 ) {
		$kwBodyIds = array();
		//查询附件名，返回相关附件信息
		$queryParam = "uid = " . IBOS::app()->user->uid;
		$kwAttachments = Attachment::model()->fetchAllByKeywordFileName( $keyword, $queryParam );
		if ( !empty( $kwAttachments ) ) {
			// 思路：把这些含关键字的附件ID求出与邮件中的附件ID交集，把交集中的邮件ID添加到sql条件中
			// 取出附件的ID
			$kwAids = array_keys( $kwAttachments );
			//查找含有附件的邮件
			$emailData = $this->fetchAllByArchiveIds( 'e.*,eb.*,', "{$whereAdd} AND attachmentid!=''", $queryArchiveId );
			foreach ( $emailData as $email ) {
				//求名字中含有关键字的附件的ID与邮件的附件ID交集
				if ( array_intersect( $kwAids, explode( ',', $email['attachmentid'] ) ) ) {
					//记录该邮件的bodyid
					$kwBodyIds[] = $email['bodyid'];
				}
			}
		}
		return $kwBodyIds;
	}

	/**
	 * 设置指定$uid的邮件为已读
	 * @param integer $uid 用户ID
	 * @return integer 更新的行数
	 */
	public function setAllRead( $uid ) {
		return $this->setField( 'isread', 1, 'toid = ' . intval( $uid ) );
	}

	/**
	 * 设置指定id的邮件为已读
	 * @param integer $id
	 * @return integer 更新的行数
	 */
	public function setRead( $id ) {
		return $this->setField( 'isread', 1, 'emailid = ' . intval( $id ) );
	}

	/**
	 * 更新email表字段值
	 * @param string $field 字段名
	 * @param mixed $value 字段值
	 * @param string $conditions 更新条件
	 * @return integer 更新的行数
	 */
	public function setField( $field, $value, $conditions = '' ) {
		return $this->updateAll( array( $field => $value ), $conditions );
	}

	/**
	 * 发送邮件
	 * @param integer $bodyId 邮件主体ID
	 * @param array $bodyData 邮件主体
	 * @param integer $inboxId 收件箱ID
	 */
	public function send( $bodyId, $bodyData, $inboxId = BaseController::INBOX_ID, $threadId = 0 ) {
		// 所有用户ID集合
		$toids = $bodyData['toids'] . ',' . $bodyData['copytoids'] . ',' . $bodyData['secrettoids'];
		$toid = String::filterStr( $toids );
		foreach ( explode( ',', $toid ) as $uid ) {
			$email = array(
				'toid' => $uid,
				'fid' => $inboxId,
				'bodyid' => $bodyId,
			);
			$newId = $this->add( $email, true );
			// 发送提醒处理
			// DEBUG:: 效率问题。可能会发生在推送QQ提醒时 by banyan
			$file = IBOS::getPathOfAlias( 'application.modules.email.views.remindcontent' ) . '.php';
			extract( array( 'body' => $bodyData ), EXTR_PREFIX_SAME, 'data' );
			ob_start();
			ob_implicit_flush( false );
			require($file);
			$content = ob_get_clean();
			$config = array(
				'{sender}' => IBOS::app()->user->realname,
				'{subject}' => $bodyData['subject'],
				'{url}' => IBOS::app()->urlManager->createUrl( 'email/content/show', array( 'id' => $newId ) ),
				'{content}' => $content,
				'{orgContent}' => String::filterCleanHtml( $bodyData['content'] ),
				'id' => $newId,
			);
			Notify::model()->sendNotify( $uid, 'email_message', $config );
			// 是否关联主线
			if ( $threadId ) {
				$fromUid = IBOS::app()->user->uid;
				$dynamic = IBOS::lang( 'Relative thread', '', array(
							'{realname}' => User::model()->fetchRealnameByUid( $uid ),
							'{url}' => IBOS::app()->urlManager->createUrl( 'email/content/show', array( 'id' => $newId ) ),
							'{subject}' => $bodyData['subject']
						) );
				ThreadUtil::getInstance()->relative( $fromUid, $threadId, 'email', $newId, $dynamic );
			}
		}
	}

	public function recall( $emailIds, $uid ) {
		$emails = $this->fetchAllByPk( explode( ',', $emailIds ) );
		$ids = array();
		foreach ( $emails as $email ) {
			if ( !$email['isread'] ) {
				$ids[] = $email['emailid'];
			}
		}
		if ( !empty( $ids ) ) {
			return $this->completelyDelete( $ids, $uid );
		}
		return 0;
	}

	/**
	 * 彻底删除邮件及其主体
	 * @param array $emailIds
	 * @return integer 删除条数
	 */
	public function completelyDelete( $emailIds, $uid, $archiveId = 0 ) {
		$isSuccess = 0;
		$emailIds = is_array( $emailIds ) ? $emailIds : array( $emailIds );
		$mainTable = sprintf( '{{%s}}', $this->getTableName( $archiveId ) );
		$bodyTable = sprintf( '{{%s}}', EmailBody::model()->getTableName( $archiveId ) );
		$bodyIds = IBOS::app()->db->createCommand()
				->select( 'bodyid' )
				->from( $mainTable )
				->where( "FIND_IN_SET(emailid,'" . implode( ',', $emailIds ) . "')" )
				->queryAll();
		if ( $bodyIds ) {
			$bodyIds = Convert::getSubByKey( $bodyIds, 'bodyid' );
		}
		foreach ( $bodyIds as $i => $bodyId ) {
			$body = IBOS::app()->db->createCommand()->select( 'fromid,attachmentid' )
					->from( $bodyTable )
					->where( "bodyid = {$bodyId} AND fromid = {$uid}" )
					->queryRow();
			if ( $body || !isset( $emailIds[$i] ) ) {
				if ( isset( $emailIds[$i] ) ) {
					$readerRows = IBOS::app()->db->createCommand()->select( 'bodyid' )
							->from( $mainTable )
							->where( "emailid = $emailIds[$i] AND isread != 0 AND toid != {$uid}" )
							->queryRow();
				} else {
					$readerRows = false;
				}
				if ( $readerRows ) {
					if ( IBOS::app()->db->createCommand()->update( $bodyTable, array( 'issenderdel' => 1 ), 'bodyid = ' . $bodyId ) ) {
						$isSuccess = 1;
					}
				} else {
					if ( isset( $emailIds[$i] ) ) {
						$nextStep = IBOS::app()->db->createCommand()->delete( $mainTable, 'emailid = ' . $emailIds[$i] );
					} else {
						IBOS::app()->db->createCommand()->delete( $bodyTable, 'bodyid = ' . $bodyId );
						$nextStep = true;
					}
					if ( $nextStep ) {
						if ( $body['attachmentid'] !== '' ) {
							Attach::delAttach( $body['attachmentid'] );
						}
						$isSuccess = 1;
					}
				}
			} else {
				$lastRows = IBOS::app()->db->createCommand()->select( 'toid' )
						->from( $mainTable )
						->where( "bodyid = {$bodyId} AND toid != {$uid}" )
						->queryRow();
				if ( !$lastRows ) { //如果是最后一个收件人,删除
					IBOS::app()->db->createCommand()->delete( $mainTable, 'emailid = ' . $emailIds[$i] );
					$attachmentId = IBOS::app()->db->createCommand()
							->select( 'attachmentid' )
							->from( $bodyTable )
							->where( 'bodyid = ' . $bodyId )
							->queryScalar();
					if ( $attachmentId && $attachmentId !== '' ) {
						Attach::delAttach( $attachmentId );
					}
					$isSuccess++;
				} else { //否则只删除emailid
					IBOS::app()->db->createCommand()->delete( $mainTable, "emailid = {$emailIds[$i]} AND toid = {$uid}" );
					$isSuccess++;
				}
			}
		}
		return $isSuccess;
	}

	/**
	 * 根据文件夹id和uid获取邮件ID
	 * @param integer $fid 文件夹
	 * @param integer $uid 用户ID
	 * @return array 邮件ID数组
	 */
	public function fetchAllEmailIdsByFolderId( $fid, $uid ) {
		$record = $this->fetchAllByAttributes( array( 'fid' => $fid, 'toid' => $uid ), array( 'select' => 'emailid' ) );
		$emailIds = Convert::getSubByKey( $record, 'emailid' );
		return $emailIds;
	}

	/**
	 * 根据条件搜索指定的一个或者多个邮件表中的邮件
	 * @param string $conditions
	 * @param mixed $tids 表ID [int][array]
	 * @author denglh
	 */
	public function fetchAllByArchiveIds( $field = '*', $conditions = '', $archiveId = 0, $tableAlias = array( 'e', 'eb' ), $offset = null, $length = null, $order = SORT_DESC, $sort = 'sendtime' ) {
		$aidList = is_array( $archiveId ) ? $archiveId : array( $archiveId );
		$emailData = array();
		//声明一个数组记录已查询的tid
		$queryTable = array();
		foreach ( $aidList as $aid ) {
			$emailTableName = $this->getTableName( $aid );
			$emailbodyTableName = EmailBody::model()->getTableName( $aid );
			if ( in_array( $emailTableName, $queryTable ) ) {
				continue;
			}
			$list = IBOS::app()->db->createCommand()
					->select( $field )
					->from( sprintf( "{{%s}} %s", $emailTableName, $tableAlias[0] ) )
					->leftJoin( sprintf( "{{%s}} %s", $emailbodyTableName, $tableAlias[1] ), "{$tableAlias[0]}.bodyid = {$tableAlias[1]}.bodyid" )
					->where( $conditions )
					->queryAll();
			$sortRefer = array();
			$emailFetchData = array();
			foreach ( $list as $email ) {
				$email['aid'] = $aid;
				$sortRefer[$email['emailid']] = $email[$sort];
				$emailFetchData[] = $email;
			}
			$queryTable[] = $emailTableName;
		}
		foreach ( $emailFetchData as $emailInfo ) {
			$emailData[$emailInfo['emailid']] = $emailInfo;
		}
		//根据关键字排序
		array_multisort( $sortRefer, $order, $emailData );
		if ( !is_null( $offset ) && !is_null( $length ) ) {
			//截取数组
			$emailData = array_slice( $emailData, $offset, $length, false );
		}
		return $emailData;
	}

	/**
	 * 根据列表查询参数获得列表数据
	 * @param string $operation 列表动作
	 * @param integer $uid 用户ID
	 * @param integer $fid 文件夹ID
	 * @param integer $archiveId 存档表ID
	 * @param integer $limit 条数
	 * @param integer $offset 当前页
	 * @return array
	 */
	public function fetchAllByListParam( $operation, $uid = 0, $fid = 0, $archiveId = 0, $limit = 10, $offset = 0, $subOp = '' ) {
		$param = $this->getListParam( $operation, $uid, $fid, $archiveId, false, $subOp );
		if ( empty( $param['field'] ) ) {
			$param['field'] = 'e.emailid, e.isread, eb.fromid, eb.subject, eb.sendtime, eb.fromwebmail,' .
					'eb.important, e.ismark, eb.attachmentid';
		}
		if ( empty( $param['order'] ) ) {
			$param['order'] = "eb.sendtime DESC";
		}
		$sql = "SELECT %s FROM %s WHERE %s";
		if ( !empty( $param['group'] ) ) {
			$sql .= ' GROUP BY ' . $param['group'];
		}
		$sql .= " ORDER BY {$param['order']} LIMIT {$offset},{$limit}";
		$db = IBOS::app()->db->createCommand();
		$list = $db->setText( sprintf( $sql, $param['field'], $param['table'], $param['condition'] ) )->queryAll();
		foreach ( $list as &$value ) {
			if ( !empty( $value['fromid'] ) ) {
				$value['fromuser'] = User::model()->fetchRealnameByUid( $value['fromid'] );
			} else {
				$value['fromuser'] = $value['fromwebmail'];
			}
		}
		return (array) $list;
	}

	/**
	 * 根据列表数据获取指定动作未读邮件数
	 * @param string $operation 列表动作
	 * @param integer $uid 用户ID
	 * @param integer $fid 文件夹ID
	 * @param integer $archiveId 存档表ID
	 * @return integer 统计数
	 */
	public function countUnreadByListParam( $operation, $uid = 0, $fid = 0, $archiveId = 0, $subOp = '' ) {
		$param = $this->getListParam( $operation, $uid, $fid, $archiveId, true, $subOp );
		return $this->countListParam( $param );
	}

	/**
	 * 根据列表查询参数统计总数
	 * @param string $operation 列表动作
	 * @param integer $uid 用户ID
	 * @param integer $fid 文件夹ID
	 * @param integer $archiveId 存档表ID
	 * @return integer 统计数
	 */
	public function countByListParam( $operation, $uid = 0, $fid = 0, $archiveId = 0, $subOp = '' ) {
		$param = $this->getListParam( $operation, $uid, $fid, $archiveId, false, $subOp );
		return $this->countListParam( $param );
	}

	/**
	 * 执行查询列表参数操作
	 * @param array $param 列表查询参数
	 * @return integer
	 */
	private function countListParam( $param ) {
		if ( empty( $param['field'] ) ) {
			$param['field'] = 'emailid';
		}
		if ( empty( $param['order'] ) ) {
			$param['order'] = "eb.sendtime DESC";
		}
		$sql = "SELECT COUNT(%s) as count FROM %s WHERE %s";
		if ( !empty( $param['group'] ) ) {
			$sql .= ' GROUP BY ' . $param['group'];
		}
		$result = IBOS::app()->db->createCommand()
				->setText( sprintf( $sql, $param['field'], $param['table'], $param['condition'] ) )
				->queryAll();
		//含有gourp by的分组统计返回一个多维数组，每个数组含有每个分组的条数（邮件的主体对应多少封邮件）
		//但是我们只需要知道有多少个多维数组（邮件主体）就可以了
		//而不含有group by的统计只返回一个包含所有主体数的邮件，所以这两种情况要分开处理
		$count = count( $result ) == 1 ? $result[0]['count'] : count( $result );
		return intval( $count );
	}

	/**
	 * 获取列表查询参数
	 * @param string $operation 列表动作
	 * @param integer $uid 用户ID
	 * @param integer $fid 文件夹ID
	 * @param integer $archiveId 存档表ID
	 * @return array 列表查询参数数组
	 */
	public function getListParam( $operation, $uid = 0, $fid = 0, $archiveId = 0, $getUnread = false, $subOp = '' ) {
		if ( !$uid ) {
			$uid = IBOS::app()->user->uid;
		}
		$mainTable = $this->getTableName( $archiveId );
		$bodyTable = EmailBody::model()->getTableName( $archiveId );
		$param = array(
			'field' => '',
			'table' => "{{{$mainTable}}} e LEFT JOIN {{{$bodyTable}}} eb on e.bodyid = eb.bodyid",
			'condition' => $getUnread ? 'e.isread = 0 AND ' : '',
			'order' => '',
			'group' => ''
		);
		switch ( $operation ) {
			case 'inbox': // 收件箱
				$param['condition'] .= "e.toid ='{$uid}' AND e.fid ='1' AND e.isdel ='0' AND e.isweb = '0'";
				break;
			case 'todo': // 待办邮件
				$param['condition'] .= "e.toid ='{$uid}' AND e.isdel = 0 AND e.ismark = 1";
				break;
			case 'draft':// 草稿箱
				$param['field'] = '*';
				$param['table'] = "{{{$bodyTable}}} eb";
				$param['condition'] = "eb.fromid = '{$uid}' AND eb.issend != 1";
				break;
			case 'send': // 发件箱
				$param['condition'] = "eb.fromid = '{$uid}' AND eb.issend = 1 AND e.fid = 1 AND eb.issenderdel != 1";
				$param['group'] = 'eb.bodyid';
				break;
			case 'archive':// 存档邮件
				if ( $archiveId && $subOp ) {
					//存在归档表
					if ( $subOp == 'in' ) {
						$param['condition'] .= "e.toid ='{$uid}' AND e.fid = 1 AND e.isdel = 0";
					} elseif ( $subOp == 'send' ) {
						$param['field'] = "*";
						$param['group'] = 'eb.bodyid';
						$param['condition'] .= "eb.fromid = '{$uid}' AND eb.issend = 1 AND e.fid = 1 AND eb.issenderdel != 1";
					}
					break;
				}
			case 'del': // 已删除
				$param['condition'] .= "e.toid ='{$uid}' AND (e.isdel = 3 OR e.isdel = 4 OR e.isdel = 1)";
				break;
			case 'folder': // 个人文件夹
				if ( $fid ) {
					$param['condition'] .= "(e.toid='{$uid}' OR eb.fromid='{$uid}') AND e.fid = {$fid} AND e.isdel !=3";
					break;
				}
			case 'web' :
				$param['condition'] .= "e.toid ='{$uid}' AND e.isdel =0 AND eb.issend = 1 AND e.isweb = 1";
				break;
			default:
				$param['condition'] .= '1=2';
				break;
		}
		return $param;
	}

	/**
	 * debug
	 * @param array $emailids
	 * @param integer $source
	 * @param integer $target
	 * @return boolean|integer
	 */
	public function moveByBodyId( $emailids, $source, $target ) {
		$source = intval( $source );
		$target = intval( $target );
		if ( $source != $target ) {
			$db = IBOS::app()->db->createCommand();
			$text = sprintf( "REPLACE INTO {{%s}} SELECT * FROM {{%s}} WHERE bodyid IN ('%s')", $this->getTableName( $target ), $this->getTableName( $source ), implode( ',', $emailids ) );
			$db->setText( $text )->execute();
			return $db->delete( $this->getTableName( $source ), "FIND_IN_SET(bodyid,'" . implode( ',', $emailids ) . ")" );
		} else {
			return false;
		}
	}

	/**
	 * 获取所有存档表的id
	 * @return array
	 */
	public function fetchTableIds() {
		$tableIds = array( '0' => 0 );
		$name = $this->getTableSchema()->name;
		$tables = IBOS::app()->db->createCommand()
				->setText( "SHOW TABLES LIKE '" . str_replace( '_', '\_', $this->tableName() . '_%' ) . "'" )
				->queryAll( false );
		foreach ( $tables as $table ) {
			$tableName = $table[0];
			preg_match( '/^' . $name . '_([\d])+$/', $tableName, $match );
			if ( empty( $match[1] ) ) {
				continue;
			} else {
				$tableId = intval( $match[1] );
			}
			$tableIds[$tableId] = $tableId;
		}
		return $tableIds;
	}

	/**
	 * 
	 * @param type $conditions
	 * @return type
	 */
	public function getSplitSearchContdition( $conditions ) {
		$whereArr = array();
		if ( !empty( $conditions['emailidmin'] ) ) {
			$whereArr[] = 'e.emailid >= ' . $conditions['emailidmin'];
		}
		if ( !empty( $conditions['emailidmax'] ) ) {
			$whereArr[] = 'e.emailid <= ' . $conditions['emailidmax'];
		}
		if ( !empty( $conditions['timerange'] ) ) {
			// 计算时间（几个月以前）
			$timeRange = TIMESTAMP - (intval( $conditions['timerange'] ) * 86400 * 30);
			$whereArr[] = 'b.sendtime <= ' . $timeRange;
		}
		$whereSql = !empty( $whereArr ) && is_array( $whereArr ) ? implode( ' AND ', $whereArr ) : '';
		return $whereSql;
	}

	/**
	 * 统计分表存档时的数据条数
	 * @param integer $tableId 存档表ID
	 * @param string $conditions 附加条件
	 * @return integer 统计数目
	 */
	public function countBySplitCondition( $tableId, $conditions = '' ) {
		$condition = $this->mergeSplitCondition( $conditions );
		$db = IBOS::app()->db->createCommand();
		$count = $db->select( 'COUNT(*)' )
				->from( '{{' . $this->getTableName( $tableId ) . '}} e' )
				->rightJoin( '{{' . EmailBody::model()->getTableName( $tableId ) . '}} b', 'e.`bodyid` = b.`bodyid`' )
				->where( $condition )
				->queryScalar();
		return intval( $count );
	}

	/**
	 * 查找分表存档的数据列表
	 * @param integer $tableId 存档表ID
	 * @param string $conditions 附加条件
	 * @param integer $offset 页数
	 * @param integer $limit 每页多少条
	 * @return array 列表数据
	 */
	public function fetchAllBySplitCondition( $tableId, $conditions = '', $offset = null, $limit = null ) {
		$condition = $this->mergeSplitCondition( $conditions );
		$db = IBOS::app()->db->createCommand();
		$list = $db->select( 'e.emailid,b.fromid,b.subject,b.sendtime,b.bodyid' )
				->from( '{{' . $this->getTableName( $tableId ) . '}} e' )
				->rightJoin( '{{' . EmailBody::model()->getTableName( $tableId ) . '}} b', 'e.`bodyid` = b.`bodyid`' )
				->where( $condition )
				->order( 'sendtime ASC' )
				->offset( $offset )
				->limit( $limit )
				->queryAll();
		return $list;
	}

	/**
	 * 根据存档表id获取存档表名
	 * @param integer $tableId 存档表id
	 * @return string
	 */
	public function getTableName( $tableId = 0 ) {
		$tableId = intval( $tableId );
		return $tableId > 0 ? "email_{$tableId}" : 'email';
	}

	/**
	 * 获取指定存档表的状态
	 * @param integer $tableId 存档表id
	 * @return array
	 */
	public function getTableStatus( $tableId = 0 ) {
		return Database::getTableStatus( $this->getTableName( $tableId ) );
	}

	/**
	 * 删除表
	 * @param integer $tableId 存档表id
	 * @param boolean $force 强制删除
	 * @return boolean 删除成功与否
	 */
	public function dropTable( $tableId, $force = false ) {
		$tableId = intval( $tableId );
		if ( $tableId ) {
			$rel = Database::dropTable( $this->getTableName( $tableId ), $force );
			if ( $rel === 1 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 创建一个表
	 * @param integer $maxTableId
	 * @return boolean
	 */
	public function createTable( $maxTableId ) {
		if ( $maxTableId ) {
			return Database::cloneTable( $this->getTableName(), $this->getTableName( $maxTableId ) );
		} else {
			return false;
		}
	}

	/**
	 * 私有方法，合并存档分表的查询条件，返回组合后的条件
	 * @param string $conditions
	 * @return string
	 */
	private function mergeSplitCondition( $conditions = '' ) {
		$conditions .= strpos( $conditions, 'WHERE' ) ? ' AND' : '';
		//附加公共条件，待办邮件和未读邮件不能移动
		$conditions .= ' e.`ismark`=0 AND e.`isread`=1 AND b.`bodyid` IS NOT NULL';
		//附加子条件
		$addition = array();
		$addition[] = 'e.`fid` = 1 AND e.`isdel` = 0';  //收件箱
		$addition[] = 'e.`fid` = 1 AND b.`issend` = 1 AND b.`issenderdel` != 1'; //已发送
		$addition[] = 'b.`issend` = 1 AND b.`issenderdel` != 1 AND b.`towebmail`!=\'\''; //外发邮件
		//连接条件
		$conditions .= ' AND ((' . implode( ') OR (', $addition ) . '))';
		return $conditions;
	}

	/**
	 * 根据查询条件获取邻近的邮件数据
	 * @param string $condition
	 * @param integer $archiveId
	 * @return array
	 */
	private function getSiblingsByCondition( $condition, $order, $archiveId = 0 ) {
		$siblings = IBOS::app()->db->createCommand()
				->select( 'e.emailid,eb.subject' )
				->from( sprintf( '{{%s}} e', $this->getTableName( $archiveId ) ) )
				->leftJoin( sprintf( '{{%s}} eb', EmailBody::model()->getTableName( $archiveId ) ), 'e.bodyid = eb.bodyid' )
				->where( $condition )
				->order( $order )
				->limit( 1 )
				->queryRow();
		return $siblings ? $siblings : array();
	}

}
