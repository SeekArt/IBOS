<?php

use application\core\utils\Ibos;
use application\core\utils\Org;
use application\core\utils\StringUtil;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/email.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <!-- Mainer content -->
        <div class="ct ctview ct-affix">
            <div class="btn-toolbar noprint" data-spy="affix" data-offset-top="70">
                <div class="btn-group">
                    <a href="javascript:history.go(-1);" class="btn"><?php echo $lang['Return']; ?></a>
                </div>
                <div class="btn-group">
                    <a href="<?php
                    echo $this->createUrl('content/add', array('op' => 'reply', 'id' => $email['bodyid']));
                    ?>" class="btn btn-primary"><?php echo $lang['Reply']; ?></a>
                    <a href="javascript:;" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                        <i class="caret"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a data-click="replyAll" data-param="{&quot;url&quot;: &quot;<?php
                            echo $this->createUrl('content/add', array('op' => 'replyall', 'id' => $email['bodyid']));
                            ?>&quot;,&quot;isSecretUser&quot;:<?php
                            echo $isSecretUser ? 1 : 0;
                            ?>}" href="javascript:;"><?php echo $lang['Reply all']; ?></a>
                        </li>
                    </ul>
                </div>
                <div class="btn-group">
                    <a href="<?php
                    echo $this->createUrl('content/add', array('op' => 'fw', 'id' => $email['bodyid']));
                    ?>" class="btn"><?php echo $lang['Forward']; ?></a>
                </div>
                <div class="btn-group">
                    <?php if ($isSend === true): ?>
                        <a class="btn" data-click="eraseOneEmail" data-param="{&quot;url&quot;:&quot;<?php
                        echo $this->createUrl('api/mark', array('op' => 'delFromSend', 'emailids' => $email['emailid']));
                        ?>&quot;}"><?php echo $lang['Completely remove']; ?></a>
                    <?php elseif ($email['isdel'] == 0 && $isSend === false): ?>
                        <a href="javascript:;" class="btn" data-click="deleteOneEmail"
                           data-param="{&quot;emailids&quot;: &quot;<?php echo $email['emailid']; ?>&quot;,&quot;fid&quot;: &quot;<?php echo $email['fid']; ?>&quot;,&quot;archiveid&quot;: &quot;<?php echo $this->archiveId; ?>&quot;,&quot;url&quot;: &quot;<?php
                           echo $this->createUrl('api/mark', array('op' => 'del'));
                           ?>&quot;}"><?php echo $lang['Delete']; ?></a>
                    <?php else: ?>
                        <a class="btn" data-click="eraseOneEmail" data-param="{&quot;url&quot;:&quot;<?php
                        echo $this->createUrl('api/cpDel', array('emailids' => $email['emailid'], 'archiveid' => $this->archiveId));
                        ?>&quot;}"><?php echo $lang['Completely remove']; ?></a>
                    <?php endif; ?>
                </div>
                <div class="btn-group">
                    <a href="javascript:;" onclick="window.print();" class="btn"><?php echo $lang['Print']; ?></a>
                </div>
                <div class="btn-group">
                    <a href="#" class="btn dropdown-toggle" data-toggle="dropdown">
                        <?php echo $lang['More option']; ?>
                        <i class="caret"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php
                            echo $this->createUrl('content/export', array('op' => 'eml', 'id' => $email['emailid']));
                            ?>" target="_blank"><?php echo $lang['Export eml']; ?></a></li>
                        <li><a href="<?php
                            echo $this->createUrl('content/export', array('op' => 'excel', 'id' => $email['emailid']));
                            ?>" target="_blank"><?php echo $lang['Export excel']; ?></a></li>
                    </ul>
                </div>
                <div class="pull-right">
                    <div class="btn-group">
                        <a <?php if (!empty($prev)): ?>href="<?php
                        echo $this->createUrl('content/show', array('id' => $prev['emailid']));
                        ?>" title="<?php echo $lang['Prev mail'] . $prev['subject']; ?>" class="btn"
                           <?php else: ?>href="javascript:;" title="<?php echo $lang['No prev mail']; ?>"
                           class="btn disabled"<?php endif; ?>>
                            <i class="glyphicon-chevron-left"></i>
                        </a>
                        <a <?php if (!empty($next)): ?>href="<?php
                        echo $this->createUrl('content/show', array('id' => $next['emailid']));
                        ?>" title="<?php echo $lang['Next mail'] . $next['subject']; ?>" class="btn"
                           <?php else: ?>href="javascript:;" title="<?php echo $lang['No next mail']; ?>"
                           class="btn disabled"<?php endif; ?>>
                            <i class="glyphicon-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="mal-view">
                <!-- Row 1 邮件头信息 -->
                <div class="ctb bglb media bdbs">
                    <a data-toggle="usercard" data-param="uid=<?php echo $email['fromid']; ?>" href="<?php
                    echo Ibos::app()->urlManager->createUrl('user/home/index', array('uid' => $email['fromid']));
                    ?>" class="pull-left avatar-circle" title="<?php echo $email['fromName'] ?>">
                        <img src="<?php echo Org::getDataStatic($email['fromid'], 'avatar', 'middle') ?>"
                             alt="<?php echo $email['fromName'] ?>">
                    </a>
                    <div class="media-body">
                        <a href="javascript:;" class="pull-right mal-showmore noprint" data-click="toggleSenderDetail"
                           data-param="{&quot;briefId&quot;: &quot;mal_info_brief&quot;,&quot;detailId&quot;: &quot;mal_info_detail&quot;}">
                            <?php echo $lang['See more detail']; ?>
                            <i class="caret"></i>
                        </a>
                        <h1 class="mal-title">
                            <span
                                class="<?php echo $email['important'] == '1' ? 'xcgn' : ($email['important'] == '2' ? 'xcr' : ''); ?>"><?php echo $email['subject'] ?></span>
                            <a href="javascript:;" title="<?php echo $lang['Click to mark this message']; ?>"
                               <?php if ($email['ismark']): ?>class="o-mark"
                               <?php else: ?>class="o-unmark"<?php endif; ?> data-click="toggleMark"
                               data-param="{&quot;url&quot;: &quot;<?php
                               echo $this->createUrl('api/mark', array('op' => 'todo', 'emailids' => $email['emailid']));
                               ?>&quot;}"></a>
                        </h1>
                        <p id="mal_info_brief">
                            <?php echo $email['fromName']; ?> <?php echo $lang['At']; ?> <?php echo $email['dateTime'] ?>
                            ( <?php echo $lang['Week'] . $weekDay; ?> ) <?php echo $lang['Send to']; ?> <?php
                            echo StringUtil::cutStr(implode('、', $allUsers), 45);
                            ?> <?php if (count($allUsers) > 1): ?><?php echo $lang['Such as']; ?><?php echo count($allUsers); ?><?php echo $lang['People']; ?><?php endif; ?>
                            。
                        </p>
                        <div id="mal_info_detail" style="display:none;">
                            <div><?php echo $lang['Sender']; ?>：<?php echo $email['fromName']; ?> </div>
                            <div><?php echo $lang['Recipient']; ?>：<?php
                                echo implode('、', $toUsers);
                                ?> </div>
                            <div><?php echo $lang['CC']; ?>：<?php
                                echo implode('、', $copyToUsers);
                                ?></div>
                            <div><?php echo $lang['Time']; ?>：<?php echo $email['dateTime'] ?>
                                ( <?php echo $lang['Week'] . $weekDay; ?> )
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ctb text-overflow">
                    <div class="xcm bdbs editor-content text-break" style="min-height: 400px;">
                        <?php if ($email['isweb']): ?>
                            <div id="mainFrameContainer">
                                <iframe onload="setScale()" style="width:100%;" src="<?php
                                echo $this->createUrl('content/show', array('id' => $email['emailid'], 'op' => 'showframe'));
                                ?>" name="mainFrame" id="mainFrame" frameborder="no" scrolling="no" hidefocus></iframe>
                            </div>
                        <?php else: ?>
                            <?php echo $email['content']; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (isset($attach)): ?>
                    <div class="ctb bglg bdbs noprint">
                        <h3 class="ctbt">
                            <i class="o-paperclip"></i>
                            <strong><?php echo $lang['Attachment']; ?></strong>（<?php echo count($attach); ?><?php echo $lang['Item']; ?>
                            ）
                        </h3>
                        <ul class="attl">
                            <?php foreach ($attach as $fileInfo): ?>
                                <li>
                                    <i class="atti"><img src="<?php echo $fileInfo['iconsmall']; ?>"
                                                         alt="<?php echo $fileInfo['filename']; ?>"></i>
                                    <div class="attc">
                                        <div>
                                            <?php echo $fileInfo['filename']; ?><span
                                                class="tcm">(<?php echo $fileInfo['filesize']; ?>)</span>
                                        </div>
                                        <span class="fss">
                                            <a target="_blank"
                                               href="<?php echo $fileInfo['downurl']; ?>"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                            <?php if ($fileInfo['filetype'] != 'rar'): ?>
                                                <a href="javascript:;" data-action="viewOfficeFile"
                                                   data-param='{"href": "<?php echo $fileInfo['officereadurl']; ?>"}'
                                                   title="<?php echo $lang['Read']; ?>">
                                                    <?php echo $lang['Read']; ?>
                                                </a>
                                            <?php endif; ?>
                                            <!--<a href="#">转存到文件柜</a>-->
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php
                /* 外部邮件附件视图 */
                if (isset($atts)) {
                    ?>
                    <div class="ctb bglg bdbs noprint">
                        <h3 class="ctbt">
                            <i class="o-paperclip"></i>
                            <strong><?php echo $lang['Attachment']; ?></strong>（<?php echo count($atts); ?><?php echo $lang['Item']; ?>
                            ）
                        </h3>
                        <ul class="attl">
                            <?php foreach ($atts as $k => $att): ?>
                                <li>
                                    <i class="atti"><img src="static/image/filetype/unknown_lt.png"
                                                         alt="<?php echo $att['name']; ?>"></i>
                                    <div class="attc">
                                        <div>
                                            <?php echo $att['name']; ?><span class="tcm">(<?php
                                                echo round($att['size'] / 1024 / 1024, 2) . 'M';
                                                ?>)</span>
                                        </div>
                                        <span class="fss">
                                            <a target="_blank" href="?r=email/web/download&id=<?php echo $webid + 5;
                                            ?>&i=<?php
                                            echo $k + 10;
                                            ?>"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach;
                            ?>
                        </ul>
                    </div>
                    <?php
                }
                ?>


                <div class="ctb bglb sdi noprint">
                    <textarea id="quick_reply" data-focus="flexArea" data-blur="flexArea" class="mal-quick-reply mb"
                              placeholder="<?php echo $lang['Quick reply to'] . $email['fromName']; ?>"
                              data-param="{&quot;targetId&quot;: &quot;quick_reply_operate&quot;}"></textarea>
                    <div id="quick_reply_operate" class="clearfix" style="display:none;">
                        <div class="pull-right">
                            <a href="<?php
                            echo $this->createUrl('content/add', array('op' => 'reply', 'id' => $email['bodyid']));
                            ?>" class="cti"><?php echo $lang['Complete model']; ?></a>
                            <button type="button" id="quick_reply_send" class="btn btn-primary btn-widen"
                                    data-click="sendQuickReply"
                                    data-param="{&quot;targetId&quot;: &quot;quick_reply&quot;,&quot;formhash&quot;: &quot;<?php echo FORMHASH; ?>&quot;,&quot;url&quot;: &quot;<?php
                                    echo $this->createUrl('content/add', array('op' => 'quickReply', 'id' => $email['bodyid']));
                                    ?>&quot;}"><?php echo $lang['Send']; ?></button>
                        </div>
                    </div>
                </div>
                <?php
                if ($email['isneedreceipt'] && !$email['isreceipt'] && $email['fromid'] != Ibos::app()->user->uid):
                    ?>
                    <div class="fill">
                        <div class="alert alert-main">
                            <p class="xac mbs"><?php echo $email['fromName']; ?><?php echo $lang['Receipt tips']; ?></p>
                            <div class="xac">
                                <button type="button" class="btn btn-primary" data-click="receipt"
                                        data-param="{&quot;id&quot;: &quot;<?php echo $email['emailid']; ?>&quot;,&quot;url&quot;: &quot;<?php
                                        echo $this->createUrl('api/mark', array('op' => 'sendreceipt'));
                                        ?>&quot;}"><span>&nbsp;<?php echo $lang['Send']; ?>&nbsp;</span></button>
                                &nbsp;
                                <button type="button" class="btn" data-click="receipt"
                                        data-param="{&quot;id&quot;: &quot;<?php echo $email['emailid']; ?>&quot;,&quot;url&quot;: &quot;<?php
                                        echo $this->createUrl('api/mark', array('op' => 'cancelreceipt'));
                                        ?>&quot;}"><span>&nbsp;<?php echo $lang['Not send']; ?>&nbsp;</span></button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email.js?<?php echo VERHASH; ?>'></script>
<script>
    function setScale() {
        var frame = document.getElementById('mainFrame'), height = frame.contentDocument.documentElement.offsetHeight;
        frame.style.height = height + 'px';
    }
</script>
