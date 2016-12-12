<?php

/**
 * 招聘模块------后台控制器文件
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------后台控制器，继承DashboardBaseController
 * @package application.modules.recruit.components
 * @version $Id$
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\controllers;

use application\core\model\Regular;
use application\core\utils\Cache;
use application\core\utils\Ibos;
use application\modules\dashboard\controllers\BaseController;
use application\modules\main\model\Setting;

class DashboardController extends BaseController
{

    public function getAssetUrl($module = '')
    {
        $module = 'dashboard';
        return Ibos::app()->assetManager->getAssetsUrl($module);
    }

    /**
     * 去首页
     */
    public function actionIndex()
    {
        //取得所有配置
        $config = Ibos::app()->setting->get('setting/recruitconfig');
        $result = array();
        $allFieldRuleType = Regular::model()->fetchAllFieldRuleType();
//        echo '<pre>';print_r($result['regular']);die;
        foreach ($config as $configName => $configValue) {
            list($visi, $fieldRule) = explode(',', $configValue);
            $result[$configName]['visi'] = $visi;
            $result[$configName]['fieldrule'] = $fieldRule;
            if (in_array($fieldRule, $allFieldRuleType)) {
                $regular = Regular::model()->fetchFieldRuleByType($fieldRule);
            } else if ($fieldRule == 'notrequirement') {
                $regular['type'] = 'notrequirement';
                $regular['desc'] = Ibos::lang('Not requirement');
            } else {
                $regular['type'] = $regular['desc'] = $fieldRule;
            }
            $result[$configName]['regulartype'] = $regular['type'];
            $result[$configName]['regulardesc'] = $regular['desc'];
        }
        //给“无要求”加一个数组，给页面读取
        $notRequirementRegulars = array(array('type' => 'notrequirement', 'desc' => Ibos::lang('Not requirement')));
        //系统规则
        $sysRegulars = Regular::model()->fetchAll();
        //将无要求和系统规则合并成新数组
        $result['regular'] = array_merge($notRequirementRegulars, $sysRegulars);

        $this->render('index', array('config' => $result));
    }

    /**
     * 修改后台配置
     * @return void
     */
    public function actionUpdate()
    {
        $fieldArr = array(
            //基本信息
            'recruitrealname' => 'recruitrealname',
            'recruitsex' => 'recruitsex',
            'recruitbirthday' => 'recruitbirthday',
            'recruitbirthplace' => 'recruitbirthplace',
            'recruitworkyears' => 'recruitworkyears',
            'recruiteducation' => 'recruiteducation',
            'recruitstatus' => 'recruitstatus',
            'recruitidcard' => 'recruitidcard',
            'recruitheight' => 'recruitheight',
            'recruitweight' => 'recruitweight',
            'recruitmaritalstatus' => 'recruitmaritalstatus',
            //联系方式
            'recruitresidecity' => 'recruitresidecity',
            'recruitzipcode' => 'recruitzipcode',
            'recruitmobile' => 'recruitmobile',
            'rucruitemail' => 'rucruitemail',
            'recruittelephone' => 'recruittelephone',
            'recruitqq' => 'recruitqq',
            'recruitmsn' => 'recruitmsn',
            //求职意向
            'recruitbeginworkday' => 'recruitbeginworkday',
            'recruittargetposition' => 'recruittargetposition',
            'recruitexpectsalary' => 'recruitexpectsalary',
            'recruitworkplace' => 'recruitworkplace',
            //详细信息
            'recruitrecchannel' => 'recruitrecchannel',
            'recruitworkexperience' => 'recruitworkexperience',
            'recruitprojectexperience' => 'recruitprojectexperience',
            'recruiteduexperience' => 'recruiteduexperience',
            'recruitlangskill' => 'recruitlangskill',
            'recruitcomputerskill' => 'recruitcomputerskill',
            'recruitprofessionskill' => 'recruitprofessionskill',
            'recruittrainexperience' => 'recruittrainexperience',
            'recruitselfevaluation' => 'recruitselfevaluation',
            'recruitrelevantcertificates' => 'recruitrelevantcertificates',
            'recruitsocialpractice' => 'recruitsocialpractice'
        );

        $data = array();
        foreach ($_POST as $key => $value) {
            if (in_array($key, $fieldArr)) {
                $data[$key] = $value;
                unset($fieldArr[$key]);
            }
            $data[$key]['visi'] = isset($data[$key]['visi']) ? $data[$key]['visi'] : 0;
            $data[$key]['fieldrule'] = isset($data[$key]['fieldrule']) ? $data[$key]['fieldrule'] : 'notrequirement';
            if ($data[$key]['fieldrule'] == '')
                $data[$key]['fieldrule'] = 'notrequirement';
            $data[$key] = $data[$key]['visi'] . ',' . $data[$key]['fieldrule'];
        }
        foreach ($fieldArr as $field) {
            $data[$field] = '0,notrequirement';
        }
        //修改数据库setting
        Setting::model()->updateSettingValueByKey('recruitconfig', $data);
        Cache::update('setting');
        $this->success(Ibos::lang('Update succeed', 'message'), $this->createUrl('dashboard/index'));
    }

}
