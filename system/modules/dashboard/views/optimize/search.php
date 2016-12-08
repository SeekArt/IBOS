<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Performance optimization']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('optimize/cache'); ?>"><?php echo $lang['Optimize Cache']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Full-text search setup']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('optimize/sphinx'); ?>"><?php echo $lang['Sphnix control']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <!-- Sphinx全文检索设置 start -->
        <div class="ctb">
            <h2 class="st">Sphinx<?php echo $lang['Full-text search setup']; ?></h2>
            <div class="ctbw">
                <form action="<?php echo $this->createUrl('optimize/search'); ?>" method="post" class="form-horizontal">
                    <div class="btn-group control-group">
                        <?php foreach ($moduleList as $module): ?>
                            <a href="<?php echo $this->createUrl('optimize/search', array('op' => $module)); ?>"
                               class="btn<?php if ($operation == $module) : ?> active <?php endif; ?>">
                                <?php echo $lang[$module]; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Open or not']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="sphinxon[<?php echo $operation; ?>]" value="1"
                                   <?php if ($sphinxon[$operation] == '1'): ?>checked<?php endif; ?>
                                   data-toggle="switch" class="visi-hidden"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Sphinx sub index']; ?></label>
                        <div class="controls">
                            <input id='sphinxSubIndex' value="<?php echo $sphinxsubindex[$operation]; ?>" type="text"
                                   name='sphinxsubindex[<?php echo $operation; ?>]'/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Sphinx msg index']; ?></label>
                        <div class="controls">
                            <input id='sphinxMsgIndex' value="<?php echo $sphinxmsgindex[$operation]; ?>" type="text"
                                   name='sphinxmsgindex[<?php echo $operation; ?>]'/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Sphinx max query time']; ?></label>
                        <div class="controls">
                            <input id="sphinxMaxQueryTime" value="<?php echo $sphinxmaxquerytime[$operation]; ?>"
                                   type="text" name="sphinxmaxquerytime[<?php echo $operation; ?>]"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Sphinx limit']; ?></label>
                        <div class="controls">
                            <input id="sphinxLimit" value="<?php echo $sphinxlimit[$operation]; ?>" type="text"
                                   name="sphinxlimit[<?php echo $operation; ?>]"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Sphinx rank']; ?></label>
                        <div class="controls">
                            <select id='sphinxRank' name="sphinxrank[<?php echo $operation; ?>]">
                                <option
                                    <?php if ($sphinxrank[$operation] == 'SPH_RANK_PROXIMITY_BM25'): ?>selected<?php endif; ?>
                                    value="SPH_RANK_PROXIMITY_BM25">SPH_RANK_PROXIMITY_BM25
                                </option>
                                <option <?php if ($sphinxrank[$operation] == 'SPH_RANK_BM25'): ?>selected<?php endif; ?>
                                        value="SPH_RANK_BM25">SPH_RANK_BM25
                                </option>
                                <option <?php if ($sphinxrank[$operation] == 'SPH_RANK_NONE'): ?>selected<?php endif; ?>
                                        value="SPH_RANK_NONE">SPH_RANK_NONE
                                </option>
                            </select>
                            <p class="help-block"></p>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"></label>
                        <div class="controls">
                            <input type="hidden" name="operation" value="<?php echo $operation; ?>"/>
                            <button type="submit" name="searchSubmit"
                                    class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_optimize.js"></script>