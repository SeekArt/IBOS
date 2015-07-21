<!-- 已授权 -->

<?php

use application\core\utils\IBOS;
?>
<?php if ( defined( "LICENCE_VER" ) ): ?>
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
                <td><?php echo date( 'Y-m-d', LICENCE_STIME ); ?> <?php echo $lang['To']; ?> <?php echo date( 'Y-m-d', LICENCE_ETIME ); ?></td>
            </tr>
            <tr>
                <th><?php echo $lang['License url']; ?></th>
                <td><?php echo LICENCE_URL; ?></td>
            </tr>
            <!--			
            <tr>
                <th><?php //echo $lang['Authkey'];  ?></th>
                <td>
                    <span><?php //echo $authkey;  ?></span>
                </td>
            </tr>
            -->
        </table>
    </div>
<?php else: ?>
    <!-- 未授权 -->
    <div class="license-cert unauthorized">
        <table>
            <tr>
                <th><?php echo $lang['License status']; ?></th>
                <td><?php echo $lang['Unauthorized']; ?></td>
            </tr>
            <tr>
                <th><?php echo $lang['Contact the official website']; ?></th>
                <td><a href="http://www.ibos.com.cn" target="_blank">http://www.ibos.com.cn</a></td>
            </tr>
            <tr>
                <th><?php echo $lang['QQ marketing']; ?></th>
                <td>4008381185</td>
            </tr>
            <tr>
                <th></th>
                <td><a href="<?php echo IBOS::app()->urlManager->createUrl( 'dashboard/default/index' ); ?>" target="_blank"><?php echo $lang['Application for authorization']; ?></a></td>
            </tr>
        </table>
    </div>
<?php endif; ?>
