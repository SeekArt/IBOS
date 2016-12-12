<?php

use application\core\utils\StringUtil;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/report.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php echo $this->getSidebar(); ?>
        <!-- Mainer right -->
        <form action="<?php echo $this->createUrl('default/edit', array('op' => 'update')); ?>" id="report_form"
              method="post">
            <div class="mcr">
                <div class="page-list">
                    <div class="ctform">
                        <div class="mb">
                            <input type="text" name="subject" id="" value="<?php echo $report['subject']; ?>">
                        </div>
                        <div>
                            <label><?php echo $lang['Reporting to']; ?></label>
                            <input type="text" name="toid" id="rp_to"
                                   value="<?php echo StringUtil::wrapId($report['toid']); ?>">
                            <div id="rp_to_box"></div>
                        </div>
                    </div>
                    <div class="page-list-mainer">
                        <table class="rp-detail-table">
                            <!-- 工作小结 -->
                            <tbody id="rp_complete">
                            <tr>
                                <td colspan="3">
                                    <div class="fill-ss">
                                        <div class="pull-right">
                                            <div class="calendar-group pull-left">
                                                <div class="datepicker form_datetime" id="date_summary_start"
                                                     data-value="<?php echo date('Y-m-d', $report['begindate']); ?>">
                                                    <a href="javascript:;" class="datepicker-btn"></a>
                                                    <input type="text" class="datepicker-input" name="begindate"
                                                           value="<?php echo date('Y-m-d', $report['begindate']); ?>">
                                                </div>
                                                <span class="sep"><?php echo $lang['To']; ?></span>
                                                <div class="datepicker form_datetime" id="date_summary_end">
                                                    <a href="javascript:;" class="datepicker-btn"></a>
                                                    <input type="text" class="datepicker-input" name="enddate"
                                                           value="<?php echo date('Y-m-d', $report['enddate']); ?>">
                                                </div>
                                            </div>
                                            <div class="btn-group ml">
                                                <button type="button" class="btn" id="date_summary_prev"
                                                        data-action="prevSummaryDate"
                                                        data-param='{"type": "<?php echo $reportType['intervaltype']; ?>", "intervals": "<?php echo $reportType['intervals']; ?>" }'>
                                                    <i class="glyphicon-chevron-left"></i>
                                                </button>
                                                <button type="button" class="btn" id="date_summary_next"
                                                        data-action="nextSummaryDate"
                                                        data-param='{"type": "<?php echo $reportType['intervaltype']; ?>", "intervals": "<?php echo $reportType['intervals']; ?>" }'>
                                                    <i class="glyphicon-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <h4><?php echo $lang['Work summary']; ?></h4>
                                    </div>
                                </td>
                            </tr>
                            <!-- 原计划 -->
                            <tr>
                                <th width="68" class="sep"><?php echo $lang['Original plan']; ?></th>
                                <td width="3" class="sep"></td>
                                <td>
                                    <ul class="np-list" id="rp_in_plan_list">
                                        <?php foreach ($orgPlanList as $k1 => $orgPlan): ?>
                                            <li class="np-list-row">
                                                <div class="bamboo-pgb pull-right">
                                                    <span class="pull-left fss xcn"
                                                          id="processbar_info_<?php echo $orgPlan['recordid'] ?>"><?php echo $orgPlan['process'] * 10 ?>
                                                        %</span>
                                                    <span data-toggle="bamboo-pgb"
                                                          data-id="<?php echo $orgPlan['recordid'] ?>"></span>
                                                    <input type="hidden"
                                                           id="processinput_<?php echo $orgPlan['recordid'] ?>"
                                                           name="orgPlan[<?php echo $orgPlan['recordid'] ?>][process]"
                                                           value="<?php echo $orgPlan['process'] ?>">
                                                </div>
                                                <span class="rp-detail-num" data-toggle="badge"><?php echo $k1 + 1 ?>
                                                    .</span> <?php echo $orgPlan['content']; ?>
                                                <div class="rp-exec-status">
                                                    <span><?php echo $lang['Implementation']; ?>：</span>
                                                    <input type="text"
                                                           name="orgPlan[<?php echo $orgPlan['recordid'] ?>][exedetail]"
                                                           class="input-small span6"
                                                           value="<?php echo $orgPlan['exedetail']; ?>">

                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            <!-- 计划外 -->
                            <tr>
                                <th width="68" class="sep"><?php echo $lang['Outside plan']; ?></th>
                                <td class="sep" width="3"></td>
                                <td>
                                    <ul class="np-list bdbs" id="rp_out_plan_list">
                                        <?php foreach ($outSidePlanList as $k2 => $outSidePlan): ?>
                                            <li class="np-list-row"
                                                data-id="<?php echo $outSidePlan['recordid'] + 100; ?>">
                                                <div class="bamboo-pgb pull-right">
                                                    <a href="javascript:;" class="o-trash cbtn pull-right ml"
                                                       title="<?php echo $lang['Delete']; ?>"
                                                       data-id="<?php echo $outSidePlan['recordid'] + 100; ?>"></a>
                                                    <span class="pull-left fss xcn"
                                                          id="processbar_info_<?php echo $outSidePlan['recordid'] + 100; ?>"><?php echo $outSidePlan['process'] * 10 ?>
                                                        %</span>
                                                    <span data-toggle="bamboo-pgb"
                                                          data-id="<?php echo $outSidePlan['recordid'] + 100; ?>"></span>
                                                    <input
                                                        id="processinput_<?php echo $outSidePlan['recordid'] + 100; ?>"
                                                        type="hidden"
                                                        name="outSidePlan[<?php echo $outSidePlan['recordid'] + 100; ?>][process]"
                                                        value="<?php echo $outSidePlan['process']; ?>">
                                                </div>
                                                <span class="rp-detail-num"
                                                      data-toggle="badge"><?php echo count($orgPlanList) + $k2 + 1; ?>
                                                    .</span>
                                                <input type="text"
                                                       name="outSidePlan[<?php echo $outSidePlan['recordid'] + 100; ?>][content]"
                                                       class="rp-input span7"
                                                       value="<?php echo $outSidePlan['content']; ?>"
                                                       data-id="<?php echo $outSidePlan['recordid'] + 100; ?>">
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="fill-sn">
                                        <a href="javascript:;" class="add-one" id="rp_report_add">
                                            <i class="cbtn o-plus"></i>
                                            <?php echo $lang['Add one item']; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                            <tbody>
                            <!-- 工作总结 -->
                            <tr>
                                <th class="sep" width="68"><?php echo $lang['Work']; ?>
                                    <br/><?php echo $lang['Summary']; ?></th>
                                <td class="sep" width="3"></td>
                                <td>
                                    <div style="min-height: 375px">
                                        <script type="text/plain" name="content"
                                                id="editor"><?php echo $report['content']; ?></script>
                                    </div>
                                </td>
                            </tr>
                            <!-- 附件 -->
                            <tr>
                                <th class="sep" width="68"><?php echo $lang['Attachement']; ?></th>
                                <td class="sep" width="3"></td>
                                <td>
                                    <div class="att">
                                        <div class="attb">
                                            <span id="upload_btn"></span>
                                            <button type="button" class="btn btn-icon vat" data-action="selectFile"
                                                    data-param='{"target": "#file_target", "input": "#attachmentid"}'>
                                                <i class="o-folder-close"></i>
                                            </button>
                                        </div>
                                        <div>
                                            <div class="attl" id="file_target" data-node-type="attachList">
                                                <?php foreach ($attachs as $attach): ?>
                                                    <div class="attl-item" data-node-type="attachItem">
                                                        <a href="javascript:;"
                                                           title="<?php echo $lang['Delete'] . $lang['Attachement']; ?>"
                                                           class="cbtn o-trash" data-id="<?php echo $attach['aid']; ?>"
                                                           data-node-type="attachRemoveBtn"></a>
                                                        <i class="atti"><img width="44" height="44"
                                                                             src="<?php echo $attach['iconsmall']; ?>"
                                                                             alt="<?php echo $attach['filename']; ?>"
                                                                             title="<?php echo $attach['filename']; ?>"></i>
                                                        <div class="attc"><?php echo $attach['filename']; ?></div>
														<span class="fss mlm">
															<a href="<?php echo $attach['downurl']; ?>"
                                                               target="_blank"><?php echo $lang['Download']; ?></a>
                                                            <?php if (isset($attach['officereadurl'])): ?>
                                                                <a href="javascript:;" class="mlm"
                                                                   data-action="viewOfficeFile"
                                                                   data-param='{"href": "<?php echo $attach['officereadurl']; ?>"}'
                                                                   title="<?php echo $lang['View']; ?>">
                                                                    <?php echo $lang['View']; ?>
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (isset($attach['officeediturl'])): ?>
                                                                <a href="javascript:;" class="mlm"
                                                                   data-action="editOfficeFile"
                                                                   data-param='{"href": "<?php echo $attach['officeediturl']; ?>"}'
                                                                   title="<?php echo $lang['Edit']; ?>">
                                                                    <?php echo $lang['Edit']; ?>
                                                                </a>
                                                            <?php endif; ?>
                                                            <!-- 转存到文件柜，等实现文件柜功能再开启 -->
                                                            <!--<a href="#">转存到文件柜</a>-->
														</span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <input type="hidden" name="attachmentid" id="attachmentid"
                                       value="<?php echo $report['attachmentid']; ?>"/>
                            </tr>
                            </tbody>
                            <!-- 计划 -->
                            <tbody id="rp_plan">
                            <tr>
                                <td colspan="3">
                                    <div class="fill-ss">
                                        <div class="pull-right">
                                            <div class="calendar-group pull-left">
                                                <div class="datepicker form_datetime" id="date_plan_start"
                                                     data-value="<?php echo date('Y-m-d', $nextPlanDate['planBegindate']); ?>">
                                                    <a href="javascript:;" class="datepicker-btn"></a>
                                                    <input type="text" class="datepicker-input" name="planBegindate"
                                                           value="<?php echo date('Y-m-d', $nextPlanDate['planBegindate']); ?>">
                                                </div>
                                                <span class="sep"><?php echo $lang['To']; ?></span>
                                                <div class="datepicker form_datetime" id="date_plan_end">
                                                    <a href="javascript:;" class="datepicker-btn"></a>
                                                    <input type="text" class="datepicker-input" name="planEnddate"
                                                           value="<?php echo date('Y-m-d', $nextPlanDate['planEnddate']); ?>">
                                                </div>
                                            </div>
                                            <div class="btn-group ml">
                                                <button type="button" class="btn" id="date_plan_prev"
                                                        data-action="prevPlanDate"
                                                        data-param='{"type": "<?php echo $reportType['intervaltype']; ?>", "intervals": "<?php //echo $intervals; ?>" }'>
                                                    <i class="glyphicon-chevron-left"></i>
                                                </button>
                                                <button type="button" class="btn" id="date_plan_next"
                                                        data-action="nextPlanDate"
                                                        data-param='{"type": "<?php echo $reportType['intervaltype']; ?>", "intervals": "<?php //echo $intervals; ?>" }'>
                                                    <i class="glyphicon-chevron-right"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <h4><?php echo $lang['Work plan']; ?></h4>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th width="68" class="sep" id="rp_plan_rowspan"><?php echo $lang['Work']; ?>
                                    <br/><?php echo $lang['Plan']; ?></th>
                                <td class="sep" width="3"></td>
                                <td>
                                    <ul class="np-list bdbs" id="rp_new_plan_list"></ul>
                                    <div class="fill-sn">
                                        <a href="javascript:;" class="add-one" id="rp_plan_add">
                                            <i class="cbtn o-plus"></i>
                                            <?php echo $lang['Add one item']; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="fill-sn">
                            <button type="button" class="btn btn-large btn-submit"
                                    onclick="history.back();"><?php echo $lang['Return']; ?></button>
                            <button type="submit"
                                    class="btn btn-large btn-submit btn-primary pull-right"><?php echo $lang['Save']; ?></button>
                        </div>
                    </div>
                </div>
                <!-- Mainer content -->
            </div>
            <input type="hidden" name="repid" value="<?php echo $report['repid']; ?>"/>
            <input type="hidden" name="typeid" value="<?php echo $report['typeid']; ?>"/>
            <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
        </form>
    </div>
