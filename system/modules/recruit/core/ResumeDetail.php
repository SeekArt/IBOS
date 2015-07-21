<?php

/**
 * 招聘模块------ICResumeDetail类
 *
 * @link http://www.ibos.com.cn/
 * @copyright Copyright &copy; 2008-2013 IBOS Inc
 * @author gzwwb <gzwwb@ibos.com.cn>
 */
/**
 * 招聘模块------ICResumeDetail类
 * @package application.modules.recruit.core
 * @version $Id: ResumeDetail.php 5175 2015-06-17 13:25:24Z Aeolus $
 * @author gzwwb <gzwwb@ibos.com.cn>
 */

namespace application\modules\recruit\core;

use application\core\utils\IBOS;
use application\core\utils\String;
use application\modules\position\utils\Position;
use application\modules\recruit\model\Resume;

class ResumeDetail {

    /**
     * 处理resume/add页面发过来的表单数据
     */
    public static function processAddRequestData() {
        $fieldArr = array(
            'avatarid' => '',
            'realname' => '',
            'gender' => 0,
            'birthday' => 0,
            'birthplace' => '',
            'workyears' => '',
            'education' => '',
            'residecity' => '',
            'zipcode' => '',
            'idcard' => '',
            'height' => '',
            'weight' => '',
            'maritalstatus' => 0,
            'mobile' => '',
            'email' => '',
            'telephone' => '',
            'qq' => '',
            'msn' => '',
            'beginworkday' => '',
            'positionid' => 0,
            'expectsalary' => '',
            'workplace' => '',
            'recchannel' => '',
            'workexperience' => '',
            'projectexperience' => '',
            'eduexperience' => '',
            'langskill' => '',
            'computerskill' => '',
            'professionskill' => '',
            'trainexperience' => '',
            'selfevaluation' => '',
            'relevantcertificates' => '',
            'socialpractice' => '',
            'status' => 0,
            'attachmentid' => ''
        );
        foreach ( $_POST as $key => $value ) {
            if ( in_array( $key, array_keys( $fieldArr ) ) ) {
                $fieldArr[$key] = $value;
            }
        }
        $fieldArr['positionid'] = implode( ',', String::getId( $fieldArr['positionid'] ) );
        return $fieldArr;
    }

    /**
     * 处理resume/list页面要显示的数据
     * @param type $resumeList
     * @return array
     */
    public static function processListData( $resumeList ) {
        $position = Position::loadPosition();
        foreach ( $resumeList as $k => $resume ) {
            $resumeList[$k]['age'] = self::handleAge( $resume['birthday'] );
            $resumeList[$k]['gender'] = self::handleGender( $resume['gender'] );
            $resumeList[$k]['workyears'] = self::handleWorkyears( $resume['workyears'] );
            $resumeList[$k]['status'] = self::handleResumeStatus( $resume['status'] );
            $resumeList[$k]['education'] = self::handleEdu( $resume['education'] );
            $resumeList[$k]['targetposition'] = isset( $position[$resume['positionid']] ) ? $position[$resume['positionid']]['posname'] : '';
        }
        return $resumeList;
    }

    /**
     * 简历详细页面数据输出处理
     * @param array $resumeDetail  简历
     * @return array  处理过后的简历数组
     */
    public static function processShowData( $resumeDetail ) {
        $position = Position::loadPosition();
        $resumeDetail['targetposition'] = isset( $position[$resumeDetail['positionid']] ) ? $position[$resumeDetail['positionid']]['posname'] : '';
        $resumeDetail['age'] = self::handleAge( $resumeDetail['birthday'] );
        $resumeDetail['gender'] = self::handleGender( $resumeDetail['gender'] );
        $resumeDetail['workyears'] = self::handleWorkyears( $resumeDetail['workyears'] );
        $resumeDetail['education'] = self::handleEdu( $resumeDetail['education'] );
        $resumeDetail['maritalstatus'] = self::handleMaritalstatus( $resumeDetail['maritalstatus'] );
        $resumeDetail['status'] = Resume::model()->fetchStatusByResumeid( $resumeDetail['resumeid'] );
        return $resumeDetail;
    }

