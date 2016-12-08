<?php

use application\core\utils\Convert;

?>

<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Database']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('database/backup'); ?>"><?php echo $lang['Backup']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('database/restore'); ?>"><?php echo $lang['Restore']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Optimize']; ?></span>
            </li>
        </ul>
    </div>
    <div>
        <form action="<?php echo $this->createUrl('database/optimize'); ?>" method="post" class="form-horizontal">
            <!-- 待优化数据表列表 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Optimize table list']; ?></h2>
                <div class="alert trick-tip">
                    <div class="trick-tip-title">
                        <i></i>
                        <strong><?php echo $lang['Skills prompt']; ?></strong>
                    </div>
                    <div class="trick-tip-content">
                        <?php echo $lang['Optimize tip']; ?>
                    </div>
                </div>
                <table class="table table-bordered table-striped table-operate">
                    <thead>
                    <tr>
                        <th>
                            <label class="checkbox">
                                <span class="icon"></span>
                                <span class="icon-to-fade"></span>
                                <input type="checkbox" data-name="tables"/>
                            </label>
                        </th>
                        <th><?php echo $lang['Ibos data table']; ?></th>
                        <th><?php echo $lang['Type']; ?></th>
                        <th><?php echo $lang['Records']; ?></th>
                        <th><?php echo $lang['Data']; ?></th>
                        <th><?php echo $lang['Index']; ?></th>
                        <th><?php echo $lang['Debris']; ?></th>
                        <th><?php echo $lang['Size']; ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $key => $value) : ?>
                        <tr>
                            <td>
                                <label class="checkbox">
                                    <span class="icon"></span>
                                    <span class="icon-to-fade"></span>
                                    <input type="checkbox" data-check="tables" value="<?php echo $value['Name']; ?>"
                                           name="optimizeTables[]" <?php echo $value['checked']; ?> />
                                </label>
                            </td>
                            <td><?php echo $value['Name']; ?></td>
                            <td><?php echo $value[$value['tableType']]; ?></td>
                            <td><?php echo $value['Rows']; ?></td>
                            <td><?php echo $value['Data_length']; ?></td>
                            <td><?php echo $value['Index_length']; ?></td>
                            <td><?php echo $value['Data_free']; ?></td>
                            <td><?php echo Convert::sizeCount(($value['Data_length'] + $value['Index_length'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="8">
                            <?php if ($totalSize > 0): ?>
                                <?php echo $lang['Size'] . ':' . Convert::sizeCount($totalSize); ?>
                            <?php else: ?>
                                <?php echo $lang['No need to optimize']; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <?php if ($totalSize > 0): ?>
                <div>
                    <button type="submit" name="dbSubmit"
                            class="btn btn-primary btn-large btn-submit"><?php echo $lang['Optimize']; ?></button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>