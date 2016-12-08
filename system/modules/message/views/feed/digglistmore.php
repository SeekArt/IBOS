<?php foreach ($list as $val): ?>
    <tr>
        <td width="30">
            <a href="<?php echo $val['user']['space_url']; ?>" target="_blank"
               class="avatar-circle avatar-circle-small pull-left">
                <img src="<?php echo $val['user']['avatar_small']; ?>" alt="<?php echo $val['user']['realname']; ?>">
            </a>
        </td>
        <td>
            <strong><?php echo $val['user']['realname']; ?></strong>
            <p><?php echo $val['user']['deptname'] ? $val['user']['deptname'] . '·' : ''; ?><?php echo $val['user']['posname'] ? $val['user']['posname'] : ''; ?></p>
        </td>
        <td width="80">
			<span class="wb-cb">
				<?php if (isset($followstates[$val['user']['uid']])): ?>
                    <?php $states = $followstates[$val['user']['uid']]; ?>
                    <?php if ($states['following'] && $states['follower']): ?>
                        <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow"
                           data-param='{"fid": <?php echo $val['user']['uid']; ?>}' data-loading-text="取消中...">
                            <i class="om-geoc"></i>
                            互相关注
                        </a>
                    <?php elseif ($states['following']): ?>
                        <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn" data-action="unfollow"
                           data-param='{"fid": <?php echo $val['user']['uid']; ?>}' data-loading-text="取消中...">
                            <i class="om-gcheck"></i>
                            已关注
                        </a>
                    <?php else: ?>
                        <a href="javascript:;" class="btn btn-small" data-action="follow"
                           data-param='{"fid": <?php echo $val['user']['uid']; ?>}' data-loading-text="关注中...">
                            <i class="om-gplus"></i>
                            关注
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
			</span>
        </td>
    </tr>
<?php endforeach; ?>