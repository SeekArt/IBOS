<table class="table" style="margin-bottom: 0;width:380px;">
    <?php if (isset($users)): ?>
        <tr>
            <th>用户</th>
            <td><?php echo $users; ?></td>
        </tr>
    <?php endif; ?>
    <?php if (isset($dept)): ?>
        <tr>
            <th>部门</th>
            <td><?php echo $dept; ?></td>
        </tr>
    <?php endif; ?>
    <?php if (isset($pos)): ?>
        <tr>
            <th>岗位</th>
            <td><?php echo $pos; ?></td>
        </tr>
    <?php endif; ?>
</table>
