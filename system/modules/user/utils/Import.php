<?php

namespace application\modules\user\utils;

use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\StringUtil;
use application\modules\main\utils\ImportInterface;
use application\modules\main\utils\ImportParent;

/**
 * Description
 *
 * @namespace application\modules\user\utils
 * @filename Import.php
 * @encoding UTF-8
 * @author mumu <2317216477@qq.com>
 * @link https://www.ibos.com.cn
 * @copyright Copyright &copy; 2012-2015 IBOS Inc
 * @datetime 2016-3-24 17:06:34
 * @version $Id: Import.php 7750 2016-08-03 09:26:25Z tanghang $
 */
class Import extends ImportParent implements ImportInterface {

	public function __construct( $tpl ) {
		parent::__construct( $tpl );
	}

	public function table() {
		$table = array(
			'user' => array(
				'u' => '{{user}}',
				'up' => '{{user_profile}}',
				'p' => '{{position}}',
				'd' => '{{department}}',
				'r' => '{{role}}',
			),
		);
		return parent::returnArray( $table );
	}

	public function pk() {
		$pk = array(
			'user' => array(
				'u' => 'uid',
				'up' => 'uid',
				'p' => 'positionid',
				'd' => 'deptid',
				'r' => 'roleid',
			),
		);
		return parent::returnArray( $pk );
	}

	public function rules() {
		$rules = array(
			'user' => array(
				array( array( 'u.mobile' ), array( 'mobile', 'unique', 'required', ), ),
				array( array( 'u.email' ), array( 'email', 'unique', ), ),
				array( array( 'u.username' ), array( 'unique', ), ),
				array( array( 'u.weixin' ), array( 'unique', ), ),
				array( array( 'u.jobnumber' ), array( 'unique', ), ),
				array( array( 'up.birthday' ), array( 'datetime' ) ),
			),
		);
		return parent::returnArray( $rules );
	}

//    public function format() {
//        $format = array(
//            'user' => array(
//                array( array( 'up.birthday' ), array( 'strtotime' ), ),
//                array( array( 'u.gender' ), array( 'formatGender' ), ),
////                array( array( 'stringRandom', 'add' ), array( 'u.salt' ) ),
////                array( array( 'md5Salt' ), array( 'u.password' ) ),
////                array( array( 'createGuid' ), array( 'u.guid' ) ),
////                array( array( 'createtime' ), array( 'u.createtime' ) ),
//            ),
//        );
//        return parent::returnArray( $format );
//    }

	public function field() {
		$field = array(
			'user' => array(
				'手机号' => 'u.mobile',
				'密码' => 'u.password',
				'真实姓名' => 'u.realname',
				'性别' => 'u.gender',
				'邮箱' => 'u.email',
				'微信号' => 'u.weixin',
				'工号' => 'u.jobnumber',
				'用户名' => 'u.username',
				'生日' => 'up.birthday',
				'住宅电话' => 'up.telephone',
				'地址' => 'up.address',
				'QQ' => 'up.qq',
				'自我介绍' => 'up.bio',
				'岗位' => 'p.posname',
				'部门' => 'd.deptname',
				'角色' => 'r.rolename',
			),
		);
		return parent::returnArray( $field );
	}

	public function config() {
		$config = array(
			'user' => array(
				'module' => 'user',
				'type' => 'common',
				'name' => '用户导入模版',
				'filename' => 'user_import.xls',
				'fieldline' => 1,
				'line' => 1,
			),
		);
		return parent::returnArray( $config );
	}

	protected function force() {
		$table = array(
			'user' => array(
				'{{user_profile}}'
			),
		);
		return parent::returnArray( $table );
	}

	public function import() {
		return parent::import();
	}

	protected function start() {
		parent::start();
		$deptArray = Ibos::app()->db->createCommand()
				->select( 'deptid,deptname,pid' )
				->from( '{{department}}' )
				->queryAll();
		$roleArray = Ibos::app()->db->createCommand()
				->select( 'roleid,rolename' )
				->from( '{{role}}' )
				->queryAll();
		$positionArray = Ibos::app()->db->createCommand()
				->select( 'posname,positionid' )
				->from( '{{position}}' )
				->queryAll();
		$arrayD = $arrayR = $arrayP = array();
		if ( !empty( $deptArray ) ) {
			foreach ( $deptArray as $dept ) {
				$arrayD[$dept['pid']][] = $dept;
			}
		}
		if ( !empty( $roleArray ) ) {
			foreach ( $roleArray as $role ) {
				$arrayR[$role['rolename']] = $role['roleid'];
			}
		}
		if ( !empty( $positionArray ) ) {
			foreach ( $positionArray as $position ) {
				$arrayP[$position['posname']] = $position['positionid'];
			}
		}
		$this->session->add( 'import_userTpl_deptArray', $arrayD );
		$this->session->add( 'import_userTpl_roleArray', $arrayR );
		$this->session->add( 'import_userTpl_positionArray', $arrayP );
	}

	protected function end() {
		parent::end();
		Cache::update( array( 'Position', 'Role' ) );
		Org::update();
		$this->session->remove( 'import_userTpl_deptArray' );
		$this->session->remove( 'import_userTpl_roleArray' );
		$this->session->remove( 'import_userTpl_positionArray' );
	}

