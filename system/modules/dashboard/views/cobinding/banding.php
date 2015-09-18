<form action="#" method="post" id="bind_info_form">
    <div class="dialog-info-wrap">
        <div class="match-btn-wrap">
            <button type="button" class="btn btn-primary" data-action="matchAction">自动匹配酷办公与OA相同的名字</button>
        </div>
        <div class="manual-bind-wrap">
            <div class="toggle-hook">
                <span class="info-toggle-hook active" data-action="infoDisplayToggle" data-self="user_info_wrap" data-target="result_info_wrap">
                    <i class="caret"></i>
                    <span>手动绑定</span>
                </span>
            </div>
            <div id="user_info_wrap" style="display:none;">
                <div class="clearfix match-type-wrap">
                    <div class="span6">
                        <label for="iboscoUser" class="xwb">酷办公用户</label>
                        <select id="iboscoUser" size="10">
                        </select>
                    </div>
                    <div class="span6">
                        <label for="oaUser" class="xwb">OA用户</label>
                        <select id="oaUser" size="10">
                        	<option value="1">管理员</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="btn mls" data-action="ceratRelationship" id="relative_btn">建立绑定关系</button>
            </div>
        </div>
        <div class="manual-bind-wrap mbs">
            <div class="toggle-hook">
                <span class="info-toggle-hook active" data-action="infoDisplayToggle" data-self="result_info_wrap" data-target="user_info_wrap">
                    <i class="caret"></i>
                    <span>已绑定</span>
                    <span id="binding_user" class="fsl xcbu"></span>
                    <span>人，</span>
                    <span class="mlm">新添加</span>
                    <span class="fsl" id="addCount">0</span>
                    <span>人</span>
                </span>
            </div>
        </div>
        <div id="result_info_wrap">
            <div class="clearfix result-opt-wrap">
                <button type="button" class="btn pull-left" data-action="clearAll">清空</button>
            </div>
            <div class="page-list">
                <table class="table table-hover table-striped result-info-table" id="result_info_table">
                    <thead>
                        <tr>
                            <th>酷办公用户姓名</th>
                            <th>OA用户姓名</th>
                            <th width="40">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <input type="hidden" name="map" id="result_map_value" value="">
                <input type="hidden" name="_csrf" value='<?php echo Ibos::$app->getRequest()->getCsrfToken(); ?>' />
            </div>
        </div>
    </div>
</form>
<script src="<?php echo $assetUrl; ?>/js/db_cobinding_banding.js"></script>