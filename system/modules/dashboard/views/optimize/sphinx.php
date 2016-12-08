<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Performance optimization']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('optimize/cache'); ?>"><?php echo $lang['Optimize Cache']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('optimize/search'); ?>"><?php echo $lang['Full-text search setup']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Sphnix control']; ?></span>
            </li>
        </ul>
    </div>
    <div>
        <!-- Sphinx控制 start -->
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Sphnix control']; ?></h2>
            <div class="ctbw">
                <form method="post" action="<?php echo $this->createUrl('optimize/sphinx'); ?>" class="form-horizontal">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Sphinx host']; ?></label>
                        <div class="controls">
                            <input type="text" id='sphinxHost' name="sphinxhost" value="<?php echo $sphinxHost; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Sphinx port']; ?></label>
                        <div class="controls">
                            <input type="text" id='sphinxPort' name="sphinxport" value="<?php echo $sphinxPort; ?>"/>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"></label>
                        <div class="controls">
                            <button type="submit" name="sphinxSubmit"
                                    class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_optimize.js"></script>