<!-- 已授权 -->
<?php
use application\core\utils\Ibos;

?>
<div class="license-cert">
    <table>
        <tr>
            <th><?php echo $lang['License ver']; ?></th>
            <td><?php echo LICENCE_VERNAME; ?></td>
        </tr>
        <tr>
            <th><?php echo $lang['Company name']; ?></th>
            <td><?php echo LICENCE_FULLNAME; ?></td>
        </tr>
        <tr>
            <th><?php echo $lang['Company shortname']; ?></th>
            <td><?php echo LICENCE_NAME; ?></td>
        </tr>
        <tr>
            <th><?php echo $lang['The number of users']; ?></th>
            <td><?php echo LICENCE_LIMIT; ?></td>
        </tr>
        <tr>
            <th><?php echo $lang['License time']; ?></th>
            <td><?php echo date('Y-m-d', LICENCE_STIME); ?><?php echo $lang['To']; ?><?php echo date('Y-m-d', LICENCE_ETIME); ?></td>
        </tr>
        <tr>
            <th><?php echo $lang['License url']; ?></th>
            <td><?php echo LICENCE_URL; ?></td>
        </tr>
    </table>
</div>