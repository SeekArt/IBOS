<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/recruit.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php echo $sidebar; ?>
        <!-- Mainer right -->
        <div class="mcr">
            <!-- Mainer content -->
            <div class="ct ctview ct-affix">
                <div class="btn-toolbar noprint" data-spy="affix" data-offset-top="70">
                    <div class="btn-group">
                        <a href="<?php echo $this->createUrl('resume/edit', array('resumeid' => $resumeDetail['resumeid'])); ?>"
                           class="btn btn-primary pull-left"><?php echo $lang['Edit']; ?></a>
                    </div>
                    <div class="btn-group">
                        <a href="javascript:;" class="btn" onclick="window.print()"><?php echo $lang['Print']; ?></a>
                    </div>
                    <div class="pull-right">
                        <div class="btn-group">
                            <a <?php if (!empty($prevAndNextPK['prevPK'])): ?>
                                href="<?php echo $this->createUrl('resume/show', array('resumeid' => $prevAndNextPK['prevPK'])); ?>" class="btn" title="<?php echo $lang['Prev resume'] ?>"
                            <?php else: ?>
                                href="javascript:;" class="btn disabled" title="<?php echo $lang['This is the latest resume']; ?>"
                            <?php endif; ?>>
                                <i class="glyphicon-chevron-left"></i>
                            </a>
                            <a <?php if (!empty($prevAndNextPK['nextPK'])): ?>
                                href="<?php echo $this->createUrl('resume/show', array('resumeid' => $prevAndNextPK['nextPK'])); ?>" class="btn" title="<?php echo $lang['next resume'] ?>"
                            <?php else: ?>
                                href="javascript:;" class="btn disabled" title="<?php echo $lang['No sooner resume']; ?>"
                            <?php endif; ?>>
                                <i class="glyphicon-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- 简历信息 -->
                <div>
                    <div id="resume_detail">
                        <?php if ($resumeDetail['status'] == 1): ?>
                            <i class="o-rsm-status-interview"></i>
                        <?php elseif ($resumeDetail['status'] == 2): ?>
                            <i class="o-rsm-status-pass"></i>
                        <?php elseif ($resumeDetail['status'] == 4): ?>
                            <i class="o-rsm-status-arranged"></i>
                        <?php elseif ($resumeDetail['status'] == 5): ?>
                            <i class="o-rsm-status-eliminate"></i>
                        <?php endif; ?>
                        <!-- 基本信息 -->
                        <div class="bglb bdpt bdbs fill clearfix">
                            <div class="pull-left">
                                <div class="rsm-avt">
                                    <img <?php if (!empty($resumeDetail['avatarUrl'])): ?> src="<?php echo $resumeDetail['avatarUrl']; ?>" <?php elseif ($resumeDetail['gender'] == '女'): ?> class="rsm-avt-female" <?php else: ?> class="rsm-avt-male" <?php endif; ?>
                                        height="156" width="108"/>
                                </div>
                            </div>
                            <div class="rsm-psn-info">
                                <div class="rsm-psn-header">
                                    <h2 class="pull-left"><?php echo $resumeDetail['realname']; ?></h2>
                                    <span class="ml tcm"><?php echo $resumeDetail['gender']; ?></span>
                                    <span class="ml tcm"><?php echo $resumeDetail['age']; ?></span>
                                    <span class="ml tcm"><?php echo $resumeDetail['maritalstatus']; ?></span>
                                    <a href="javascript:;" class="pull-right fss noprint"
                                       data-action="togglePersonalDetail"><?php echo $lang['View complete details']; ?>
                                        <i class="caret caret-small vam mls"></i></a>
                                </div>
                                <div>
                                    <table class="rsm-psn-table" id="rsm_psn_table">
                                        <tbody>
                                        <tr>
                                            <th><?php echo $lang['Job candidates']; ?></th>
                                            <td style="color: #3497db;"><?php echo $resumeDetail['targetposition']; ?>
                                                <?php if (!empty($resumeDetail['beginworkday'])): ?>
                                                    （<?php echo $resumeDetail['beginworkday']; ?>）
                                                <?php endif; ?>
                                            </td>
                                            <th><?php echo $lang['Salary expectations']; ?></th>
                                            <td><?php echo $resumeDetail['expectsalary']; ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo $lang['Work years']; ?></th>
                                            <td><?php echo $resumeDetail['workyears']; ?></td>
                                            <th><?php echo $lang['Record of formal schooling']; ?></th>
                                            <td><?php echo $resumeDetail['education']; ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo $lang['Phone']; ?></th>
                                            <td style="line-height: 40px"><?php echo $resumeDetail['mobile']; ?><i
                                                    class="o-rsm-phone pull-right"></i></td>
                                            <th><?php echo $lang['Mail']; ?></th>
                                            <td style="line-height: 40px"><?php echo $resumeDetail['email']; ?><i
                                                    class="o-rsm-email pull-right"></i></td>
                                        </tr>
                                        <tr class="rsm-row-hide">
                                            <th><?php echo $lang['Hometown']; ?></th>
                                            <td><?php echo $resumeDetail['birthplace']; ?></td>
                                            <th><?php echo $lang['Idcard']; ?></th>
                                            <td><?php echo $resumeDetail['idcard']; ?></td>
                                        </tr>
                                        <tr class="rsm-row-hide">
                                            <th><?php echo $lang['Height']; ?></th>
                                            <td><?php echo $resumeDetail['height']; ?></td>
                                            <th><?php echo $lang['Body weight']; ?></th>
                                            <td><?php echo $resumeDetail['weight']; ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- 工作经验 -->
                        <div>
                            <div class="field-title">
                                <h4><?php echo $lang['Work Experience']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['workexperience']; ?>
                            </div>
                        </div>
                        <!-- 项目经验 -->
                        <div>
                            <div class="field-title">
                                <h4><?php echo $lang['Project Experience']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['projectexperience']; ?>
                            </div>
                        </div>
                        <!-- 教育背景 -->
                        <div>
                            <div class="field-title">
                                <h4><?php echo $lang['Educational background']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['eduexperience']; ?>
                            </div>
                        </div>
                        <!-- 语言能力 -->
                        <div data-expand-target="recruitlangskill" style="display: none">
                            <div class="field-title">
                                <h4><?php echo $lang['Language skills']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['langskill']; ?>
                            </div>
                        </div>
                        <!-- IT技能 -->
                        <div data-expand-target="recruitcomputerskill" style="display: none">
                            <div class="field-title">
                                <h4><?php echo $lang['IT skills']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['computerskill']; ?>
                            </div>
                        </div>
                        <!-- 职业技能 -->
                        <div data-expand-target="recruitprofessionskill" style="display: none">
                            <div class="field-title">
                                <h4><?php echo $lang['Vocational skills']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['professionskill']; ?>
                            </div>
                        </div>
                        <!-- 培训经历 -->
                        <div data-expand-target="recruittrainexperience" style="display: none">
                            <div class="field-title">
                                <h4><?php echo $lang['Training experience']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['trainexperience']; ?>
                            </div>
                        </div>
                        <!-- 自我评价 -->
                        <div data-expand-target="recruitselfevaluation" style="display: none">
                            <div class="field-title">
                                <h4><?php echo $lang['Self-evaluation']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['selfevaluation']; ?>
                            </div>
                        </div>
                        <!-- 相关证书 -->
                        <div data-expand-target="recruitrelevantcertificates" style="display: none">
                            <div class="field-title">
                                <h4><?php echo $lang['Relevant certificate']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['relevantcertificates']; ?>
                            </div>
                        </div>
                        <!-- 社会实践 -->
                        <div data-expand-target="recruitsocialpractice" style="display: none">
                            <div class="field-title">
                                <h4><?php echo $lang['Social practice']; ?></h4>
                            </div>
                            <div class="fill">
                                <?php echo $resumeDetail['socialpractice']; ?>
                            </div>
                        </div>
                        <!-- 显示更多栏目 -->
                        <div class="fill">
                            <div class="alternate-bar noprint">
                                <a href="javascript:;" class="btn" data-action="expandAll"
                                   data-expand-all="resume_detail">+</a>
                                <a href="javascript:;" data-action="expandItem"
                                   data-expand="recruitlangskill"> <?php echo $lang['Language skills']; ?></a>
                                <span>/</span>
                                <a href="javascript:;" data-action="expandItem"
                                   data-expand="recruitcomputerskill"> <?php echo $lang['IT skills']; ?></a>
                                <span>/</span>
                                <a href="javascript:;" data-action="expandItem"
                                   data-expand="recruitprofessionskill"> <?php echo $lang['Vocational skills']; ?></a>
                                <span>/</span>
                                <a href="javascript:;" data-action="expandItem"
                                   data-expand="recruittrainexperience"> <?php echo $lang['Training experience']; ?></a>
                                <span>/</span>
                                <a href="javascript:;" data-action="expandItem"
                                   data-expand="recruitselfevaluation"> <?php echo $lang['Self-evaluation']; ?></a>
                                <span>/</span>
                                <a href="javascript:;" data-action="expandItem"
                                   data-expand="recruitrelevantcertificates"> <?php echo $lang['Relevant certificate']; ?></a>
                                <span>/</span>
                                <a href="javascript:;" data-action="expandItem"
                                   data-expand="recruitsocialpractice"> <?php echo $lang['Social practice']; ?></a>
                            </div>

                        </div>
                        <!-- 联系方式 -->
                        <div>
                            <div class="field-title">
                                <h4><?php echo $lang['Contact mode']; ?></h4>
                            </div>
                            <div class="fill">
                                <dl class="tcol-list">
                                    <dt><?php echo $lang['Residence address']; ?></dt>
                                    <dd><?php echo $resumeDetail['residecity']; ?><?php echo ' ( ' . $lang['Zipcode'] . ':' . $resumeDetail['zipcode'] . ' )'; ?></dd>
                                    <dt><?php echo $lang['Phone']; ?></dt>
                                    <dd><?php echo $resumeDetail['mobile']; ?></dd>
                                    <dt><?php echo $lang['Telephone']; ?></dt>
                                    <dd><?php echo $resumeDetail['telephone']; ?></dd>
                                    <dt><?php echo $lang['QQ']; ?></dt>
                                    <dd><?php echo $resumeDetail['qq']; ?></dd>
                                    <dt><?php echo $lang['MSN']; ?></dt>
                                    <dd><?php echo $resumeDetail['msn']; ?></dd>
                                </dl>
                            </div>
                        </div>
                        <!-- 附件 -->
                        <div class="noprint">
                            <?php if (isset($resumeDetail['attach'])): ?>
                                <div class="field-title">
                                    <h4><?php echo $lang['Attachment']; ?>
                                        （<?php echo count($resumeDetail['attach']); ?><?php echo $lang['Piece']; ?>
                                        ）</h4>
                                </div>
                                <div class="fill">
                                    <ul class="attl">
                                        <?php foreach ($resumeDetail['attach'] as $fileInfo): ?>
                                            <li>
                                                <i class="atti">
                                                    <img src="<?php echo $fileInfo['iconsmall']; ?>"
                                                         alt="<?php echo $fileInfo['filename']; ?>">
                                                </i>
                                                <div class="attc">
                                                    <div class="mbm">
                                                        <?php echo $fileInfo['filename']; ?>
                                                        <span class="tcm">(<?php echo $fileInfo['filesize']; ?>)</span>
                                                    </div>
												<span class="fss">
													<a href="<?php echo $fileInfo['downurl']; ?>" target="_blank"
                                                       class="anchor"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                                    <?php if (isset($fileInfo['officereadurl'])): ?>
                                                        <a href="javascript:;" data-action="viewOfficeFile"
                                                           data-param='{"href": "<?php echo $fileInfo['officereadurl']; ?>"}'
                                                           title="<?php echo $lang['View']; ?>">
                                                            <?php echo $lang['View']; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    <!-- 转存至文件柜 -->
                                                    <!--<a href="#" class="anchor"><?php // echo $lang['Dump file cabinet']; ?></a>-->
												</span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <input type="hidden" name="resumeid" id="fullname" value="<?php echo $resumeDetail['realname']; ?>">
                    <!--新的代码-->
                    <div class="mb noprint">
                        <div>
                            <ul class="nav nav-tabs nav-tabs-large nav-justified" id="record">
                                <li class="active">
                                    <a href="javascript:;" data-toggle="tab" data-target="#contact_record">
                                        <i class="o-art-text"></i>
                                        <?php echo $lang['Contact record']; ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-toggle="tab" data-target="#interview_record">
                                        <i class="o-art-picm"></i>
                                        <?php echo $lang['Interview record']; ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-toggle="tab" data-target="#backdrop_record">
                                        <i class="o-art-link"></i>
                                        <?php echo $lang['Background investigation']; ?>
                                    </a>
                                </li>
                            </ul>
                            <div class="nav-content bdrb tab-content">
                                <!-- 联系记录 -->
                                <div id="contact_record" class="tab-pane active">
                                    <div class="page-list">
                                        <div class="page-list-header">
                                            <button class="btn btn-primary"
                                                    data-action="addContact"><?php echo $lang['Add']; ?></button>
                                            <button class="btn"
                                                    data-action="deleteContacts"><?php echo $lang['Delete']; ?></button>
                                            <button class="btn"
                                                    data-action="exportContact"><?php echo $lang['Export']; ?></button>
                                        </div>
                                        <div class="page-list-mainer">
                                            <table class="table table-striped table-hover" id="contact_list">
                                                <thead>
                                                <tr>
                                                    <th width="20">
                                                        <label class="checkbox">
                                                            <input type="checkbox" name="" data-name="contact[]"
                                                                   id="contact_all_select">
                                                        </label>
                                                    </th>
                                                    <th width="70"><?php echo $lang['Contact staff']; ?></th>
                                                    <th width="70"><?php echo $lang['Contact date']; ?></th>
                                                    <th width="70"><?php echo $lang['Contact method']; ?></th>
                                                    <th width="70"><?php echo $lang['Contact purpose']; ?></th>
                                                    <th width="100"><?php echo $lang['Content']; ?></th>
                                                    <th width="100"><?php echo $lang['Operating']; ?></th>
                                                </tr>
                                                </thead>
                                                <tbody id="contact_tbody">
                                                <?php foreach ($contactList as $contact) : ?>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox">
                                                                <input type="checkbox" name="contact[]"
                                                                       value="<?php echo $contact['contactid']; ?>">
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <?php echo $contact['input']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $contact['inputtime']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $contact['contact']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $contact['purpose']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $contact['detail']; ?>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:" data-action="editContact"
                                                               data-id="<?php echo $contact['contactid']; ?>"
                                                               title="<?php echo $lang['Update']; ?>"
                                                               class="cbtn o-edit"></a>
                                                            <a href="javascript:" data-action="deleteContact"
                                                               data-id="<?php echo $contact['contactid']; ?>"
                                                               title="<?php echo $lang['Delete']; ?>"
                                                               class="cbtn o-trash"></a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <div
                                                class="no-data-tip" <?php if (count($contactList) > 0): ?> style="display:none" <?php endif; ?>
                                                id="no_contact_tip"></div>
                                        </div>
                                    </div>

                                </div>
                                <!-- 面试记录 -->
                                <div id="interview_record" class="tab-pane ct">
                                    <div class="page-list">
                                        <div class="page-list-header">
                                            <button class="btn btn-primary"
                                                    data-action="addInterview"><?php echo $lang['Add']; ?></button>
                                            <button class="btn"
                                                    data-action="deleteInterviews"><?php echo $lang['Delete']; ?></button>
                                            <button class="btn"
                                                    data-action="exportInterview"><?php echo $lang['Export']; ?></button>
                                        </div>
                                        <div class="page-list-mainer">
                                            <table class="table table-striped table-hover" id="interview_list">
                                                <thead>
                                                <tr>
                                                    <th width="20">
                                                        <label class="checkbox">
                                                            <input type="checkbox" name="" data-name="interview[]"
                                                                   id="interview_all_select">
                                                        </label>
                                                    </th>
                                                    <th width="70"><?php echo $lang['Interview time']; ?></th>
                                                    <th width="70"><?php echo $lang['Interview people'] ?></th>
                                                    <th width="70"><?php echo $lang['Interview types']; ?></th>
                                                    <th width="100"><?php echo $lang['Interview process']; ?></th>
                                                    <th width="100"><?php echo $lang['Operating']; ?></th>
                                                </tr>
                                                </thead>
                                                <tbody id="interview_tbody">
                                                <?php foreach ($interviewList as $interview) : ?>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox">
                                                                <input type="checkbox" name="interview[]"
                                                                       value="<?php echo $interview['interviewid']; ?>">
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <?php echo $interview['interviewtime']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $interview['interviewer']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $interview['type']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $interview['process']; ?>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:" data-action="editInterview"
                                                               data-id="<?php echo $interview['interviewid']; ?>"
                                                               title="<?php echo $lang['Update']; ?>"
                                                               class="cbtn o-edit"></a>
                                                            <a href="javascript:" data-action="deleteInterview"
                                                               data-id="<?php echo $interview['interviewid']; ?>"
                                                               title="<?php echo $lang['Delete']; ?>"
                                                               class="cbtn o-trash"></a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <div
                                                class="no-data-tip" <?php if (count($interviewList) > 0): ?> style="display:none" <?php endif; ?>
                                                id="no_interview_tip"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 背景调查 -->
                                <div id="backdrop_record" class="tab-pane">
                                    <div class="page-list">
                                        <div class="page-list-header">
                                            <button class="btn btn-primary"
                                                    data-action="addBgcheck"><?php echo $lang['Add']; ?></button>
                                            <button class="btn"
                                                    data-action="deleteBgchecks"><?php echo $lang['Delete']; ?></button>
                                            <button class="btn"
                                                    data-action="exportBgcheck"><?php echo $lang['Export']; ?></button>
                                        </div>
                                        <div class="page-list-mainer">
                                            <table class="table table-striped table-hover" id="bgchecks_list">
                                                <thead>
                                                <tr>
                                                    <th width="20">
                                                        <label class="checkbox">
                                                            <input type="checkbox" name="" data-name="bgcheck[]"
                                                                   id="bgcheck_all_select">
                                                        </label>
                                                    </th>
                                                    <th width="70"><?php echo $lang['Work unit']; ?></th>
                                                    <th width="70"><?php echo $lang['Position']; ?></th>
                                                    <th width="70"><?php echo $lang['Entry time']; ?></th>
                                                    <th width="100"><?php echo $lang['Departure time']; ?></th>
                                                    <th width="100"><?php echo $lang['Operating'] ?></th>
                                                </tr>
                                                </thead>
                                                <tbody id="bgchecks_tbody">
                                                <?php foreach ($bgcheckList as $bgcheck) : ?>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox">
                                                                <input type="checkbox" name="bgcheck[]"
                                                                       value="<?php echo $bgcheck['checkid']; ?>">
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <?php echo $bgcheck['company']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $bgcheck['position']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $bgcheck['entrytime']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $bgcheck['quittime']; ?>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:" data-action="editBgcheck"
                                                               data-id="<?php echo $bgcheck['checkid']; ?>"
                                                               title="<?php echo $lang['Update']; ?>"
                                                               class="cbtn o-edit"></a>
                                                            <a href="javascript:" data-action="deleteBgcheck"
                                                               data-id="<?php echo $bgcheck['checkid']; ?>"
                                                               title="<?php echo $lang['Delete']; ?>"
                                                               class="cbtn o-trash"></a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <div
                                                class="no-data-tip" <?php if (count($bgcheckList) > 0): ?> style="display:none" <?php endif; ?>
                                                id="no_bgchecks_tip"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 增加/编辑联系记录 -->
