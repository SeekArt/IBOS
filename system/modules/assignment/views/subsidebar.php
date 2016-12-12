<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\core\utils\Org;

?>
<!-- 任务指派 -->
<div class="aside" id="aside">
    <div class="sbb sbbl sbbf">
        <ul class="nav nav-strip nav-stacked">
            <li <?php if ($this->getId() == 'unfinished' && $this->getAction()->getId() == 'index'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('unfinished/index'); ?>">
                    <?php if ($unfinishCount > 0): ?>
                        <span class="badge pull-right"><?php echo $unfinishCount; ?></span>
                    <?php endif; ?>
                    <i class="o-am-unfinished"></i>
                    <?php echo Ibos::lang('Unfinished assignment') ?>
                </a>
            </li>
            <li <?php if ($this->getId() == 'finished'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('finished/index'); ?>">
                    <i class="o-am-finished"></i>
                    <?php echo Ibos::lang('Finished assignment') ?>
                </a>
            </li>
            <li <?php if ($this->getId() == 'unfinished' && $this->getAction()->getId() == 'sublist'): ?>class="active"<?php endif; ?>>
                <a href="<?php echo $this->createUrl('unfinished/subList'); ?>">
                    <i class="o-am-under"></i>
                    <?php echo Ibos::lang('Under assignment') ?>
                </a>
                <div>
                    <ul class="mng-list" id="mng_list">
                        <?php if (!empty($deptArr)): ?>
                            <?php foreach ($deptArr as $dept): ?>
                                <li>
                                    <div class="mng-item mng-department active" data-action="toggleUnderlingsList">
                                        <span class="o-caret dept"><i class="caret"></i></span>
                                        <a href="javascript:;">
                                            <i class="o-org"></i>
                                            <?php echo $dept['deptname']; ?>
                                        </a>
                                    </div>
                                    <ul class="mng-scd-list cal-underling-list">
                                        <?php foreach ($dept['user'] as $user): ?>
                                            <li>
                                                <div class="mng-item sub">
                                                    <span class="o-caret g-sub" data-action="toggleSubUnderlingsList"
                                                          data-param='{"uid":"<?php echo $user['uid']; ?>"}'><?php if ($user['hasSub']): ?>
                                                            <i class="caret"></i><?php endif; ?></span>
                                                    <a href="<?php echo $this->createUrl('unfinished/subList', array('uid' => $user['uid'])); ?>"
                                                       <?php if (Env::getRequest('uid') == $user['uid']): ?>style="color:#3497DB;"<?php endif; ?>>
                                                        <img
                                                            src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
                                                            alt="">
                                                        <?php echo $user['realname']; ?>
                                                    </a>
                                                </div>
                                                <!--下属资料,ajax调用生成-->
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>

<script>
    // 侧栏伸缩
    var $mngList = $("#mng_list");
    $mngList.on("click", ".view-all", function () {
        var $el = $(this);
        $.get('<?php echo $this->createUrl('unfinished/subList', array('op' => 'getsubordinates', 'item' => '99999')) ?>', {uid: $el.attr('data-uid')}, function (data) {
            $el.parent().replaceWith(data);
        });
    });
    Ibos.evt.add({
        // 展开/收起下属列表
        "toggleUnderlingsList": function (param, elem) {
            $(elem).toggleClass("active").next("ul").toggle();
        },
        // 展开/收起下属的下属列表
        "toggleSubUnderlingsList": function (param, elem) {
            var $elem = $(elem),
                $item = $elem.closest(".mng-item");

            if (!$elem.data("init")) {
                $.get(Ibos.app.url('assignment/unfinished/subList', {op: 'getsubordinates'}), {uid: param.uid}, function (res) {
                    $elem.data("init", "1").parent().after(res);
                }, "html")
            }
            $item.toggleClass("active").next("ul").toggle();
        }
    })

</script>