<?php

namespace application\modules\dashboard\controllers;

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\core\utils\Cache;
use application\modules\main\model\Setting;
use application\modules\dashboard\model\Credit;
use application\modules\dashboard\model\CreditRule;
use application\modules\dashboard\utils\Dashboard as DashboardUtil;

class CreditController extends BaseController {

    const MAX_RULE_ID = 5; // 积分允许的最大规则数

    /**
     * 积分设置
     * @return void 
     */

    public function actionSetup() {
        $operation = Env::getRequest( 'op' );
        if ( !empty( $operation ) && in_array( $operation, array( 'add', 'del' ) ) ) {
            $method = $operation . 'Credit';
            if ( method_exists( $this, $method ) ) {
                return $this->$method();
            }
        }
        $formSubmit = Env::submitCheck( 'creditSetupSubmit' );
        if ( $formSubmit ) {
            // 积分变动提示
            $changeRemind = isset( $_POST['changeRemind'] ) ? 1 : 0;
            Setting::model()->updateSettingValueByKey( 'creditremind', $changeRemind );
            // 积分条目
            $credits = $_POST['credit'];
            foreach ( $credits as $cid => $credit ) {
                if ( isset( $credit['enable'] ) ) {
                    $credit['enable'] = 1;
                } else {
                    $credit['enable'] = 0;
                }
                Credit::model()->modify( $cid, $credit );
            }
            Cache::update( array( 'setting' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $credits = Credit::model()->fetchAll();
            $data = array(
                // 积分数据
                'data' => $credits,
                // 当前积分条目最大数
                'curentMaxId' => Credit::model()->getMaxId( 'cid' ),
                // 允许的积分条目最大数
                'maxId' => self::MAX_RULE_ID,
				// 积分变动提示
				'changeRemind' => IBOS::app()->setting->get( 'setting/creditremind' )
            );
            $this->render( 'setup', $data );
        }
    }

    /**
     * 积分公式
     */
    public function actionFormula() {
        $formSubmit = Env::submitCheck( 'creditSetupSubmit' );
        if ( $formSubmit ) {
            // 积分公式
            $formula = $_POST['creditsFormula'];
            $formulaCheckCorrect = DashboardUtil::checkFormulaCredits( $formula );
            if ( $formulaCheckCorrect ) {
                Setting::model()->updateSettingValueByKey( 'creditsformula', $formula );
            } else {
                $this->error( IBOS::lang( 'Credits formula invalid' ) );
            }
            // 积分表达式
            $formulaExp = $_POST['creditsFormulaExp'];
            Setting::model()->updateSettingValueByKey( 'creditsformulaexp', $formulaExp );
            Cache::update( array( 'setting' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $credits = Credit::model()->fetchAll();
            $data = array(
                // 积分数据
                'data' => $credits,
                // 积分公式
                'creditsFormula' => Setting::model()->fetchSettingValueByKey( 'creditsformula' ),
                // 积分表达式
                'creditFormulaExp' => Setting::model()->fetchSettingValueByKey( 'creditsformulaexp' )
            );
            $this->render( 'formula', $data );
        }
    }

    /**
     * 积分策略
     * @return void 
     */
    public function actionRule() {
        $formSubmit = Env::submitCheck( 'creditRuleSubmit' );
        if ( $formSubmit ) {
            // 接收表单，共三个部分
            $cycles = $_POST['cycles'];
            $credits = $_POST['credits'];
            $rewardNums = $_POST['rewardnums'];
            // 处理更新条件
            $rulesParam = array();
            // 奖励周期
            foreach ( $cycles as $ruleId => $cycle ) {
                $rulesParam[$ruleId]['cycletype'] = $cycle;
            }
            // 奖励积分
            foreach ( $credits as $ruleId => $credit ) {
                foreach ( $credit as $extcreditOffset => $creditValue ) {
                    $rulesParam[$ruleId]['extcredits' . $extcreditOffset] = $creditValue;
                }
            }
            // 奖励次数
            foreach ( $rewardNums as $ruleId => $rewardNum ) {
                $rulesParam[$ruleId]['rewardnum'] = $rewardNum;
            }
            // 更新
            foreach ( $rulesParam as $ruleId => $updateValue ) {
                CreditRule::model()->modify( $ruleId, $updateValue );
            }
            Cache::update( array( 'creditRule' ) );
            $this->success( IBOS::lang( 'Save succeed', 'message' ) );
        } else {
            $rules = CreditRule::model()->fetchAll();
            $credits = Credit::model()->fetchAll();
            $data = array(
                'rules' => $rules,
                'credits' => $credits,
            );
            $this->render( 'rule', $data );
        }
    }

    /**
     * 扩展积分添加
     * @return void 
     */
    private function addCredit() {
        if ( IBOS::app()->getRequest()->getIsAjaxRequest() ) {
            $attributes = array(
                'name' => Env::getRequest( 'name' ),
                'initial' => Env::getRequest( 'initial' ),
                'lower' => Env::getRequest( 'lower' ),
                'enable' => Env::getRequest( 'enable' )
            );
            $newId = Credit::model()->add( $attributes, true );
            if ( $newId ) {
                $return = array( 'id' => $newId, 'IsSuccess' => true );
                $this->ajaxReturn( $return );
            }
        }
    }

    /**
     * 删除扩展积分
     * @return void
     */
    private function delCredit() {
        if ( IBOS::app()->getRequest()->getIsAjaxRequest() ) {
            $id = Env::getRequest( 'id' );
            $affected = Credit::model()->deleteByPk( $id );
            if ( $affected ) {
                $this->ajaxReturn( array( 'IsSuccess' => true ) );
            }
        }
    }

}