<div id="contact_dialog" style="width: 500px; display:none;">
    <form id="contact_dialog_form">
        <input type="hidden" name="detailid" value="<?php echo $resumeDetail['detailid']; ?>">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Contact time']; ?></label>
                <div class="controls span6">
                    <div class="datepicker" id="contact_time">
                        <a href="javascript:;" class="datepicker-btn"></a>
                        <input type="text" class="datepicker-input" name="inputtime"
                               value="<?php echo date('Y-m-d', time()); ?>">
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Contact staff']; ?></label>
                <div class="controls span6">
                    <input type="text" name="upuid" data-toggle="userSelect" id="user_contact" value="">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Contact method']; ?></label>
                <div class="controls span6">
                    <select name="contact" id="contact">
                        <option value="<?php echo $lang['Telephone']; ?>"><?php echo $lang['Telephone']; ?></option>
                        <option value="<?php echo $lang['Letters']; ?>"><?php echo $lang['Letters']; ?></option>
                        <option value="<?php echo $lang['Mail']; ?>"><?php echo $lang['Mail']; ?></option>
                        <option value="<?php echo $lang['Visit']; ?>"><?php echo $lang['Visit']; ?></option>
                        <option value="<?php echo $lang['QQ']; ?>"><?php echo $lang['QQ']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Purpose']; ?></label>
                <div class="controls span6">
                    <select name="purpose" id="purpose">
                        <option
                            value="<?php echo $lang['Notification primaries']; ?>"><?php echo $lang['Notification primaries']; ?></option>
                        <option
                            value="<?php echo $lang['Tracking contact']; ?>"><?php echo $lang['Tracking contact']; ?></option>
                        <option
                            value="<?php echo $lang['Inform the interview']; ?>"><?php echo $lang['Inform the interview']; ?></option>
                        <option
                            value="<?php echo $lang['Background investigation']; ?>"><?php echo $lang['Background investigation']; ?></option>
                        <option
                            value="<?php echo $lang['Notification of results']; ?>"><?php echo $lang['Notification of results']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Content']; ?></label>
                <div class="controls">
                    <textarea name="detail" id="detail" rows="4" cols="20"></textarea>
                </div>
            </div>
        </div>
        <input type="hidden" name="detailid" value="<?php echo $resumeDetail['resumeid']; ?>"/>
    </form>
