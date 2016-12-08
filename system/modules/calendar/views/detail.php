<!-- 日程详情对话框 -->
<div id="d_cal_detail" class="form-horizontal form-compact" style="width:400px;">
    <form>
        <div class="control-group" style="margin-bottom: 10px;">
            <label class="control-label">内容</label>
            <div class="controls">
                <textarea name="content" rows="5"></textarea>
            </div>
        </div>
        <div class="control-group">
            <!-- 未完成状态 -->
            <div class="controls hide">
                <button type="button" class="btn btn-small btn-widen" data-complete="0">完成</button>
                <span class="pull-right lht" style="display: none;">已完成</span>
            </div>
            <!-- 完成状态 -->
            <div class="controls">
                <button type="button" class="btn btn-small btn-widen" data-complete="1">取消完成</button>
                <span class="pull-right lht">已完成</span>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">全天</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox" name="fullday" id="cal_fullday">
                </label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">主题</label>
            <div class="controls">
                <!-- @Todo: 样式待完善 -->
                <a href="javascript:;" class="color-picker" id="cal_color"><span
                        style="background-color: #99C8E8;"></span></a>
                <input type="hidden" name="theme">
            </div>
        </div>
        <!-- 当为时间段时，默认显示-->
        <div id="cal_time_interval">
            <div class="control-group">
                <label class="control-label">开始时间</label>
                <div class="controls">
                    <div class="input-operates date form_datetime" id="date_time_start"
                         data-date-enddate="2013-07-19 16:00">
                        <input type="text" readonly value="2013-07-19 15:00" name="starttime">
                        <div class="input-operate">
                            <a href="javascript:;" class="operate-btn">
                                <i class="o-ex-calendar"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">结束时间</label>
                <div class="controls">
                    <div class="input-operates date form_datetime" id="date_time_end"
                         data-date-startdate="2013-07-19 15:00">
                        <input type="text" readonly value="2013-07-19 16:00" name="endtime">
                        <div class="input-operate">
                            <a href="javascript:;" class="operate-btn">
                                <i class="o-ex-calendar"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 当为全天时，默认显示 -->
        <div id="cal_date_interval" style="display:none">
            <div class="control-group">
                <label class="control-label">开始时间</label>
                <div class="controls">
                    <div class="input-operates date form_datetime" id="date_start">
                        <input type="text" readonly name="startday">
                        <div class="input-operate">
                            <a href="javascript:;" class="operate-btn">
                                <i class="o-ex-calendar"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>