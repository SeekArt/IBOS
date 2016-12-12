<?php

use application\core\utils\Cloud;
use application\core\utils\Env;
?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/contactList.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar -->    
    <?php echo $this->getSidebar(); ?>
    <div class="mcr cl-mcr">
        <div class="page-list">
            <div class="page-list-header cl-list-header" id="cl_list_header">
                <div class="clearfix cl-funbar" id="cl_funbar">
                    <div class="search pull-left span7" id="name_search">
                        <input type="text" placeholder="<?php echo $lang['Enter the name check']; ?>" id="search_area">
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" id="normal_search">
                    </div>
                    <div class="pull-right mr">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                <?php echo $lang['Batch operation']; ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="javascript:;" data-action="educeCont"><?php echo $lang['Export contacts']; ?></a>
                                    <form action="<?php echo $this->createUrl( 'default/export' ); ?>" method="post" id="export_contact" name="export_contact">
                                        <input type="hidden" value="" name="uids">
                                    </form>
                                </li>
                                <li><a href="javascript:;" data-action="printCont"><?php echo $lang['Pring contacts']; ?></a></li>
                            </ul>
                        </div>
                        <div class="btn-group mlm cl-btn-group">
                            <a title="<?php echo $lang['By organization']; ?>" class="btn active" href="<?php echo $this->createUrl( $this->id . '/index', array( 'op' => 'dept', 'deptid' => Env::getRequest( 'deptid' ) ) ); ?>"><i class="o-organization-chart"></i></a>
                            <a title="<?php echo $lang['By letter']; ?>" class="btn" href="<?php echo $this->createUrl( $this->id . '/index', array( 'op' => 'letter', 'deptid' => Env::getRequest( 'deptid' ) ) ); ?>"><i class="o-cl-letter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-list-mainer">
                <div class="cl-rolling-sidebar" id="cl_rolling_sidebar">
                    <div class="personal-info" id="personal_info" style="width:520px; height:100%;"></div>
                </div>
                <div class="exist-data" id="user_datalist"></div>
                <div class="pull-left contact-table" id="user_searchlist"></div>
                <div class="inexist-data">
                    <div class="no-data-tip"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    Ibos.app.setPageParam({
        "op": "<?php echo Env::getRequest( 'op' ); ?>",
        "deptid": "<?php echo Env::getRequest( 'deptid' ); ?>"
    });
<?php if ( Cloud::getInstance()->isOpen( ) ): ?>
       $('#cl_rolling_sidebar').delegate("#call_land",'click', function() {
            var phone = $('#care_mobile').html();
            if (phone !== '' && U.regex(phone, 'mobile')) {
                var info = Ibos.app.g("formatdata"),
                    param = {data: info};
                var url = Ibos.app.url('main/call/unidirec', param);
            Ui.openFrame(url, {width: '580px', height: '523px', title: false, skin: 'call-dialog'});
            } else {
                Ui.tip('手机号码格式错误', 'error')
            }
        })
<?php endif; ?>
</script>
<script type="text/template" id="tpl_contact_table">
    <% var UIDS = []; %>
    <% for (var deptid in datas) { %>
    <% var dept = datas[deptid]; %>
    <% var users = dept['users']; %>
    <div class="group-item">
    <% if (dept['pDeptids'].length === 1) { %>
    <div class="cl-type-title"><%= dept['pDeptids'][0] %></div>
    <% } else { %>
    <div class="cl-info-brc clearfix">
    <% for (var i = 0, plen = dept['pDeptids'].length - 1; i < plen; i++) { %>
    <a href="javascript:;" class="xgh"><%= dept['pDeptids'][i] %></a>
    <% } %>
    <a href="javascript:;" class="xwb"><%= dept['pDeptids'][plen] %></a>
    </div>
    <% } %>
    <table class="table table-hover cl-info-table contact-list">
    <tbody>
    <% for (var j = 0, ulen = users.length; j < ulen; j++) { %>
    <% var user = users[j], uid = user['id'].slice(2); %>
    <% UIDS.push(user['id']); %>
    <tr id="cl_tr_<%= uid %>" data-id="<%= uid %>" class="contact-list-item" data-preg="<%= user['preg'] %>" data-action="getUserInfo">
    <td width="60">
    <div class="avatar-box">
    <span class="avatar-circle">
    <img src="<%= user['avatar'] %>">
    </span>
    </div>
    </td>
    <td width="90">
    <span class="xcm pc-name"><%= user['text'] %></span>
    </td>
    <td width="133">
    <span class="fss"><%= Ibos.data.getText(user['posid']) %></span>
    </td>
    <td width="130">
    <span class="fss"><%= user['telephone'] ? user['telephone'] : '' %></span>
    </td>
    <td width="143">
    <span class="fss"><%= user['phone'] %></span>
    </td>
    <td width="153">
    <div class="w120">
    <span class="fss"><%= user['email'] %></span>
    </div>
    </td>
    </tr>
    <% } %>
    </tbody>
    </table>
    </div>
    <% } %>
    <% Ibos.app.s('uids', UIDS.join(',')); %>
