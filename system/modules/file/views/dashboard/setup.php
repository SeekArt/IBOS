<form action="<?php echo $this->createUrl('dashboard/index'); ?>" class="form-horizontal" method="post">
    <div class="ct">
        <div class="clearfix">
            <h1 class="mt"><?php echo $lang['Folder']; ?></h1>
            <ul class="mn">
                <li>
                    <span><?php echo $lang['Folder setting']; ?></span>
                </li>
                <!--				<li>
                                    <a href="<?php echo $this->createUrl('dashboard/store'); ?>"><?php echo $lang['Store setting']; ?></a>
                                </li>-->
                <li>
                    <a href="<?php echo $this->createUrl('dashboard/trash'); ?>"><?php echo $lang['Trash']; ?></a>
                </li>
            </ul>
        </div>
        <div>
            <!-- 个人网盘设置 -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Personal folder setting']; ?></h2>
                <div class="ctbw">
                    <div class="control-group span8">
                        <label class="control-label"><?php echo $lang['Default capacity allocation']; ?></label>
                        <div class="controls">
                            <div class="input-group">
                                <span class="input-group-addon"><?php echo $lang['Everyone']; ?></span>
                                <input type="text" name="filedefsize" value="<?php echo $setting['filedefsize']; ?>">
                                <span class="input-group-addon">MB</span>
                            </div>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Specify capacity allocation']; ?></label>
                        <div class="controls file-controls">
                            <?php if (!empty($setting['filecapasity'])): ?>
                                <?php foreach ($setting['filecapasity'] as $key => $value): ?>

                                    <div class="mbs">
                                        <input type="text" name="role[<?php echo $key; ?>][mem]"
                                               data-id="<?php echo $key; ?>" id="roleallocation_<?php echo $key; ?>"
                                               value="<?php echo $value['mem']; ?>">
                                        <div id="roleallocation_<?php echo $key; ?>_box"></div>
                                    </div>
                                    <div class="input-group mbs">
                                        <span class="input-group-addon"><?php echo $lang['Everyone']; ?></span>
                                        <input type="text" name="role[<?php echo $key; ?>][size]"
                                               value="<?php echo $value['size']; ?>">
                                        <span class="input-group-addon">MB</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="mbs">
                                    <input type="text" name="role[0][mem]" id="roleallocation" value=""
                                           placeholder="<?php echo $lang['Select user']; ?>">
                                    <div id="roleallocation_box"></div>
                                </div>
                                <div class="input-group mbs">
                                    <span class="input-group-addon"><?php echo $lang['Everyone']; ?></span>
                                    <input type="text" name="role[0][size]" value="">
                                    <span class="input-group-addon">MB</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
                            <a href="javascript:;" class="add-one" id="file_option_add">
                                <i class="circle-btn-small o-plus"></i>
                                <?php echo $lang['Add one item']; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 公司网盘设置 -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Company folder setting']; ?></h2>
                <div class="ctbw">
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Folder manager']; ?></label>
                        <div class="controls">
                            <input type="text" name="filecompmanager" value="<?php echo $setting['filecompmanager']; ?>"
                                   id="filecompanymanager" placeholder="<?php echo $lang['Select user']; ?>">
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

<!-- 新增容量分配模板 -->
<script type="text/ibos-template" id="file_template">
    <div class="mbs">
        <input type="text" name="<%=name%>" id="<%=id%>" value="" placeholder="<?php echo $lang['Select user']; ?>">
        <div id="<%=boxid%>"></div>
    </div>
    <div class="input-group mbs">
        <span class="input-group-addon"><?php echo $lang['Everyone']; ?></span>
        <input type="text" name="<%=size%>" value="">
        <span class="input-group-addon">MB</span>
    </div>
</script>

<script type="text/javascript">
    (function () {
        $("#roleallocation").userSelect({
            data: Ibos.data.get()
        });
        $('#file_option_add').on('click', function () {
            var date = new Date();
            var id = date.getTime();
            var data = {
                    name: 'role[' + id + '][mem]',
                    id: 'roleallocation_' + id,
                    boxid: 'roleallocation_' + id + '_box',
                    size: 'role[' + id + '][size]'
                },
                temp = $.template('file_template', data);
            $('.file-controls').append(temp);

            $('#' + data.id).userSelect({
                data: Ibos.data.get()
            });
        });
        $("#filecompanymanager").userSelect({
            data: Ibos.data.get()
        });
    })();
    $(document).ready(function () {
        $('.file-controls input[type=text]').each(function () {
            var dataId = $(this).data('id'), id = 'roleallocation_' + dataId, boxId = 'roleallocation_' + dataId + '_box';
            $('#' + id).userSelect({
                data: Ibos.data.get()
            });
        });
    });
</script>
