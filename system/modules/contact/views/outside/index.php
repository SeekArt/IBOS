<?php

use application\core\utils\Cloud;
use application\core\utils\Convert;

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
						<a href="javascript:;" class="btn btn-primary" data-action="addContacter">添加联系人</a>
						<div class="btn-group mlm">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<?php echo $lang['Batch operation']; ?>
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<li><a href="javascript:;" data-action="educeCont" data-uids="<?php echo $uids; ?>"><?php echo $lang['Export contacts']; ?></a></li>
								<li><a href="javascript:;" data-action="printCont" data-uids="<?php echo $uids; ?>"><?php echo $lang['Pring contacts']; ?></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="page-list-mainer">
				<div class="cl-rolling-sidebar" id="cl_rolling_sidebar">
					<div class="personal-info" id="personal_info" style="width:520px; height:100%;"></div>
				</div>
				<div class="cl-rolling-sidebar" id="add_contacter_wrap">
					<div class="personal-info">
						<div class="personal-info-wrap">
							<a href="javascript:;" class="cl-window-ctrl" id="close_add_wrap" data-action="closeAddMunberWrap"></a>
							<div class="mb">
								<span class="fsl">添加外部联系人</span>
							</div>
							<form action="" method="post" id="add_user_form">
								<div class="clearfix mb">
									<div class="pull-left">
										<label class="pc-avatar-wrap" id="pc_avatar_wrap">
											<div class="upload-trigger">
												<span id="upload_img" class="upload-img"></span>
											</div>
											<input type="hidden" id='img_src' name='src' />
											<div class="img-upload-imgwrap" id="portrait_img_wrap">
												<img class="portrait-img" id="portrait_img" src="">
											</div>
											<div class="tip-tier" id="tip_tier">
												<div class="tip-bg"></div>
												<div class="tip-content">重新上传</div>
											</div>
										</label>
									</div>
									<div class="pull-left ml">
										<div class="clearfix mb">
											<div class="pull-left pc-name-tip">
												<span>姓名</span>
											</div>
											<div class="pull-right pc-name-input">
												<input type="text" name="name" id="add_user_name" >
											</div>
										</div>
										<div class="clearfix">
											<span class="dib pull-left pc-sex-tip">性别</span>
											<div class="sex-radio-wrap dib clearfix">
												<label class="radio radio-inline pull-left">
													<input type="radio" name="sex" value="男" checked="checked" />男
												</label>
												<label class="radio radio-inline pull-left ml">
													<input type="radio" class="radio" name="sex" value="女" />女
												</label>
											</div>
										</div>
									</div>
								</div>
								<div class="pc-info-wrap">
									<div class="clearfix mb">
										<span class="pull-left pc-list-tip">公司</span>
										<input type="text" name="conpanyname" class="pull-right span9" />
									</div>
									<div class="clearfix mb">
										<span class="pull-left pc-list-tip">职务</span>
										<div class="pul-right pc-position-info">
											<input type="text" placeholder="部门" name="" class="dib">
											<input type="text" placeholder="岗位" name="" class="dib mls">
										</div>
									</div>
									<div class="clearfix mb">
										<span class="pull-left pc-list-tip">手机</span>
										<input type="text" name="phone" class="pull-right span9" id="add_user_phone" />
									</div>
									<div class="clearfix mb">
										<span class="pull-left pc-list-tip">邮箱</span>
										<input type="text" name="email" class="pull-right span9" id="add_user_email" />
									</div>
									<div class="clearfix mb">
										<span class="pull-left pc-list-tip">QQ</span>
										<input type="text" name="QQ" class="pull-right span9" id="add_user_qq" />
									</div>
									<div class="clearfix mb">
										<span class="pull-left pc-list-tip">生日</span>
										<div class="datepicker pull-right span9" id="date_time">
											<a href="javascript:;" class="datepicker-btn"></a>
											<input type="text" class="datepicker-input" name="birthday">
										</div>
									</div>
									<div class="clearfix mb">
										<span class="pull-left pc-list-tip">传真</span>
										<input type="text"  name="birthday" class="datepicker-input pull-right span9" id="birthday" />
									</div>
								</div>
								<div class="clearfix">
									<a href="javascript:;" class="btn btn-large pull-left" data-action="closeAddMunberWrap">取消</a>
									<button type="submit" class="btn btn-large btn-primary pull-right">保存</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="posr ovh clearfix">
					<?php if ( count( $datas ) > 0 ): ?>
						<div class="exist-data">
							<div class="pull-left">
								<?php foreach ( $datas as $letter => $users ): ?>
									<div class="group-item">
										<div class="cl-letter-title fsst" id="target_<?php echo $letter; ?>"><?php echo $letter; ?></div>
										<table class="table table-hover common-uer-table contact-list" id="contact_list">
											<tbody>
												<?php foreach ( $users as $k => $user ): ?>
													<tr id="cl_tr_<?php echo $user['uid']; ?>" data-id="<?php echo $user['uid']; ?>" class="contact-list-item" data-preg="<?php echo $user['realname'] . Convert::getPY( $user['realname'] ) . Convert::getPY( $user['realname'], true ); ?>">
														<td width="50">
															<div class="avatar-box">
																<span class="avatar-circle">
																	<span class="last-name-wrap"></span>
																</span>
															</div>
														</td>
														<td width="90">
															<span class="xcm pc-name"><?php echo $user['realname']; ?></span>
														</td>
														<td width="103">
															<span class="fss"><?php echo $user['posname']; ?></span>
														</td>
														<td width="120">
															<span class="fss"><?php echo $user['telephone']; ?></span>
														</td>
														<td width="133">
															<span class="fss"><?php echo $user['mobile']; ?></span>
														</td>
														<td width="163">
															<div class="w140">
																<span class="fss"><?php echo $user['email']; ?></span>
															</div>
														</td>                                                      
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="cl-letter-sidebar" id="cl_letter_sidebar">
								<ul class="letter-list">
									<?php $existLetters = array_keys( $datas ); ?>
									<?php foreach ( $allLetters as $theLetter ): ?>
										<li>
											<?php if ( !in_array( $theLetter, $existLetters ) ): ?>
												<a href="javascript:;" class="cl-letter">
													<span class="inexistence-letter"><?php echo $theLetter; ?></span>
												</a>
											<?php else: ?>
												<a href="#<?php echo $theLetter; ?>" class="cl-letter letter-mark" id="#link_<?php echo $theLetter; ?>" data-id="<?php echo $theLetter; ?>">
													<span><?php echo $theLetter; ?></span>
												</a>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
						<div class="inexist-data">
							<div class="no-data-tip"></div>
						</div>
					<?php else: ?>
						<div class="no-data-tip"></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
    Ibos.app.setPageParam({
        "deptid": "<?php echo intval( Env::getRequest( 'deptid' ) ); ?>"
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
<script type="text/template" id="tpl_rolling_sidebar">
   <div class="personal-info" id="personal_info">
        <div class="cl-pc-top posr">
            <div class="cl-pc-banner">
                <img src= <%= user.bg_big %> id="card_bg"></div>
            <div class="cl-pc-usi">
                <div class="cl-pc-bg"></div>
                <div class="cl-pc-avatar posr">
                   <div class="rolling-name-wrap">
						<span class="rolling-name-content" id="rolling_name_content"></span>
					</div>
                </div>
            </div>
            <div class="cl-uic-operate">
                <a href= <%= "javascript:Ibos.showCallingDialog(" + user.uid + ");void(0);" %> title="打电话" class="co-tcall"></a>
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
<script src="<?php echo $assetUrl; ?>/js/contactList.js?<?php echo VERHASH; ?>"></script>
