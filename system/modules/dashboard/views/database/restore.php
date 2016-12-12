<?php

use application\core\utils\Convert;
use application\core\utils\Ibos;
use application\core\utils\StringUtil;

?>
<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Database']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('database/backup'); ?>"><?php echo $lang['Backup']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Restore']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('database/optimize'); ?>"><?php echo $lang['Optimize']; ?></a>
            </li>
        </ul>
    </div>
    <div>
        <div class="ctb">
            <!-- 数据备份记录 start -->
            <h2 class="st"><?php echo $lang['Data backup record']; ?></h2>
            <div class="alert trick-tip">
                <div class="trick-tip-title">
                    <i></i>
                    <strong><?php echo $lang['Skills prompt']; ?></strong>
                </div>
                <div class="trick-tip-content">
                    <?php echo $lang['Restore tip']; ?>
                </div>
            </div>
            <div class="page-list">
                <div class="page-list-header">
                    <div class="row">
                        <div class="span8">
                            <button type="button" data-act="del"
                                    class="btn"><?php echo $lang['Delete backup']; ?></button>
                        </div>
                    </div>
                </div>
                <div class="page-list-mainer">
                    <form id="sys_dbrestore_form" method="post" class="form-horizontal"
                          action="<?php echo $this->createUrl('database/restore'); ?>">
                        <table class="table table-striped" id="restore_table">
                            <thead>
                            <tr>
                                <th>
                                    <label class="checkbox">
                                        <input type="checkbox" data-name="key"/>
                                    </label>
                                </th>
                                <th><?php echo $lang['File name']; ?></th>
                                <th><?php echo $lang['Version']; ?></th>
                                <th><?php echo $lang['Time']; ?></th>
                                <th><?php echo $lang['Type']; ?></th>
                                <th><?php echo $lang['Size']; ?></th>
                                <th><?php echo $lang['Way']; ?></th>
                                <th><?php echo $lang['Volume']; ?></th>
                                <th><?php echo $lang['Operation']; ?></th>
                            </tr>
                            </thead>
                            <?php foreach ($list['exportLog'] as $key => $value) : ?>
                                <?php
                                $info = $value[1];
                                $random = StringUtil::random(5);
                                $info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? $lang['DBMultivol'] : $lang['DBShell']) : '';
                                $info['volume'] = count($value);
                                ?>
                                <tbody>
                                <tr>
                                    <td>
                                        <label class="checkbox">
                                            <input type="checkbox" data-check='key' value="<?php echo $key; ?>"
                                                   name="key[<?php echo $key; ?>]">
                                        </label>
                                    </td>
                                    <td>
                                        <a href="#restore_<?php echo $random; ?>_body"
                                           data-act="collapse"><?php echo $key; ?></a>
                                    </td>
                                    <td><?php echo $info['version']; ?></td>
                                    <td><?php echo is_int($info['dateline']) ? date('Y-m-d H:i', $info['dateline']) : $lang['Unknown']; ?></td>
                                    <td><?php echo $lang['Backup method ' . $info['type']]; ?></td>
                                    <td><?php echo Convert::sizeCount($list['exportSize'][$key]); ?></td>
                                    <td><?php echo $info['method']; ?></td>
                                    <td><?php echo $info['volume']; ?></td>
                                    <td>
                                        <a data-act="import" data-id="<?php echo $info['filename']; ?>"
                                           href="javascript:void(0);"
                                           class="btn btn-small"><?php echo $lang['Import']; ?></a>
                                    </td>
                                </tr>
                                </tbody>
                                <!-- 子列表 -->
                                <!-- a:start -->
                                <tbody id="restore_<?php echo $random; ?>_body" style="display:none;">
                                <?php foreach ($value as $index => $row) : ?>
                                    <?php
                                    $info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? $lang['DBMultivol'] : $lang['DBShell']) : '';
                                    ?>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <a href="<?php echo $row['filename']; ?>"><?php echo substr(strrchr($row['filename'], "/"), 1); ?></a>
                                        </td>
                                        <td><?php echo $row['version']; ?></td>
                                        <td><?php echo is_int($row['dateline']) ? date('Y-m-d H:i', $row['dateline']) : $lang['Unknown']; ?></td>
                                        <td></td>
                                        <td><?php echo Convert::sizeCount($row['size']); ?></td>
                                        <td></td>
                                        <td><?php echo $row['volume']; ?></td>
                                        <td></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <!-- a:end -->
                            <?php endforeach; ?>
                            <!-- zip文件 -->
                            <?php foreach ($list['exportZipLog'] as $key => $value) : ?>
                                <tbody>
                                <tr>
                                    <td>
                                        <label class="checkbox">
                                            <span class="icon"></span>
                                            <span class="icon-to-fade"></span>
                                            <input type="checkbox" data-check='key'
                                                   value="<?php echo basename($value['filename']); ?>"
                                                   name="key[<?php echo $key; ?>]">
                                        </label>
                                    </td>
                                    <td>
                                        <a href="<?php echo $value['filename']; ?>"><?php echo substr(strrchr($value['filename'], "/"), 1); ?></a>
                                    </td>
                                    <td></td>
                                    <td><?php echo is_int($value['dateline']) ? date('Y-m-d H:i', $value['dateline']) : $lang['Unknown']; ?></td>
                                    <td>ZIP</td>
                                    <td><?php echo Convert::sizeCount($value['size']); ?></td>
                                    <td><?php echo $value['type']; ?></td>
                                    <td></td>
                                    <td>
                                        <a href="javascript:void(0);" data-act="decompress"
                                           data-id="<?php echo $value['filename']; ?>"
                                           class="btn btn-small"><?php echo $lang['Decompress']; ?></a>
                                    </td>
                                </tr>
                                </tbody>
                            <?php endforeach; ?>
                        </table>
                        <input type="hidden" name="dbSubmit" value="1"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="<?php echo $assetUrl; ?>/js/db_database.js"></script>