	protected function beforeFormatData( &$data ) {
		foreach ( $data as &$row ) {
			$row['u.gender'] = trim( $row['u.gender'] );
			$row['up.birthday'] = trim( $row['up.birthday'] );
			$deptExplode = array_filter( explode( '/', $row['d.deptname'] ) );
			$deptid = $this->findDept( $deptExplode );
			$roleid = $this->findRole( $row['r.rolename'] );
			$positionid = $this->findPosition( $row['p.posname'] );
			$row['d.deptid'] = $deptid;
			$row['r.roleid'] = $roleid;
			$row['p.positionid'] = $positionid;
		}
	}

	//键为角色名，值为roleid
	private function findRole( $roleName ) {
		if ( !empty( $roleName ) ) {
			$roleArray = $this->session->get( 'import_userTpl_roleArray' );
			if ( !empty( $roleArray[$roleName] ) ) {
				return $roleArray[$roleName];
			} else {
				Ibos::app()->db->createCommand()
						->insert( '{{role}}', array(
							'rolename' => $roleName,
						) );
				$findRoleid = Ibos::app()->db->getLastInsertID();
				$roleArray[$roleName] = $findRoleid;
				$this->session->add( 'import_userTpl_roleArray', $roleArray );
				return $findRoleid;
			}
		}
	}

	//键为岗位名，值为positionid
	private function findPosition( $positionName ) {
		if ( !empty( $positionName ) ) {
			$positionArray = $this->session->get( 'import_userTpl_positionArray' );
			if ( !empty( $positionArray[$positionName] ) ) {
				return $positionArray[$positionName];
			} else {
				Ibos::app()->db->createCommand()
						->insert( '{{position}}', array(
							'posname' => $positionName,
						) );
				$findpositionid = Ibos::app()->db->getLastInsertID();
				$positionArray[$positionName] = $findpositionid;
				$this->session->add( 'import_userTpl_positionArray', $positionArray );
				return $findpositionid;
			}
		}
	}

	//键为pid，值为索引数组，值有部门名，部门id，pid
	private function findDept( $deptExplode, $pid = 0 ) {
		if ( !empty( $deptExplode ) ) {
			$deptArray = $this->session->get( 'import_userTpl_deptArray' );
			$deptname = array_shift( $deptExplode );
			$deptnameArray = array();
			$findDeptid = NULL;
			if ( !empty( $deptArray[$pid] ) ) {
				foreach ( $deptArray[$pid] as $row ) {
					if ( $row['deptname'] == $deptname ) {
						$findDeptid = $row['deptid'];
					}
				}
			}
			if ( NULL === $findDeptid ) {
				Ibos::app()->db->createCommand()
						->insert( '{{department}}', array(
							'pid' => $pid,
							'deptname' => $deptname,
						) );
				$findDeptid = Ibos::app()->db->getLastInsertID();
				$deptArray[$pid][] = array(
					'pid' => $pid,
					'deptname' => $deptname,
					'deptid' => $findDeptid,
				);
				$this->session->add( 'import_userTpl_deptArray', $deptArray );
			}
			if ( !empty( $deptExplode ) ) {
				return $this->findDept( $deptExplode, $findDeptid );
			} else {
				return $findDeptid;
			}
		} else {
			return 0;
		}
	}

	protected function formatData( &$data, $isInsert ) {
		$data['u.gender'] = $data['u.gender'] == '男' ? 1 : 0;
		$data['up.birthday'] = !empty( $data['up.birthday'] ) ? strtotime( $data['up.birthday'] ) : 0;
		$data['up.uid'] = function($data) {
			return $data['u.uid'];
		};
		if ( !empty( $data['d.deptid'] ) ) {
			$data['u.deptid'] = $data['d.deptid'];
		}
		if ( !empty( $data['p.positionid'] ) ) {
			$data['u.positionid'] = function($data) {
				return $data['p.positionid'];
			};
		}
		if ( !empty( $data['r.roleid'] ) ) {
			$data['u.roleid'] = function($data) {
				return $data['r.roleid'];
			};
		}
		if ( $isInsert ) {
			$salt = StringUtil::random( 6 );
			$data['u.salt'] = $salt;
			if ( empty( $data['u.password'] ) ) {
				//如果密码为空，又是插入，则取手机号后六位
				$data['u.password'] = substr( $data['u.mobile'], -6 );
			}
			$data['u.password'] = md5( md5( $data['u.password'] ) . $salt );
			$data['u.guid'] = StringUtil::createGuid();
			$data['u.createtime'] = TIMESTAMP;
		} else {
			//如果密码不为空，又是更新，才更新密码，否则不做任何处理
			if ( !empty( $data['u.password'] ) ) {
				$data['u.password'] = function($data, $row) {
					return md5( md5( $data['u.password'] ) . $row['u.salt'] );
				};
			}

			$data['u.createtime'] = TIMESTAMP;
		}
	}

	protected function afterHandleData( $connection ) {
		$saveData = $this->import->saveData;
		$ip = Ibos::app()->setting->get( 'clientip' );
		foreach ( $saveData as $i => $data ) {
			$connection->schema->commandBuilder
					->createInsertCommand( '{{user_count}}'
							, array( 'uid' => $data['u.uid'] ) )
					->execute();
			$connection->schema->commandBuilder
					->createInsertCommand( '{{user_status}}'
							, array( 'uid' => $data['u.uid'], 'regip' => $ip, 'lastip' => $ip ) )
					->execute();
		}
	}

}
