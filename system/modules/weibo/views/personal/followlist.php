<?php
use application\core\utils\StringUtil;

?>
<?php if (!empty($list)): ?>
    <?php foreach ($list as $uid => $followState) : ?>
        <li>
            <div class="wb-fans-card">
                <div class="wbc-box rdt bdbs">
                    <div class="mbs">
                        <a href="<?php echo $followState['user']['space_url']; ?>" class="avatar-circle">
                            <img src="<?php echo $followState['user']['avatar_middle']; ?>"
                                 alt="<?php echo $followState['user']['realname']; ?>"/>
                        </a>
                    </div>
                    <div class="wb-fans-name">
                        <strong><?php echo $followState['user']['realname']; ?></strong><?php if (!empty($followState['user']['posname'])): ?>
                            <span>&nbsp;·&nbsp;</span>
                            <span><?php echo $followState['user']['posname']; ?></span><?php endif; ?>
                    </div>
                    <div class="wb-fans-from">
                        <?php if (!empty($followState['user']['bio'])): ?><?php echo StringUtil::cutStr($followState['user']['bio'], 20); ?><?php else: ?>TA什么都没有写<?php endif; ?>
                    </div>
                </div>
                <div class="rdb wbc-box2">
                    <span class="wb-cb followedboth">
                        <?php if (!$followState['following']): ?>
                            <a href="javascript:;" class="btn btn-small btn-warning" data-action="follow"
                               data-param='{"fid": <?php echo $followState['user']['uid']; ?>}'
                               data-loading-text="关注中...">
                                <i class="om-plus"></i>
                                关注
                            </a>
                        <?php elseif ($followState['following'] && $followState['follower']): ?>
                            <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn"
                               data-action="unfollow" data-param='{"fid": <?php echo $followState['user']['uid']; ?>}'
                               data-loading-text="取消中...">
                                <i class="om-geoc"></i>
                                互相关注
                            </a>
                        <?php elseif ($followState['following']): ?>
                            <a href="javascript:;" class="btn btn-small" data-node-type="unfollowBtn"
                               data-action="unfollow" data-param='{"fid": <?php echo $followState['user']['uid']; ?>}'
                               data-loading-text="取消中...">
                                <i class="om-gcheck"></i>
                                已关注
                            </a>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>