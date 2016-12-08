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
        <form
            action="<?php echo $this->createUrl('split/index', array('op' => 'moving', 'mod' => 'email', 'sourcetableid' => $sourceTableId)); ?>"
            method="post" class="form-horizontal">
            <div class="ctb">
                <h2 class="st"><?php echo $lang['email']; ?></h2>
                <div class="btn-group control-group" data-toggle="buttons-radio">
                    <a href="<?php echo $this->createUrl('split/index', array('op' => 'manage', 'mod' => 'email')); ?>"
                       class="btn"><?php echo $lang['Split manage']; ?></a>
                    <a href="<?php echo $this->createUrl('split/index', array('op' => 'move', 'mod' => 'email')); ?>"
                       class="btn active"><?php echo $lang['Split move']; ?></a>
                </div>
            </div>
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Match conditions']; ?>：<?php echo $count; ?></h2>
                <div class="page-list">
                    <div class="page-list-mainer">
                        <table class="table table-condensed">
                            <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th><?php echo $lang['Move to']; ?>：</th>
                                <th><?php echo $lang['Data row']; ?></th>
                                <th><?php echo $lang['Data size']; ?></th>
                                <th><?php echo $lang['Index size']; ?></th>
                                <th><?php echo $lang['Table create time']; ?></th>
                                <th><?php echo $lang['Memo']; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td rowspan="2"><label class="radio"><input type="radio" name="tableid"
                                                                            <?php if ($sourceTableId == 0): ?>disabled<?php endif; ?>
                                                                            value="0"/></label></td>
                                <td><?php echo $main['Name']; ?></td>
                                <td><?php echo $main['Rows']; ?></td>
                                <td><?php echo $main['Data_length']; ?></td>
                                <td><?php echo $main['Index_length']; ?></td>
                                <td><?php echo $main['Create_time']; ?></td>
                                <td rowspan="2"><?php echo $tableInfo[0]['memo']; ?></td>
                            </tr>
                            <tr>
                                <td style="padding-left:10px;"><?php echo $body['Name']; ?></td>
                                <td><?php echo $body['Rows']; ?></td>
                                <td><?php echo $body['Data_length']; ?></td>
                                <td><?php echo $body['Index_length']; ?></td>
                                <td><?php echo $body['Create_time']; ?></td>
                            </tr>
                            </tbody>
                            <?php foreach ($tables as $tableId => $value): ?>
                                <tbody>
                                <tr>
                                    <td rowspan="2"><label class="radio"><input type="radio" name="tableid"
                                                                                <?php if ($sourceTableId == $tableId): ?>disabled<?php endif; ?>
                                                                                value="<?php echo $tableId; ?>"/></label>
                                    </td>
                                    <td><?php echo $value['main']['Name']; ?></td>
                                    <td><?php echo $value['main']['Rows']; ?></td>
                                    <td><?php echo $value['main']['Data_length']; ?></td>
                                    <td><?php echo $value['main']['Index_length']; ?></td>
                                    <td><?php echo $value['main']['Create_time']; ?></td>
                                    <td rowspan="2"><?php echo isset($tableInfo[$tableId]) ? $tableInfo[$tableId]['memo'] : ''; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding-left:10px;"><?php echo $value['body']['Name']; ?></td>
                                    <td><?php echo $value['body']['Rows']; ?></td>
                                    <td><?php echo $value['body']['Data_length']; ?></td>
                                    <td><?php echo $value['body']['Index_length']; ?></td>
                                    <td><?php echo $value['body']['Create_time']; ?></td>
                                </tr>
                                </tbody>
                            <?php endforeach; ?>
                        </table>
                    </div>
                    <?php if (isset($list)): ?>
                        <div class="ctb">
                            <h2 class="st"><?php echo $lang['Email list']; ?></h2>
                            <div class="page-list">
                                <div class="page-list-mainer">
                                    <table class="table table-condensed">

                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="page-list-footer">
                        <div class="span3">
                            <div class="input-group">
                                <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
                                <input type="hidden" name="detail" value="<?php echo $detail; ?>"/>
                                <input type="hidden" name="readytomve"
                                       value="<?php echo isset($readyToMove) ? $readyToMove : 0; ?>"/>
                                <input type="hidden" name="conditions" value="<?php echo $conditions; ?>"/>
                                <span class="input-group-addon"><?php echo $lang['Pertime email']; ?></span>
                                <input type="text" name="pertime" class="form-control" value="200">
								<span class="input-group-btn">
									<button class="btn btn-primary"
                                            type="submit"><?php echo $lang['Submit']; ?></button>
								</span>
                            </div><!-- /input-group -->
                            <br/>
                            <label title="<?php echo $lang['Pertime desc']; ?>" class="checkbox"><input type="checkbox"
                                                                                                        name="setcron"
                                                                                                        value="1"/><?php echo $lang['Save my settings to schedule task']; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>