/**
 * 后台模块中文语言包
 *
 */

var L = L || {};
$.extend(true, L, {
    DB: {
        "TIP": "提示",
        // Index
        "SHUTDOWN_SYSTEM_FAILED": "关闭系统失败",
        "LOAD_SECURITY_INFO_FAILED": "载入提示失败",
        "LICENSE_KEY": "授权KEY",
        "ENTER_LICENSEKEY": "请输入授权码...",

        // 全局 -- 积分设置
        "CREDIT_RULE_NUM_OVER": "已经超过新增规则指定条数",
        "CREDIT_TIP": "总积分是衡量用户级别的唯一标准，您可以在此设定用户的总积分计算公式，公式中可使用包括 + - * / () 在内的运算符号",

        // 全局 -- 性能优化
        "SPHINX_PORT_TIP": "例如，9312，主机名填写 socket 地址的，则此处不需要设置",
        "SPHINX_HOST_TIP": "本地主机填写“localhost”，或者填写 Sphinx 服务 socket 地址，必须是绝对地址：例如，/tmp/sphinx.sock",

        // 全局 -- 即时通讯绑定
        "BIND_USER": "绑定用户",
        "BIND_SUCCESS": "绑定成功",
        "BIND_FAILED": "未知错误，绑定失败",

        "RTX_SYNC_CONFIRM": "确认要开始同步吗？该操作不可恢复！",
        "RTX_SYNC_TITLE": "同步组织架构",
        "RTX_SYNC_CONTENT": "同步中，请稍等……",
        "RTX_SYNC_SUCCESS": "同步成功完成",

        // 界面 -- 快捷导航设置
        "REMOVE_QUICKNAV_CONFIRM": "确定要删除该快捷导航吗？",

        //界面 -- 后台导航设置
        "REMOVE_CHILD_NAVIGATION": "请先将子导航移除!",

        //界面 -- 导航设置
        "SINGLE_PAGE": "单页图文",
        "PREVIEW": "预览",

        //界面 -- 权限设置
        "POSITION_NAME_CANNOT_BE_EMPTY": "岗位名称不能为空",
        "POWERLESS": "无权限",
        "ME": "本人",
        "AND_SUBORDINATE": "本人及下属",
        "CURRENT_BRANCH": "当前分支机构",
        "ALL": "全部",
        "ADD_LIMIT": "添加权限",
        "EDIT_LIMIT": "编辑权限",
        "DELET_LIMIT": "确定删除该权限?",
        "SELLECT_ROLE": "请选择角色"
    },
    // 用户模块
    ORG: {
        "POSITION_NAME_CANNOT_BE_EMPTY": "岗位名称不能为空",
        "DEPARTMENT_NAME_CANNOT_BE_EMPTY": "部门名称不能为空",
        "ROLE_NAME_CANNOT_BE_EMPTY": "角色名称不能为空",
        "POWERLESS": "无权限",
        "ME": "本人",
        "AND_SUBORDINATE": "本人及下属",
        "CURRENT_BRANCH": "当前分支机构",
        "ALL": "全部",

        // 部门管理
        "WRONG_PARENT_DEPARTMENT": "当前部门不能与上级部门相同",
        "MOVEUP_FAILED": "向上移动部门失败",
        "MOVEDOWN_FAILED": "向下移动部门失败",
        "DELETE_DEPARTMENT_CONFIRM": "确认删除部门吗？该操作无法恢复",

        // 用户管理
        "SYNC_USER": "同步用户",
        "IS_THE_FIRST_PAGE": "当前为第一页",
        "IS_THE_LAST_PAGE": "当前是最后一页",
        "BATCH_IMPORT_USER": "批量导入新成员",
        "BATCH_IMPORT_RESULT": "导入结果",
        "SELECT_IMPORT_FILE": "请选择导入文件",
        "VIEW_SUBORDINATE_RELATIONSHIP": "查看上下级关系",
        "EDIT_DEPARTMENT_INFO": "编辑部门信息",
        "DELETE_DEPARTMENT_TIP": "删除部门",
        "SURE_DELETE_DEPARTMENT": "确定删除<%= name %>吗？",

        // 岗位管理
        "DELETE_POSITIONS_CONFIRM": "确认删除选中岗位吗?该操作无法恢复",
        "CREAT_CLASSIFICATION": "新建分类",
        "EDIT_CLASSIFICATION": "编辑分类",
        "ADD_CLASSIFICATION_SUCEESS": "添加成功",
        "ADD_CLASSIFICATION_FAILED": "添加失败",
        "EDIT_CLASSIFICATION_SUCEESS": "编辑成功",
        "EDIT_CLASSIFICATION_FAILED": "编辑失败",
        "SURE_DELET_POSITION": "确定删除<%= name %>吗？",
        "EDIT_CLASSIFICATION_INFO": "编辑分类信息",
        "DELET_DEPARTMENT_ZTREE": "删除部门",

        // 角色权限管理
        "SURE_DELETE_ITEM": "确定删除该项?",

        INTRO: {
            "BRANCH": "总部下可创建多个分支机构，分支机构可以再创建分支机构，部门不允许创建分支机构。分支机构与部门的图标不一样哦！",
            "SUPERVISOR": "设置该职员的直属领导后，直属领导在各个模块的下属栏目中可直接查阅职员的工作日志、日程总结计划等模块数据。",
            "AUXILIARY_DEPT": "如果职员兼任多个部门工作，可在此设置辅助部门。",
            "POSITION": "岗位角色的设置决定职员的系统使用权限，可在岗位角色管理中添加新岗位角色！",
            "ACCOUNT_STATUS": "在这儿，你可以选择账号状态，区别如下：<br /> 1. 启用，允许登录并使用；<br /> 2. 锁定，禁止登录但仍然接收系统数据；<br /> 3. 禁用，禁止登录并不接收任何数据。",
            "POSITION_ADD": "点这儿可添加岗位角色成员，添加后的成员即拥有该岗位角色的所有访问及使用权限！"
        }
    },
    // 绑定酷办公
    CO: {
        "CO_REG_SUCCESS": "酷办公注册成功～",
        "SET_CO_LOGIN_PWD": "请设置酷办公登录初始密码",
        "BINDING_USER": "绑定用户",
        "LOGIN_SUCCESS": "登录成功",
        "IBOS_ADD_LIST": "IBOS新增成员",
        "IBOS_DEL_LIST": "IBOS禁用成员",
        "CO_ADD_LIST": "酷办公加入成员",
        "CO_DEL_LIST": "酷办公移除成员",
        "UNBINDING_SUCCESS": "解绑成功",
        "UNBINDING_IBOSCO_CONFIRM": "确定要解绑吗？解绑后用户绑定也将会被清空。",
        "SURE_BINDING_COMPANY": "确定要绑定到酷办公企业“<%= corpname %>”吗？",
        "SURE_UNBINDING_AND_LINK_NEW_ADRESS": "“<%= corpname %>”已绑定过IBOS地址“<%= systemurl %>”。</br>确定要解绑并绑定到当前地址吗？",
        "SURE_EXIT_COMPANY": "确定退出“ <%= corpname %> ”吗？",
        "CREATE_AND_BINDING_COMPANY": "创建酷办公并绑定"
    },
    // 审批流程
    APPROVE: {
        "SURE_DELET_APPROVE_FLOW": "确定要删除该审批流程吗？",
        "APPROVE_NAME_CANNOT_BE_EMPTY": "审批流程名称不能为空！"
    },
    // 积分设置
    CREDIT: {
        "SURE_RESET_CREDIT_SETTING": "确定重置积分设置？"
    },
    //数据库
    DATABASE: {
        "CONFIRM_ACTION": "确认操作",
        "CONFIRM_IMPORT": "确认要导入该备份吗？",
        "AT_LEAST_ONE_RECORD": "请至少选择一条数据",
        "CONFIRM_DECOMPRESS": "确认要解压该备份吗？"
    },
    //即时通讯绑定
    IM: {
        "SYNCHRONIZE_OA": "导入RTX组织架构到OA"
    },
    //性能优化
    OPTIMIZE: {
        "SPHINX_SUBINDEX_IP": "填写 Sphinx 配置中的标题主索引名及标题增量索引名.注意：多个索引使用半角逗号",
        "SPHINX_MSGINDEX_TIP": "填写 Sphinx 配置中的全文主索引名及全文增量索引名.注意：多个索引使用半角逗号",
        "SPHINX_MAXQUERY_TIP": "填写最大搜索时间，以毫秒为单位。参数必须是非负整数。默认值为 0，意思是不做限制",
        "SPHINX_LIMIT_TIP": "填写最大返回匹配项数目，必须是非负整数，默认值10000",
        "SPH_RANK_PROXIMITY_BM25_DESC": "默认模式，同时使用词组评分和 BM25 评分，并且将二者结合。[默认]",
        "SPH_RANK_BM25_DESC": "统计相关度计算模式，仅使用 BM25 评分计算(与大多数全文检索引擎相同)。这个模式比较快，但是可能使包含多个词的查询的结果质量下降。",
        "SPH_RANK_NONE_DESC": "禁用评分的模式，这是最快的模式。实际上这种模式与布尔搜索相同。所有的匹配项都被赋予权重1"
    },
    //手机短信
    SMS: {
        "SMS_DEL_CONFIRM": "您确定要删除选中短信记录吗?",
        "SMS_ADVANCED_SEARCH": "短信高级搜索"
    },
    //图片上传
    UPLOAD: {
        "UPLOAD_PICTURE_FIRST": "请先上传图片",
        "WATERMARK_PREVIEW": "水印预览"
    },
    // 在线升级
    UPGRADE: {
        UPGRADE_AUTOMATICALL: "自动升级",
        SURE_OPERATE: "确认操作",
        UPGRADE_BACKUP_REMIND: "自动升级前请您先备份程序及数据库，确定开始升级吗？",
        SURE: "确定",
        DOWN: '下载',
        UPGRADE_FORCE: "强制升级",
        UPGRADE_REGULAR: "正常升级",
        FTP_SETTING: "ftp设置"
    }
});