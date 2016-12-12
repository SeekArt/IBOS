<div class="wbc-right pull-right">
    <div class="mpanel mb">
        <div class="wbc-box rdt bdbs">
            <i class="o-wbr-relation"></i>
            <strong class="wbc-tit">人事关系</strong>
        </div>
        <div class="bglb rdb">
            <div class="wb-contacts-col" data-node-type="relationBox">
                <div class="wb-col-tit">
                    <a href="javascript:;" data-action="nextRelation" data-offset="4"
                       data-param='{"type": "colleague"}'>
                        <i class="glyphicon-chevron-right"></i>
                    </a>
                    <span>部门同事（<?php echo $colleagues['count']; ?>）</span>
                </div>
                <?php if ($colleagues['count'] > 0): ?>
                    <div class="wb-col-cont" data-node-type="relationContent">
                        <?php echo $this->renderPartial('relation', array('list' => $colleagues['list'])); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!$isMe): ?>
                <?php if ($bothfollow['count'] > 0): ?>
                    <div class="wb-contacts-col" data-node-type="relationBox">
                        <div class="wb-col-tit">
                            <a href="javascript:;" data-action="nextRelation" data-offset="4"
                               data-param='{"type": "bothfollow"}'>
                                <i class="glyphicon-chevron-right"></i>
                            </a>
                            <span>共同关注（<?php echo $bothfollow['count']; ?>）</span>
                        </div>
                        <div class="wb-col-cont" data-node-type="relationContent">
                            <?php echo $this->renderPartial('relation', array('list' => $bothfollow['list'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($secondfollow['count'] > 0): ?>
                    <div class="wb-contacts-col" data-node-type="relationBox">
                        <div class="wb-col-tit">
                            <a href="javascript:;" data-action="nextRelation" data-offset="4"
                               data-param='{"type": "secondfollow"}'>
                                <i class="glyphicon-chevron-right"></i>
                            </a>
                            <span>我关注的人也关注TA（<?php echo $secondfollow['count']; ?>）</span>
                        </div>
                        <div class="wb-col-cont" data-node-type="relationContent">
                            <?php echo $this->renderPartial('relation', array('list' => $secondfollow['list'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <!--<div class="mpanel">
        <div>
            <div class="wbc-box rdt bdbs">
                <i class="o-wbr-album"></i>
                <strong class="wbc-tit">相册</strong>
            </div>
            <div class="wb-albums-view rdb">
                <ul class="wb-albums-view-list clearfix">
                    <li class="wb-avbig">
                        <a href="#">
                            <img src="/image/test/dora1.png" alt="" />
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <img src="/image/test/dora2.png" alt="" />
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <img src="/image/test/dora3.png" alt="" />
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <img src="/image/test/dora4.png" alt="" />
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <img src="/image/test/dora5.png" alt="" />
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>-->
</div>