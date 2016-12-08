<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Performance optimization']; ?></h1>
        <ul class="mn">
            <li>
                <span><?php echo $lang['Optimize Cache']; ?></span>
            </li>
            <!-- <li>
				<a href="<?php echo $this->createUrl('optimize/search'); ?>"><?php echo $lang['Full-text search setup']; ?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl('optimize/sphinx'); ?>"><?php echo $lang['Sphnix control']; ?></a>
			</li> -->
        </ul>
    </div>
    <div>
        <!-- 当前内存工作状态 start -->
        <div class="ctb">
            <h2 class="st"><?php echo $lang['Current cache status']; ?></h2>
            <div class="alert trick-tip clearfix">
                <div class="trick-tip-title">
                    <strong><?php echo $lang['Skills prompt']; ?></strong>
                </div>
                <div class="trick-tip-content">
                    <?php echo $lang['Optimize cache tips']; ?>
                </div>
            </div>
            <table class="table table-bordered table-striped table-operate">
                <thead>
                <tr>
                    <th><?php echo $lang['Cache interface']; ?></th>
                    <th width="100"><?php echo $lang['Php environment']; ?></th>
                    <th width="100"><?php echo $lang['Clear cache']; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $cacheName => $option) : ?>
                    <tr>
                        <td><?php echo $cacheName; ?></td>
                        <td>
                            <?php
                            if ($option['extension']) {
                                echo $lang['Support'];
                            } else {
                                echo $lang['Nonsupport'];
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($option['op']) : ?>
                                <a href="<?php echo $this->createUrl('optimize/cache', array('op' => 'clear')); ?>"><?php echo $lang['Clear']; ?></a>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>