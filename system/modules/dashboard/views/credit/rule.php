<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Integral set']; ?></h1>
        <ul class="mn">
            <li>
                <a href="<?php echo $this->createUrl('credit/setup'); ?>"><?php echo $lang['Expand credit']; ?></a>
            </li>
            <li>
                <a href="<?php echo $this->createUrl('credit/formula'); ?>"><?php echo $lang['Credit formula']; ?></a>
            </li>
            <li>
                <span><?php echo $lang['Credit rule']; ?></span>
            </li>
        </ul>
    </div>
    <div>
        <!-- 积分策略 start -->
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Credit rule']; ?></h2>
            <div class="alert trick-tip clearfix">
                <div class="trick-tip-title">
                    <strong><?php echo $lang['Skills prompt']; ?></strong>
                </div>
                <p class="trick-tip-content">
                    <?php echo $lang['Credit rule tips']; ?>
                </p>
            </div>
            <form action="<?php echo $this->createUrl('credit/rule'); ?>" method="post" class="form-horizontal">
                <table class="table table-bordered table-striped table-operate">
                    <!-- Todo :: fixed layer，跟随显示表头效果-->
                    <thead>
                    <tr>
                        <th><?php echo $lang['Rule name']; ?></th>
                        <th width="100"><?php echo $lang['Cycle']; ?></th>
                        <th width="100"><?php echo $lang['Cycle reward nums']; ?></th>
                        <?php foreach ($credits as $credit) : ?>
                            <th width="60"><?php echo $credit['name']; ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rules as $rule) : ?>
                        <tr>
                            <td><?php echo $rule['rulename']; ?></td>
                            <td>
                                <select data-type="cycletype" data-value="<?php echo $rule['rid']; ?>"
                                        name="cycles[<?php echo $rule['rid']; ?>]" class="input-small">
                                    <option value="1"
                                            <?php if ($rule['cycletype'] == '1'): ?>selected<?php endif; ?>><?php echo $lang['Once']; ?></option>
                                    <option value="2"
                                            <?php if ($rule['cycletype'] == '2'): ?>selected<?php endif; ?>><?php echo $lang['Per hour']; ?></option>
                                    <option value="3"
                                            <?php if ($rule['cycletype'] == '3'): ?>selected<?php endif; ?>><?php echo $lang['Every day']; ?></option>
                                    <option value="4"
                                            <?php if ($rule['cycletype'] == '4'): ?>selected<?php endif; ?>><?php echo $lang['Weekly']; ?></option>
                                    <option value="5"
                                            <?php if ($rule['cycletype'] == '5'): ?>selected<?php endif; ?>><?php echo $lang['Per mensem']; ?></option>
                                </select>
                            </td>
                            <td>
                                <input id="reward_num_<?php echo $rule['rid']; ?>" type="text"
                                       value="<?php echo $rule['rewardnum']; ?>"
                                       name="rewardnums[<?php echo $rule['rid']; ?>]" class="input-small">
                            </td>
                            <?php foreach ($credits as $index => $credit) : ?>
                                <?php $offset = (integer)($index + 1); ?>
                                <td>
                                    <input type="text" value="<?php echo $rule['extcredits' . $offset] ?>" length="4"
                                           name="credits[<?php echo $rule['rid']; ?>][<?php echo $offset; ?>]"
                                           class="input-small">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="formhash" value="<?php echo FORMHASH; ?>"/>
                <button name="creditRuleSubmit" class="btn btn-primary btn-large btn-submit"
                        type="submit"><?php echo $lang['Submit']; ?></button>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('[data-type="cycletype"]').on('change', function () {
            var val = this.value,
                identifier = $(this).attr('data-value');
            if (val === '1') {
                $('#reward_num_' + identifier).val('').attr('disabled', true);
            } else {
                $('#reward_num_' + identifier).removeAttr('disabled');
            }
        });
        $('[data-type="cycletype"]').change();
    });
</script>