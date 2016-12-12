<?php

use application\core\utils\Env;
use application\core\utils\Org;

?>
<div>
    <ul class="mng-list" id="mng_list">
        <?php if (!empty($deptArr)): ?>
            <?php foreach ($deptArr as $dept): ?>
                <li>
                    <div class="mng-item mng-department active">
                        <span class="o-caret dept"><i class="caret"></i></span>
                        <a href="<?php echo $this->getController()->createUrl('stats/review', array('typeid' => $typeid, 'uid' => $dept['subUids'])); ?>">
                            <i class="o-org"></i>
                            <?php echo $dept['deptname']; ?>
                        </a>
                    </div>
                    <ul class="mng-scd-list">
                        <?php foreach ($dept['user'] as $user): ?>
                            <li>
                                <div class="mng-item">
                                    <span
                                        class="o-caret g-sub" <?php if ($user['hasSub']): ?> data-action="toggleSubUnderlingsList" <?php endif; ?>
                                        data-uid="<?php echo $user['uid']; ?>"><?php if ($user['hasSub']): ?><i
                                            class="caret"></i><?php endif; ?></span>
                                    <a href="<?php echo $this->getController()->createUrl('stats/review', array('typeid' => $typeid, 'uid' => $user['uid'])); ?>"
                                       <?php if (Env::getRequest('uid') == $user['uid']): ?>style="color:#3497DB;"<?php endif; ?>>
                                        <img src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
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
<script>
    Ibos.app.setPageParam({
        'currentSubUid': "<?php echo(Env::getRequest('uid') ? Env::getRequest('uid') : 0); ?>"
    });
</script>
<script>
    $(function () {
        // 侧栏伸缩
        var $mngList = $("#mng_list");
        $mngList.on("click", ".g-sub", function () {
            var $el = $(this),
                $item = $el.parents(".mng-item").eq(0),
                $next = $item.next();

            if (!$el.attr('data-init')) {
                $.get(Ibos.app.url("report/review/index", {op: "getsubordinates", act: "stats"}), {
                    uid: $el.attr('data-uid')
                }, function (res) {
                    $el.parent().after(res);
                    $item.addClass('active');
                    $el.attr('data-init', '1');
                });
            }

            if ($next.is("ul")) {
                Report.toggleTree($next, function (isShowed) {
                    $item.toggleClass("active", !isShowed);
                });
            }
        });

        //展开部门
        $mngList.on("click", ".dept", function () {
            var $el = $(this),
                $item = $el.parents(".mng-item").eq(0),
                $next = $item.next();
            Report.toggleTree($next, function (isShowed) {
                $item.toggleClass("active", !isShowed);
            });
        });

        //查看所有下属
        $mngList.on("click", ".view-all", function () {
            var $el = $(this);
            $.get(Ibos.app.url("report/review/index", {op: "getsubordinates", item: "99999"}), {
                uid: $el.attr('data-uid')
            }, function (res) {
                $el.parent().replaceWith(res);
            });
        });

        $('[data-action="toggleSubUnderlingsList"][data-uid="' + Ibos.app.g("currentSubUid") + '"]').click();
    });
</script>