<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/diary.css?<?php echo VERHASH; ?>">

<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Mainer right -->
    <form action="<?php echo $this->createUrl('default/edit', array('op' => 'update')); ?>" method="post"
          id="diary_form">
        <div class="mcr">
            <div class="page-list">
                <div class="fill-ss">
                    <div class="mini-date">
                        <strong><?php echo $diary['diarytime']['day']; ?></strong>
                        <div class="mini-date-body">
                            <p><?php echo $diary['diarytime']['weekday']; ?></p>
                            <p><?php echo $diary['diarytime']['year']; ?>
                                -<?php echo $diary['diarytime']['month']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="page-list-mainer">
                    <!-- 工作记录 -->
                    <table class="da-detail-table">
                        <tbody id="da_complete">
                        <!-- 原计划 -->
                        <?php if (count($data['originalPlanList']) > 0) { ?>
                            <tr>
                                <th rowspan="<?php echo count($data['originalPlanList']) + 1; ?>" width="68"
                                    class="sep"><?php echo $lang['Original plan']; ?></th>
                            </tr>
                            <?php foreach ($data['originalPlanList'] as $key => $diaryRecord): ?>
                                <tr class="da-detail-row">
                                    <td class="sep" width="3"></td>
                                    <td>
                                        <div class="fill" data-node-type="oldPlan">
                                            <div class="bamboo-pgb pull-right">
                                                <span class="pull-left xcn fss"
                                                      id="processbar_info_<?php echo $diaryRecord['recordid']; ?>"><?php echo $diaryRecord['schedule'] * 10 . "%"; ?></span>
                                                <span data-node-type="starProgress"
                                                      data-id="<?php echo $diaryRecord['recordid']; ?>"></span>
                                                <input type="hidden"
                                                       id="processinput_<?php echo $diaryRecord['recordid']; ?>"
                                                       name="originalPlan[<?php echo $diaryRecord['recordid']; ?>]"
                                                       value="<?php echo $diaryRecord['schedule']; ?>">
                                            </div>
                                            <span class="da-detail-num" data-toggle="badge"><?php echo $key + 1; ?>
                                                .</span> <?php echo $diaryRecord['content']; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <input type="hidden" name="originalPlantime"
                                   value="<?php echo $data['originalPlanList'][0]['plantime']; ?>">
                        <?php } ?>

                        <!-- 计划外 -->
                        <?php if (count($data['outsidePlanList']) > 0): ?>
                            <tr>
                                <th id="schedule_plan" class="sep" width="68"
                                    rowspan="<?php echo count($data['outsidePlanList']) + 2; ?>"><?php echo $lang['Unplanned']; ?></th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th id="schedule_plan" class="sep" width="68"
                                    rowspan="3"><?php echo $lang['Unplanned']; ?></th>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="sep" width="3"></td>
                            <td>
                                <div class="fill-sn">
                                    <a href="javascript:;" class="add-one" data-action="addRecord">
                                        <i class="cbtn o-plus"></i>
                                        <?php echo $lang['Add one Item']; ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tbody>
                        <!-- 工作总结 -->
                        <tr>
                            <th class="sep" width="68"><?php echo $lang['Work']; ?><br/><?php echo $lang['Summary']; ?>
                            </th>
                            <td class="sep" width="3"></td>
                            <td>
                                <div style="min-height: 375px;">
                                    <script name="diaryContent" type="text/plain"
                                            id="editor"><?php echo $diary['content']; ?></script>
                                </div>
                            </td>
                        </tr>
                        <!-- 附件 -->
                        <tr>
                            <th class="sep" width="68"><?php echo $lang['Attachment']; ?></th>
                            <td class="sep" width="3"></td>
                            <td>
                                <div class="att">
                                    <div class="attb">
                                        <span id="upload_btn"></span>
                                        <!-- 文件柜,以后做到再打开 -->
                                        <button type="button" class="btn btn-icon vat" data-action="selectFile"
                                                data-param='{"target": "#file_target", "input": "#attachmentid"}'>
                                            <i class="o-folder-close"></i>
                                        </button>
                                        <input type="hidden" name="attachmentid" id="attachmentid"
                                               value="<?php echo $diary['attachmentid']; ?>">
                                    </div>
                                    <div class="attl" id="file_target">
                                        <?php if (isset($attach)): ?>
                                            <?php foreach ($attach as $value): ?>
                                                <div class="attl-item" data-node-type="attachItem">
                                                    <a href="javascript:;" title="删除附件" class="cbtn o-trash"
                                                       data-id="<?php echo $value['aid']; ?>"
                                                       data-node-type="attachRemoveBtn"></a>
                                                    <i class="atti"><img width="44" height="44"
                                                                         src="<?php echo $value['iconsmall']; ?>"
                                                                         alt="<?php echo $value['filename']; ?>"
                                                                         title="<?php echo $value['filename']; ?>"></i>
                                                    <div class="attc"><?php echo $value['filename']; ?></div>
                    								<span class="fss mlm">
                    									<a href="<?php echo $value['downurl']; ?>"
                                                           target="_blank"><?php echo $lang['Download']; ?></a>
                                                        <?php if (isset($value['officereadurl'])): ?>
                                                            <a href="javascript:;" class="mlm"
                                                               data-action="viewOfficeFile"
                                                               data-param='{"href": "<?php echo $value['officereadurl']; ?>"}'
                                                               title="<?php echo $lang['Read']; ?>">
                                                                <?php echo $lang['Read']; ?>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if (isset($value['officeediturl'])): ?>
                                                            <a href="javascript:;" class="mlm"
                                                               data-action="editOfficeFile"
                                                               data-param='{"href": "<?php echo $value['officeediturl']; ?>"}'
                                                               title="<?php echo $lang['Edit']; ?>">
                                                                <?php echo $lang['Edit']; ?>
                                                            </a>
                                                        <?php endif; ?>
                    								</span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!-- 工作计划 -->
                    <div class="clearfix">
                        <div class="mini-date fill-ss pull-left" id="da_plan_date_display">
                            <strong><?php echo $diary['nextDiarytime']['day']; ?></strong>
                            <div class="mini-date-body">
                                <p><?php echo $diary['nextDiarytime']['weekday']; ?></p>
                                <p><?php echo $diary['nextDiarytime']['year']; ?>
                                    -<?php echo $diary['nextDiarytime']['month']; ?></p>
                            </div>
                        </div>
                        <div class="fill-ss pull-left">
                            <button type="button" class="btn btn-icon" id="da_plan_date_btn">
                                <span class="o-ex-calendar vat"></span>
                            </button>
                        </div>
                        <div class="btn-group fill-ss pull-right">
                            <a href="javascript:;" class="btn" data-action="changePlanDate"
                               data-param='{"dir": "prev"}'><i class="glyphicon-chevron-left"></i></a>
                            <a href="javascript:;" class="btn" data-action="changePlanDate"
                               data-param='{"dir": "next"}'><i class="glyphicon-chevron-right"></i></a>
                        </div>
                        <div class="input-operates date form_datetime hide" id="da_plan_date">
                            <input type="text" name="plantime" readonly=""
                                   value="<?php echo $diary['nextDiarytime']['year']; ?>-<?php echo $diary['nextDiarytime']['month']; ?>-<?php echo $diary['nextDiarytime']['day']; ?>">
                        </div>
                    </div>
                    <div class="posr">
                        <table class="da-detail-table">
                            <!-- 工作计划 -->
                            <tbody id="da_plan">
                            <tr>
                                <th width="68" rowspan="4" class="sep" id="da_plan_rowspan"><?php echo $lang['Work']; ?>
                                    <br/><?php echo $lang['Plan']; ?></th>
                            </tr>
                            <tr>
                                <td class="sep" width="3"></td>
                                <td>
                                    <div class="fill">
                                        <a href="javascript:;" class="add-one" data-action="addPlan">
                                            <i class="cbtn o-plus"></i>
                                            <?php echo $lang['Add one Item']; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--默认共享-->
                    <?php if ($dashboardConfig['sharepersonnel']): ?>
                        <div class="cti">
                            <h4 class="mb"><?php echo $lang['Sharing of personnel']; ?></h4>
                            <div class="row">
                                <div class="span9">
                                    <input type="text" name="shareuid" id="da_shared"
                                           value="<?php echo $diary['shareuid']; ?>">
                                </div>
                                <div class="span3">
                                    <button type="button" class="btn pull-right"
                                            id="da_share_set"><?php echo $lang['Set as default']; ?></button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="fill">
                        <button type="button" class="btn btn-large btn-submit"
                                onclick="history.back();"><?php echo $lang['Return']; ?></button>
                        <button type="submit"
                                class="btn btn-large btn-submit btn-primary pull-right"><?php echo $lang['Save']; ?></button>
                    </div>
                </div>
            </div>
            <!-- Mainer content -->
        </div>
        <input type="hidden" name="diaryid" value="<?php echo $diary['diaryid']; ?>">
        <input type="hidden" name="diarytime" value="<?php echo $diary['originalDiarytime']; ?>">
    </form>
