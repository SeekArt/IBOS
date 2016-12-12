<?php

use application\core\utils\Convert;
use application\core\utils\Env;

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
        <form id="email_form" action="<?php echo $this->createUrl('content/add'); ?>" method="post" id="email_form"
              class="form-horizontal form-narrow">
            <div class="ct ctform cti">
                <div class="btn-toolbar mbs">
                    <button type="button" class="btn pull-left"
                            data-action="back"><?php echo $lang['Return']; ?></button>
                    <!-- 此处视图上是 “保存” 在 “发布” 之前， 但为了按 enter 时可以发布，在排版上 “发布” 在 “保存” 之前  -->
                    <div class="pull-left mls">
                        <button type="submit" name="emailbody[issend]" value="1"
                                class="btn btn-primary pull-right mls"><?php echo $lang['Send']; ?></button>
                        <button type="submit" name="emailbody[issend]" value="0"
                                class="btn"><?php echo $lang['Save draft']; ?></button>
                    </div>
                </div>
                <div class="well well-full">
                    <div class="mal-form">
                        <!-- Row 1 收件人 -->
                        <div class="control-group">
                            <label class="control-label">
                                <div class="fsl"><?php echo $lang['Recipient']; ?></div>
                            </label>
                            <div class="controls" id="toids_row">
                                <input type="hidden" id="toids" name="emailbody[toids]" class="span12"
                                       value="<?php echo !empty($in) ? implode(',', $in) : ''; ?>">
                                <div id="toids_box"></div>
                            </div>
                        </div>
                        <!-- Row 2  -->
                        <div class="control-group" data-toggle="buttons-checkbox">
                            <div class="controls btn-group">
                                <a href="javascript:;" class="btn btn-small" data-click="toggleRec"
                                   data-param="{&quot;targetId&quot;: &quot;cc_bcc&quot;, &quot;inputId&quot;: &quot;cc_bcc_value&quot;}"><?php echo $lang['CC']; ?>
                                    / <?php echo $lang['Secret to']; ?></a>
                                <?php if ($this->allowWebMail): ?><a href="javascript:;"
                                                                     class="btn btn-small <?php if (!empty($web)): ?>active<?php endif; ?>"
                                                                     data-click="toggleRec"
                                                                     data-param="{&quot;targetId&quot;: &quot;web_rec&quot;, &quot;inputId&quot;: &quot;web_rec_value&quot;}"><?php echo $lang['External recipient']; ?></a><?php endif; ?>
                            </div>
                            <input type="hidden" id="cc_bcc_value" name="emailbody[isOtherRec]" value="0">
                            <input type="hidden" id="web_rec_value" name="emailbody[isWebRec]"
                                   value="<?php if (!empty($web)): ?>1<?php else: ?>0<?php endif; ?>">
                        </div>

                        <div id="cc_bcc" style="display:none">
                            <!-- Row 3 抄送  -->
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['CC']; ?></label>
                                <div class="controls">
                                    <input type="text" id="copytoids" name="emailbody[copytoids]" value="">
                                    <div id="copytoids_box"></div>
                                </div>
                            </div>
                            <!-- Row 4 密送  -->
                            <div class="control-group">
                                <label class="control-label"><?php echo $lang['Secret to']; ?></label>
                                <div class="controls">
                                    <input type="text" id="secrettoids" name="emailbody[secrettoids]" value="">
                                    <div id="secrettoids_box"></div>
                                </div>
                            </div>
                        </div>
                        <?php if ($allowWebMail): ?>
                            <div id="web_rec" <?php if (empty($web)): ?>style="display:none"<?php endif; ?>>
                                <!-- Row 5 外部收件人  -->
                                <div class="control-group">
                                    <label class="control-label"><?php echo $lang['External recipient']; ?></label>
                                    <div class="controls">
                                        <input type="text" name="emailbody[towebmail]" id="to_web_email"
                                               placeholder="<?php echo $lang['Recipient tips']; ?>"
                                               value="<?php echo !empty($web) ? implode(';', $web) : ''; ?>"/>
                                    </div>
                                </div>
                                <!-- Row 6 外部邮件箱  -->
                                <div class="control-group">
                                    <label class="control-label"><?php echo $lang['External mailbox']; ?></label>
                                    <div class="controls span5">
                                        <select name="emailbody[fromwebid]" id="webid">
                                            <?php if (empty($webMails)): ?>
                                                <option value=""><?php echo $lang['Empty web mail box']; ?></option>
                                            <?php else : ?>
                                                <?php foreach ($webMails as $val): ?>
                                                    <option <?php if ($val['isdefault']): ?>selected<?php endif; ?>
                                                            value="<?php echo $val['webid']; ?>"><?php echo $val['address']; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <p class="tcm tcmh"><?php echo $lang['Webmail tips']; ?></p>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <div class="controls btn-group">
                                        <a href="javascript:;" class="btn btn-small"
                                           data-click="addWebMail"><?php echo $lang['Add']; ?></a>
                                        <a href="<?php echo $this->createUrl('web/index'); ?>" target="_blank"
                                           class="btn btn-small"><?php echo $lang['Go setup']; ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <!-- Row 7 邮件主题  -->
                        <div class="control-group">
                            <div class="control-label control-label-btn btn-group">
                                <a href="#" class="btn btn-block dropdown-toggle" data-toggle="dropdown" id="mal_level"
                                   data-selected="0">
                                    <span><?php echo $lang['Normal mail']; ?></span>
                                    <i class="caret"></i>
                                </a>
                                <ul class="dropdown-menu xal">
                                    <li data-value="0" class="active"><a
                                            href="javascript:;"><?php echo $lang['Normal mail']; ?></a></li>
                                    <li data-value="1"><a href="javascript:;"><?php echo $lang['Important mail']; ?></a>
                                    </li>
                                    <li data-value="2"><a href="javascript:;"><?php echo $lang['Urgent mail']; ?></a>
                                    </li>
                                </ul>
                                <input type="hidden" value="0" name="emailbody[important]" id="mal_level_val">
                            </div>
                            <div class="controls">
                                <input type="text" name="emailbody[subject]"
                                       placeholder="<?php echo $lang['Email subject tips']; ?>" id="mal_title"
                                       value="<?php echo $subject; ?>"/>
                            </div>
                        </div>
                    </div>
                    <!-- Row 8 编辑器  -->
                    <div class="bdbs">
                        <script id="editor" name="emailbody[content]" type="text/plain"><?php echo $content; ?></script>
                    </div>
                    <div class="att">
                        <div class="attb">
                            <a href="javascript:;" id="upload_btn"></a>
                            <a href="javascript:;" class="btn btn-icon vat" title="从文件柜选择" data-action="selectFile"
                               data-param='{ "target": "#file_target", "input": "#attachmentid" }'>
                                <i class="o-folder-close"></i>
                            </a>
                            <input type="hidden" name="emailbody[attachmentid]" id="attachmentid" value="<?php
                            if (isset($attach)) {
                                echo implode(',', Convert::getSubByKey($attach, 'aid'));
                            }
                            ?>"/>
                            <span><?php echo $lang['File size limit'] ?><?php echo($uploadConfig['max'] / 1024); ?>
                                MB</span>
                        </div>
                        <div class="attl" id="file_target">
                            <?php if (isset($attach)): ?>
                                <?php foreach ($attach as $value): ?>
                                    <div class="attl-item" data-node-type="attachItem">
                                        <a href="javascript:;" title="删除附件" class="cbtn o-trash"
                                           data-id="<?php echo $value['aid']; ?>" data-node-type="attachRemoveBtn"></a>
                                        <i class="atti"><img width="44" height="44"
                                                             src="<?php echo $value['iconsmall']; ?>"
                                                             alt="<?php echo $value['filename']; ?>"
                                                             title="<?php echo $value['filename']; ?>"></i>
                                        <div class="attc"><?php echo $value['filename']; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Row 9 -->
                <div class="row mb">
                    <div class="span4">
                        <div class="stand">
                            <i class="o-comment"></i> <?php echo $lang['Need receipt']; ?>
                            <div class="pull-right">
                                <input type="checkbox" name="emailbody[isneedreceipt]" value="1" data-toggle="switch"/>
                            </div>
                        </div>
                    </div>
                    <?php if ($isInstallThread): ?>
                        <div class="span4">
                            <select name="threadid">
                                <?php $threadId = intval(Env::getRequest('threadid')); ?>
                                <option value="0"><?php echo $lang['Not associated']; ?></option>
                                <?php foreach ($threadList as $thread): ?>
                                    <option value="<?php echo $thread['threadid'] ?>"
                                            <?php if ($thread['threadid'] == $threadId): ?>selected<?php endif; ?>><?php echo $thread['subject']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Row 10 Button -->
                <div id="submit_bar" class="clearfix">
                    <button type="button" class="btn btn-large pull-left"
                            onclick="javascript:history.go(-1);"><?php echo $lang['Return']; ?></button>
                    <div class="pull-right">
                        <button type="submit" name="emailbody[issend]" value="0"
                                class="btn btn-large btn-submit"><?php echo $lang['Save draft']; ?></button>
                        <button type="submit" name="emailbody[issend]" value="1"
                                class="btn btn-large btn-submit btn-primary btn-submit"
                                id="send_email_btn"><?php echo $lang['Send']; ?></button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="op" value="<?php echo $op; ?>">
            <input type="hidden" name="backurl" value="<?php echo $backurl; ?>">
            <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
        </form>
    </div>
</div>

<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/email_content_common.js?<?php echo VERHASH; ?>'></script>
<script>
    $(function () {
        var ue = UE.getEditor("editor");
        var urlParam = U.getUrlParam();
        ue.ready(function () {
            if (!urlParam.op) {
                (new Ibos.EditorCache(ue, null, "mail_add_editor")).restore();
            }
        });
    });
</script>