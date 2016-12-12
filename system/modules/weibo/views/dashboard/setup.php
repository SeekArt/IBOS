<form action="<?php echo $this->createUrl('dashboard/setup'); ?>" class="form-horizontal" method="post" id="weibo_form">
    <div class="ct">
        <div class="clearfix">
            <h1 class="mt"><?php echo $lang['Enterprise weibo']; ?></h1>
            <ul class="mn">
                <li>
                    <span><?php echo $lang['Weibo setup']; ?></span>
                </li>
                <li>
                    <a href="<?php echo $this->createUrl('dashboard/manage'); ?>"><?php echo $lang['Manage weibo']; ?></a>
                </li>
                <li>
                    <a href="<?php echo $this->createUrl('dashboard/comment'); ?>"><?php echo $lang['Manage comment']; ?></a>
                </li>
                <!--<li>
					<a href="<?php //echo $this->createUrl('dashboard/topic'); ?>"><?php //echo $lang['Manage topic']; ?></a>
				</li>-->
            </ul>
        </div>
        <div>
            <!-- 微博设置 -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Weibo setup']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Weibo Post limit']; ?></label>
                        <div class="controls">
                            <div class="input-group">
                                <input type="text" name="wbnums" value="<?php echo $config['wbnums']; ?>">
                                <span class="input-group-addon"><?php echo $lang['Word']; ?></span>
                            </div>
                            <span class="help-inline"><?php echo $lang['Larger than 140 count']; ?></span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Weibo Post frequency']; ?></label>
                        <div class="controls">
                            <div class="input-group">
                                <input type="text" name="wbpostfrequency"
                                       value="<?php echo $config['wbpostfrequency']; ?>">
                                <span class="input-group-addon"><?php echo $lang['Second']; ?></span>
                            </div>
                            <span class="help-inline"><?php echo $lang['At least 5s to wait']; ?></span>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Allowed post type']; ?></label>
                        <div class="controls">
                            <label class="checkbox checkbox-inline">
                                <input type="checkbox" value="1"
                                       <?php if ($config['wbposttype']['image'] == 1): ?>checked<?php endif; ?>
                                       name="wbposttype[image]"/>
                                <?php echo $lang['Image']; ?>
                            </label>
                            <!--<label class="checkbox checkbox-inline">
								<input type="checkbox" value="1" name="wbposttype[topic]" />
							<?php echo $lang['Topic']; ?>
							</label>-->
                            <!--<label class="checkbox checkbox-inline">
								<input type="checkbox" value="1" name="wbposttype[praise]" />
							<?php echo $lang['Praise']; ?>
							</label>-->
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Watermark function']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="wbwatermark" value="1" data-toggle="switch"
                                   <?php if ($config['wbwatermark'] == 1): ?>checked<?php endif; ?> />
                        </div>
                    </div>
                    <!--<div class="control-group">
						<label class="control-label"><?php echo $lang['Welcome collect']; ?></label>
						<div class="controls">
							<input type="checkbox" value="1" name="wbwcenabled" data-toggle="switch" checked />
						</div>
					</div>-->
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Movement']; ?></label>
                        <div class="controls">
                            <div>
                                <?php if (!empty($movementModule)): ?>
                                <?php foreach ($movementModule as $key => $module): ?>
                                <?php if ($key % 3 == 0): ?></div>
                            <div><?php endif; ?>
                                <label class="checkbox checkbox-inline">
                                    <input value="1" type="checkbox"
                                           <?php if (isset($config['wbmovement'][$module['module']]) && $config['wbmovement'][$module['module']] == 1): ?>checked<?php endif; ?>
                                           name="wbmovements[<?php echo $module['module']; ?>]">
                                    <?php echo $module['name']; ?>
                                </label>
                                <?php endforeach; ?>
                                <?php else: ?>
                                    <?php echo $lang['Temporarily no dynamic module']; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="control-group">
        <label for="" class="control-label"></label>
        <div class="controls">
            <button type="submit" class="btn btn-primary btn-large btn-submit"> <?php echo $lang['Submit']; ?> </button>
        </div>
    </div>
    <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
</form>
<script type="text/javascript" src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript" src='<?php echo $moduleAssetUrl; ?>/js/weibo_setup.js?<?php echo VERHASH; ?>'></script>