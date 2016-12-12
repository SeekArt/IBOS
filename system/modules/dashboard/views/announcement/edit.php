<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['System announcement']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('announcement/setup'); ?>"><?php echo $lang['Manage']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('announcement/add'); ?>"><?php echo $lang['Add']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('announcement/edit'); ?>" id="sys_announcement_form" method="post"
              class="form-horizontal">
            <!-- 添加系统公告 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Edit'] . $lang['System announcement']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Subject']; ?></label>
                        <div class="controls">
                            <div id="anc_title" class="imi-input mbs"
                                 contentEditable><?php echo $record['subject']; ?></div>
                            <div id="anc_title_editor"></div>
                            <input type="hidden" id="subject" name="subject"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Start time']; ?></label>
                        <div class="controls">
                            <div class="datepicker" id="date_start">
                                <a href="javascript:;" class="datepicker-btn"></a>
                                <input type="text" class="datepicker-input" name="starttime"
                                       value="<?php echo date('Y-m-d H:i', $record['starttime']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['End time']; ?></label>
                        <div class="controls">
                            <div class="datepicker" id="date_end">
                                <a href="javascript:;" class="datepicker-btn"></a>
                                <input type="text" class="datepicker-input" name="endtime"
                                       value="<?php echo date('Y-m-d H:i', $record['endtime']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Announcement type']; ?></label>
                        <div class="controls">
                            <label class="radio">
                                <input type="radio" name="type" value="0"
                                       <?php if ($record['type'] === '0'): ?>checked<?php endif; ?> />
                                <?php echo $lang['Announcement text']; ?>
                            </label>
                            <label class="radio">
                                <input type="radio" name="type" value="1"
                                       <?php if ($record['type'] === '1'): ?>checked<?php endif; ?> />
                                <?php echo $lang['Announcement link']; ?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Content']; ?></label>
                        <div class="controls">
                            <textarea name="message" id="an_content" rows="5" data-toggle="popover"
                                      data-trigger="focus"><?php echo $record['message']; ?></textarea>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <button name="announcementSubmit" type="submit"
                                    class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
            <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
        </form>
    </div>
</div>
<script src="<?php echo $this->getAssetUrl(); ?>/js/db_announcement.js"></script>