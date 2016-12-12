<?php

namespace application\modules\recruit\utils;

/**
 * 一建分析字段配置类
 * 配置格式说明
 * id        字段的唯一标识，不能与其它字段重复
 * field    字段名数组，如果简历中包含这些字段，将会返回该字段的内容，字段越多匹配率越高，但同时也影响其它字段的匹配结果，所以要慎重。字段名不需要考虑字符之间的空格和制表符，即 '求职意向' 同等于 '求    职 意  向'、'求 职 意 向'... 程序会自动作出处理
 * type        特殊字段，特殊字段用于一些有固定规则的字段，提高匹配率，例如邮箱、身份证、手机号等。同一特殊字段的类型只能声明一次，目前已有的特殊字段有： 'name', 'gender', 'email', 'age', 'mobile', 'phone', 'idcard', 'maritalstatus'，默认是'normal'
 *
 */
class AnalysisConfig
{

    public static function getAnalconf()
    {
        $config = array(
            array(
                'id' => 'name',
                'field' => array(
                    '名字', '姓名', '名称'
                ),
                'type' => 'name',
            ),
            array(
                'id' => 'job_objective',
                'field' => array(
                    '求职意向', '应聘', '应聘职位', '申请职位', '应聘岗位', '申请岗位', '目标职能', '期望职业', '期望行业', '目标职能'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'expectsalary',
                'field' => array(
                    '期望月薪', '希望月薪', '期望待遇', '希望待遇', '期望薪水'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'workplace',
                'field' => array(
                    '工作地区', '工作地点'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'beginworkday',
                'field' => array(
                    '上岗时间', '到岗时间'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'gender',
                'field' => array(
                    '性别'
                ),
                'type' => 'gender',
            ),
            array(
                'id' => 'birthday',
                'field' => array(
                    '出生年月', '生日', '出生日期', '出生年份'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'birthplace',
                'field' => array(
                    '籍贯'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'household_register',
                'field' => array(
                    '户籍', '户口'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'height',
                'field' => array(
                    '身高'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'weight',
                'field' => array(
                    '体重'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'residecity',
                'field' => array(
                    '住址', '地址', '家庭住址', '居住地', '居住地址', '现居住于', '目前居住', '目前居住于'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'nation',
                'field' => array(
                    '民族'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'email',
                'field' => array(
                    '邮箱', '邮箱地址', '电子邮箱', '电子邮箱地址'
                ),
                'type' => 'email',
            ),
            array(
                'id' => 'mobile',
                'field' => array(
                    '手机'
                ),
                'type' => 'mobile',
            ),
            array(
                'id' => 'phone',
                'field' => array(
                    '电话', '家庭电话', '座机', '联系电话'
                ),
                'type' => 'phone',
            ),
            array(
                'id' => 'age',
                'field' => array(
                    '年龄', '岁数'
                ),
                'type' => 'age',
            ),
            array(
                'id' => 'zipcode',
                'field' => array(
                    '邮编', '邮政编码'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'idcard',
                'field' => array(
                    '证件', '身份证', '证件号码', '身份证号码', '身份证'
                ),
                'type' => 'idcard',
            ),
            array(
                'id' => 'eduexperience',
                'field' => array(
                    '教育经历'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'education',
                'field' => array(
                    '学历'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'evaluation',
                'field' => array(
                    '自我评价'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'workexperience',
                'field' => array(
                    '个人经历', '工作实习经历', '实习经历', '工作经历', '经历'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'socialpractice',
                'field' => array(
                    '社会实践', '社会经历'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'workyears',
                'field' => array(
                    '工作年限', '工作年龄', '工龄'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'projectexperience',
                'field' => array(
                    '作品', '个人作品', '项目经验成果', '项目经验', '工作经验', '经验', '个人经验', '工作实习经验', '实习经验'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'languageskill',
                'field' => array(
                    '语言能力', '英语水平'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'trainexperience',
                'field' => array(
                    '培训经历', '教育培训'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'relevantcertificates',
                'field' => array(
                    '相关证书', '证书', '证书技能'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'major',
                'field' => array(
                    '专业', '大学专业', '学校专业'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'graduateschool',
                'field' => array(
                    '毕业学校', '毕业学院', '学校', '学院'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'relatedskill',
                'field' => array(
                    '相关技能', '技能', '专长', '技能专长', '专业技能'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'awards',
                'field' => array(
                    '获奖', '获奖情况', '得奖', '荣誉', '获奖经历'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'graduatetime',
                'field' => array(
                    '毕业时间'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'maritalstatus',
                'field' => array(
                    '婚姻状况'
                ),
                'type' => 'maritalstatus',
            ),
            array(
                'id' => 'computerskill',
                'field' => array(
                    '计算机能力', '计算机等级'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'imtype',
                'field' => array(
                    'QQ', 'qq', 'MSN', 'msn', '飞信', '旺旺', 'UC', 'uc', 'YY', 'yy', 'Gtalk', 'gtalk'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'qq',
                'field' => array(
                    'qq', 'QQ'
                ),
                'type' => 'normal',
            ),
            array(
                'id' => 'msn',
                'field' => array(
                    'msn', 'MSN'
                ),
                'type' => 'normal',
            )
        );

        return $config;
    }

}
