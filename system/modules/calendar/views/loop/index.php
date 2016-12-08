<?php

?>
<!-- private css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/calendar.css?<?php echo VERHASH; ?>">
<!-- Loop start -->
<div class="mc clearfix">
    <!-- Sidebar start -->
    <?php echo $this->getSidebar(); ?>
    <!-- Sidebar end -->
    <!-- Loop right start -->
    <div class="mcr">
        <div class="page-list">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <button class="btn btn-primary pull-left" data-action="addLoop"><?php echo $lang['New']; ?></button>
                    <button class="btn pull-left" data-action="deleteLoops"><?php echo $lang['Delete']; ?></button>
                </div>
            </div>
            <div class="page-list-mainer">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th width="20">
                            <label class="checkbox">
                                <input type="checkbox" data-name="loop[]">
                            </label>
                        </th>
                        <th><?php echo $lang['Subject']; ?></th>
                        <th width="160"><?php echo $lang['Last modified date']; ?></th>
                        <th width="240"><?php echo $lang['Cycle']; ?></th>
                        <th width="70"><?php echo $lang['Operation']; ?></th>
                    </tr>
                    </thead>
                    <tbody id="loop_tbody"></tbody>
                </table>
                <div class="no-data-tip" style="display:none" id="no_data_tip"></div>
            </div>
            <div class="page-list-footer">
                <div class="pull-right">
                    <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Loop right end -->
</div>
<!-- Loop end -->

<!-- 新增周期性事务窗口 -->

<div id="loop_dialog" style="width: 520px; display: none;">
    <form method="post" name="" id="add_calendar_form">
        <div class="mb">
            <textarea id="loop_subject" style="height:100px;"
                      placeholder="<?php echo $lang['No subject']; ?>"></textarea>
        </div>
        <div>
            <div class="row mb">
                <div class="span5">
                    <div class="input-group datepicker" style="" id="loop_start_time_datepicker" style="width: 180px;">
                        <span class="input-group-addon"><?php echo $lang['From']; ?></span>
                        <a href="javasrcipt:;" class="datepicker-btn"></a>
                        <input type="text" id="loop_start_time" class="datepicker-input pull-left">
                    </div>
                </div>
                <div class="span5">
                    <div class="input-group datepicker" id="loop_end_time_datepicker">
                        <span class="input-group-addon"><?php echo $lang['To']; ?></span>
                        <a href="javasrcipt:;" class="datepicker-btn"></a>
                        <input type="text" id="loop_end_time" class="datepicker-input pull-left">
                    </div>
                </div>
                <div class="span2">
                    <div class="color-picker-btn pull-right" id="color_picker_btn"></div>
                    <input type="hidden" id="loop_theme" value="0">
                </div>
            </div>
        </div>
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label for="loop_type" class="control-label"><?php echo $lang['Repeat']; ?></label>
                <div class="controls">
                    <select name="" id="loop_type" class="span6">
                        <option value="week"><?php echo $lang['Weekly']; ?></option>
                        <option value="month"><?php echo $lang['Per month']; ?></option>
                        <option value="year"><?php echo $lang['Per year']; ?></option>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Repetition time']; ?></label>
                <div class="controls">
                    <!-- 周循环 -->
                    <div id="repeat_per_week">
                        <label class="checkbox checkbox-inline">一<input type="checkbox" name="weekbox[]"
                                                                        value="1"></label>
                        <label class="checkbox checkbox-inline">二<input type="checkbox" name="weekbox[]"
                                                                        value="2"></label>
                        <label class="checkbox checkbox-inline">三<input type="checkbox" name="weekbox[]"
                                                                        value="3"></label>
                        <label class="checkbox checkbox-inline">四<input type="checkbox" name="weekbox[]"
                                                                        value="4"></label>
                        <label class="checkbox checkbox-inline">五<input type="checkbox" name="weekbox[]"
                                                                        value="5"></label>
                        <label class="checkbox checkbox-inline">六<input type="checkbox" name="weekbox[]"
                                                                        value="6"></label>
                        <label class="checkbox checkbox-inline">日<input type="checkbox" name="weekbox[]"
                                                                        value="7"></label>
                    </div>
                    <!-- 月循环 -->
                    <div id="repeat_per_month" style="display:none;">
                        <select id="loop_month_day" class="span6">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">18</option>
                            <option value="19">19</option>
                            <option value="20">20</option>
                            <option value="21">21</option>
                            <option value="22">22</option>
                            <option value="23">23</option>
                            <option value="24">24</option>
                            <option value="25">25</option>
                            <option value="26">26</option>
                            <option value="27">27</option>
                            <option value="28">28</option>
                            <option value="29">29</option>
                            <option value="30">30</option>
                            <option value="31">31</option>
                        </select>
                        <a href="javascript:;" class="datepicker-btn"></a>
                    </div>
                    <!-- 年循环 -->
                    <div id="repeat_per_year" style="display:none;">
                        <div class="datepicker span6" id="loop_year_day_picker">
                            <input type="text" value="" id="loop_year_day" class="datepicker-input">
                            <a href="javascript:;" class="datepicker-btn"></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Start date']; ?></label>
                <div class="controls">
                    <div class="datepicker span6" id="loop_start_day_datepicker">
                        <input type="text" value="<?php echo date('Y-m-d', time()); ?>" id="loop_start_day"
                               class="datepicker-input">
                        <a href="javascript:;" class="datepicker-btn"></a>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['End date']; ?></label>
                <div class="controls">
                    <div class="datepicker span6" id="loop_end_day_datepicker">
                        <input type="text" value="" id="loop_end_day" class="datepicker-input">
                        <a href="javascript:;" class="datepicker-btn"></a>
                    </div>
                    <?php echo $lang['Empty does not end']; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- 插入联系信息模板 -->
<script type="text/template" id="loop_template">
    <tr id="loop_row_<%=calendarid%>">
        <td>
            <label class="checkbox">
                <input type="checkbox" name="loop[]" value="<%=calendarid%>">
            </label>
        </td>
        <td>
            <span class="cal-theme-square" style="background-color: <%=bgcolor%>"></span>
            <a href="javascript:"><%=subject%></a>
        </td>
        <td>
            <%=uptime%>
        </td>
        <td>
            <%=cycle%>
        </td>
        <td>
            <a href="javascript:" data-action="editLoop" data-id="<%=calendarid%>" title="<?php echo $lang['Edit']; ?>"
               class="cbtn o-edit"></a>
            <a href="javascript:" data-action="deleteLoop" data-id="<%=calendarid%>"
               title="<?php echo $lang['Delete']; ?>" class="cbtn o-trash"></a>
        </td>
    </tr>
</script>

<script>
    Ibos.app.s({
        "loopList": <?php echo CJSON::encode($loopList); ?>
    })
</script>
<script src='<?php echo STATICURL ?>/js/lib/moment.min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/calendar_loop_index.js?<?php echo VERHASH; ?>'></script>
