<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Archive Split']; ?></h1>
        <ul class="mn">
            <li>
                <span><?php echo $lang['email']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('split/index', array('op' => 'manager', 'mod' => 'diary')); ?>"><?php echo $lang['diary']; ?></a>
            </li>
        </ul>
    </div>
    <!-- 运行记录 start -->
    <div>
        <form action="<?php echo $this->createUrl('split/index', array('op' => 'movechoose', 'mod' => 'email')); ?>"
              method="post" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['email']; ?></h2>
                <div class="btn-group control-group" data-toggle="buttons-radio">
                    <a href="<?php echo $this->createUrl('split/index', array('op' => 'manage', 'mod' => 'email')); ?>"
                       class="btn"><?php echo $lang['Split manage']; ?></a>
                    <a href="<?php echo $this->createUrl('split/index', array('op' => 'move', 'mod' => 'email')); ?>"
                       class="btn active"><?php echo $lang['Split move']; ?></a>
                </div>
                <div class="alert trick-tip">
                    <div class="trick-tip-title">
                        <i></i>
                        <strong><?php echo $lang['Skills prompt']; ?></strong>
                    </div>
                    <div class="trick-tip-content">
                        <?php echo $lang['Email move tip']; ?>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Archivessplit show email detail']; ?>:</label>
                <div class="controls" id="backup_type">
                    <label class="radio"><input type="radio" value="1" name="detail"><?php echo $lang['Yes']; ?></label>
                    <label class="radio"><input type="radio" value="0" checked name="detail"><?php echo $lang['No']; ?>
                    </label>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Archivessplit search scope']; ?>:</label>
                <div class="controls" id="backup_type">
                    <select name="sourcetableid" class="span3">
                        <?php foreach ($tableSelect as $tableId => $select): ?>
                            <option value="<?php echo $tableId; ?>"><?php echo $select; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo $lang['Archivessplit time scope']; ?>:</label>
                <div class="controls" id="backup_type">
                    <label class="radio"><input type="radio" value="3" checked
                                                name="timerange"><?php echo $lang['Archivessplit time 3 months ago']; ?>
                    </label>
                    <label class="radio"><input type="radio" value="6"
                                                name="timerange"><?php echo $lang['Archivessplit time 6 months ago']; ?>
                    </label>
                    <label class="radio"><input type="radio" value="12"
                                                name="timerange"><?php echo $lang['Archivessplit time 1 years ago']; ?>
                    </label>
                    <label class="radio"><input type="radio" value="24"
                                                name="timerange"><?php echo $lang['Archivessplit time 2 years ago']; ?>
                    </label>
                    <label class="radio"><input type="radio" value="36"
                                                name="timerange"><?php echo $lang['Archivessplit time 3 years ago']; ?>
                    </label>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"></label>
                <div class="controls" id="backup_type">
                    <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>">
                    <button type="submit" name="archiveSubmit"
                            class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                </div>
            </div>
        </form>
    </div>
</div>