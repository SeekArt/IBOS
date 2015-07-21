<?php 

use application\core\utils\String;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/artDialog/skins/ibos.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/recruit.css?<?php echo VERHASH; ?>">

<!-- Mainer -->
<div class="wrap">
	<div class="mc clearfix">
		<!-- Sidebar -->
		<?php echo $sidebar; ?>
		<!-- Sidebar end -->

		<!-- Mainer right -->
		<div class="mcr">
			<form action="<?php echo $this->createUrl( 'resume/edit', array('op'=> 'update') ); ?>" method="post" class="form-horizontal" id="resume_form">
				<div class="ct ctform bglb bdbs" id="basic_info">
					<!-- Row 1 -->
                    <div class="row">
                        <div class="span4">
                            <!-- 上传头像 -->
							<div class="control-group">
                                <div class="rsm-avt posr" id="rsm_avt_wrap" style="margin-left: 40px">
									<img id="pic_frame" height="156" width="108" src="<?php echo $resumeDetail['avatarUrl']; ?>" 
										 <?php if($resumeDetail['gender'] == '2'): ?> 
											class="rsm-avt-female" 
										<?php else: ?> 
											class="rsm-avt-male" 
										<?php endif; ?>
									/>
                                    <!--<img class="rsm-avt-male" id="pic_frame">-->
									<input type="hidden" name="avatarid" value="<?php echo $resumeDetail['avatarid'] ?>" id="avatarid">
                                    <div class="rsm-avt-upload">
                                        <?php echo $lang['Upload photo'] ?>
                                        <div class="rsm-avt-upload-btn">
                                            <span id="avatar_upload_btn"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="span8">
                            <div class="row">
                                <!-- 姓名 -->
                                <div class="span6" data-expand-target="recruitrealname" <?php if( !$dashboardConfig['recruitrealname']['visi'] ): ?>style="display: none;"<?php endif; ?>>
                                    <div class="control-group">
                                        <label for=""><?php echo $lang['Full name']; ?></label>
                                        <input id="recruitrealname" type="text" name="realname" value="<?php echo $resumeDetail['realname']; ?>">
                                    </div>
                                </div>
                                <!-- 性别 -->
                                <div class="span6" data-expand-target="recruitsex" <?php if( !$dashboardConfig['recruitsex']['visi'] ): ?>style="display: none;"<?php endif; ?>>
                                    <label class="control-label"><?php echo $lang['Sex']; ?></label>
                                    <div class="controls">
										<label class="radio radio-inline">
											<input id="recruitsex" type="radio" name="gender" value="1" <?php if( $resumeDetail['gender'] == 1 ): ?>checked<?php endif; ?>>
											<?php echo $lang['Male']; ?>
										</label>
										<label class="radio radio-inline">
											<input id="recruitsex" type="radio" name="gender" value="2" <?php if( $resumeDetail['gender'] == 2 ): ?>checked<?php endif; ?>>
											<?php echo $lang['Female']; ?>
										</label>
									</div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- 生日 -->
                                <div class="span6" data-expand-target="recruitbirthday" <?php if( !$dashboardConfig['recruitbirthday']['visi'] ): ?>style="display: none;"<?php endif; ?>>
                                    <div class="control-group">
                                        <label for=""><?php echo $lang['Date of birth']; ?></label>
                                        <div class="datepicker" id="date_time">
                                        	<a href="javascript:;" class="datepicker-btn"></a>
                                        	<input type="text" id="recruitbirthday" name="birthday" class="datepicker-input" value="<?php echo $resumeDetail['birthday']; ?>">
                                        </div>
                                    </div>
                                </div>
                                <!-- 籍贯 -->
                                <div class="span6" data-expand-target="recruitbirthplace" <?php if( !$dashboardConfig['recruitbirthplace']['visi'] ): ?>style="display: none;"<?php endif; ?>>
                                    <div class="control-group">
                                        <label for=""><?php echo $lang['Hometown']; ?></label>
                                        <input id="recruitbirthplace" type="text" name="birthplace" value="<?php echo $resumeDetail['birthplace']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
					<!-- Row 2 -->
					<div class="row">
						<!-- 工作年限 -->
						<div class="span4" data-expand-target="recruitworkyears" <?php if( !$dashboardConfig['recruitworkyears']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Work Experience']; ?></label>
                                <select name="workyears"  id="recruitworkyears">
									<option value="0"><?php echo $lang['Graduates']; ?></option>
									<option value="1"><?php echo $lang['More than one year']; ?></option>
									<option value="2"><?php echo $lang['More than two years']; ?></option>
									<option value="3"><?php echo $lang['More than three years']; ?></option>
									<option value="5"><?php echo $lang['More than five years']; ?></option>
									<option value="10"><?php echo $lang['More than a decade']; ?></option>
								</select>
								<script>$('#workyears').val('<?php echo $resumeDetail['workyears']; ?>')</script>
							</div>
						</div>
						<!-- 学历 -->
						<div class="span4" data-expand-target="recruiteducation" <?php if( !$dashboardConfig['recruiteducation']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Educational background']; ?></label>
								<select name="education"  id="recruiteducation">
									<option value="EMPTY"><?php echo $lang['Please select']; ?></option>
									<option value="JUNIOR_HIGH"><?php echo $lang['Junior high school']; ?></option>
									<option value="SENIOR_HIGH"><?php echo $lang['Senior middle school']; ?></option>
									<option value="TECHNICAL_SECONDARY"><?php echo $lang['Secondary']; ?></option>
									<option value="COLLEGE"><?php echo $lang['College']; ?></option>
									<option value="BACHELOR_DEGREE"><?php echo $lang['Undergraduate course']; ?></option>
									<option value="MASTER"><?php echo $lang['Master']; ?></option>
									<option value="DOCTOR"><?php echo $lang['Doctor']; ?></option>
								</select>
								<script>$('#education').val('<?php echo $resumeDetail['education']; ?>');</script>
							</div>
						</div>
						<!-- 简历状态 -->
                        <div class="span4" data-expand-target="recruitstatus" <?php if( !$dashboardConfig['recruitstatus']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
                                <label for=""><?php echo $lang['CV status']; ?></label>
								<select name="status"  id="recruitstatus">
									<option value="4"><?php echo $lang['To be arranged']; ?></option>
									<option value="1"><?php echo $lang['Audition']; ?></option>
									<option value="2"><?php echo $lang['Hire']; ?></option>
									<option value="5"><?php echo $lang['Eliminate']; ?></option>
								</select>
								<script>$('#status').val('<?php echo $resumeDetail['status']; ?>');</script>
							</div>
						</div>
					</div>
					<!-- Row 3 -->
					<div class="row">
						<!-- 身高 -->
                        <div class="span4" data-expand-target="recruitheight" <?php if( !$dashboardConfig['recruitheight']['visi'] ): ?>style="display: none;"<?php endif; ?>>
                            <label for=""><?php echo $lang['Height']; ?></label>
                            <div class="input-group">
                                <input id="recruitheight" type="text" name="height" value="<?php echo $resumeDetail['height']; ?>">
                                <span class="input-group-addon">CM</span>										
                            </div>
                        </div>
                        <!-- 体重 -->
                        <div class="span4" data-expand-target="recruitweight" <?php if( !$dashboardConfig['recruitweight']['visi'] ): ?>style="display: none;"<?php endif; ?>>
                            <label for=""><?php echo $lang['Body weight']; ?></label>
                            <div class="input-group">
                                <input id="recruitweight" type="text" name="weight" value="<?php echo $resumeDetail['weight']; ?>">
                                <span class="input-group-addon">KG</span>										
                            </div>
                        </div>
                        <!-- 身份证 -->
                        <div class="span4" data-expand-target="recruitidcard" <?php if( !$dashboardConfig['recruitidcard']['visi'] ): ?>style="display: none;"<?php endif; ?>>
                            <div class="control-group">
                                <label for=""><?php echo $lang['Idcard']; ?></label>
                                <input id="recruitidcard" type="text" name="idcard" value="<?php echo $resumeDetail['idcard']; ?>">
                            </div>
                        </div>
					</div>
					<!-- Row 4 -->
					<div class="row">
						<!-- 婚姻状态 -->
						<div class="span4" data-expand-target="recruitmaritalstatus" <?php if( !$dashboardConfig['recruitmaritalstatus']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label><?php echo $lang['Marital status']; ?></label>
								<div>
									<label class="radio radio-inline">
										<input id="recruitmaritalstatus" type="radio" name="maritalstatus" value="0" <?php if( $resumeDetail['maritalstatus'] ==0 ): ?>checked<?php endif; ?>>
										<?php echo $lang['Unmarried']; ?>
									</label>
									<label class="radio radio-inline">
										<input id="recruitmaritalstatus" type="radio" name="maritalstatus" value="1" <?php if( $resumeDetail['maritalstatus'] ==1 ): ?>checked<?php endif; ?>>
										<?php echo $lang['Married']; ?>
									</label>
								</div>
							</div>
						</div>	
					</div>
					<!-- Row5 -->
                    <div>
                        <div class="alternate-bar">
                            <a href="javascript:;" class="btn btn-small btn-fix" data-action="expandAll" data-expand-all="basic_info">+</a>
                            <?php if( !$dashboardConfig['recruitrealname']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitrealname"><?php echo $lang['Full name']; ?></a<span>></span>/
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitsex']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitsex"><?php echo $lang['Sex']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitbirthday']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitbirthday"><?php echo $lang['Date of birth']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitbirthplace']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitbirthplace"><?php echo $lang['Hometown']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitworkyears']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitworkyears"><?php echo $lang['Work years']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruiteducation']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruiteducation"><?php echo $lang['Record of formal schooling']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitstatus']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitstatus"><?php echo $lang['CV status']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitidcard']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitidcard"><?php echo $lang['Idcard']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitheight']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitheight"><?php echo $lang['Height']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitweight']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitweight"><?php echo $lang['Body weight']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitmaritalstatus']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitmaritalstatus"><?php echo $lang['Marital status']; ?></a><span>/</span>
                            <?php endif; ?>
                        </div>
                    </div>
				</div>
                <!--联系方式start-->
                <div class="field-title"><h4><?php echo $lang['Contact mode']; ?></h4></div>
				<div class="ct ctform" id="contact_info">
					<!-- Row 6 -->
					<div class="row">
						<div class="span8" data-expand-target="recruitresidecity" <?php if( !$dashboardConfig['recruitresidecity']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Residence address']; ?></label>
								<input id="recruitresidecity" type="text" name="residecity" value="<?php echo $resumeDetail['residecity']; ?>">
							</div>
						</div>
                        <!-- 邮编 -->
						<div class="span4" data-expand-target="recruitzipcode" <?php if( !$dashboardConfig['recruitzipcode']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Zipcode']; ?></label>
								<input id="recruitzipcode" type="text" name="zipcode" value="<?php echo $resumeDetail['zipcode']; ?>">
							</div>
						</div>
					</div>
					<!-- Row 7 -->
					<div class="row">
						<!-- 手机 -->
                        <div class="span4" data-expand-target="recruitmobile" <?php if( !$dashboardConfig['recruitmobile']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Phone']; ?></label>
								<input id="recruitmobile" type="text" name="mobile" value="<?php echo $resumeDetail['mobile']; ?>">
							</div>
						</div>
						<!-- 邮箱 -->
                        <div class="span4" data-expand-target="rucruitemail" <?php if( !$dashboardConfig['rucruitemail']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Email']; ?></label>
								<input id="rucruitemail" type="text" name="email" value="<?php echo $resumeDetail['email']; ?>">
							</div>
						</div>
						<!-- 电话 -->
						<div class="span4" data-expand-target="recruittelephone" <?php if( !$dashboardConfig['recruittelephone']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Telephone']; ?></label>
								<input  id="recruittelephone" type="text" name="telephone" value="<?php echo $resumeDetail['telephone']; ?>">
							</div>
						</div>
					</div>
                    <!-- Row 8 -->
                    <div class="row">
						<!-- QQ -->
                        <div class="span4" data-expand-target="recruitqq" <?php if( !$dashboardConfig['recruitqq']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['QQ']; ?></label>
								<input id="recruitqq" type="text" name="QQ" value="<?php echo $resumeDetail['qq']; ?>">
							</div>
						</div>
						<!-- MSN -->
						<div class="span4" data-expand-target="recruitmsn" <?php if( !$dashboardConfig['recruitmsn']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['MSN']; ?></label>
								<input id="recruitmsn" type="text" name="MSN" value="<?php echo $resumeDetail['msn']; ?>">
							</div>
						</div>
                    </div>
					<!-- Row 9 -->
                    <div>
                        <div class="alternate-bar">
                            <a href="javascript:;" class="btn btn-small btn-fix" data-action="expandAll" data-expand-all="contact_info">+</a>
                            <?php if( !$dashboardConfig['recruitresidecity']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitresidecity"><?php echo $lang['Residence address']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitzipcode']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitzipcode"><?php echo $lang['Zipcode']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitmobile']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitmobile"><?php echo $lang['Phone']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['rucruitemail']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="rucruitemail"><?php echo $lang['Email']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruittelephone']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruittelephone"><?php echo $lang['Telephone']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitqq']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitqq"><?php echo $lang['QQ']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitmsn']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitmsn"><?php echo $lang['MSN']; ?></a><span>/</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!--联系方式end-->
                <!--求职意向start-->
                <div class="field-title"><h4><?php echo $lang['Job target']; ?></h4></div>
                <div class="ct ctform" id="apply_info">
					<!-- Row 10 -->
					<div class="row">
						<!-- 到岗时间 -->
						<div class="span4" data-expand-target="recruitbeginworkday" <?php if( !$dashboardConfig['recruitbeginworkday']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Work time']; ?></label>
								<input id="recruitbeginworkday" type="text" name="beginworkday" value="<?php echo $resumeDetail['beginworkday']; ?>">
							</div>
						</div>
						<!-- 应聘岗位 -->
						<div class="span4" data-expand-target="recruittargetposition" <?php if( !$dashboardConfig['recruittargetposition']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Job candidates']; ?></label>
								<input type="text" name="positionid" id="recruittargetposition" value="<?php echo String::wrapId( $resumeDetail['positionid'], 'p' ); ?>">
							</div>
						</div>
						<!-- 期望月薪 -->
                        <div class="span4" data-expand-target="recruitexpectsalary" <?php if( !$dashboardConfig['recruitexpectsalary']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Salary expectations']; ?></label>
								<input id="recruitexpectsalary" type="text" name="expectsalary" value="<?php echo $resumeDetail['expectsalary']; ?>">
							</div>
						</div>
					</div>
                    <!-- Row 11 -->
                    <div class="row">
						<!-- 工作地点 -->
						<div class="span4" data-expand-target="recruitworkplace" <?php if( !$dashboardConfig['recruitworkplace']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Workplace']; ?></label>
								<input id="recruitworkplace" type="text" name="workplace" value="<?php echo $resumeDetail['workplace']; ?>">
							</div>
						</div>
					</div>
					<!-- Row 12 -->
                    <div>
                        <div class="alternate-bar">
                            <a href="javascript:;" class="btn btn-small btn-fix" data-action="expandAll" data-expand-all="apply_info">+</a>
                            <?php if( !$dashboardConfig['recruitbeginworkday']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitbeginworkday"><?php echo $lang['Work time']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruittargetposition']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruittargetposition"><?php echo $lang['Job candidates']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitexpectsalary']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitexpectsalary"><?php echo $lang['Salary expectations']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitworkplace']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitworkplace"><?php echo $lang['Workplace']; ?></a><span>/</span>
                            <?php endif; ?>
                        </div>
                    </div>
				</div>
                <!--求职意向end-->
                <!--详细信息start-->
                <div class="field-title"><h4><?php echo $lang['Details']; ?></h4></div>
                <div class="ct ctform" id="detail_info">
					<!-- Row 13 -->
					<div class="row">
						 <!-- 简历来源 -->
						<div class="span4" data-expand-target="recruitrecchannel" <?php if( !$dashboardConfig['recruitrecchannel']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Resume source']; ?></label>
								<select name="recchannel"  id="recruitrecchannel">
									<option value="<?php echo $lang['Alliance']; ?>"><?php echo $lang['Alliance']; ?></option>
									<option value="<?php echo $lang['Worry-free future']; ?>"><?php echo $lang['Worry-free future']; ?></option>
									<option value="<?php echo $lang['Southern talent']; ?>"><?php echo $lang['Southern talent']; ?></option>
									<option value="<?php echo $lang['Other']; ?>"><?php echo $lang['Other']; ?></option>
									<option value="<?php echo $lang['None']; ?>"><?php echo $lang['None']; ?></option>
								</select>
								<script>$('#recchannel').val('<?php echo $lang['Alliance']; ?>');</script>
							</div>
						</div>
						 <!-- 工作经历 -->
						<div class="span12" data-expand-target="recruitworkexperience" <?php if( !$dashboardConfig['recruitworkexperience']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Work Experience']; ?></label>
								<textarea id="recruitworkexperience" name="workexperience" rows="4" cols="20"><?php echo $resumeDetail['workexperience']; ?></textarea>
							</div>
						</div>
						 <!-- 项目经验 -->
						<div class="span12" data-expand-target="recruitprojectexperience" <?php if( !$dashboardConfig['recruitprojectexperience']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Project Experience']; ?></label>
								<textarea  id="recruitprojectexperience" name="projectexperience" rows="4" cols="20"><?php echo $resumeDetail['projectexperience']; ?></textarea>
							</div>
						</div>
						 <!-- 教育背景 -->
						<div class="span12" data-expand-target="recruiteduexperience" <?php if( !$dashboardConfig['recruiteduexperience']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Educational background']; ?></label>
								<textarea id="recruiteduexperience" name="eduexperience" rows="4" cols="20"><?php echo $resumeDetail['eduexperience']; ?></textarea>
							</div>
						</div>
						 <!-- 语言能力 -->
						<div class="span12" data-expand-target="recruitlangskill" <?php if( !$dashboardConfig['recruitlangskill']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Language skills']; ?></label>
								<textarea id="recruitlangskill" name="langskill" rows="4" cols="20"><?php echo $resumeDetail['langskill']; ?></textarea>
							</div>
						</div>
						 <!-- IT技能 -->
						<div class="span12" data-expand-target="recruitcomputerskill" <?php if( !$dashboardConfig['recruitcomputerskill']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['IT skills']; ?></label>
								<textarea id="recruitcomputerskill" name="computerskill" rows="4" cols="20"><?php echo $resumeDetail['computerskill']; ?></textarea>
							</div>
						</div>
						 <!-- 职业技能 -->
						<div class="span12" data-expand-target="recruitprofessionskill" <?php if( !$dashboardConfig['recruitprofessionskill']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Vocational skills']; ?></label>
								<textarea id="recruitprofessionskill" name="professionskill" rows="4" cols="20"><?php echo $resumeDetail['professionskill']; ?></textarea>
							</div>
						</div>
						 <!-- 培训经历 -->
						<div class="span12" data-expand-target="recruittrainexperience" <?php if( !$dashboardConfig['recruittrainexperience']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Training experience']; ?></label>
								<textarea id="recruittrainexperience" name="trainexperience" rows="4" cols="20"><?php echo $resumeDetail['trainexperience']; ?></textarea>
							</div>
						</div>
						 <!-- 自我评价 -->
						<div class="span12" data-expand-target="recruitselfevaluation" <?php if( !$dashboardConfig['recruitselfevaluation']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Self-evaluation']; ?></label>
								<textarea id="recruitselfevaluation" name="selfevaluation" rows="4" cols="20"><?php echo $resumeDetail['selfevaluation']; ?></textarea>
							</div>
						</div>
						 <!-- 相关证书 -->
						<div class="span12" data-expand-target="recruitrelevantcertificates" <?php if( !$dashboardConfig['recruitrelevantcertificates']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Relevant certificate']; ?></label>
								<textarea id="recruitrelevantcertificates" name="relevantcertificates" rows="4" cols="20"><?php echo $resumeDetail['relevantcertificates']; ?></textarea>
							</div>
						</div>
						 <!-- 社会实践 -->
						<div class="span12" data-expand-target="recruitsocialpractice" <?php if( !$dashboardConfig['recruitsocialpractice']['visi'] ): ?>style="display: none;"<?php endif; ?>>
							<div class="control-group">
								<label for=""><?php echo $lang['Social practice']; ?></label>
								<textarea id="recruitsocialpractice" name="socialpractice" rows="4" cols="20"><?php echo $resumeDetail['socialpractice']; ?></textarea>
							</div>
						</div>
					</div>
					<!-- Row 14 -->
                    <div>
                        <div class="alternate-bar">
                            <a href="javascript:;" class="btn btn-small btn-fix" data-action="expandAll" data-expand-all="detail_info">+</a>
                            <?php if( !$dashboardConfig['recruitrecchannel']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitrecchannel"><?php echo $lang['Resume source']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitworkexperience']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitworkexperience"><?php echo $lang['Work Experience']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitprojectexperience']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitprojectexperience"><?php echo $lang['Project Experience']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruiteduexperience']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruiteduexperience"><?php echo $lang['Educational background']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitlangskill']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitlangskill"><?php echo $lang['Language skills']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitcomputerskill']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitcomputerskill"><?php echo $lang['IT skills']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitprofessionskill']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitprofessionskill"><?php echo $lang['Vocational skills']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruittrainexperience']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruittrainexperience"><?php echo $lang['Training experience']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitselfevaluation']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitselfevaluation"><?php echo $lang['Self-evaluation']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitrelevantcertificates']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitrelevantcertificates"><?php echo $lang['Relevant certificate']; ?></a><span>/</span>
                            <?php endif; ?>
                            <?php if( !$dashboardConfig['recruitsocialpractice']['visi'] ): ?>
                            <a href="javascript:;" data-action="expandItem" data-expand="recruitsocialpractice"><?php echo $lang['Social practice']; ?></a><span>/</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!--详细信息end-->
                <!--附件start-->
                <div class="field-title"><h4><?php echo $lang['Attachment']; ?></h4></div>
                <div>
					<!-- Row 15 -->
					<div class="att">
						<div class="attb">
							<span id="upload_btn"></span>
							<!-- 文件柜 -->
							<button type="button" class="btn btn-icon vat" data-action="selectFile" data-param='{"target": "#file_target", "input": "#attachmentid"}'>
								<i class="o-folder-close"></i>
							</button>
							<input type="hidden" id="attachmentid" name="attachmentid" value="<?php echo $resumeDetail['attachmentid'] ?>">
							<span class="mls"><?php echo $lang['File size limit'] ?><?php echo $uploadConfig['max']/1024; ?>MB</span>
						</div>
						<div>
							<div class="attl" id="file_target">
								<?php if ( isset( $resumeDetail['attach'] ) ): ?>
								<?php $attachArr = array_values( $resumeDetail['attach'] ); ?>
								<?php foreach ( $attachArr as $k => $fileInfo ): ?>
								<div class="attl-item" data-node-type="attachItem">
									<a href="javascript:;" title="删除附件" class="cbtn o-trash" data-id="<?php echo $fileInfo['aid']; ?>" data-node-type="attachRemoveBtn"></a>
									<i class="atti">
										<img width="44" height="44" src="<?php echo $fileInfo['iconsmall']; ?>" alt="<?php echo $fileInfo['filename']; ?>" title="<?php echo $fileInfo['filename']; ?>">
									</i>
									<div class="attc"><?php echo $fileInfo['filename']; ?></div>
								</div>
								<?php endforeach; ?>
								<?php endif; ?>
							</div>
						</div>
					</div>
                    <!--附件end-->
                </div>
                <div class="fill">
					<div id="submit_bar" class="clearfix">
						<div class="pull-right">
							<button type="submit" class="btn btn-large btn-submit btn-primary"><?php echo $lang['Submit']; ?></button>
						</div>
					</div>
					<input type="hidden" name="resumeid" value="<?php echo $resumeDetail['resumeid']; ?>">
                    <input type="hidden" name="detailid" value="<?php echo $resumeDetail['detailid']; ?>">
				</div>
			</form>	
		</div>
	</div>
</div>
<script>
    Ibos.app.setPageParam({
        // 各字段验证规则
        "resumeFieldRule": '<?php echo $dashboardConfigToJson; ?>',
        // 各验证规则详情
        "resumeFieldRuleRegex": '<?php echo $regulars; ?>'
    });
</script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/recruit.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/recruit_resume_add.js?<?php echo VERHASH; ?>'></script>

