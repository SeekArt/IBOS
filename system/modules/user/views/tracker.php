<?php
use application\core\utils\Ibos;

?>
<div class="dib right-sidebar">
    <div class="sidebar-header">
        <i class="o-complete-case"></i><span class="header-title vam">完善情况</span>
    </div>
    <div class="sidebar-body">
        <div class="mb clearfix">
            <div
                class="pull-left"><?php if ($percent == 100): ?>亲，记得及时更新哦！<?php else: ?>赶快来完善一下资料吧！<?php endif; ?></div>
            <div class="pull-right">
                完成度:
                <span class="xcgn xwb"><?php echo $percent; ?>%</span>
            </div>
        </div>
        <div class="progress mb">
            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percent; ?>"
                 aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%;"></div>
        </div>
        <?php if ($percent !== 100): ?>
            <div class="mbs">你可以，继续完善下面内容...</div>
            <div class="step-tip">
                <?php if ($tip == 'mobile'): ?>
                    <span class="dib"> <i class="o-tip-phone"></i></span>
                    <div class="tip-content">
                        <a href="<?php echo Ibos::app()->createUrl('user/home/personal', array('op' => 'remind')); ?>"
                           class="dib xcn xwb">绑定手机</a>
                        <div class="tcm fss tip-progress">
                            <span class="tcm">绑定手机进度</span>
                            <span class="xco">+20%</span>
                        </div>
                    </div>
                <?php elseif ($tip == 'email'): ?>
                    <span class="dib"> <i class="o-tip-email"></i></span>
                    <div class="tip-content">
                        <a href="<?php echo Ibos::app()->createUrl('user/home/personal', array('op' => 'remind')); ?>"
                           class="dib xcn xwb">绑定邮箱</a>
                        <div class="tcm fss tip-progress">
                            <span class="tcm">绑定邮箱进度</span>
                            <span class="xco">+10%</span>
                        </div>
                    </div>
                <?php elseif ($tip == 'others'): ?>
                    <span class="dib"> <i class="o-tip-pc-info"></i></span>
                    <div class="tip-content">
                        <a href="<?php echo Ibos::app()->createUrl('user/home/personal', array('op' => 'profile')); ?>"
                           class="dib xcn xwb">填写个人信息</a>
                        <div class="tcm fss tip-progress">
                            <span class="tcm">填写个人信息进度</span>
                            <span class="xco">+10%</span>
                        </div>
                    </div>
                <?php elseif ($tip == 'birthday'): ?>
                    <a class="dib"> <i class="o-tip-birthday"></i></a>
                    <div class="tip-content">
                        <a href="<?php echo Ibos::app()->createUrl('user/home/personal', array('op' => 'profile')); ?>"
                           class="dib xcn xwb">填写生日</a>
                        <div class="tcm fss tip-progress">
                            <span class="tcm">填写生日进度</span>
                            <span class="xco">+10%</span>
                        </div>
                    </div>
                <?php elseif ($tip == 'avatar'): ?>
                    <span class="dib"> <i class="o-tip-avatar"></i></span>
                    <div class="tip-content">
                        <a href="<?php echo Ibos::app()->createUrl('user/home/personal', array('op' => 'avatar')); ?>"
                           class="dib xcn xwb">上传头像</a>
                        <div class="tcm fss tip-progress">
                            <span class="tcm">上传真实头像进度</span>
                            <span class="xco">+30%</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>