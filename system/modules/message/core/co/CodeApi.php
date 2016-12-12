<?php

namespace application\modules\message\core\co;

class CodeApi
{

    /**
     * 目前只有成功的用上了，继续这块代码编写的注意了：
     * 如果哪个用上了，就把那个改成字符串
     * ps：对应的接口记得不要用数字去判断，php不喜欢字符串，
     * 如果在判断里用了这里的“code”，而这里的又写成了数字，php会强行把字符串变成数字，最明显的错误例子就是var_dump('1e2'==100);这个结果是true
     */
    const SUCCESS = '0';  // 成功
    const TOKEN_INVALID = -1; // token 验证错误
    const TOKEN_EXPIRY = -2; // token 过期
    const TOKEN_OUT_RANGE = -3; // token 超出权限范围
    const PARAM_ERROR = -4;  // 未知参数错误
    const PARAM_MISSING = -5; // 参数缺失
    const TOKEN_REQUIRE_PERSONAL = -6;  // 需要个人级别的授权
    const TOKEN_REQUIRE_CORP = -7; // 需要企业级别授权
    const SAVE_ERROR = -8; // 保存数据出错
    const SIGN_ERROR = -9; // 签名验证错误
    const INVALID_AUTHOR_CODE = -10; // 错误的授权码;
    const RECORD_NOT_EXISTS = -11; // 记录不存在
    const RECORD_ALREADY_EXISTS = -12; // 记录已存在
    const INVALID_QRCODE_LENGTH = -13; // 二维码字符串长度超出限制
    //--------用户接口代码开始--------
    const USER_REQUIRE_IDENTITY = -1001; // 需要手机号/邮箱/用户名三项中的一项，不能同时为空
    const USER_EMAIL_ALREADY_TAKEN = -1002; // 用户邮箱已注册
    const USER_NAME_ALREADY_TAKEN = -1003; // 用户名已注册
    const USER_MOBILE_ALREADY_TAKEN = -1004; // 用户手机已注册
    const USER_INVALID_IDENTITY = -1005; //用户身份验证错误
    const USER_REQUIRE_JOIN_CORP = -1006; // 用户需要加入企业才可操作
    const USER_NOT_IN_SAME_CORP = -1007; // 获取的用户信息必须要与当前令牌在同一家企业内才可获取
    const USER_IM_TOKEN_ERROR = -1008; // 无法获取IM TOKEN，创建用户失败
    const USER_ACCOUNT_ERROR = -1009; //用户帐户不存在
    const USER_PASSWORD_ERROR = -1010; //帐户的密码错误
    // --------企业代码开始--------
    const CORP_ALREADY_JOINED = -2001; // 已加入企业
    const CORP_CODE_ALREADY_TAKEN = -2002; // 企业代码重复
    const CORP_ALREADY_APPLY = -2003; // 已提交申请加入企业
    const CORP_REQUIRE_ADMIN_IDENTITY = -2004; // 需要管理员身份进行操作
    const CORP_MISSING = -2005; // 无法找到企业
    const CORP_ALREADY_CREATED = -2006; // 已经创建过企业
    const CORP_CREATEOR_CANT_EXIT = -2007; // 企业创建者不能退出
    const CORP_REQUIRE_BIND_IBOS = -2008; // 需要绑定IBOS
    const CORP_CODE_NOTALLOWTYPE = -2009; //不合法的长度或密码	
    const CORP_CODE_KEPPCODE = -2010; //保留的系统CODE，请更换	
    const CORP_CREATEOR_CAN_DEL = -2011; // 企业创建者才可注销企业
    // --------部门代码------
    const DEPT_NOT_EXISTS = -3001; // 部门不存在
    const DEPT_HAS_CHILD_DEPT = -3002; // 当前部门还有子部门
    const DEPT_HAS_USER = -3003; // 当前部门下还有用户
    const DEPT_NOT_JOINED = -3004; // 用户还没加入部门
    const DEPT_NOT_SUPPORT_BATCH = -3005; // 不支持批量操作
    // --------讨论组代码--------
    const DISCUSSION_NOT_EXISTS = -4001; // 讨论组不存在
    // --------名片代码--------
    const CARD_INVALID = -5001; //名片无效

}