</div>

<!-- 新建工作日志模板 -->
<script type="text/ibos-template" id="tpl_diary_record">
    <tr class="da-detail-row">
        <td class="sep" width="3"></td>
        <td>
            <div class="fill-sn">
                <div class="pull-right">
                    <a href="javascript:;" class="o-trash cbtn pull-right ml" title="<?php echo $lang['Delete']; ?>"
                       data-id="<%=id%>"></a>
                    <span class="pull-left xcn fss" id="processbar_info_<%=id%>"><%= schedule * 10 %>%</span>
                    <span data-node-type="starProgress" data-id="<%=id%>"></span>
                    <input type="hidden" id="processinput_<%=id%>" name="planOutside[<%=id%>][schedule]"
                           value='<%=schedule%>'/>
                </div>
                <span class="da-detail-num" data-toggle="badge"></span></span>
                <input type="text" name="planOutside[<%=id%>][content]" class="da-input span7" value="<%=subject%>"
                       data-node-type="oldPlanInput" data-id="<%=id%>" placeholder="点击写日志，按回车键换行">
            </div>
        </td>
    </tr>
</script>
<!-- 新建工作计划模板 -->
<script type="text/ibos-template" id="tpl_da_plan">
    <tr class="da-detail-row">
        <td class="sep" width="3"></td>
        <td>
            <div class="da-plan-item fill <%=  range ? 'da-reminded': '' %>" data-node-type="planRow">
                <input type="hidden" name="plan[<%=id%>][timeremind]" value="<%=range%>" data-node-type="remindInput">
                <div class="posr">
                    <div class="da-plan-opbar pull-right" data-node-type="planOperate">
                        <?php if ($isInstallCalendar): ?>
                            <% if (range) { var arr = range.split(","); %>
                            <div class="da-remind-bar">
                                <i class="o-clock"></i>
                                <%= Ibos.date.numberToTime(arr[0]) %>-<%= Ibos.date.numberToTime(arr[1]) %>
                                <a href="javascript:;" class="o-close-small" data-action="removeRemind"></a>
                            </div>
                            <% } %>
                            <a href="javascript:;" class="co-clock" title="<?php echo $lang['Set remind']; ?>"
                               data-action="addRemind"></a>
                        <?php endif; ?>
                        <a href="javascript:;" class="o-trash mlm" title="<?php echo $lang['Delete']; ?>"
                           data-id="<%=id%>"></a>
                        <a href="javascript:;" class="o-ok" title="" data-action="saveRemind"></a>
                        <a href="javascript:;" class="co-close mlm" title="" data-action="cancelRemind"></a>
                    </div>
                    <span class="da-detail-num" data-toggle="badge"><%=id%></span>
                    <input type="text" name="plan[<%=id%>][content]" class="da-input span7" value="<%=subject%>"
                           data-node-type="newPlanInput" data-id="<%=id%>" placeholder="点击写计划，按回车键换行">
                </div>
            </div>
        </td>
    </tr>
</script>

<!-- Page Param  -->
<script>
    // 这里的参数由后端输出， 属于可后台配置的
    <?php if($isInstallCalendar): ?>
    Ibos.app.setPageParam({
        // 提醒是否可用
        isRemindAvailable: true,
        // 标尺设置（即上班时间范围）
        scaleplateSettings: {
            cell: <?php echo $workTime['cell']; ?>,
            min: <?php echo $workTime['start']; ?>,
            step: 0.5,
            subcell: 2
        }
    })
    <?php endif; ?>

    Ibos.app.setPageParam({
        unplannedData: <?php echo CJSON::encode($data['outsidePlanList']); ?>,
        planData: <?php echo CJSON::encode($data['tomorrowPlanList']); ?>
    })
</script>

<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>

<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/src/belt.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary_default_common.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/diary_default_edit.js?<?php echo VERHASH; ?>'></script>
