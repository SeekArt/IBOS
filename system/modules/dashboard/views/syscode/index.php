<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['System code setting']; ?></h1>
    </div>
    <div>
        <form id="sys_code_form" action="<?php echo $this->createUrl('syscode/index'); ?>" class="form-horizontal"
              method='post'>
            <!-- 系统代码设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['System code setting']; ?></h2>
                <div class="alert trick-tip">
                    <div class="trick-tip-title">
                        <i></i>
                        <strong><?php echo $lang['Tips']; ?></strong>
                    </div>
                    <div class="trick-tip-content">
                        <ul>
                            <li><?php echo $lang['In the same code number is not the same as an entry']; ?></li>
                        </ul>
                    </div>
                </div>
                <div>
                    <table class="mst table table-bordered table-striped table-operate" id="system_code_table">
                        <thead>
                        <tr>
                            <th width="30" class="mst-icon-th"></th>
                            <th width="60"><?php echo $lang['Sort numbers']; ?></th>
                            <th><?php echo $lang['Category Name']; ?></th>
                            <th width="200"><?php echo $lang['Code number']; ?></th>
                        </tr>
                        </thead>
                        <?php $count = 0; ?>
                        <?php foreach ($data as $key => $val): ?>
                            <?php $count++; ?>
                            <tbody class="mstp <?php if ($count == 1): ?>active<?php endif; ?>">
                            <tr>
                                <td class="mst-icon"><a href="javascript:void(0);"
                                                        data-target="#system_code_<?php echo $key; ?>_body"
                                                        data-act="collapse"></a></td>
                                <td>
                                    <input type="text" class="input-small" name='codes[<?php echo $key; ?>][sort]'
                                           value="<?php echo $val['sort']; ?>">
                                </td>
                                <td>
                                    <div class="row">
                                        <div class="span6">
                                            <input type="text" class="input-small" data-type="name"
                                                   name='codes[<?php echo $key; ?>][name]'
                                                   value="<?php echo $val['name']; ?>">
                                        </div>
                                        <div class="span6">
                                            <a href="javascript:void(0);"
                                               data-target="#system_code_<?php echo $key; ?>_body" class="cbtn o-plus"
                                               data-act="add"></a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span><?php echo $val['number']; ?></span>
                                </td>
                            </tr>
                            </tbody>
                            <tbody class="msts" data-id='<?php echo $key; ?>' id="system_code_<?php echo $key; ?>_body"
                                   <?php if ($count != 1): ?>style="display:none;"<?php endif; ?>>
                            <?php $subCount = count($val['child']); ?>
                            <?php $subIndex = 0; ?>
                            <?php foreach ($val['child'] as $subKey => $subVal): ?>
                                <?php $subIndex++; ?>
                                <tr data-id='<?php echo $subVal['id']; ?>'
                                    <?php if ($subIndex == $subCount): ?>class="msts-last"<?php endif; ?>>
                                    <td></td>
                                    <td>
                                        <input type="text" name='codes[<?php echo $subVal['id']; ?>][sort]'
                                               class="input-small" value="<?php echo $subVal['sort']; ?>">
                                    </td>
                                    <td class="mst-board">
                                        <div class="row">
                                            <div class="span2"></div>
                                            <div class="span6">
                                                <input type="text" name='codes[<?php echo $subVal['id']; ?>][name]'
                                                       data-type="name" class="input-small"
                                                       value="<?php echo $subVal['name']; ?>">
                                            </div>
                                            <?php if ($subVal['system'] == '0'): ?>
                                                <div class="span4"><a href="javascript:void(0);"
                                                                      data-target="<?php echo $subVal['id']; ?>"
                                                                      data-act="remove" class="cbtn o-trash"></a></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($subVal['system'] == '0'): ?>
                                            <input type="text" name='codes[<?php echo $subVal['id']; ?>][number]'
                                                   data-type="number" class="input-small"
                                                   value="<?php echo $subVal['number']; ?>">
                                        <?php else: ?>
                                            <span><?php echo $subVal['number']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        <?php endforeach; ?>
                    </table>
                </div>
                <div>
                    <button type="submit" name='sysCodeSubmit'
                            class="btn btn-primary btn-large btn-submit"><?php echo $lang['Submit']; ?></button>
                </div>
            </div>
            <input type="hidden" id='removeId' name="removeId"/>
        </form>
    </div>
</div>
<script type="text/ibos-template" id="new_code">
    <td></td>
    <td><input type="text" class="input-small" name="newcodes[<%=id%>][sort]"></td>
    <td class="mst-board">
        <div class="row">
            <div class="span2"></div>
            <div class="span6">
                <input type="text" name='newcodes[<%=id%>][name]' data-type="name" class="input-small"/>
            </div>
            <div class="span4">
                <a href="javascript:void(0);" data-act="remove" class="cbtn o-trash"></a>
            </div>
        </div>
    </td>
    <td>
        <input type="text" name='newcodes[<%=id%>][number]' data-type="number" class="input-small"/>
        <input type="hidden" name='newcodes[<%=id%>][pid]' value="<%=pid%>"/>
    </td>
</script>
<script src="<?php echo $assetUrl; ?>/js/db_syscode.js"></script>