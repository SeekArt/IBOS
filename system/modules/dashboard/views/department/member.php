<?php

use application\core\utils\Org;
use application\modules\department\utils\Department as DepartmentUtil;
use application\modules\user\model\User;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization_role.css?<?php echo VERHASH; ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt">部门管理</h1>
    </div>
    <div>
        <!-- 部门信息 start -->
        <div class="ctb">
            <h2 class="st">编辑部门</h2>
            <div class="">
                <div class="btn-group mb">
                    <a href="<?php echo $this->createUrl('department/edit', array('op' => 'get', 'id' => $id)); ?>"
                       class="btn">部门设置</a>
                    <a href="javascript:;" class="btn active">部门成员管理</a>
                </div>
                <div class="limit-info-wrap">
                    <div class="page-list-header clearfix">
                        <div class="pull-left">
                            <a href="javascript:;" class="btn btn-primary" id="org_member_add">添加成员</a>
                        </div>
                        <div class="pull-right" id="mumber_search_wrap">
                            <form action="<?php ?>" method="post">
                                <div class="search">
                                    <input type="text" placeholder="输入名字搜索人员" name="keyword" id="mn_search" nofocus/>
                                    <input type="hidden" name="search" value="1"/>
                                    <a href="javascript:;">search</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div>
                        <form action="<?php echo $this->createUrl('department/edit', array('op' => 'member')); ?>"
                              method="post" class="role-member-form">
                            <div class="posr" id="position_member">
                                <div class="fill-nn position-mumber-wrap">
                                    <ul class="org-member-list clearfix" id="org_member_list">
                                        <?php $depts = DepartmentUtil::loadDepartment(); ?>
                                        <?php foreach ($pageUids as $uid): ?>
                                            <?php
                                            $user = User::model()->fetchByUid($uid);
                                            if (empty($user)) {
                                                continue;
                                            }
                                            ?>
                                            <li id="member_u_<?php echo $uid; ?>">
                                                <a href="javascript:;" class="cbtn o-trash pull-right"
                                                   data-act="removeMember" data-id="u_<?php echo $uid; ?>"></a>
                                                <div class="avatar-box">
                                                    <a href="javascript:;" class="avatar-circle"><img
                                                            src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
                                                            alt=""></a>
                                                </div>
                                                <div class="org-member-item-body">
                                                    <p class="xcn xwb"><?php echo $user['realname']; ?></p>
                                                    <p class="tcm"><?php echo $user['deptid'] ? $depts[$user['deptid']]['deptname'] : '--'; ?></p>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <!-- 当成员数为零时显示 -->
                                    <?php if (empty($uids)): ?>
                                        <div class="no-data-tip" id="no_data_tip"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="page-list-footer">
                                <?php
                                if (isset($pages)) {
                                    $this->widget('application\core\widgets\Page', array('pages' => $pages));
                                }
                                ?>
                            </div>
                            <!-- 当成员数为零时隐藏 -->
                            <div class="fill-sn" id="submit_put_wrap">
                                <button type="submit" class="btn btn-large btn-primary">提交</button>
                            </div>
                            <input type="hidden" name="postsubmit" value="1"/>
                            <input type="hidden" name="resDelId" id="res_del_id"/>
                            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                            <input type="hidden" name="member" id="member" value="<?php
                            echo isset($uids) ? implode(',', array_map(function ($id) {
                                return 'u_' . $id;
                            }, $uids)) : '';
                            ?>"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="member_select_box"></div>
<!-- Template: 新增成员模板  -->
<script type="text/template" id="org_member_tpl">
    <li id="member_<%=id%>">
        <a href="javascript:;" class="cbtn o-trash pull-right" data-act="removeMember" data-id="<%=id%>"></a>
        <div class="avatar-box">
            <a href="javascript:;" class="avatar-circle"><img src="<%=imgurl%>" alt=""></a>
        </div>
        <div class="org-member-item-body">
            <p class="xcn xwb"><%=user%></p>
            <p class="tcm"><%=department%></p>
        </div>
    </li>
</script>
<script>
    Ibos.app.s({
        "members": [<?php echo isset($uidString) ? $uidString : ''; ?>]
    })
</script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/organization.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/org_position_edit.js?<?php echo VERHASH; ?>'></script>