</div>

<!--增加/修改面试信息-->
<div id="interview_dialog" style="width: 500px; display:none;">
    <form id="interview_dialog_form">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview methods']; ?></label>
                <div class="controls span6">
                    <select name="method" id="method">
                        <option value="<?php echo $lang['Telephone']; ?>"><?php echo $lang['Telephone']; ?></option>
                        <option value="<?php echo $lang['Letters']; ?>"><?php echo $lang['Letters']; ?></option>
                        <option value="<?php echo $lang['Mail']; ?>"><?php echo $lang['Mail']; ?></option>
                        <option value="<?php echo $lang['Visit']; ?>"><?php echo $lang['Visit']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Type']; ?></label>
                <div class="controls span6">
                    <select name="type" id="type">
                        <option value="<?php echo $lang['First test']; ?>"><?php echo $lang['First test']; ?></option>
                        <option value="<?php echo $lang['Audition']; ?>"><?php echo $lang['Audition']; ?></option>
                        <option value="<?php echo $lang['Retest']; ?>"><?php echo $lang['Retest']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview time']; ?></label>
                <div class="controls span6">
                    <div class="datepicker" id="interview_time">
                        <a href="javascript:;" class="datepicker-btn"></a>
                        <input type="text" class="datepicker-input" name="interviewtime"
                               value="<?php echo date('Y-m-d', time()); ?>">
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview people']; ?></label>
                <div class="controls span6">
                    <input type="text" name="interviewer" data-toggle="userSelect" id="user_interview" value="">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Interview process']; ?></label>
                <div class="controls">
                    <textarea name="process" id="process" rows="4" cols="20"></textarea>
                </div>
            </div>
        </div>
        <input type="hidden" name="interviewid" id="interviewid"/>
        <input type="hidden" name="detailid" value="<?php echo $resumeDetail['resumeid']; ?>"/>
    </form>