</script>
<script type="text/template" id="tpl_search_table">
    <div class="cl-letter-title fsst" style="width: 750px;">搜索结果</div>
    <% var UIDS = []; %>
    <% for (var letter in datas ) { %>
    <% var users = datas[letter]; %>
    <div class="group-item">
    <table class="table table-hover cl-info-table contact-list">
    <tbody>
    <% for (var i = 0, len = users.length; i < len; i++) { %>
    <% var user = users[i], uid = user['id'].slice(2); %>
    <% UIDS.push(user['id']); %>
    <tr id="cl_tr_<%= uid %>" data-id="<%= uid %>" class="contact-list-item" data-preg="<%= user['preg'] %>" data-action="getUserInfo">
    <td width="60">
    <div class="avatar-box">
    <span class="avatar-circle">
    <img src="<%= user['avatar'] %>">
    </span>
    </div>
    </td>
    <td width="90">
    <span class="xcm pc-name"><%= user['text'] %></span>
    </td>
    <td width="123">
    <span class="fss"><%= Ibos.data.getText(user['posid']) %></span>
    </td>
    <td width="120">
    <span class="fss"><%= user['telephone'] ? user['telephone'] : '' %></span>
    </td>
    <td width="133">
    <span class="fss"><%= user['phone'] %></span>
    </td>
    <td width="133">
    <div class="w120 ellipsis" title="<%= user['email'] %>">
    <span class="fss"><%= user['email'] %></span>
    </div>
    </td>
    </tr>
    <% } %>
    </tbody>
    </table>
    </div>
    <% } %>
    <% Ibos.app.s('uids', UIDS.join(',')); %>
</script>
<script type="text/template" id="tpl_rolling_sidebar">
   <div class="personal-info" id="personal_info">
        <div class="cl-pc-top posr">
            <div class="cl-pc-banner">
                <img src= <%= user.bg_big %> id="card_bg"></div>
            <div class="cl-pc-usi">
                <div class="cl-pc-bg"></div>
                <div class="cl-pc-avatar posr">
                    <a href= <%= Ibos.app.url('user/home/index', {uid: user.uid}) %> target="_blank" class="pc-avatar" id="card_home_url">
                        <img src= <%= user.avatar_big %> alt="" width="96" height="96" id="card_avatar">
                    </a>
                </div>
            </div>
            <div class="cl-uic-operate">
                <a href=<%= "javascript:Ibos.showCallingDialog(" + user.uid + ");void(0);" %> title="打电话" class="co-tcall"></a>
                <a target="_blank" href= <%= Ibos.app.url('email/content/add', {toid: user.uid}) %> title="<?php echo $lang['Email TA']; ?>" class="co-temail" id="card_email_url"></a>
                <a title="<?php echo $lang['Send a private letter to TA']; ?>" href= <%= "javascript:Ibos.showPmDialog(['u_" + user.uid + "'],{url:'" + Ibos.app.url('message/pm/post') + "'});void(0);" %> class="co-tpm" id="card_pm"> 
                    <i class="o-pm-offline"></i>
                </a>
            </div>
            <div class="cl-pc-name"> 
                <i class=<%= user.gender == '1' ? "om-male" : "om-female" %> id="card_gender"></i> 
                <strong id="card_realname" class="fsst"><%= user.realname %></strong>
                <span id="card_deptname" class="mlm"> <%= user.deptname %> </span>
                <% if(user.deptname !== '' && user.posname !== ''){ %>
                    <strong id="card_connect">·</strong>
                <% } %>
                <span id="card_posname"><%= user.posname %></span>
            </div>
        </div>
        <div class="pc-info-content posr">
            <a href="javascript:;" class="cl-window-ctrl" data-action="closeSidebar"></a>
            <div class="pc-info-list">
                <div class="mb">
                    <span>
                        <i class="o-home-phone"></i>
                    </span>
                    <span class="mls xwb"><?php echo $lang['Phone']; ?></span>
                    <span class="ml xcm" id="card_telephone"><%= user.telephone == '' ? Ibos.l("CONTACT.NOT_AVAILABLE") : user.telephone %> </span>
                </div>
                <div class="mb">
                    <span>
                        <i class="o-pc-phone"></i>
                    </span>
                    <span class="mls xwb"><?php echo $lang['Cell phone']; ?></span>
                    <span class="ml xcm" id="care_mobile"><%= user.mobile == '' ? Ibos.l("CONTACT.NOT_AVAILABLE") : user.mobile %></span>
                    <?php if ( Cloud::getInstance()->isOpen( ) ): ?>
                        <button class='btn' id='call_land'>在线呼叫</button>
                    <?php endif; ?>
                </div>
                <div class="mb">
                    <span>
                        <i class="o-pc-email"></i>
                    </span>
                    <span class="mls xwb"><?php echo $lang['Email']; ?></span>
                    <span class="ml xcm" id="card_email"> <%= user.email == '' ? Ibos.l("CONTACT.NOT_AVAILABLE") : user.email %></span>
                </div>
                <div class="mb">
                    <span>
                        <i class="o-pc-qq"></i>
                    </span>
                    <span class="mls xwb"><?php echo $lang['QQ']; ?></span>
                    <span class="ml xcm card-qq" id="card_qq"> <%= user.qq == '' ? Ibos.l("CONTACT.NOT_AVAILABLE") : user.qq %> </span>
                </div>
                <div class="mb">
                    <span>
                        <i class="o-pc-birthday"></i>
                    </span>
                    <span class="mls xwb"><?php echo $lang['Birthday']; ?></span>
                    <span class="ml xcm card-birthday" id="card_birthday"> <%= user.birthday == '' ? Ibos.l("CONTACT.NOT_AVAILABLE") : user.birthday %>  </span>
                </div>
                <div class="mb">
                    <span>
                        <i class="o-pc-fax"></i>
                    </span>
                    <span class="mls xwb"><?php echo $lang['Fax']; ?></span>
                    <span class="ml xcm card-fax" id="card_fax"> <%= user.fax == '' ? Ibos.l("CONTACT.NOT_AVAILABLE") : user.fax %> </span>
                </div>
            </div>
        </div>
    </div>
</script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src="<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/contact.js?<?php echo VERHASH; ?>"></script>
