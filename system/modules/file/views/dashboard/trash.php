<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\main\utils\Main as MainUtil;
?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Folder']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl( 'dashboard/index' ); ?>"><?php echo $lang['Folder setting']; ?></a>
            </li>
            <!--			<li>
                            <a href="<?php echo $this->createUrl( 'dashboard/store' ); ?>"><?php echo $lang['Store setting']; ?></a>
                        </li>-->
            <li>
                <span><?php echo $lang['Trash']; ?></span>
            </li>
        </ul>
    </div>
    <div>
        <!-- 管理评论 -->
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Trash mamage']; ?></h2>
            <div><?php echo Ibos::lang( 'Total of size to deal with', '', array( '{size}' => $size ) ); ?></div>
            <div class="page-list">
                <div class="page-list-header">
                    <div class="row">
                        <div class="span9">
                            <button type="button" id="empty" class="btn"><?php echo $lang['Empty']; ?></button>
                            <button type="button" id="del" class="btn"><?php echo $lang['Delete']; ?></button>
                            <button type="button" id="restore" class="btn"><?php echo $lang['Restore']; ?></button>
                            <?php if ( $search ): ?>
                                <?php echo $lang['Match conditions']; ?>：<?php echo $count; ?>, <a href="<?php echo $this->createUrl( 'dashboard/trash' ); ?>"><?php echo $lang['Return list']; ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="span3">
                            <form action="<?php echo $this->createUrl( 'dashboard/trash', array( 'param' => 'search' ) ); ?>" class="form-horizontal" method="post">
                                <div class="search">
                                    <input type="text" name="keyword" placeholder="Search" id="trash_search" <?php if ( Env::getRequest( 'param' ) ): ?>value="<?php echo MainUtil::getCookie( 'keyword' ); ?>"<?php endif; ?>>
                                    <input type="hidden" name="type" value="normal_search">
                                    <input type="submit" class="hide" />
                                    <a href="javascript:;">search</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="page-list-mainer">
                    <table class="table table-striped span12" id="file_trash_table">
                        <thead>
                            <tr>
                                <th width="20">
                                    <label class="checkbox" for="checkbox_0"><span class="icon"></span><span class="icon-to-fade"></span>
                                        <input type="checkbox" value="" data-name="fids[]" id="checkbox_0">
                                    </label>
                                </th>
                                <th width="80"><?php echo $lang['File list']; ?></th>
                                <th width="100"><?php echo $lang['Belong user']; ?></th>
                                <th><?php echo $lang['Original location']; ?></th>
                                <th width="120"><?php echo $lang['File size']; ?></th>
                                <th width="140"><?php echo $lang['Delete time']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $datas as $data ): ?>
                                <tr id="list_tr_<?php echo $data['id']; ?>">
                                    <td>
                                        <label class="checkbox" for="checkbox_<?php echo $data['fid']; ?>"><span class="icon"></span><span class="icon-to-fade"></span>
                                            <input type="checkbox" value="<?php echo $data['fid']; ?>" id="checkbox_<?php echo $data['fid']; ?>" name="fids[]">
                                        </label>
                                    </td>
                                    <td>
                                        <div class="ellipsis" style="max-width:200px;" title="<?php echo $data['location']; ?>">
                                            <?php echo $data['name']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $data['realname']; ?></td>
                                    <td>
                                        <div class="ellipsis" style="max-width:460px;" title="<?php echo $data['location']; ?>">
                                            <?php echo $data['location']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $data['size']; ?></td>
                                    <td><?php echo $data['delDate']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="page-list-footer"><?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        function emptyRecycleBin() {
            var url = Ibos.app.url("file/dashboard/trash", {
                op: 'setEmpty'
            }),
                    formhash = Ibos.app.g("formHash");

            $.post(url, {
                formhash: formhash
            }, function (res) {
                if (res.isSuccess) {
                    Ui.tip(res.msg, 'success');
                    location.reload();
                } else {
                    Ui.tip(res.msg, 'warning');
                }
            }, 'json');
        }

        function permanentlyRemoveFile() {
            var url = Ibos.app.url("file/dashboard/trash", {
                op: 'del'
            }),
                    fids = U.getCheckedValue("fids[]", $("#file_trash_table")),
                    formhash = Ibos.app.g("formHash");

            if (!fids) {
                Ui.tip("@SELECT_AT_LEAST_ONE_ITEM", "warning");
                return false;
            }

            $.post(url, {
                fids: fids,
                formhash: formhash
            }, function (res) {
                if (res.isSuccess) {
                    Ui.tip(res.msg, 'success');
                    location.reload();
                } else {
                    Ui.tip(res.msg, 'warning');
                }
            }, 'json');
        }

        function restoreFile() {
            var url = Ibos.app.url("file/dashboard/trash", {
                op: 'restore'
            }),
                    fids = U.getCheckedValue("fids[]", $("#file_trash_table")),
                    formhash = Ibos.app.g("formHash");

            if (!fids) {
                Ui.tip("@SELECT_AT_LEAST_ONE_ITEM", "warning");
                return false;
            }

            $.post(url, {
                fids: fids,
                formhash: formhash
            }, function (res) {
                if (res.isSuccess) {
                    Ui.tip(res.msg, 'success');
                    location.reload();
                } else {
                    Ui.tip(res.msg, 'warning');
                }
            }, 'json');
        }

        // 清空
        $("#empty").click(emptyRecycleBin);
        // 删除
        $("#del").click(permanentlyRemoveFile);
        // 还原
        $("#restore").click(restoreFile);
    });
</script>