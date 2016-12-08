<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Date setup']; ?></h1>
    </div>
    <div>
        <!-- 时间和日期格式 start -->
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Time and date format']; ?></h2>
            <div class="ctbw">
                <form action="<?php echo $this->createUrl('date/index'); ?>" class="form-horizontal" method="post">
                    <div class="control-group">
                        <label for="" class="control-label"><?php echo $lang['Default date format']; ?></label>
                        <div class="controls">
                            <select name="dateFormat">
                                <option
                                    <?php if (strcasecmp($date['dateformat'], 'Y-n-j') == 0): ?>selected<?php endif; ?>
                                    value="Y-n-j">2012-12-21
                                </option>
                                <option
                                    <?php if (strcasecmp($date['dateformat'], 'Y/n/j') == 0): ?>selected<?php endif; ?>
                                    value="Y/n/j">2012/12/21
                                </option>
                                <option
                                    <?php if (strcasecmp($date['dateformat'], 'Y.n.j') == 0): ?>selected<?php endif; ?>
                                    value="Y.n.j">2012.12.21
                                </option>
                                <option
                                    <?php if (strcasecmp($date['dateformat'], 'Y年n月j日') == 0): ?>selected<?php endif; ?>
                                    value="Y年n月j日">2012年12月21日
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Default time format']; ?></label>
                        <div class="controls">
                            <label class="radio">
                                <input type="radio"
                                       <?php if (strcmp($date['timeformat'], 'H:i') === 0): ?>checked<?php endif; ?>
                                       name="timeFormat" value="H:i"><?php echo $lang['24 hours']; ?>
                            </label>
                            <label class="radio">
                                <input type="radio"
                                       <?php if (strcmp($date['timeformat'], 'h:i') === 0): ?>checked<?php endif; ?>
                                       name="timeFormat" value="h:i"/><?php echo $lang['12 hours']; ?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Human time format']; ?> <i
                                class="icon-question"></i></label>
                        <div class="controls w40">
                            <label class="radio" title="<?php echo $lang['Human time introduction']; ?>"
                                   id="humanization_ins">
                                <input type="radio" <?php if ($date['dateconvert'] == '1'): ?>checked<?php endif; ?>
                                       name="dateConvert" value="1"/> <?php echo $lang['Yes']; ?>
                            </label>
                            <label class="radio">
                                <input type="radio" <?php if ($date['dateconvert'] == '0'): ?>checked<?php endif; ?>
                                       name="dateConvert" value="0"/><?php echo $lang['No']; ?>
                            </label>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Default time difference']; ?></label>
                        <div class="controls">
                            <select name="timeOffset">
                                <?php foreach ($timeZone as $key => $value): ?>
                                    <option value="<?php echo $key; ?>"
                                            <?php if ($date['timeoffset'] == $key): ?>selected<?php endif; ?>><?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="" class="control-label"></label>
                        <div class="controls">
                            <button type="submit" name="dateSetupSubmit"
                                    class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $("#humanization_ins").tooltip({placement: "right"})
</script>