</div>
<!--增加/修改背景记录-->
<div id="bgcheck_dialog" style="width: 500px; display:none;">
    <form id="bgcheck_dialog_form">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Work unit']; ?></label>
                <div class="controls">
                    <input id="bgcompany" type="text" name="company" value="" class="span6">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Contact people']; ?></label>
                <div class="controls">
                    <input id="bgcontact" type="text" name="contact" value="" class="span6">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Telephone']; ?></label>
                <div class="controls">
                    <input id="phone" type="text" name="phone" value="" class="span6">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Office time'] ?></label>
                <div class="controls">
                    <div class="row">
                        <div class="span6">
                            <div class="datepicker input-group" id="entrytime_datepicker">
                                <span class="input-group-addon"><?php echo $lang['From']; ?></span>
                                <a href="javascript:;" class="datepicker-btn"></a>
                                <input type="text" id="entrytime" name="entrytime" class="datepicker-input">
                            </div>
                        </div>
                        <div class="span6">
                            <div class="datepicker input-group" id="quittime_datepicker">
                                <span class="input-group-addon"><?php echo $lang['To']; ?></span>
                                <a href="javascript:;" class="datepicker-btn"></a>
                                <input type="text" id="quittime" name="quittime" class="datepicker-input">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Post office']; ?></label>
                <div class="controls">
                    <input id="bgposition" type="text" name="position" value="" class="span6">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo $lang['Details']; ?></label>
                <div class="controls">
                    <textarea name="detail" id="bgdetail" rows="4" cols="20"></textarea>
                </div>
            </div>
        </div>
        <input type="hidden" name="detailid" value="<?php echo $resumeDetail['resumeid']; ?>"/>
    </form>
