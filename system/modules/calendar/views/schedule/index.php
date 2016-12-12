<?php

use application\core\utils\Ibos;
use application\modules\calendar\utils\Calendar;

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/calendar.css?<?php echo VERHASH; ?>">
<link href="<?php echo STATICURL; ?>/js/lib/fullcalendar/fullcalendar.css?<?php echo VERHASH; ?>" rel="stylesheet"
      type="text/css"/>
<!-- Schedule start -->
<div class="wrap">
    <div class="mc clearfix">
        <!-- Sidebar start -->
        <?php echo $this->getSidebar(); ?>
        <!-- Sidebar end -->
        <!-- Mainer right -->
        <div class="mcr">
            <div class="mc-header">
                <div class="mc-header-info clearfix">
                    <div class="usi-terse">
                        <a href="" class="avatar-box">
							<span class="avatar-circle">
								<img class="mbm" src="<?php echo $user['avatar_middle']; ?>" alt="">
							</span>
                        </a>
                        <span class="usi-terse-user"><?php echo $user['realname']; ?></span>
                        <span class="usi-terse-group"><?php echo $user['deptname']; ?></span>
                    </div>
                </div>
            </div>
            <div class="page-list">
                <div id="calendar" class="fc-ibos"></div>
                <input type="hidden" id="time_range"/>
            </div>
        </div>
        <!-- Mainer right end -->
    </div>
</div>

<!-- Template: 编辑对话框模板 -->
<script type="text/template" id="cal_edit_tpl">
    <div class="cal-dl">
        <!-- 删除周期性日程 -->
        <% if (isLoopRemove) { %>
        <div>
            <h5>删除周期性日程</h5>
            <div class="mbs">
                <button type="button" data-cal="removeLoop" data-loop="only" class="btn btn-small">仅此次日程</button>
                此系列中的其他所有日程均会保留。
            </div>
            <div class="mbs">
                <button type="button" data-cal="removeLoop" data-loop="after" class="btn btn-small">所有的后续日程</button>
                此活动和所有后续日程均会被删除。
            </div>
            <div class="mbs">
                <button type="button" data-cal="removeLoop" data-loop="all" class="btn btn-small">此系列的所有日程</button>
                此系列中的所有日程均会被删除。
            </div>
            <div>
                <button type="button" data-cal="returnEdit" class="btn btn-small pull-right">返回</button>
            </div>
        </div>
        <!-- 查看、编辑、新建 -->
        <% } else { %>
        <div class="mb">
            <% if(isNew || isEdit) { %>
            <a href="javascript:;" class="cal-dl-colorpicker" data-color="<%=color%>"
               style="background-color: <%=color%>"></a>
            <% } %>
            <span><%=interval%></span>
        </div>
        <div class="cal-dl-content mb">
            <% if (isNew || isEdit) { %>
            <div class="cal-dl-content-editor">
                <textarea rows="4"><%=title%></textarea>
            </div>
            <% } else { %>
            <div class="cal-dl-content-body <% if(status == '1') { %>cal-dl-finish<% } %>"
                 style="border-color: <%=color%>; "><%=title%>
            </div>
            <% } %>
        </div>
        <div class="cal-dl-toolbar">
            <% if(status == "1") { %>
				<span class="fss">
					已完成
				</span>
            <% } %>
            <div class="pull-right">
                <% if (isNew) { %>
                <button class="btn btn-small btn-primary" data-cal="save">创建日程</button>
                <% } else if (isEdit) { %>
                <button class="btn btn-small" data-cal="returnEdit">取消</button>
                <button class="btn btn-small btn-primary" data-cal="save">保存</button>
                <% } else { %>
                <button class="btn btn-small" data-cal="remove">删除</button>
                <button class="btn btn-small" data-cal="finish"><%= status == "1" ? "取消完成" : "完成" %></button>
                <% } %>
            </div>
        </div>
        <% } %>
    </div>
</script>
<script type="text/template" id="cal_info_menu_tpl">
    <div class="cal-info-menu">
        <p>时间：<%= time %></p>
        <p>活动：<%= evt %></p>
        <p>属性：<%= type %></p>
    </div>
</script>

<!-- Schedule end -->

<script src='<?php echo STATICURL; ?>/js/lib/moment.min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/fullcalendar/fullcalendar.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/fullcalendar/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>

<script type="text/template" id="tpl_calender_list">
    <div class="clearfix time-switch">
        <div class="btn-group ml pull-right">
            <button type="button" class="btn" data-action="changeTime" data-type="subtract" id="prev_month">
                <i class="glyphicon-chevron-left"></i>
            </button>
            <button type="button" class="btn" data-action="changeTime" data-type="add" id="next_month">
                <i class="glyphicon-chevron-right"></i>
            </button>
        </div>
    </div>
    <div class="calendar-list-wrap">
        <ul class="calendar-list">
            <% var _lastDay = ""; %>
            <% if(dataArray.length) { %>
            <% for(var i = 0; i < dataArray.length; i++ ) { %>
            <li
            <% if(dataArray[i].day === _lastDay) { %>class="same-day" <%} else{ %>class="diff-day" <% }%>>
            <div class="cal-main-item">
                <table class="table table-hover cal-table">
                    <tbody>
                    <tr>
                        <td width="1">
                            <div class="color-info" style="background-color:<%= dataArray[i].color %>"></div>
                        </td>
                        <td width="100">
                            <div class="clearfix cal-date">
                                <div class="pull-left cal-day-time"><%= dataArray[i].day %></div>
                                <div class="pull-left mls fss">
                                    <p><%= dataArray[i].week %></p>
                                    <p><%= dataArray[i].yearAndMonth %></p>
                                </div>
                            </div>
                            <% _lastDay = dataArray[i].day;%>
                        </td>
                        <td width="100">
										<span class="tcm">
											<% if(dataArray[i].allDay) { %>
												全天
											<% }else{ %>
												<%= dataArray[i].start %> - <%= dataArray[i].end %>
											<% } %>
										</span>
                        </td>
                        <td>
                            <span
                                class="xcm <% if (dataArray[i].status == '1') { %> cal-finish <% }else{ %> <% } %>fc-title"><%= dataArray[i].title %></span>
                        </td>
                        <td width="80">
                            <a href="javascript:;"
                               class="<% if ( dataArray[i].status == '0') { %>o-ok <% }else{ %> o-finish <% } %> cbtn"
                            <% if ( dataArray[i].status == '0') { %> data-action="finishCal" <% }else{ %>
                            data-action="unfinishCal" <% } %> data-id="<%= dataArray[i].id %>" title="完成"></a>
                            <a href="javascript:;" class="o-trash cbtn mls" data-action="deleteCal"
                               data-id="<%= dataArray[i].id %>" title="删除"></a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            </li>
            <% } %>
            <% } %>
        </ul>
    </div>
</script>

<script>
    Ibos.app.s({
        calSettings: {
            // 日程开始时间
            minTime: <?php echo Calendar::getSetupStartTime(Ibos::app()->user->uid); ?>,
            // 日程结束时间
            maxTime: <?php echo Calendar::getSetupEndTime(Ibos::app()->user->uid); ?>,
            hiddenDays: [<?php echo Calendar::getSetupHiddenDays(Ibos::app()->user->uid); ?>],
            uid: Ibos.app.g('uid'),
            addable: true,
            editable: true
        },
        FORMHASH: "<?php echo FORMHASH; ?>",
        curPage: 'Index'
    });
</script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/calendar.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/calendarins.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/calendar_schedule_index.js?<?php echo VERHASH; ?>'></script>
