<form class="form-horizontal form-narrow fill" id="bind_form" method="post">
    <div class="control-group">
        <label class="control-label">绑定用户</label>
        <div class="controls">
            <div class="row mb">
                <div class="span6">
                    <label for="oauser">OA用户</label>
                    <select id="oauser" size="10">
                        <?php foreach ($ibosUsers as $user): ?>
                            <option value="<?php echo $user['uid']; ?>"><?php echo $user['realname']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="span6">
                    <label for="bqquser">企业QQ用户</label>
                    <?php if (!empty($bqqUsers)): ?>
                        <select id="bqquser" size="10">
                            <?php foreach ($bqqUsers as $buser): ?>
                                <option
                                    value="<?php echo $buser['open_id']; ?>"><?php echo $buser['realname']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        无法获取企业QQ用户列表
                    <?php endif; ?>
                </div>
            </div>
            <button type="button" class="btn" id="relative_btn">建立对应关系</button>
            <button type="button" class="btn" id="filter_same">筛选相同姓名</button>
        </div>
    </div>
    <div class="control-group">
        <label for="subflow_field_map" class="control-label">绑定关系</label>
        <div class="controls controls-content span6">
            <ul id="field_map_list"></ul>
            <input type="hidden" name="map" id="field_map_value" value="">
            <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
        </div>
    </div>
</form>
<script src="<?php echo $this->getAssetUrl(); ?>/js/db_im_qqbinding.js"></script>
<script>
    <?php if ( !empty($binds) ): ?>
    var data = '<?php echo json_encode($binds); ?>';
    var obj = $.parseJSON(data);
    $.each(obj, function (i, n) {
        var oaobj = $('#oauser').find("option[value='" + n.uid + "']");
        var bqqobj = $('#bqquser').find("option[value='" + n.bindvalue + "']");
        fieldMatchUp.add(n.uid, n.bindvalue, oaobj.text(), bqqobj.text());
    });
    <?php endif; ?>
</script>