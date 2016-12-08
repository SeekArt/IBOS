<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Sms setting']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('sms/setup'); ?>"><?php echo $lang['Sms setup']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('sms/manager'); ?>"><?php echo $lang['Sms sent manager']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Sms access']; ?></span>
            </li>
        </ul>
    </div>
    <div>
        <form onsubmit="return beforeSubmit()" action="<?php echo $this->createUrl('sms/access'); ?>" method="post"
              class="form-horizontal">
            <!-- 指定模块权限 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Specify the module permissions']; ?></h2>
                <div class="alert trick-tip">
                    <div class="trick-tip-title">
                        <i></i>
                        <strong><?php echo $lang['Skills prompt']; ?></strong>
                    </div>
                    <div class="trick-tip-content">
                        <?php echo $lang['SMS module permission tips']; ?>
                    </div>
                </div>
                <div class="ctbw cross-selector">
                    <div class="row">
                        <div class="span5">
                            <div><?php echo $lang['Allowed to send SMS']; ?></div>
                            <select id="select_left" multiple>
                                <?php $disabledModule = array(); ?>
                                <?php foreach ($enableModule as $module): ?>
                                    <?php if (in_array($module['module'], $smsModule)): ?>
                                        <option
                                            value="<?php echo $module['module'] ?>"><?php echo $module['name'] ?></option>
                                    <?php else: ?>
                                        <?php $disabledModule[] = $module; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn"
                                    id="select_all_left"><?php echo $lang['Select all']; ?></button>
                        </div>
                        <div class="span2">
                            <div class="cross-selector-operate">
                                <a href="javascript:;" id="toLeftBtn" class="btn btn-small">
                                    <i class="glyphicon-chevron-left"></i>
                                </a>
                                <a href="javascript:;" id="toRightBtn" class="btn btn-small">
                                    <i class="glyphicon-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="span5">
                            <div><?php echo $lang['Not allowed to send SMS']; ?></div>
                            <select id="select_right" multiple>
                                <?php foreach ($disabledModule as $module): ?>
                                    <option
                                        value="<?php echo $module['module'] ?>"><?php echo $module['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn"
                                    id="select_all_right"><?php echo $lang['Select all']; ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <input type="hidden" name="enabled" id="enabled_module" value=""/>
                <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
                <button name="smsSubmit" class="btn btn-primary btn-large btn-submit"
                        type="submit"><?php echo $lang['Submit']; ?></button>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_sms.js"></script>