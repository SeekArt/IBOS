<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Integral set']; ?></h1>
        <ul class="mn">
            <li>
                <span><?php echo $lang['Expand credit']; ?></span>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('credit/formula'); ?>"><?php echo $lang['Credit formula']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('credit/rule'); ?>"><?php echo $lang['Credit rule']; ?></a>
            </li>
        </ul>
    </div>
    <form action="<?php echo $this->createUrl('credit/setup'); ?>" method="post" class="form-horizontal">
        <div>
            <!-- 扩展积分设置 start -->
            <div class="ctb">
                <h2 class="st"><?php echo $lang['Expand credit setup']; ?></h2>
                <table class="point-table table table-bordered table-striped table-operate" id="point_setup_table">
                    <thead>
                    <tr>
                        <th><?php echo $lang['Credit name']; ?></th>
                        <th width="60"><?php echo $lang['Initial credit']; ?></th>
                        <th width="60"><?php echo $lang['Credit lower']; ?></th>
                        <th width="80"><?php echo $lang['Credit enable']; ?></th>
                        <th width="40"><?php echo $lang['Operation']; ?></th>
                    </tr>
                    </thead>
                    <tbody id="point_setup_tbody">
                    <?php foreach ($data as $val): ?>
                        <tr id="credit_<?php echo $val['cid']; ?>">
                            <td>
                                <input type="text" class="input-small" name="credit[<?php echo $val['cid']; ?>][name]"
                                       value="<?php echo $val['name']; ?>">
                            </td>
                            <td>
                                <input type="text" class="input-small"
                                       name="credit[<?php echo $val['cid']; ?>][initial]"
                                       value="<?php echo $val['initial']; ?>">
                            </td>
                            <td>
                                <input type="text" class="input-small" name="credit[<?php echo $val['cid']; ?>][lower]"
                                       value="<?php echo $val['lower']; ?>">
                            </td>
                            <td>
                                <input type="checkbox" name="credit[<?php echo $val['cid']; ?>][enable]"
                                       <?php if ($val['enable'] == 1): ?>checked<?php endif; ?> data-toggle="switch"
                                       class="visi-hidden">
                            </td>
                            <td>
                                <?php if ($val['system'] != 1): ?>
                                    <a href="javascript:;" data-action="removeCreditRule"
                                       data-id="<?php echo $val['cid']; ?>" class="cbtn o-trash"
                                       title="<?php echo $lang['Del']; ?>"></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">
                            <a href="javascript:;" data-action="addCreditRule" class="cbtn o-plus"
                               title="<?php echo $lang['Add credit']; ?>"></a>
                        </td>
                        <td>
                            <a href="javascript:;" data-action="resetCreditRule" class="cbtn o-cancel"
                               title="<?php echo $lang['Restore system Settings']; ?>"></a>
                        </td>
                    </tr>
                    </tfoot>
                </table>
                <div>
                    <div class="control-group">
                        <label class="control-label"><?php echo $lang['Credit change remind']; ?></label>
                        <div class="controls">
                            <input type="checkbox" name="changeRemind" data-toggle="switch" class="visi-hidden"
                                   <?php if ($changeRemind): ?>checked<?php endif; ?>>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <input type="hidden" name="removeId" id="removeId"/>
            <button class="btn btn-primary btn-large btn-submit" name="creditSetupSubmit" value="1"
                    type="submit"><?php echo $lang['Submit'] ?></button>
        </div>
        <img id="loading_img" style="display:none;" src='<?php echo STATICURL ?>/image/common/loading.gif'/>
    </form>
</div>
<script type="text/template" id="ext_credit_tpl">
    <tr id="credit_<%= cid %>">
        <td><input type="text" class="input-small" name="credit[<%= cid %>][name]"></td>
        <td><input type="text" class="input-small" name="credit[<%= cid %>][initial]"></td>
        <td><input type="text" class="input-small" name="credit[<%= cid %>][lower]"></td>
        <td><input type="checkbox" data-toggle="switch" class="visi-hidden" name="credit[<%= cid %>][enable]"></td>
        <td><a href="javascript:;" data-action="removeCreditRule" data-id="<%= cid %>" class="cbtn o-trash"
               title="<?php echo $lang['Del']; ?>"></a></td>
    </tr>
</script>
<script>
    Ibos.app.s({
        "maxExtNum": <?php echo $maxId; ?>
    })
</script>
<script src="<?php echo $assetUrl; ?>/js/db_credit_setup.js"></script>