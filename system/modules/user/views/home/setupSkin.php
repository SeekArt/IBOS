<!-- @?: 这个页面还有用吗？ -->
<div class="mc mcf clearfix">
    <?php echo $this->getHeader($lang); ?>
    <div>
        <div>
            <ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
                <li>
                    <a href="<?php echo $this->createUrl('home/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Home page']; ?></a>
                </li>
                <li>
                    <a href="<?php echo $this->createUrl('home/personal', array('uid' => $this->getUid())); ?>"><?php echo $lang['Profile']; ?></a>
                </li>
                <?php if ($this->getIsWeiboEnabled()): ?>
                    <li><a
                        href="<?php echo Ibos::app()->urlManager->createUrl('weibo/personal/index', array('uid' => $this->getUid())); ?>"><?php echo $lang['Weibo']; ?></a>
                    </li><?php endif; ?>
                <?php if ($this->getIsMe()): ?>
                    <li>
                        <a href="<?php echo $this->createUrl('home/credit', array('uid' => $this->getUid())); ?>"><?php echo $lang['Credit']; ?></a>
                    </li>
                    <li class="active"><a
                            href="<?php echo $this->createUrl('home/setup', array('uid' => $this->getUid())); ?>"><?php echo $lang['Setup']; ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="pc-container clearfix">
            <div class="pc-header clearfix">
                <h4 class="pull-left"><?php echo $lang['Setup']; ?></h4>
                <ul class="tab">
                    <li>
                        <a href="<?php echo $this->createUrl('home/setup', array('op' => 'password', 'uid' => $this->getUid())); ?>"><?php echo $lang['Change password']; ?></a>
                    </li>
                    <li><span><?php echo $lang['Skin setup']; ?></span></li>
                </ul>
            </div>
            <div class="fill-nn">
                <ul class="pc-skin-list" id="skin_list">
                    <li class="active" data-id="1">
                        <img src="../static/image/temp/bn1.jpg" width="294" height="165">
                        <!-- <img src="../static/image/temp/bn1.jpg" alt=""> -->
                        <div class="pc-skin-name-wrap">
                            <span class="pc-skin-name">星云</span>
                        </div>
                        <i class="pc-skin-select"></i>
                        <span class="pc-skin-new"></span>
                    </li>
                    <li data-id="2">
                        <img src="../static/image/temp/bn2.jpg" width="294" height="165">
                        <div class="pc-skin-name-wrap">
                            <span class="pc-skin-name">初音未来</span>
                        </div>
                        <i class="pc-skin-select"></i>
                    </li>
                    <li data-id="3">
                        <img src="../static/image/temp/bn3.jpg" width="294" height="165">
                        <div class="pc-skin-name-wrap">
                            <span class="pc-skin-name">五年目</span>
                        </div>
                        <i class="pc-skin-select"></i>
                    </li>
                    <li data-id="4">
                        <img src="../static/image/temp/bn4.jpg" width="294" height="165">
                        <div class="pc-skin-name-wrap">
                            <span class="pc-skin-name">天空</span>
                        </div>
                        <i class="pc-skin-select"></i>
                    </li>
                    <li data-id="5">
                        <img src="../static/image/temp/bn5.jpg" width="294" height="165">
                        <div class="pc-skin-name-wrap">
                            <span class="pc-skin-name">日本桥</span>
                        </div>
                        <i class="pc-skin-select"></i>
                    </li>
                    <li data-id="6">
                        <img src="../static/image/temp/bn7.jpg" width="294" height="165">
                        <div class="pc-skin-name-wrap">
                            <span class="pc-skin-name">秒速五厘米</span>
                        </div>
                        <i class="pc-skin-select"></i>
                    </li>
                </ul>
                <div>
                    <input type="hidden" name="" id="skin_id">
                    <input type="submit" value="保存" class="btn btn-large btn-primary btn-great">
                </div>
            </div>
        </div>
    </div>
</div>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
<script>
    (function () {
        // 选择皮肤
        var $skinList = $("#skin_list"),
            $skinIdInput = $("#skin_id");

        // @Todo 是否有即时效果展示
        $skinList.on("click", "li", function () {
            $(this).addClass("active").siblings().removeClass("active");
            $skinIdInput.val($.attr(this, "data-id"));
        });

    })();
</script>
