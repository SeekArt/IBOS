<div class="ct">
    <div class="clearfix">
        <h1 class="mt"><?php echo $lang['Search result'] ?><?php if (isset($total)): ?>[<?php echo $total; ?>] <?php echo $lang['Item']; ?><?php endif; ?></h1>
    </div>
    <div>
        <?php if (isset($msg)): ?>
            <div class="alert alert-error">
                <?php echo $msg; ?>
            </div>
        <?php else: ?>
            <?php if (isset($html)): ?>
                <?php echo implode('<br/>', $html); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script>
    (function () {
        <?php if ( isset($total) ): ?>
        // 高亮关键字
        var keys = new Array(<?php echo implode(',', $kws); ?>);
        var reblog = eval("/(" + keys.join('|') + ")/gi");
        $('[data-class="highlight"]').each(function (i, n) {
            var innerHtml = $(n).html();
            $(n).html(innerHtml.replace(reblog, "<font color=red>$1</font>"));
        });
        <?php endif; ?>
    })();
</script>
