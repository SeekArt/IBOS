<table class="table table-striped table-head-condensed table-head-inverse mal-folder-table mbz">
    <tbody id="custom_folder_list">
    <tr>
        <th width="30"><?php echo $lang['Number']; ?></th>
        <th><?php echo $lang['Name']; ?></th>
        <th width="80"><?php echo $lang['Used space']; ?></th>
        <th width="70"><?php echo $lang['Operation']; ?></th>
    </tr>
    <tr>
        <td><span></span></td>
        <td><span><?php echo $lang['Inbox']; ?></span></td>
        <td><?php echo $inbox; ?></td>
        <td></td>
    </tr>
    <tr>
        <td><span></span></td>
        <td><span><?php echo $lang['External inbox']; ?></span></td>
        <td><?php echo $web; ?></td>
        <td></td>
    </tr>
    <tr>
        <td><span></span></td>
        <td><span><?php echo $lang['Sent box']; ?></span></td>
        <td><?php echo $sent; ?></td>
        <td></td>
    </tr>
    <tr>
        <td><span></span></td>
        <td><span><?php echo $lang['Trash']; ?></span></td>
        <td><?php echo $deleted; ?></td>
        <td></td>
    </tr>
    <?php foreach ($folders as $folder): ?>
        <tr id="box_<?php echo $folder['fid']; ?>">
            <td><?php echo $folder['sort']; ?></td>
            <td><?php echo $folder['name']; ?></td>
            <td><?php echo $folder['size']; ?></td>
            <td>
                <a href="javascript:;" class="anchor" data-click="editFolder"
                   data-param="{&quot;saveUrl&quot;: &quot;<?php echo $this->createUrl('folder/edit'); ?>&quot;,&quot;fid&quot;: &quot;<?php echo $folder['fid']; ?>&quot;}"><?php echo $lang['Edit']; ?></a>
                &nbsp;
                <a href="javascript:;" class="anchor" data-click="deleteFolder"
                   data-param="{&quot;delUrl&quot;:&quot;<?php echo $this->createUrl('folder/del'); ?>&quot;,&quot;fid&quot;: &quot;<?php echo $folder['fid']; ?>&quot;}"><?php echo $lang['Delete']; ?></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tbody>
    <tr class="table-row-condensed">
        <td>
            <input type="text" id="custom_folder_sort" name="sort" class="input-small" size="2"
                   onkeyup="this.value = this.value.replace(/[^\d]/g, '');">
        </td>
        <td>
            <input type="text" id="custom_folder_name" name="name" class="input-small"
                   placeholder="<?php echo $lang['Fill up folder name']; ?>">
        </td>
        <td></td>
        <td colspan="2">
            <button type="button" class="btn btn-small btn-primary" data-click="addFolder"
                    data-param="{&quot;addUrl&quot;: &quot;<?php echo $this->createUrl('folder/add'); ?>&quot;}">
                <span><?php echo $lang['Add']; ?></span></button>
        </td>
    </tr>
    </tbody>
    <tfoot>
    <tr class="table-row-condensed">
        <td></td>
        <td colspan="3">
            <span class="xwb">您的邮箱容量为<?php echo $userSize; ?>M,当前已用：</span><span><strong
                    class="xco"><?php echo $total; ?></strong></span>
        </td>
    </tr>
    </tfoot>
</table>
<script id="add_folder_tpl" type="text/template">
    <tr id="box_<%=fid%>">
        <td><%=sort%></td>
        <td><%=name%></td>
        <td>0 Bytes</td>
        <td>
            <a href="javascript:;" class="anchor" data-click="editFolder"
               data-param="{&quot;saveUrl&quot;:&quot;<?php echo $this->createUrl('folder/edit'); ?>&quot;,&quot;fid&quot;:&quot;<%=fid%>&quot;}"><?php echo $lang['Edit']; ?></a>
            &nbsp;
            <a href="javascript:;" class="anchor" data-click="deleteFolder"
               data-param="{&quot;delUrl&quot;:&quot;<?php echo $this->createUrl('folder/del'); ?>&quot;,&quot;fid&quot;:&quot;<%=fid%>&quot;}"><?php echo $lang['Delete']; ?></a>
        </td>
    </tr>
</script>