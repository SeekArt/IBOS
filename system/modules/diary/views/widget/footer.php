<?php if (!empty($delay) || !empty($nums)): ?>
    <div class="statistics-box">
        <span class="fsl mb log-title">上交情况</span>
        <div class="fill-nn">
            <div>
                <?php if (!empty($nums)): ?>
                    <div class="mb">
                        <span class="xwb xco fsm"><?php echo count($nums); ?></span>
                        <span class="xwb fsm">人已写日志</span>
                    </div>
                    <div class="ml mb">
                        <ul class="list-inline log-info-list">
                            <?php foreach ($nums as $count): ?>
                                <li>
                                    <div class="clearfix">
                                        <a href="<?php echo $count['user']['space_url']; ?>"
                                           class="avatar-box pull-left">
										<span class="avatar-circle">
											<img class="mbm" src="<?php echo $count['user']['avatar_middle']; ?>"
                                                 alt="<?php echo $count['user']['realname']; ?>">
										</span>
                                        </a>
                                        <div class="dib pull-right pc-info">
                                            <p class="xwb"><?php echo $count['user']['realname']; ?></p>
                                            <p class="tcm fss">累计<span
                                                    class="xco fill-mm fst"><?php echo $count['count']; ?></span>次</p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (!empty($delay)): ?>
                    <div class="mb">
                        <span class="xwb xco fsm"><?php echo count($delay); ?></span>
                        <span class="xwb fsm">人迟写日志</span>
                    </div>
                    <div class="ml">
                        <ul class="list-inline log-info-list">
                            <?php foreach ($delay as $count): ?>
                                <li>
                                    <div class="clearfix">
                                        <a href="" class="avatar-box pull-left">
										<span class="avatar-circle">
											<img class="mbm" src="<?php echo $count['user']['avatar_middle']; ?>"
                                                 alt="<?php echo $count['user']['realname']; ?>">
										</span>
                                        </a>
                                        <div class="dib pull-right pc-info">
                                            <p class="xwb"><?php echo $count['user']['realname']; ?></p>
                                            <p class="tcm fss">累计<span
                                                    class="xco fill-mm fst"><?php echo $count['count']; ?></span>次</p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>