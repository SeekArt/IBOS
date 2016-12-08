<div class="limit-edit-dialog" id="limit_edit_dialog">
    <form class="form-horizontal" method="post" id="limit_edit_from">
        <div class="control-group">
            <label class="control-label">角色</label>
            <div class="controls">
                <input type="hidden" id="role_select" name="roleid" value="" placeholder="请选择角色"/>
            </div>
        </div>
        <div class="dsh-limit-setup mb" id="limit_setup">
            <div class="dsh-limit-box">
                <div class="dsh-limit-body fill">
                    <?php foreach ($moduleList as $mkey => $modules): ?>
                        <div class="dsh-limit-entry">
                            <label class="checkbox">
                                <input type="checkbox" data-node="modCheckbox" data-id="<?php echo $mkey; ?>">
                                <div><?php echo $modules['groupName']; ?></div>
                            </label>
                        </div>
                        <ul class="dsh-limit-list clearfix">
                            <?php foreach ($modules['node'] as $node): ?>
                                <?php $isData = $node['type'] === 'data'; ?>
                                <?php if ($node['type'] == "data"): ?>
                                    <li class="dsh-limit-privilege-wrap">
                                        <label>
                                            <input type="checkbox" name="nodes[<?php echo $node['id']; ?>]"
                                                   value="<?php echo $isData ? 'data' : $node['id']; ?>"
                                                   data-node="funcCheckbox" data-pid="<?php echo $mkey; ?>">
                                            <?php if ($isData): ?>
                                                <div class="dsh-limit-privilege">
                                                    <?php foreach ($node['node'] as $data): ?>
                                                        <input value="" checked
                                                               name="data-privilege[<?php echo $node['id']; ?>][<?php echo $data['id']; ?>]"
                                                               type="text" data-text="<?php echo $data['name']; ?>"
                                                               data-toggle="privilegeLevel" style="display: none;">
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div style="width:40px;"><?php echo $node['name']; ?></div>
                                        </label>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <label>
                                            <input type="checkbox" name="nodes[<?php echo $node['id']; ?>]"
                                                   value="<?php echo $node['id']; ?>" data-node="funcCheckbox"
                                                   data-pid="<?php echo $mkey; ?>">
                                            <div><?php echo $node['name']; ?></div>
                                        </label>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($module == "organization" || $module == "officialdoc"): ?>
                <div class="fill">
                    <img src="<?php echo $assetUrl; ?>/image/illustrate.png" alt="权限说明图解">
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>
<style>.tooltip {
        z-index: 10000
    }</style>

<script>
    // 指定授权角色
    $("#role_select").ibosSelect({
        data: <?php echo json_encode($roles); ?>,
        width: '100%',
        multiple: true
    });

    //初始化权限管理提示
    $(".privilege-level").tooltip();

    $(".dsh-limit-entry").find("input[type=checkbox]").label();
</script>
<script src='<?php echo $assetUrl; ?>/js/db_permissions.js'></script>
