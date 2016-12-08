<!-- @?? 这个页面没有使用 -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/message.css?<?php echo VERHASH; ?>">
<div class="mc clearfix">
    <!-- Sidebar goes here-->
    <?php echo $this->getSidebar(array('lang' => $lang)); ?>
    <!-- Mainer right -->
    <div class="mcr">
        <div class="page-list">
            <div class="page-list-mainer">
                <ul class="main-list main-list-hover praise-list">
                    <li class="main-list-item">
                        <div class="avatar-box pull-left">
                            <a href="#" class="avatar-circle">
                                <img class="mbm" src="../../../../../static/image/har/aili.png" alt="">
                            </a>
                        </div>
                        <div class="main-list-item-body">
                            <div>
                                <p class="mbm">
                                    <a href="#"><strong>薄荷：</strong></a>
                                    <span class="xcm">赞了我</span>
                                </p>
                                <p class="tcm mb">
                                    对我的评论
                                    <!-- 单行，过长时截断字符 -->
                                    <a href="#">“cookie家的小孩”</a>
                                </p>
                                <div>
                                    <span class="tcm fss">今天6:30</span>
                                    <div class="pull-right">
                                        <a href="#" class="cbtn o-chat" title="发消息"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="page-list-footer">
                页码
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>
<script src='<?php echo $assetUrl; ?>/js/message.js?<?php echo VERHASH; ?>'></script>
