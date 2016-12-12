<?php

/**
 * 简历分析类
 *
 * 基本思路如下：
 *
 * 匹配每个词的位置（考虑每个词的相同规则（求职意向、工作经历等不定格式内容）以及特殊规则（电话、邮箱、身份证等确定格式内容））
 *
 * 计算每个位置的权重
 * 此时拥有大概是这样的数据：
 * A词1： 权重，起始位置，结束位置
 * A词2： 权重，起始位置，结束位置
 * ...
 * B词1： 权重，起始位置，结束位置
 * B词2： 权重，起始位置，结束位置
 * 这些数据指字段对应的内容，而不包括字段名本身，但是计算权重时则需要考虑字段所处的位置以及周围的情况
 *
 * 交叉枚举A词和B词的权重和位置，在枚举数据中找出彼此位置没有冲突（尽量）、匹配内容最多（起始、结束位置最长）且权重最高的最佳位置（考虑程序效率问题）
 *
 * 解释每个字段各自位置组成的阵列，然后把每个字段的内容分割出来
 *
 * TODO::算法优化：目前匹配率较理想，但是准确率不太好，匹配的多余字符较多、二级以上的权重利用率不高，并没有进行交叉枚举（难度较大，考虑效率问题）
 */

namespace application\modules\recruit\utils;

class ResumeAnalysis
{

    private $charset = 'utf-8';
    private $content = '';
    private $fieldpos = array();
    private $fieldweights = array();
    private $valpos = array();
    private $config = array();

    public function __construct($content = '', $config = array())
    {
        $this->content = $content;
        $this->config = $config;
    }

    /**
     * 解释简历内容
     * @return array
     * TODO::还可以进一步分解为更多的子程序
     */
    public function parse_content()
    {
        $result = array();
        $specialpostypes = array();
        //读取配置数据
        foreach ($this->config as $configinfo) {
            $poslist = &$this->fieldpos[$configinfo['id']];
            $weightlist = &$this->fieldweights[$configinfo['id']];
            $poslist = $weightlist = array();
            foreach ($configinfo['field'] as $index => $field) {
                $poslist = array_merge($poslist, $this->get_pos($field));
            }
            $poslist = $this->pos_clear_include($poslist);
            $weightlist = $this->assess_weight($poslist);
            //纠正和补充含有特殊规则的字段
            if (in_array($configinfo['type'], array('name', 'gender', 'email', 'age', 'mobile', 'phone', 'idcard', 'maritalstatus'))) {
                list($poslist, $weightlist) = $this->restore_special_pos($poslist, $weightlist, $configinfo['type']);
            }
            $poslist = $this->pos_sort_by_weight($poslist, $weightlist);
            $result[$configinfo['id']] = '';
        }
        //解释位置阵列
        $posarray = $this->parse_pos_array($this->fieldpos);
        //得到阵列后就可以开始截取内容了
        for ($i = 0, $len = count($posarray); $i < $len; $i++) {
            $id = $this->valpos[$i];
            $spos = $posarray[$i];
            if (isset($posarray[$i + 1])) {
                $nextid = $this->valpos[$i + 1];
                $max_weight_pos = $this->fieldpos[$nextid][0];
                $epos = $max_weight_pos[1] - ($max_weight_pos[1] - $max_weight_pos[0]);
            } else {
                $epos = 9999999999;
            }
            //再严格调整一下范围
            //先获取这段内容
            $limitstr = mb_substr($this->content, $spos, ($epos - $spos), $this->charset);
            if (strpos($limitstr, "\n")) {
                $matches = array();
                if (
                    //如果开始位置和结束位置一样（特殊字段才会有这种情况）
                    ($this->fieldpos[$id][0][0] == $this->fieldpos[$id][0][1] && preg_match_all('/[^\s\t:：　]+/', $limitstr, $matches) > 3) ||
                    //有可能多个字段并列一行，第一个条件去除字段内容开头是:+换行的情况，第二个条件匹配 v1 v2 v3 或者 a: v1 b: v2 c: v3这些情况
                    (!preg_match('/^[\s\t　:：]*[\r\n]+/', $limitstr) && preg_match_all('/[^\s\t:：　]+[\s\t:：]+[^\s\t:：　]+[\s\t　]*/', $limitstr, $matches) > 2)
                ) {
                    //取最靠近字段位置的值，也就是第一个值
                    $near_field_str = $matches[0][0];
                    $strpos = mb_strpos($limitstr, $near_field_str, 0, $this->charset);
                    //因为之前从开始位置截取内容，所以现在要加上开始位置
                    $limitepos = $spos + $strpos + mb_strlen($near_field_str, $this->charset);
                    $epos = ($limitepos < $epos) ? $limitepos : $epos;
                }
            }
            $fieldvalue = mb_substr($this->content, $spos, ($epos - $spos), $this->charset);
            //格式化一下输出
            $result[$id] = trim($fieldvalue, " 　:：;；\t\n\r/"); //注意开头是一个半角空格和一个全角空格
        }
        return $result;
    }

// 提高准确率的函数，用于改进算法，暂时没时间，先注释掉
//	/**
//	 * 获取被其它位置包含的位置
//	 * @param array $searchposlist
//	 * @param int $spos
//	 * @param int $epos
//	 * @return array 
//	 */
//	private  function get_include_pos($searchposlist, $spos, $epos) {
//		$poslist = array();
//		foreach($searchposlist as $key => $pos) {
//			if($spos < $pos && $pos < $epos) {
//				$poslist[$key] = $pos;
//			}
//		}
//		return $poslist;
//	}
//
//	/**
//	 * 获取只有零权重的字段
//	 * @param array $fieldposlist
//	 * @param array $fieldweightlist
//	 * @return array 
//	 */
//	private function get_only_zero_weight_pos($fieldposlist, $fieldweightlist) {
//		$poslist = array();
//		foreach($fieldweightlist as $id => $weightlist) {
//			$zerokey = array_key($weightlist, 0);
//			if(count($weightlist) == 1 && $zerokey) {
//				$poslist[$id] = $fieldposlist[$id][$zerokey];
//			}
//		}
//		return $poslist;
//	}

