<?php
use application\core\utils\Convert;
use application\core\utils\DateTime;
use application\modules\user\model\User;

?>
<p>
    <br/>
</p>
<p>
    <br/>
</p>
<hr/>
<div id="origin_email_<?php echo $body['bodyid']; ?>">
    <table width="100%">
        <tbody>
        <tr>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;" width="20%">
                <?php echo $lang['Sender']; ?><br/>
            </td>
            <td valign="top" style="border-color:#ffffff;background-color:#efefef;">
                <?php echo !empty($body['fromwebmail']) ? $body['fromwebmail'] : User::model()->fetchRealnameByUid($body['fromid']); ?>
            </td>
        </tr>
        <tr>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;">
                <?php echo $lang['Send time']; ?><br/>
            </td>
            <td valign="top" style="border-color:#ffffff;background-color:#efefef;">
                <?php echo Convert::formatDate($body['sendtime']); ?>
                ( <?php echo $lang['Week'] . DateTime::getWeekDay($body['sendtime']); ?> )
            </td>
        </tr>
        <tr>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;" colspan="1"
                rowspan="1">
                <?php echo $lang['Recipient']; ?><br/>
            </td>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;" colspan="1"
                rowspan="1">
                <?php echo implode(',', $toid); ?>
            </td>
        </tr>
        <tr>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;" colspan="1"
                rowspan="1">
                <?php echo $lang['CC']; ?><br/>
            </td>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;" colspan="1"
                rowspan="1">
                <?php echo implode(',', $copyToId); ?>
            </td>
        </tr>
        <tr>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;" colspan="1"
                rowspan="1">
                <?php echo $lang['Subject']; ?><br/>
            </td>
            <td valign="top" style="word-break:break-all;border-color:#ffffff;background-color:#efefef;" colspan="1"
                rowspan="1">
                <?php echo $body['subject']; ?>
            </td>
        </tr>
        </tbody>
    </table>
    <p><br/></p>
    <p><br/></p>
    <?php echo $body['content']; ?>
</div>