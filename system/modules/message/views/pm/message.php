<?php

use application\core\utils\Convert;
use application\core\utils\StringUtil;

?>
<?php foreach ($message['data'] as $row): ?>
    <div class="main-list-item" id="msgitem_<?php echo $row['messageid']; ?>">
        <div class="avatar-box <?php if ($row['fromuid'] == $uid): ?>pull-right<?php else: ?>pull-left<?php endif; ?>">
            <a target='_blank' href="<?php echo $row['fromuser']['space_url']; ?>" class="avatar-circle"
               data-toggle='usercard' data-param='uid=<?php echo $row['fromuser']['uid']; ?>'>
                <img class="mbm" src="<?php echo $row['fromuser']['avatar_middle']; ?>"></a>
            <span class="avatar-desc"> <strong><?php echo $row['fromuser']['realname']; ?></strong></span>
        </div>
        <div class="main-list-item-body">
            <div
                class="msg-box <?php if ($row['fromuid'] == $uid): ?>msg-box-inverse bglb<?php else: ?><?php endif; ?>">
                <span class="msg-box-arrow"> <i></i></span>
                <div class="msg-box-body">
                    <p class="xcm mb"><?php echo StringUtil::parseHtml($row['content']); ?></p>
                    <div>
                        <span class="tcm fss"><?php echo Convert::formatDate($row['mtime'], 'u'); ?></span>
                        <div class="pull-right">
                            <!--
							<a href="javascript:;" onclick="replyMessage('<?php echo $row['fromuser']['realname']; ?>')">回复</a>
							-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
