<?php
use application\core\utils\Ibos;

?>
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar goes here-->
    <?php echo $this->getSidebar(array('lang' => $lang)); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="page-list" id="pm_list">
            <div class="page-list-header">
                <button type="button" onclick="location.href = '<?php echo $this->createUrl('pm/index'); ?>'"
                        class="btn"><?php echo $lang['Return']; ?></button>
            </div>
            <div class="page-list-mainer">
                <div class="msg-pm-detail-header">
                    <div class="band band-primary">
                        <div class="avatar-box pull-right">
                            <a target='_blank' data-toggle='usercard'
                               data-param='uid=<?php echo Ibos::app()->user->uid; ?>'
                               href="<?php echo Ibos::app()->user->space_url; ?>" class="avatar-circle">
                                <img class="mbm" src="<?php echo Ibos::app()->user->avatar_middle; ?>">
                            </a>
                        </div>
                        <div class="msg-pm-input msg-pm-input-fullmode" id="pm_input">
                            <div class="msg-box msg-box-inverse mbs">
                                <span class="msg-box-arrow"><i></i></span>
								<textarea name="" id="reply_content"
                                          placeholder="<?php echo $lang['Sending to...']; ?><?php
                                          foreach ($message['to'] as $k => $mb) {
                                              if ($k > 1) {
                                                  echo '、';
                                              }
                                              echo $mb['user']['realname'];
                                          }
                                          ?>"></textarea>
                            </div>
                            <div class="msg-pm-input-operate clearfix">
                                <div class="pull-left">
                                    <a href="javascript:;" class="cbtn o-expression" id="pm_exp"></a>
                                </div>
                                <div class="pull-right">
                                    <span id="pm_charcount" class="charcount"></span>
                                    <button type="button" class="btn btn-primary btn-widen" id="pm_submit"
                                            data-loading-text="<?php echo $lang['Sending ...']; ?>"
                                            disabled><?php echo $lang['Send']; ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="main-list msg-list msg-pm-detail-list" id="msg_pm_list"></div>
                <div class="fill-sn">
                    <button style="width: 100%;" class="btn" id="load_more_btn"
                            data-loading-text="等待中..."><?php echo $lang['More']; ?></button>
                </div>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>
<script>
    Ibos.app.s({
        "listId": <?php echo $message['listid']; ?>,
        "listType": '<?php echo $type; ?>',
        "defalutSince": <?php echo $message['sinceid'] - 1; ?>,
        "toUid": "<?php echo $message['to'][0]['uid']; ?>"
    })
</script>
<script src="<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo STATICURL; ?>/js/app/ibos.charCount.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/message_pm_detail.js?<?php echo VERHASH; ?>'></script>