    /**
     * 纠正和补充含有特殊规则的字段
     * @param type $type
     * @return array
     * TODO::代码复用
     */
    private function restore_special_pos($poslist, $weightlist, $type)
    {
        switch ($type) {
            case 'name':
                //百家姓
                $familysurnames = array(
                    '李', '王', '张', '刘', '陈', '杨', '黄', '孙', '周', '吴',
                    '徐', '赵', '朱', '马', '胡', '郭', '林', '何', '高', '梁',
                    '郑', '罗', '宋', '谢', '唐', '韩', '曹', '许', '邓', '萧',
                    '冯', '曾', '程', '蔡', '彭', '潘', '袁', '于', '董', '余',
                    '苏', '叶', '吕', '魏', '蒋', '田', '杜', '丁', '沈', '姜',
                    '范', '江', '傅', '钟', '卢', '汪', '戴', '崔', '任', '陆',
                    '廖', '姚', '方', '金', '邱', '夏', '谭', '韦', '贾', '邹',
                    '石', '熊', '孟', '秦', '阎', '薛', '侯', '雷', '白', '龙',
                    '段', '郝', '孔', '邵', '史', '毛', '常', '万', '顾', '赖',
                    '武', '康', '贺', '严', '尹', '钱', '施', '牛', '洪', '龚',
                    '汤', '陶', '黎', '温', '莫', '易', '樊', '乔', '文', '安',
                    '殷', '颜', '庄', '章', '鲁', '倪', '庞', '邢', '俞', '翟',
                    '蓝', '聂', '齐', '向', '申', '葛', '柴', '伍', '覃', '骆',
                    '关', '焦', '柳', '欧', '祝', '纪', '尚', '毕', '耿', '芦',
                    '左', '季', '管', '符', '辛', '苗', '詹', '曲', '欧阳', '靳',
                    '祁', '路', '涂', '兰', '甘', '裴', '梅', '童', '翁', '霍',
                    '游', '阮', '尤', '岳', '柯', '牟', '滕', '谷', '舒', '卜',
                    '成', '饶', '宁', '凌', '盛', '查', '单', '冉', '鲍', '华',
                    '包', '屈', '房', '喻', '解', '蒲', '卫', '简', '时', '连',
                    '车', '项', '闵', '邬', '吉', '党', '阳', '司', '费', '蒙',
                    '席', '晏', '隋', '古', '强', '穆', '姬', '宫', '景', '米',
                    '麦', '谈', '柏', '瞿', '艾', '沙', '鄢', '桂', '窦', '郁',
                    '缪', '畅', '巩', '卓', '褚', '栾', '戚', '全', '娄', '甄',
                    '郎', '池', '丛', '边', '岑', '农', '苟', '迟', '保', '商',
                    '臧', '佘', '卞', '虞', '刁', '冷', '应', '匡', '栗', '仇',
                    '练', '楚', '揭', '师', '官', '佟', '封', '燕', '桑', '巫',
                    '敖', '原', '植', '邝', '仲', '荆', '储', '宗', '楼', '干',
                    '苑', '寇', '盖', '南', '屠', '鞠', '荣', '井', '乐', '银',
                    '奚', '明', '麻', '雍', '花', '闻', '冼', '木', '郜', '廉',
                    '衣', '蔺', '和', '冀', '占', '公', '门', '帅', '利', '满'
                );
                $matches = array();
                $contents = preg_split('(\r|\n|\r\n)', $this->content);
                //第一行前几个字最有可能是名字
                if (preg_match('/^[\n\t\r\s　]*([^\n\t\r\s　]+)/', $contents[0], $matches)) {
                    $name = $matches[1];
                    if (
                        in_array(mb_substr($name, 0, 1, $this->charset), $familysurnames) ||
                        in_array(mb_substr($name, 0, 2, $this->charset), $familysurnames)
                    ) {
                        $spos = mb_stripos($this->content, $name, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 3; //赋予3权重
                    }
                }
                //保险起见每行都尝试匹配
                foreach ($contents as $key => $row) {
                    if (preg_match('/^[\t\s　]*([\x{4e00}-\x{9fa5}}]+)[\t\s　]*$/u', $row, $matches)) {
                        $name = $matches[1];
                        if (in_array(mb_substr($name, 0, 1, $this->charset), $familysurnames)) {
                            $spos = mb_stripos($this->content, $name, 0, $this->charset);
                            $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                            $weightlist[] = 2; //赋予2权重
                        }
                    }
                }
                break;
            case 'gender':
                $matches = array();
                if (preg_match_all('/(男|女)/u', $this->content, $matches)) {
                    $sexlist = $matches[0];
                    foreach ($sexlist as $index => $sex) {
                        $spos = mb_strpos($this->content, $sex, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 2 - $index;
                    }
                }
                break;
            case 'maritalstatus':
                $matches = array();
                if (preg_match_all('/(已婚|未婚)/u', $this->content, $matches)) {
                    $maritalslist = $matches[0];
                    foreach ($maritalslist as $index => $maritals) {
                        $spos = mb_strpos($this->content, $maritals, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 2 - $index;
                    }
                }
                break;
            case 'email':
                $matches = array();
                if (preg_match_all('/[a-zA-Z0-9_+.-]+\@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,4}/', $this->content, $matches)) {
                    $emaillist = $matches[0];
                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 5 - $index;
                    }
                }
                break;
            case 'age':
                $matches = array();
                if (preg_match_all('/\d+岁/u', $this->content, $matches)) {
                    $agelist = $matches[0];
                    foreach ($agelist as $index => $age) {
                        $spos = mb_strpos($this->content, $age, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 3 - $index;
                    }
                }
                break;
            case 'mobile':
                $matches = array();
                if (preg_match_all('/\b\d{11}\b/', $this->content, $matches)) {
                    $emaillist = $matches[0];
                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 5 - $index;
                    }
                }
                break;
            case 'phone':
                $matches = array();
                if (preg_match_all('/\b\d{7-9}\b/', $this->content, $matches)) {
                    $emaillist = $matches[0];
                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 5 - $index;
                    }
                }
                break;
            case 'idcard':
                $matches = array();
                if (preg_match_all('/\b(\d{15}|\d{18})\b/', $this->content, $matches)) {
                    $emaillist = $matches[0];
                    foreach ($emaillist as $index => $email) {
                        $spos = mb_strpos($this->content, $email, 0, $this->charset);
                        $poslist[] = array($spos, $spos); //结束位置也是开始位置，因为这里没有字段名
                        $weightlist[] = 5 - $index;
                    }
                }
                break;
            default:
                break;
        }
        return array($poslist, $weightlist);
    }

    /**
     * 处理位置在内容上的阵列
     */
    public function parse_pos_array($fieldposlist)
    {
        $array = array();
        $index = 0;
        uasort($fieldposlist, 'application\modules\recruit\utils\ResumeAnalysis::pos_sort_array');
        foreach ($fieldposlist as $id => $poslist) {
            //取$poslist权重最高的，也就是第1个的结束位置做排位
            $max_weight_pos = $poslist[0];
            if (!empty($max_weight_pos)) {
                $array[$index] = $max_weight_pos[1];
                $this->valpos[$index] = $id;
                $index++;
            }
        }
        return $array;
    }

    static private function pos_sort_array($poslist1, $poslist2)
    {
        error_reporting(E_ERROR);
        if ($poslist1[0][0] == $poslist2[0][0])
            return 0;
        return ($poslist1[0][0] < $poslist2[0][0]) ? -1 : 1;
    }

    private function pos_sort_by_weight($poslist, $weightlist)
    {
        $pos_list_sorted = array();
        //权重最高的排在首位
        arsort($weightlist);
        foreach ($weightlist as $index => $weight) {
            $pos_list_sorted[] = $poslist[$index];
        }
        return $pos_list_sorted;
    }

    /**
     * 清除包含关系的位置
     * @param array $poslist
     * @return array
     */
    private function pos_clear_include($poslist)
    {
        $unique_poslist = $poslist;
        $cover_include = array();
        $poslist1 = $poslist2 = $poslist;
        foreach ($poslist1 as $key1 => $pos1) {
            foreach ($poslist2 as $key2 => $pos2) {
                $isinclude = $pos1[0] <= $pos2[0] && $pos2[1] <= $pos1[1];
                if ($isinclude && $key1 != $key2) {
                    unset($unique_poslist[$key2]);
                }
            }
        }
        //重置数组键值
        sort($unique_poslist);
        return $unique_poslist;
    }

    /**
     * 获取字段匹配的位置
     * @param string $field 字段名
     * @return array
     */
    private function get_pos($field)
    {
        $fieldlist = array();
        $matches = array();
        //考虑文字之间可能有空格，因为求职人为了简历排版美观而增加字段之间的空格，如：姓    名:
        //debug::考虑正则表达式的安全性以及匹配准确性，使用了preg_quote过滤，
        //		 但是转化为理想的字符串需要花费更多函数，如果不太影响实际效率则值得这样做
        $exp = '[\t\s　]*'; //含全角空格
        //TODO::开始位置不能匹配
        $strexp = str_replace(preg_quote($exp), $exp, implode($exp,
            //分割字符串成数组，支持中文
            preg_split('/(?<!^)(?!$)/u', preg_quote($field))
        ));
        if (preg_match_all('/' . $strexp . '/', $this->content, $matches) > 0) {
            $fieldlist = $matches[0];
        }
        //去除重复
        $fieldlist = array_unique($fieldlist);
        $poslist = array();
        for ($i = 0, $len = count($fieldlist); $i < $len; $i++) {
            //从0位置开始搜索
            $offset = 0;
            $field = $fieldlist[$i];
            $strlen = mb_strlen($field, $this->charset);
            while (($spos = mb_strpos($this->content, $field, $offset, $this->charset)) || $spos === 0) {
                $epos = $spos + $strlen;
                $poslist[] = array($spos, $epos);
                //继续从下一次结束位置开始搜索
                $offset = $epos;
            }
        }
        return $poslist;
    }

    /**
     * 评估字段位置的权重
     * @param int $poslist
     * @param string $type
     */
    private function assess_weight($poslist)
    {
        $weightlist = array();
        //使用表驱动法影射数据
        //str_list表示如果那个位置出现这些字符，则增加权重
        //weight_list的元素键值和str_list一一对应，表示出现这个字符时增加多少权重
        $left_assess_str_list = array(' ', '　', "\t", "\n", "\r"); //注意区分全角和半角空格
        $left_assess_weight_list = array(2, 2, 2, 3, 3);
        $right_assess_str_list = array(' ', '　', "\t", ':', '：');
        $right_assess_weight_list = array(2, 2, 2, 5, 5);
        for ($i = 0, $len = count($poslist); $i < $len; $i++) {
            //初始化权重
            $weight = 0;
            $spos = $poslist[$i][0];
            $epos = $poslist[$i][1];
            //评估左边第一个字符
            $leftstr = mb_substr($this->content, $spos - 1, 1, $this->charset);
            $lkey = array_search($leftstr, $left_assess_str_list);
            if ($lkey || $lkey === 0) {
                $weight += $left_assess_weight_list[$lkey];
            }
            //评估右边第一个字符
            $rightstr = mb_substr($this->content, $epos, 1, $this->charset);
            $rkey = array_search($rightstr, $right_assess_str_list);
            if ($rkey || $rkey === 0) {
                $weight += $right_assess_weight_list[$rkey];
            }
            //补加权重，越先出现的字段权重越高，权重为0的有特殊用途，不进行补加
            if ($weight != 0) {
                $weight += $len - $i;
            }
            $weightlist[$i] = $weight;
        }
        return $weightlist;
    }

}
