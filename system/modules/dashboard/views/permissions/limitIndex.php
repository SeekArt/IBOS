<?php
use application\core\utils\Env;

?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/limit.css?<?php echo VERHASH; ?>">
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Permissions setup']; ?></h1>
    </div>
    <div>
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Permissions list']; ?></h2>
            <div class="">
                <form method="post" enctype="multipart/form-data" class="form-horizontal">
                    <div class="mb">
                        <span class="dib"><?php echo $lang['Selected modules']; ?></span>
                        <select class="span2 at-moduel-select" id="module_select">
                            <?php foreach ($modulesList as $module): ?>
                                <option data-module="<?php echo $module['module']; ?>"
                                        value="<?php echo $this->createUrl('permissions/setup', array('module' => $module['module'])); ?>"
                                        <?php if ($module['module'] == Env::getRequest('module')): ?>selected<?php endif; ?>><?php echo $module['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <table class="table table-striped" id="limit_table">
                            <thead>
                            <tr>
                                <th><?php echo $lang['Licensed to']; ?></th>
                                <th><?php echo $lang['Give permission']; ?></th>
                                <th width="100"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($contentList as $content): ?>
                                <tr>
                                    <td><?php echo $content['rolename']; ?></td>
                                    <td><?php echo $content['names']; ?></td>
                                    <td>
                                        <a href="javascript:;" data-id="<?php echo $content['roleid']; ?>"
                                           data-limit="edit" class="cbtn o-edit"></a>
                                        <a href="javascript:;" data-id="<?php echo $content['roleid']; ?>"
                                           data-limit="del" class="cbtn o-trash mlm"></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="3">
                                    <a href="javascript:;" data-limit="add">
                                        <i class="cbtn o-plus dib"></i>
                                        <span class="dib mlm"><?php echo $lang['Add']; ?></span>
                                    </a>
                                </td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // 指定授权岗位
    $('#choose_position').userSelect({
        data: Ibos.data.get("position"),
        type: "position"
    });

    //初始化权限管理提示
    $(".privilege-level").tooltip();

</script>
<script src='<?php echo $assetUrl; ?>/js/db_permissions_limit.js'></script>