    /**
     * 年龄输出处理
     * @param  $birthday
     * @return type
     */
    public static function handleAge( $birthday ) {
        if ( $birthday == 0 ) {
            $age = IBOS::lang( 'Unknown' );
        } else {
            $age = intval( date( 'Y', time() ) ) - intval( date( 'Y', $birthday ) );
        }
        return $age;
    }

    /**
     * 性别输出处理
     * @param int $gender 性别，1男，2女，0不详
     * @return string 返回性别
     */
    public static function handleGender( $gender ) {
        $sex = IBOS::lang( 'Unknown' );
        if ( $gender == 1 ) {
            $sex = IBOS::lang( 'Male' );
        } else if ( $gender == 2 ) {
            $sex = IBOS::lang( 'Female' );
        }
        return $sex;
    }

    /**
     * 工作年限处理输出
     * @param int $education 学历 0、1、2、3、5、10或者空
     * @return string 返回工作年限
     */
    public static function handleWorkyears( $workyears ) {
        $workyearsArr = array(
            'empty' => IBOS::lang( 'Unknown' ),
            '0' => IBOS::lang( 'Graduates' ),
            '1' => IBOS::lang( 'More than one year' ),
            '2' => IBOS::lang( 'More than two years' ),
            '3' => IBOS::lang( 'More than three years' ),
            '5' => IBOS::lang( 'More than five years' ),
            '10' => IBOS::lang( 'More than a decade' )
        );
        if ( in_array( $workyears, array_keys( $workyearsArr ) ) ) {
            $year = $workyearsArr[$workyears];
        } else {
            $year = IBOS::lang( 'Unknown' );
        }
        return $year;
    }

    /**
     * 教育经历输出处理
     * @param string $education 教育经历
     * @return string 返回教育经历
     */
    public static function handleEdu( $education ) {
        $eduArr = array(
            'EMPTY' => IBOS::lang( 'Unknown' ),
            'JUNIOR_HIGH' => IBOS::lang( 'Junior high school' ),
            'SENIOR_HIGH' => IBOS::lang( 'Senior middle school' ),
            'TECHNICAL_SECONDARY' => IBOS::lang( 'Secondary' ),
            'COLLEGE' => IBOS::lang( 'College' ),
            'BACHELOR_DEGREE' => IBOS::lang( 'Undergraduate course' ),
            'MASTER' => IBOS::lang( 'Master' ),
            'DOCTOR' => IBOS::lang( 'Doctor' )
        );
        if ( in_array( $education, array_keys( $eduArr ) ) ) {
            $edu = $eduArr[$education];
        } else {
            $edu = IBOS::lang( 'Unknown' );
        }
        return $edu;
    }

    /**
     *  婚姻状态显示数据
     * @param int $marriage
     * @return string
     */
    public static function handleMaritalstatus( $marriage ) {
        $marry = IBOS::lang( 'Unknown' );
        if ( $marriage == 0 ) {
            $marry = IBOS::lang( 'Unmarried' );
        } else if ( $marriage == 1 ) {
            $marry = IBOS::lang( 'Married' );
        }
        return $marry;
    }

    /**
     *  简历状态显示数据
     * @param int $status
     * @return type
     */
    public static function handleResumeStatus( $status ) {
        $statusArr = array(
            0 => '-',
            1 => IBOS::lang( 'Interview center' ),
            2 => IBOS::lang( 'Hire' ),
            3 => IBOS::lang( 'Entry' ),
            4 => IBOS::lang( 'To be arranged' ),
            5 => IBOS::lang( 'Eliminate' )
        );
        return $statusArr[$status];
    }

}
