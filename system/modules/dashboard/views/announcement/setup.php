<?php

use application\core\utils\IBOS;
use application\core\utils\String;
?>

<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['System announcement']; ?></h1>
        <ul class="mn">
            <li>
                <span><?php echo $lang['Manage']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl( 'announcement/add' ); ?>"><?php echo $lang['Add']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <form id="sys_announcement_form" method="post" class="form-horizontal">
            <!-- 系统公告 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['System announcement']; ?></h2>
                <div class="page-list">
                    <div class="page-list-header">
                        <div class="row">	
                            <div class="span8">
                                <button data-act="del" type="button" class="btn"><?php echo $lang['Delete announcement']; ?></button>
                                <button data-act="sort" type="button" class="btn mls"><?php echo $lang['Sort']; ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="page-list-mainer">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <label class="checkbox" for="checkbox_0">
                                            <input type="checkbox" data-name="id" id="checkbox_0">
                                        </label>
                                    </th>
                                    <th width='60'><?php echo $lang['Sort numbers']; ?></th>
                                    <th><?php echo $lang['Author']; ?></th>
                                    <th><?php echo $lang['Subject']; ?></th>
                                    <th><?php echo $lang['Content']; ?></th>
                                    <th><?php echo $lang['Announcement type']; ?></th>
                                    <th><?php echo $lang['Start time']; ?></th>
                                    <th><?php echo $lang['End time']; ?></th>
                                    <th width="100"><?php echo $lang['Operation']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $typeDesc = array( '0' => $lang['Announcement text'], '1' => $lang['Announcement link'] ); ?>
                                <?php foreach ( $list as $key => $value ): ?>
                                    <tr>
                                        <td>
                                            <label class="checkbox">
                                                <input type="checkbox" name="id[<?php echo $value['id']; ?>]" data-check='id' value="<?php echo $value['id']; ?>">
                                            </label>
                                        </td>
                                        <td><input class="input-small" name="sort[<?php echo $value['id']; ?>]" type="text" value="<?php echo $value['sort']; ?>" /></td>
                                        <td><?php echo $value['author']; ?></td>
                                        <td><?php echo $value['subject']; ?></td>
                                        <td><?php echo String::cutStr( $value['message'], 50 ); ?></td>
                                        <td><?php echo $typeDesc[$value['type']]; ?></td>
                                        <td><?php echo date( 'Y-m-d H:i', $value['starttime'] ); ?></td>
                                        <td><?php echo date( 'Y-m-d H:i', $value['endtime'] ); ?></td>
                                        <td>
                                            <a href="<?php echo $this->createUrl( 'announcement/edit', array( 'id' => $value['id'] ) ); ?>" class="cbtn o-edit"></a>
                                            <a href="<?php echo $this->createUrl( 'announcement/del', array( 'id' => $value['id'] ) ); ?>" class="cbtn o-trash mls"></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <input type="hidden" name="announcementSubmit" value="<?php echo FORMHASH; ?>" />
                    </div>
                    <div class="page-list-footer">
                        <?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function() {
        // 删除选中
        $('[data-act="del"]').on('click', function() {
            var id = '', url = '<?php echo $this->createUrl( 'announcement/del' ); ?>';
            $('[data-check="id"]:checked').each(function() {
                id += this.value + ',';
            });
            if (id !== '') {
                $('#sys_announcement_form').attr('action', url).submit();
            } else {
                $.jGrowl('<?php echo IBOS::lang( 'At least one record', 'error' ); ?>', {theme: 'error'});
                return false;
            }
        });
        // 排序
        $('[data-act="sort"]').on('click', function() {
            var url = '<?php echo $this->createUrl( 'announcement/setup' ); ?>';
            $('#sys_announcement_form').attr('action', url).submit();
        });
    })();
</script>