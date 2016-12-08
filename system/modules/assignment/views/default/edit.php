<!-- 发布框 -->
<form action="javascript:;">
    <div>
        <div class="am-edit-publish mb">
            <input type="text" name="subject" value="<?php echo $subject; ?>">
        </div>
        <div class="row mb">
            <div class="span4">
                <input type="text" name="chargeuid" id="am_edit_charge" value="<?php echo $chargeuid; ?>">
            </div>
            <div class="span4">
                <div class="input-group datepicker" id="am_edit_starttime">
                    <span class="input-group-addon"><?php echo $lang['From']; ?></span>
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="starttime" value="<?php echo $starttime; ?>"
                           placeholder="<?php echo $lang['When to start']; ?>">
                </div>
            </div>
            <div class="span4">
                <div class="input-group datepicker" id="am_edit_endtime">
                    <span class="input-group-addon"><?php echo $lang['To']; ?></span>
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="endtime" value="<?php echo $endtime; ?>"
                           placeholder="<?php echo $lang['When to end']; ?>">
                </div>
            </div>
        </div>
        <div class="row mb">
            <div class="span12">
                <input type="text" name="participantuid" id="am_edit_participant"
                       value="<?php echo $participantuid; ?>">
            </div>
        </div>
        <div class="mb">
            <textarea name="description" rows="4" id="am_edit_description"
                      placeholder="<?php echo $lang['Description']; ?>"><?php echo $description; ?></textarea>
        </div>
        <div class="posr mbs clearfix">
            <div class="am-att-upload">
                <span id="am_edit_att_upload"></span>
            </div>
            <button class="btn btn-icon">
                <i class="o-paperclip"></i>
            </button>
            <div class="pull-right">
                <span id="am_edit_description_charcount" class="am-desc-charcount"></span>
                <button type="button" class="btn btn-primary" data-action="updateTask"
                        data-param='{"id": <?php echo $assignmentid; ?>}'>发布
                </button>
            </div>
        </div>
        <div id="am_edit_att_list">
            <?php if (isset($attachs)): ?>
                <?php foreach ($attachs as $key => $value): ?>
                    <div class="attl-item" data-node-type="attachItem">
                        <a href="javascript:;" title="<?php echo $lang['Delete attach']; ?>" class="cbtn o-trash"
                           data-id="<?php echo $value['aid']; ?>" data-node-type="attachRemoveBtn"></a>
                        <i class="atti">
                            <img width="32" height="32" src="<?php echo $value['iconsmall']; ?>"
                                 alt="<?php echo $value['filename']; ?>" title="<?php echo $value['filename']; ?>">
                        </i>
                        <div class="attc"><?php echo $value['filename']; ?></div>
							<span class="fss mlm">
								<a target="_blank"
                                   href="<?php echo $value['downurl']; ?>"><?php echo $lang['Download']; ?></a>&nbsp;&nbsp;
                                <?php if (isset($value['officereadurl'])): ?>
                                    <a href="javascript:;" data-action="viewOfficeFile"
                                       data-param='{"href": "<?php echo $value['officereadurl']; ?>"}'
                                       title="<?php echo $lang['Read']; ?>">
                                        <?php echo $lang['Read']; ?>
                                    </a>&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if (isset($value['officeediturl'])): ?>
                                    <a href="javascript:;" data-action="editOfficeFile"
                                       data-param='{"href": "<?php echo $value['officeediturl']; ?>"}'
                                       title="<?php echo $lang['Edit']; ?>">
                                        <?php echo $lang['Edit']; ?>
                                    </a>
                                <?php endif; ?>
                                <!--<a href="#">转存到文件柜</a>-->
							</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <input type="hidden" name="attachmentid" id="am_edit_attachmentid" value="<?php echo $attachmentid; ?>">
    </div>
</form>

<script src="<?php echo STATICURL; ?>/js/app/ibos.charCount.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo $assetUrl; ?>/js/assignment_default_edit.js"></script>
