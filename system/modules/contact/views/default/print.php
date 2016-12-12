<div class="main-content">
    <style type="text/css">
        .main-content {
            margin: 0 auto;
            width: 850px;
        }

        .contact-list-info {
            margin-bottom: 10px;
            width: 850px;
            text-align: center;
        }

        .table-title {
            font-size: 30px;
        }

        .info-table {
            width: 100%;
            border-spacing: 0;
            border: 1px #000 solid;
            border-left: 0;
            border-bottom: 0;
            text-align: center;
            vertical-align: middle;
            font-family: Arial, Verdana, 'Microsoft Yahei', '微软雅黑', 'Simsun', '宋体'
        }

        .info-table td,
        .info-table th {
            padding: 5px 0;
            border-left: 1px #000 solid;
            border-bottom: 1px #000 solid;
        }

        .info-table > thead > tr > th {
            font-weight: 400;
            text-align: center;
        }
    </style>
    <div class="contact-list-info">
        <span
            class="table-title"><?php echo isset($unit['fullname']) ? $unit['fullname'] : ''; ?><?php echo $lang['Contact']; ?></span>
    </div>
    <table class="info-table">
        <thead>
        <tr>
            <th><?php echo $lang['Name']; ?></th>
            <th><?php echo $lang['Department']; ?></th>
            <th><?php echo $lang['Position']; ?></th>
            <th><?php echo $lang['Cell phone']; ?></th>
            <th><?php echo $lang['Phone']; ?></th>
            <th><?php echo $lang['Email']; ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($datas) > 0): ?>
            <?php foreach ($datas as $uid => $user): ?>
                <tr>
                    <td><?php echo $user['realname'] ?></td>
                    <td><?php echo $user['deptname']; ?></td>
                    <td><?php echo $user['posname']; ?></td>
                    <td><?php echo $user['mobile']; ?></td>
                    <td><?php echo $user['telephone']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>	
