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
    <div>
        <form action="<?php echo $this->createUrl('split/index', array('op' => 'manage', 'mod' => 'email')); ?>"
              method="post" class="form-horizontal">
            <!-- 运行记录 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['email']; ?></h2>
                <div class="btn-group control-group" data-toggle="buttons-radio">
                    <a href="<?php echo $this->createUrl('split/index', array('op' => 'manage', 'mod' => 'email')); ?>"
                       class="btn active"><?php echo $lang['Split manage']; ?></a>
                    <a href="<?php echo $this->createUrl('split/index', array('op' => 'move', 'mod' => 'email')); ?>"
                       class="btn"><?php echo $lang['Split move']; ?></a>
                </div>
                <div class="alert trick-tip">
                    <div class="trick-tip-title">
                        <i></i>
                        <strong><?php echo $lang['Skills prompt']; ?></strong>
                    </div>
                    <div class="trick-tip-content">
                        <?php echo $lang['Email archive tip']; ?>
                    </div>
                </div>
            </div>
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Main table info']; ?></h2>
                <div class="page-list">
                    <div class="page-list-mainer">
                        <table class="table table-condensed">
                            <thead>
                            <tr>
                                <th><?php echo $lang['Table name']; ?></th>
                                <th><?php echo $lang['Data row']; ?></th>
                                <th><?php echo $lang['Data size']; ?></th>
                                <th><?php echo $lang['Index size']; ?></th>
                                <th><?php echo $lang['Table create time']; ?></th>
                                <th><?php echo $lang['Memo']; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?php echo $main['Name']; ?></td>
                                <td><?php echo $main['Rows']; ?></td>
                                <td><?php echo $main['Data_length']; ?></td>
                                <td><?php echo $main['Index_length']; ?></td>
                                <td><?php echo $main['Create_time']; ?></td>
                                <td rowspan="2"><input type="text" name="memo[0]"
                                                       value="<?php echo $tableInfo[0]['memo']; ?>"/></td>
                            </tr>
                            <tr>
                                <td><?php echo $body['Name']; ?></td>
                                <td><?php echo $body['Rows']; ?></td>
                                <td><?php echo $body['Data_length']; ?></td>
                                <td><?php echo $body['Index_length']; ?></td>
                                <td><?php echo $body['Create_time']; ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Archive table info']; ?></h2>
                <div class="page-list">
                    <div class="page-list-mainer">
                        <table class="table table-condensed">
                            <thead>
                            <tr>
                                <th><?php echo $lang['Display name']; ?></th>
                                <th><?php echo $lang['Table name']; ?></th>
                                <th><?php echo $lang['Data row']; ?></th>
                                <th><?php echo $lang['Data size']; ?></th>
                                <th><?php echo $lang['Index size']; ?></th>
                                <th><?php echo $lang['Table create time']; ?></th>
                                <th><?php echo $lang['Memo']; ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <?php foreach ($tables as $tableId => $value): ?>
                                <tbody>
                                <tr>
                                    <td rowspan="2"><input type="text" name="displayname[<?php echo $tableId; ?>]"
                                                           value="<?php echo isset($tableInfo[$tableId]) ? $tableInfo[$tableId]['displayname'] : ''; ?>"/>
                                    </td>
                                    <td><?php echo $value['main']['Name']; ?></td>
                                    <td><?php echo $value['main']['Rows']; ?></td>
                                    <td><?php echo $value['main']['Data_length']; ?></td>
                                    <td><?php echo $value['main']['Index_length']; ?></td>
                                    <td><?php echo $value['main']['Create_time']; ?></td>
                                    <td rowspan="2"><input type="text" name="memo[<?php echo $tableId; ?>]"
                                                           value="<?php echo isset($tableInfo[$tableId]) ? $tableInfo[$tableId]['memo'] : ''; ?>"/>
                                    </td>
                                    <td rowspan="2"><a
                                            href="<?php echo $this->createUrl('split/index', array('op' => 'droptable', 'tableid' => $tableId, 'mod' => 'email')); ?>"
                                            class="btn"><?php echo $lang['Delete']; ?></a></td>
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
                    <div class="page-list-footer">
                        &nbsp;
                        <button type="submit" name="archiveSubmit"
                                class="btn btn-primary btn-submit"><?php echo $lang['Update table info']; ?></button>
                        <a href="<?php echo $this->createUrl('split/index', array('op' => 'addtable', 'mod' => 'email')); ?>"
                           class="btn"><?php echo $lang['Add archive']; ?></a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>