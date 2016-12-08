<div class="dib right-sidebar">
    <div class="sidebar-header">
        <i class="o-complete-case"></i><span class="header-title vam"><?php echo $lang['Credit situation']; ?></span>
    </div>
    <div class="sidebar-body">
        <div class="clearfix mb">
            <div class="xwb dib">
                <i class="lv lv<?php echo $user['level']; ?>"></i>
                <span class="dib mlm fss"><?php echo $user['group_title']; ?></span>
            </div>	
			<span class="exp-val dib">
				<em><?php echo $user['credits']; ?>/</em><?php echo $user['next_group_credit']; ?>
			</span>
        </div>
        <div class="progress mb">
            <div
                class="progress-bar <?php if ($user['upgrade_percent'] > 90): ?>progress-bar-danger<?php else: ?>progress-bar-success<?php endif; ?>"
                role="progressbar" aria-valuenow="<?php echo $user['upgrade_percent']; ?>" aria-valuemin="0"
                aria-valuemax="100" style="width: <?php echo $user['upgrade_percent']; ?>%;"></div>
        </div>
        <div class="clearfix">
            <div class="pull-left">
                <span><?php echo $lang['Upgrade needed']; ?>&nbsp;:&nbsp;</span><span
                    class="xwb"><?php echo (int)($user['next_group_credit'] - $user['credits']); ?></span>
            </div>
            <div class="pull-right">
                <span><?php echo $lang['Online time']; ?>&nbsp;:&nbsp;</span><span
                    class="xwb"><?php echo $userCount['oltime']; ?><?php echo $lang['Hour']; ?></span>
            </div>
        </div>
    </div>
    <div>
        <table class="table table-striped table-condensed mbz">
            <tbody>
            <?php
            if (is_array($extcredits)):
                foreach ($extcredits as $ext): ?>
                    <?php if (!empty($ext)): ?>
                        <!--						<tr>-->
                        <!--							<td>--><?php //echo $ext['name']; ?><!--</td>-->
                        <!--							<td class="integral-info">--><?php //echo $ext['value']; ?><!--</td>-->
                        <!--						</tr>-->
                        <?php if ($ext['value'] == 0): ?>
                            <tr>
                                <td><?php echo $ext['name'] ?></td>
                                <td class="integral-info"><?php echo $ext['initial'] ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?php echo $ext['name'] ?></td>
                                <td class="integral-info"><?php echo $ext['value']; ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach;
            endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="2">
                    <div class="xar">
                        <i class="o-doubt-img"></i>
                        <a href="javascript:;" id="integral_tip" data-placement="bottom" data-html="true"
                           data-original-title="<?php echo $lang['Credit total']; ?> = <?php echo $creditFormulaExp; ?>"><?php echo $lang['Credit formula']; ?></a>
                    </div>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>