</div>

<!-- 插入联系信息模板 -->
<script type="text/ibos-template" id="contact_template">
    <tr>
        <td>
            <label class="checkbox">
                <input type="checkbox" value="<%=contactid%>" name="contact[]">
            </label>
        </td>
        <td>
            <%=input%>
        </td>
        <td>
            <%=inputtime%>
        </td>
        <td>
            <%=contact%>
        </td>
        <td>
            <%=purpose%>
        </td>
        <td>
            <%=detail%>
        </td>
        <td>
            <a href="javascript:" data-action="editContact" data-id="<%=contactid%>"
               title="<?php echo $lang['Update']; ?>" class="cbtn o-edit"></a>
            <a href="javascript:" data-action="deleteContact" data-id="<%=contactid%>"
               title="<?php echo $lang['Delete']; ?>" class="cbtn o-trash"></a>
        </td>
    </tr>
</script>
<!-- 插入面试信息模板 -->
<script type="text/ibos-template" id="interview_template">
    <tr>
        <td>
            <label class="checkbox">
                <input type="checkbox" value="<%=interviewid%>" name="interview[]">
            </label>
        </td>
        <td>
            <%=interviewtime%>
        </td>
        <td>
            <%=interviewer%>
        </td>
        <td>
            <%=type%>
        </td>
        <td>
            <%=process%>
        </td>
        <td>
            <a href="javascript:" data-action="editInterview" data-id="<%=interviewid%>"
               title="<?php echo $lang['Update']; ?>" class="cbtn o-edit"></a>
            <a href="javascript:" data-action="deleteInterview" data-id="<%=interviewid%>"
               title="<?php echo $lang['Delete']; ?>" class="cbtn o-trash"></a>
        </td>
    </tr>
</script>
<!-- 插入背景信息模板 -->
<script type="text/ibos-template" id="bgchecks_template">
    <tr>
        <td>
            <label class="checkbox">
                <input type="checkbox" value="<%=checkid%>" name="bgcheck[]">
            </label>
        </td>
        <td>
            <%=company%>
        </td>
        <td>
            <%=position%>
        </td>
        <td>
            <%=entrytime%>
        </td>
        <td>
            <%=quittime%>
        </td>
        <td>
            <a href="javascript:" data-action="editBgcheck" data-id="<%=checkid%>"
               title="<?php echo $lang['Update']; ?>" class="cbtn o-edit"></a>
            <a href="javascript:" data-action="deleteBgcheck" data-id="<%=checkid%>"
               title="<?php echo $lang['Delete']; ?>" class="cbtn o-trash"></a>
        </td>
    </tr>
</script>
<script>
    Ibos.app.s({
        fullname: '<?php echo $resumeDetail['realname']; ?>'
    });
</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/recruit.js?<?php echo VERHASH; ?>'></script>
<script>
    $(function () {
        // 联系人选择框
        $("#user_contact, #user_interview").userSelect({
            type: "user",
            maximumSelectionSize: "1",
            data: Ibos.data.get("user")
        });
    });
</script>
