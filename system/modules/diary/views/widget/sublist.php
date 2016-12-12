<?php

use application\core\utils\Env;
use application\core\utils\Org;
use application\modules\diary\utils\Diary;

?>
<div>
    <ul class="mng-list" id="mng_list">
        <?php if (!empty($deptArr)): ?>
            <?php foreach ($deptArr as $dept): ?>
                <li>
                    <div class="mng-item active">
                        <span class="o-caret dept" data-action="toggleUnderlingsList"><i class="caret"></i></span>
                        <a href="<?php echo $this->getController()->createUrl($deptRoute, array('uid' => $dept['subUids'])); ?>">
                            <i class="o-org"></i>
                            <?php echo isset($dept['deptname']) ? $dept['deptname'] : '暂无'; ?>
                        </a>
                    </div>
                    <ul class="mng-scd-list">
                        <?php foreach ($dept['user'] as $user): ?>
                            <li>
                                <div class="mng-item mng-item-user">
                                    <span
                                        class="o-caret" <?php if ($user['hasSub']): ?> data-action="toggleSubUnderlingsList" <?php endif; ?>
                                        data-uid="<?php echo $user['uid']; ?>"
                                        data-param='{"uid": "<?php echo $user['uid']; ?>"}'><?php if ($user['hasSub']): ?>
                                            <i class="caret"></i><?php endif; ?></span>
                                    <a href="<?php echo $this->getController()->createUrl($userRoute, array('uid' => $user['uid'])); ?>"
                                       <?php if (Env::getRequest('uid') == $user['uid']): ?>style="color:#3497DB;"<?php endif; ?>>
                                        <img src="<?php echo Org::getDataStatic($user['uid'], 'avatar', 'middle') ?>"
                                             alt="<?php echo $user['realname']; ?>">
                                        <?php echo $user['realname']; ?>
                                    </a>
                                    <!-- if 未关注 -->
                                    <?php if (Diary::getIsAttention($user['uid'])): ?>
                                        <a href="javascript:;" data-node-type="udstar" class="o-gudstar pull-right"
                                           data-action="toggleAsteriskUnderling" data-id="<?php echo $user['uid'] ?>"
                                           data-param='{"id": "<?php echo $user['uid'] ?>"}'></a>
                                    <?php else: ?>
                                        <a href="javascript:;" data-node-type="udstar" class="o-udstar pull-right"
                                           data-action="toggleAsteriskUnderling" data-id="<?php echo $user['uid'] ?>"
                                           data-param='{"id": "<?php echo $user['uid'] ?>"}'></a>
                                    <?php endif; ?>
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
    // 侧栏伸缩
    // var $mngList = $("#mng_list");

    //查看所有下属
    // $mngList.on("click", ".view-all", function() {
    // 	var $el = $(this);

    //	$.get(Ibos.app.url("diary/review/index", {'op': 'getsubordinates', 'item': '99999'}), {uid: $el.attr('data-uid')}, function(res) {
    //		$el.parent().replaceWith(res);
    //	});
    // });

    Ibos.evt.add({
        // 展开下属列表
        "toggleUnderlingsList": function (param, elem) {
            var $item = $(elem).closest(".mng-item"),
                $next = $item.next();
            Diary.toggleTree($next, function (isShowed) {
                $item.toggleClass("active", !isShowed);
            })
        },
        "toggleSubUnderlingsList": function (param, elem) {
            var $elem = $(elem),
                $item = $elem.closest(".mng-item"),
                $next = $item.next();

            if (!$elem.attr('data-init')) {
                $.get(Ibos.app.url("diary/review/index", {
                    'op': 'getsubordinates',
                    'act': "<?php echo $fromController; ?>"
                }), {uid: param.uid}, function (data) {
                    $elem.parent().after(data);
                    $item.addClass('active');
                });
            }

            $elem.attr('data-init', '1');

            if ($next.is("ul")) {
                Diary.toggleTree($next, function (isShowed) {
                    $item.toggleClass("active", !isShowed);
                });
            }
        }
    })

    //查看所有下属
    $("#mng_list").on("click", ".view-all", function () {
        var $el = $(this);
        $.get('<?php echo $this->getController()->createUrl('review/index', array('op' => 'getsubordinates', 'item' => '99999', 'act' => $fromController)) ?>', {uid: $el.attr('data-uid')}, function (data) {
            $el.parent().replaceWith(data);
        });
    });

    $('[data-action="toggleSubUnderlingsList"][data-uid="' + Ibos.app.g("currentSubUid") + '"]').click();
</script>