</div>

<!-- @Template: 新建工作小结模板 -->
<script type="text/template" id="tpl_rp_out_plan">
    <li class="np-list-row" data-id="<%= id %>">
        <div class="bamboo-pgb pull-right">
            <a href="javascript:;" class="o-trash cbtn pull-right ml" title="<%= Ibos.l('DELETE') %>"
               data-id="<%= id %>"></a>
            <span class="pull-left fss xcn" id="processbar_info_<%= id %>">100%</span>
            <span data-toggle="bamboo-pgb" data-id="<%= id %>"></span>
            <input type="hidden" id="processinput_<%= id %>" name="outSidePlan[<%= id %>][process]" value="10"/>
        </div>
        <span class="rp-detail-num" data-toggle="badge"><%= index %>.</span>
        <input type="text" name="outSidePlan[<%= id %>][content]" data-id="<%= id %>" class="rp-input span7"
               placeholder="<%= Ibos.l('RP.CLICK_TO_WRITE_RECORD') %>">
    </li>
</script>

<!-- @Template: 新建工作计划模板 -->
<script type="text/template" id="tpl_rp_new_plan">
    <li class="np-list-row" data-id="<%= id %>">
        <div class="vernier rp-vernier-item"></div>
        <input type="hidden" name="nextPlan[<%= id %>][reminddate]" class="remind-value" value="<%= reminddate %>"
               nofocus>
        <div class="rp-vernier-size"></div>
        <div class="posr">
            <div class="pull-right">
                <% if(Ibos.app.g("isInstallCalendar") == 1){ %>
                <div class="rp-remind-bar"
                <% if(reminddate) { %>style="display: inline-block"<% } %>>
                <i class="o-clock"></i>
                <span class="remind-time"><%= reminddate %></span>
                <a href="javascript:;" class="o-close-small"></a>
            </div>
            <a href="javascript:;" class="co-clock remind-time-btn" title="<%= Ibos.l('RP.SETUP_REMIND') %>" <%
            if(reminddate) { %>style="display: none;"<% } %>></a>
            <% } %>
            <a href="javascript:;" class="cbtn o-trash mlm" data-id="<%= id %>" title="<%= Ibos.l('DELETE') %>"></a>
        </div>
        <span class="rp-detail-num" data-toggle="badge"><%= index %></span>
        <input type="text" name="nextPlan[<%= id %>][content]" class="rp-input span7" value="<%= content %>"
               data-id="<%= id %>" placeholder="<%= Ibos.l('RP.CLICK_TO_WRITE_PLAN') %>">
        </div>
    </li>
</script>

<script>
    Ibos.app.s({
        "isInstallCalendar": "<?php echo $isInstallCalendar ?>",
        "newPlan": <?php echo CJSON::encode($nextPlanList); ?>
    });
</script>

<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/moment.min.js?<?php echo VERHASH; ?>'></script>

<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report_default_common.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/report_default_index.js?<?php echo VERHASH; ?>'></script>
