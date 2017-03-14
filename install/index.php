<!DOCTYPE HTML>
<!--[if lt IE 9]>
<html lang="en" class="ie8">
<![endif]-->
<!--[if gt IE 8]><!-->
<html lang="en">
    <![endif]-->
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
        <meta name="renderer" content="webkit"/>
        <meta charset="utf-8">
        <title>安装引导</title>
        <meta name="keywords" content="IBOS"/>
        <meta name="generator" content="IBOS 3.0"/>
        <meta name="author" content="IBOS Team"/>
        <meta name="coryright" content="2014 IBOS Inc."/>
        <link href="../static/css/base.css" type="text/css" rel="stylesheet"/>
        <link href="../static/css/common.css" type="text/css" rel="stylesheet"/>
        <link href="../static/js/lib/artDialog/skins/ibos.css" rel="stylesheet" type="text/css"/>
        <link href="static/css/installation_guide.css" type="text/css" rel="stylesheet"/>
        <!-- IE8 fixed -->
        <!--[if lt IE 9]>
        <link rel="stylesheet" href="../static/css/iefix.css">
        <![endif]-->
        <script>
            document.createElement("section");
        </script>
    </head>
    <body>
        <div class="main">
            <div class="main-content">
                <div class="main-top posr">
                    <i class="o-top-bg"></i>
                    <div class="version-info"></div>
                </div>
                <div class="specific-content">
                    <section id="envCheck" style="display: none;"></section>
                    <section id="dbInit" style="display: none;">
                        <div class="db-install-wrap">
                            <form action="javascript:;" class="form-horizontal form-narrow" id="user_form">
                                <table class="table table-info" id="table_info">
                                    <tbody>
                                        <tr>
                                            <th>管理员账号</th>
                                            <td>
                                                <div class="control-group">
                                                    <label class="control-label">用户名<span class="xcr">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" class="span6" data-type="ADname" id="administrator_name"
                                                               name="adminName" value="admin" placeholder="请输入密码">
                                                        <span id="administrator_name_tip" class="ml nomatch-tip">用户名不能为空</span>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label">密码<span class="xcr">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" class="span6" data-type="ADpassword"
                                                               id="administrator_password" name="adminPassword" placeholder="请输入密码">
                                                        <span id="administrator_password_tip"
                                                              class="ml nomatch-tip">请填写5到32位数字或者字母！</span>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label">手机号<span class="xcr">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" class="span6" data-type="account"
                                                               id="administrator_account" name="adminAccount" placeholder="请输入手机号码">
                                                        <span id="result_account"></span>
                                                        <span id="administrator_account_tip" class="ml nomatch-tip">账号不能为空！</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>企业信息</th>
                                            <td>
                                                <div class="control-group">
                                                    <label class="control-label">企业全称<span class="xcr">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" name="fullname" class="span6" id="full_name"
                                                               data-type="fullname" placeholder="请使用工商营业执照登记名称"/>
                                                        <span id="full_name_tip" class="ml nomatch-tip">企业全称不能为空！</span>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label">企业简称<span class="xcr">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" name="shortname" class="span6" id="short_name"
                                                               data-type="shortname" placeholder="企业名称缩写，通常2-10个中文缩写"/>
                                                        <span id="short_name_tip" class="ml nomatch-tip">企业简称必须为2-10个字！</span>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label">企业代码<span class="xcr">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" name="qycode" class="span6" id="qy_code"
                                                               data-type="qycode" placeholder="通常为4~20位英文缩写，不可更改 "/>
                                                        <span id="qy_code_result"></span>
                                                        <span id="qy_code_tip" class="ml nomatch-tip">企业代码格不能为空！</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>数据库信息</th>
                                            <td>
                                                <div class="control-group">
                                                    <label class="control-label">数据库用户名<span
                                                            class="necessary-write">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" class="span6" data-type="username" id="database_name"
                                                               name="dbAccount" value="">
                                                        <span id="database_name_tip" class="ml nomatch-tip">数据库用户名不能为空</span>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label">数据库密码<span class="necessary-write">*</span></label>
                                                    <div class="controls">
                                                        <input type="text" class="span6" data-type="DBpassword"
                                                               id="database_password" name="dbPassword" value=''>
                                                        <span id="database_password_tip" class="ml nomatch-tip">数据库密码不能为空</span>
                                                    </div>
                                                </div>
                                                <div class="mbs">
                                                    <a href="javascript:;" class="dib show-info">
                                                        <span class="dib">显示更多信息</span>
                                                        <i class="o-pack-down mlm"></i>
                                                    </a>
                                                </div>
                                                <div class="hidden-info">
                                                    <div class="control-group">
                                                        <label class="control-label">数据库服务器</label>
                                                        <div class="controls">
                                                            <input type="text" class="span6" id="database_server" name="dbHost"
                                                                   value="">
                                                            <span class="write-tip">一般为127.0.0.1,比localhost快</span>
                                                        </div>
                                                    </div>
                                                    <div class="control-group">
                                                        <label class="control-label">数据库名</label>
                                                        <div class="controls">
                                                            <input type="text" class="span6" id="dbname" name="dbName" value="">
                                                            <span class="ml nomatch-tip"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="control-group install-choose" id="tablepre_exist_tip">
                                                    <label class="control-label"><span
                                                            class="constraint-install">强制安装</span></label>
                                                    <div class="controls">
                                                        <div class="constraint-label">
                                                            <label class="checkbox constraint-check">
                                                                <input type="checkbox" name="enforce" id="enforce" value="1">我要删除数据，强制安装
                                                                !!!
                                                            </label>
                                                        </div>
                                                        <div class="constraint-tip">
                                                            <span id="enforce_info"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="content-foot nbt clearfix">
                                    <div class="pull-left protocol-check">
                                        <span class="fss">
                                            点击“立即安装”即同意
                                            <a href="http://www.ibos.com.cn/" target="_blank">《IBOS用户使用协议》</a>
                                        </span>
                                    </div>
                                    <div class="pull-right">
                                        <label class="checkbox checkbox-inline mbz">
                                            <input type="checkbox" id="ext_data" name="extData" value="1" checked="checked"/>
                                            <span>使用演示数据体验</span>
                                        </label>
                                        <label class="checkbox checkbox-inline disabled user-defined">
                                            <input type="checkbox" id="user_defined" name="custom" value="0" disabled/>
                                            <span>自定义模块</span>
                                        </label>
                                        <!--1.当未勾选自定义模块时,按钮显示为立即安装,点击后去往安装页面.
                                            2.当勾选自定义模块后,按钮显示为下一步,点击后去往模块设置页面.
                                            js会动态改变a的href值,需要将url写入到js中-->
                                        <input type="hidden" id="submitDbInit" name="submitDbInit" value="1"/>
                                        <button type="button" class="btn btn-large btn-primary btn-install" id="btn_install">
                                            立即安装
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </section>
                    <section id="customInitMoudle" style="display: none;">
                        <form action="javascript:;" method="post" id="custom_form" class="form-horizontal form-narrow">
                            <div class="specific-content">
                                <div class="">
                                    <table class="table table-module">
                                        <tbody>
                                            <tr>
                                                <th>系统模块</th>
                                                <td id="coreModules"></td>
                                            </tr>
                                            <tr>
                                                <th>功能模块</th>
                                                <td style="padding-right: 20px;" id="customModules"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="content-foot clearfix nbt">
                                    <div class="pull-left ml">
                                        <a href="javascript:dbInit.init();" class="btn btn-large">上一步</a>
                                    </div>
                                    <div class="pull-right">
                                        <input type="submit" name="submitInstallModule" class="btn btn-large btn-primary"
                                               value="立即安装"/>
                                    </div>
                                </div>
                        </form>
                    </section>
                    <section id="installing" style="display: none;">
                        <div>
                            <div class="mlst">
                                <div class="dib vam">
                                    <p class="mb"><i class="o-install-tip"></i></p>
                                    <p class="mbs fsm">全新IBOS V4支持绑定微信企业号，提供多项办</p>
                                    <p class="fsm">公应用，充分满足企业移动办公需求。</p>
                                </div>
                                <div class="dib">
                                    <i class="o-installing-tip"></i>
                                </div>
                            </div>
                            <div class="clearfix mlg">
                                <div class="progress progress-striped span11 pull-left progress-area">
                                    <div id="progressbar" class="progress-bar" role="progressbar" aria-valuenow="20"
                                         aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    </div>
                                </div>
                                <div class="pull-right rate-of-progress">
                                    <span class="xcbu" id="show_process">0%</span>
                                </div>
                            </div>
                            <div class="project-tip" id="install_info">正在安装 "<span id="mod_name"></span>" ,请稍等...</div>
                        </div>
                        <div class="content-foot clearfix">
                            <button type="button" class="btn btn-large pull-right disabled" disabled>正在安装...</button>
                        </div>
                    </section>
                    <section id="result_success" style="display: none;">
                        <div>
                            <div class="content-header">
                                <div class="install-success clearfix">
                                    <i class="o-install-success"></i>
                                    <span class="">恭喜，IBOS安装成功！</span>
                                </div>
                                <a class="btn btn-large pull-right install-login">进入IBOS</a>
                            </div>
                            <div class="binding-mc">
                                <iframe src="" name="binding" id="binding" width="100%" height="100%" frameborder="0"></iframe>
                            </div>
                        </div>
                    </section>
                    <section id="result_error" style="display: none;">
                        <div class="mlg nht">
                            <div class="dib vam">
                                <i class="o-install-failure"></i>
                            </div>
                            <div class="dib vam">
                                <p class="mb"><i class="o-failure-tip"></i></p>
                                <span class="dib mb">安装失败信息：</span>
                                <div class="failure-info scroll">
                                    <ul class="failure-info-list">
                                        <li>
                                            <i class="o-not-pass"></i>
                                            <span class="mlm failure-msg"></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="content-foot clearfix">
                            <a href="javascript:location.reload();" class="btn btn-large btn-primary pull-right">返回</a>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <script type="text/template" id="env_check_tpl">
            <div class="fill-nn">
            <div class="mb ovh posr">
            <span class="ic-divider"></span>
            <span class="check-project-title prs">
            <% if(envCheck.envCheckRes){ %>
            <i class="o-normal-tip"></i>
            <span class="mlm">环境检查</span>
            <% }else{ %>
            <i class="o-warm-tip"></i>
            <span class="mlm warn">环境检查未能通过！</span>
            <% } %>
            </span>
            <a href="javascript:;" class="pull-right showmore">
            <span>收起</span>
            <i class="o-pack-up"></i>
            </a>
            </div>
            <!-- 环境检测 -->
            <div class="environment-check">
            <table class="table table-condensed table-check">
            <tbody>
            <tr>
            <td>环境检测</td>
            <td>所需配置</td>
            <td>推荐配置</td>
            <td>当前服务器</td>
            <td width="20"></td>
            </tr>
            <% for(var item in envCheck.envItems){ %>
            <tr>
            <td><%= install[item] || item %></td>
            <td><%= install[envCheck.envItems[item].r] || envCheck.envItems[item].r %></td>
            <td><%= install[envCheck.envItems[item].b] || envCheck.envItems[item].b %></td>
            <td><%= install[envCheck.envItems[item].current] || envCheck.envItems[item].current %></td>
            <td>
            <i class="<% if(envCheck.envItems[item].status){ %>o-normal-pass<% }else{ %>o-not-pass<% } %>"></i>
            </td>
            <tr>
            <% } %>
            </tbody>
            </table>
            </div>
            <!-- 文件、目录权限检查 -->
            <div class="mb ovh posr">
            <span class="ic-divider"></span>
            <span class="check-flie-title prs">
            <% if( dirfileCheck['dirfileCheckRes'] ){ %>
            <i class="o-normal-tip"></i>
            <span class="mlm">目录、文件权限检查</span>
            <% }else{ %>
            <i class="o-warm-tip"></i>
            <span class="mlm warn">目录、文件权限检查未能通过！</span>
            <% } %>
            </span>
            <a href="javascript:;" class="pull-right showmore">
            <span>展开</span>
            <i class="o-pack-down"></i>
            </a>
            </div>
            <div class="file-check">
            <table class="table table-condensed table-file">
            <tbody>
            <tr>
            <td>目录文件</td>
            <td>所需状态</td>
            <td>当前状态</td>
            <td width="20"></td>
            </tr>
            <% for(var item in dirfileCheck.dirfileItems){ %>
            <tr>
            <td><%= dirfileCheck.dirfileItems[item].path %></td>
            <td><%= install.Writeable %></td>
            <td><%= dirfileCheck.dirfileItems[item].msg %></td>
            <td>
            <i class="<% if(dirfileCheck.dirfileItems[item].status == 1){ %>o-normal-pass<% }else{ %>o-not-pass<% } %>"></i>
            </td>
            <tr>
            <% } %>
            </tbody>
            </table>
            </div>
            <!-- 函数依赖性检查 -->
            <div class="mb ovh posr">
            <span class="ic-divider"></span>
            <span class="check-flie-title prs">
            <% if( funcCheck.funcCheckRes && filesorkCheck.filesorkCheckRes && extLoadedCheck.extLoadedCheckRes ){ %>
            <i class="o-normal-tip"></i>
            <span class="mlm">函数依赖性检查</span>>
            <% }else{ %>
            <i class="o-warm-tip"></i>
            <span class="mlm warn">函数依赖性检查未能通过！</span>
            <% } %>

            </span>
            <a href="javascript:;" class="pull-right showmore">
            <span>展开</span>
            <i class="o-pack-down"></i>
            </a>
            </div>
            <div class="function-check">
            <table class="table table-condensed table-function">
            <tbody>
            <tr>
            <td>函数名称</td>
            <td>检查结果</td>
            <td>建议</td>
            <td width="20"></td>
            </tr>
            <% for( var item in funcCheck.funcItems ){ %>
            <tr>
            <td><%= item %></td>
            <td>
            <% if ( funcCheck.funcItems[item].status ) { %>
            支持
            <% }else{ %>
            支持
            <% } %>
            </td>
            <td><%= funcCheck.funcItems[item].advice %></td>
            <td>
            <i class="<% if( funcCheck.funcItems[item].status ){ %>o-normal-pass<% }else{ %>o-not-pass<% } %>"></i>
            </td>
            </tr>
            <% } %>
            <% if ( !filesorkCheck.filesorkCheckRes ){ %>
            <% for( var item in filesorkCheck.filesockItems){ %>
            <tr>
            <td><%= item %></td>
            <td>
            <% if ( filesorkCheck.filesockItems[item].status ) { %>
            支持
            <% }else{ %>
            支持
            <% } %>
            </td>
            <td><%= filesorkCheck.filesockItems[item].advice %></td>
            <td>
            <i class="<% if( filesorkCheck.filesockItems[item].status ){ %>o-normal-pass<% }else{ %>o-not-pass<% } %>"></i>
            </td>
            </tr>
            <% } %>
            <% } %>
            <% if ( !extLoadedCheck.extLoadedCheckRes ){ %>
            <% for( var item in extLoadedCheck.extLoadedItems){ %>
            <tr>
            <td><%= item %></td>
            <td>
            <% if ( extLoadedCheck.extLoadedItems[item].status ) { %>
            支持
            <% }else{ %>
            支持
            <% } %>
            </td>
            <td width="360"><%= extLoadedCheck.extLoadedItems[item].advice %></td>
            <td>
            <i class="<% if( extLoadedCheck.extLoadedItems[item].status ){ %>o-normal-pass<% }else{ %>o-not-pass<% } %>"></i>
            </td>
            </tr>
            <% } %>
            <% } %>
            </tbody>
            </table>
            </div>
            </div>
            <div class="content-foot clearfix">
            <button class="btn btn-large btn-primary pull-right">重新检测</button>
            </div>
        </script>
        <script type="text/template" id="module_tpl">
            <% for(var attr in module){ %>
            <label class="checkbox dib ml">
            <input type="checkbox" name="<%= name %>[]" value="<%= attr %>" checked <% if(name == 'coreModules'){
            %>disabled<% } %> />
            <span><%= module[attr].name %></span>
            </label>
            <% } %>
        </script>
        <script src="../static/js/src/core.js"></script>
        <script src="../static/js/src/base.js"></script>
        <script src='../static/js/lib/artDialog/artDialog.min.js'></script>
        <script src="../static/js/src/common.js"></script>
        <script src="static/js/lang/zh-cn.js"></script>
        <script src="static/js/install_guide.js"></script>
    </body>
</html>
