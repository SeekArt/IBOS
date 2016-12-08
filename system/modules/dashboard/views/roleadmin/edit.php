<?php ?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization_role.css?<?php echo VERHASH; ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt">管理员管理</h1>
    </div>
    <div>
        <!-- 部门信息 start -->
        <div class="ctb">
            <h2 class="st">普通管理员</h2>
            <div>
                <div class="btn-group mb">
                    <a href="javascript:;" class="btn active">权限设置</a>
                    <a href="<?php echo $this->createUrl('roleadmin/edit', array('op' => 'member', 'id' => $id)); ?>"
                       class="btn">成员管理</a>
                </div>
                <div class="limit-info-wrap">
                    <form action="<?php echo $this->createUrl('roleadmin/edit'); ?>" method="post"
                          id="position_edit_form">
                        <div class="page-list-header clearfix">
                            <div class="pull-left">
                                <span class="xwb">名称</span>
                                <input class="role-name mls" type="text" name="rolename" id="role_name"
                                       value="<?php echo $role['rolename']; ?>"/>
                            </div>
                            <!-- <div class="pull-right">
                                <div class="search">
                                    <input type="text" placeholder="输入关键字查找权限" name="keyword" id="mn_search" nofocus />
                                    <a href="javascript:;">search</a>
                                </div>
                            </div> -->
                        </div>
                        <div>
                            <div class="org-limit org-limit-setup posr" id="limit_setup">
                                <!-- 认证项输出 begin -->
                                <?php foreach ($authItem as $key => $auth) : ?>
                                    <div class="org-limit-box">
                                        <div class="org-limit-header">
                                            <button type="button" class="btn btn-small j-limit-checkall pull-right"
                                                    data-node="cateCheckbox" data-id="<?php echo $key; ?>">全选
                                            </button>
                                            <h4><?php echo $auth['category']; ?></h4>
                                        </div>
                                        <div class="org-limit-body">
                                            <?php if (isset($auth['group'])): ?>
                                                <?php foreach ($auth['group'] as $gKey => $group) : ?>
                                                    <div class="org-limit-entry">
                                                        <label class="checkbox">
                                                            <input type="checkbox" data-id="<?php echo $gKey; ?>"
                                                                   data-node="modCheckbox"
                                                                   data-pid="<?php echo $key; ?>">
                                                            <?php echo $group['groupName']; ?>
                                                        </label>
                                                    </div>
                                                    <div class="fill-nn">
                                                        <ul class="org-limit-list clearfix">
                                                            <?php foreach ($group['node'] as $nIndex => $node): ?>
                                                                <?php
                                                                $isData = $node['type'] === 'data';
                                                                $checked = isset($related[$node['module']][$node['key']]);
                                                                ?>
                                                                <li <?php if ($isData): ?>class="org-limit-privilege-wrap"<?php endif; ?>>
                                                                    <div
                                                                        class="posr <?php if ($checked): ?>active<?php endif; ?>">
                                                                        <label class="checkbox">
                                                                            <input
                                                                                <?php if ($checked): ?>checked<?php endif; ?>
                                                                                type="checkbox"
                                                                                name="nodes[<?php echo $node['id']; ?>]"
                                                                                value="<?php echo $isData ? 'data' : $node['id']; ?>"
                                                                                data-node="funcCheckbox"
                                                                                data-pid="<?php echo $gKey; ?>">
                                                                            <span
                                                                                class="mlft"><?php echo $node['name']; ?></span>
                                                                        </label>
                                                                        <?php if ($isData): ?>
                                                                            <div class="org-limit-privilege">
                                                                                <?php foreach ($node['node'] as $dIndex => $data): ?>
                                                                                    <?php $checked = isset($related[$data['module']][$data['key']][$data['node']]); ?>
                                                                                    <input
                                                                                        value="<?php echo $checked ? $related[$data['module']][$data['key']][$data['node']] : ''; ?>"
                                                                                        <?php if ($checked): ?>checked<?php endif; ?>
                                                                                        name="data-privilege[<?php echo $node['id']; ?>][<?php echo $data['id']; ?>]"
                                                                                        type="text"
                                                                                        data-text="<?php echo $data['name']; ?>"
                                                                                        data-toggle="privilegeLevel">
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="fill-nn">
                                                    <ul class="org-limit-list clearfix">
                                                        <?php foreach ($auth['node'] as $nIndex => $node): ?>
                                                            <?php
                                                            $isData = $node['type'] === 'data';
                                                            $checked = isset($related[$node['module']][$node['key']]);
                                                            ?>
                                                            <li <?php if ($isData): ?>class="org-limit-privilege-wrap"<?php endif; ?>>
                                                                <div
                                                                    class="posr <?php if ($checked): ?>active<?php endif; ?>">
                                                                    <label class="checkbox">
                                                                        <input
                                                                            <?php if ($checked): ?>checked<?php endif; ?>
                                                                            type="checkbox"
                                                                            name="nodes[<?php echo $node['id']; ?>]"
                                                                            value="<?php echo $isData ? 'data' : $node['id']; ?>"
                                                                            data-pid="<?php echo $key; ?>">
                                                                        <span
                                                                            class="mlft"><?php echo $node['name']; ?></span>
                                                                    </label>
                                                                    <?php if ($isData): ?>
                                                                        <div class="org-limit-privilege">
                                                                            <?php foreach ($node['node'] as $dIndex => $data): ?>
                                                                                <?php $checked = isset($related[$data['module']][$data['key']][$data['node']]); ?>
                                                                                <input
                                                                                    value="<?php echo $checked ? $related[$data['module']][$data['key']][$data['node']] : ''; ?>"
                                                                                    <?php if ($checked): ?>checked<?php endif; ?>
                                                                                    type="text"
                                                                                    name="data-privilege[<?php echo $node['id']; ?>][<?php echo $data['id']; ?>]"
                                                                                    data-text="<?php echo $data['name']; ?>"
                                                                                    data-toggle="privilegeLevel">
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="fill-nn">
                                <button type="submit" class="btn btn-large btn-primary">提交</button>
                            </div>
                            <input type="hidden" name="posSubmit" value="1"/>
                            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_position_edit.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
    $(".privilege-level").tooltip({left: "-15px"});
</script>

