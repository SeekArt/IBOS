<?php

use application\core\utils\Ibos;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/officialdoc.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->

<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <div class="aside">
        <div class="sbbf">
            <ul class="nav nav-strip nav-stacked">
                <li class="active">
                    <a href="<?php echo $this->createUrl('officialdoc/index'); ?>">
                        <i class="o-art-doc"></i>
                        <?php echo Ibos::lang('Officialdoc'); ?>
                    </a>
                    <ul id="tree" class="ztree">
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- Sidebar -->

    <!-- Mainer right -->
    <div class="mcr">
        <form action="" class="form-horizontal">
            <div class="ct ctview ctview-art">
                <!-- 文章 -->
                <div class="art">
                    <div class="art-container">
                        <!-- 套红 -->
                        <div class="mb art-content" id="art_content">
                            <div class="officialdoc-content">
                                <?php echo $content; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(function () {
        //替换百度编辑器换页标识符操作
        var content = $("#art_content").html();
        var replaceCont = content.replace(/_baidu_page_break_tag_/g, "</div><div class='officialdoc-content'>");
        $("#art_content").html(replaceCont);
        //设置页码数
        var $offContents = $("#art_content .officialdoc-content");
        $offContents.each(function (key, val) {
            $("<span class='page-num'>" + (key + 1) + " / " + $offContents.length + "</span>").appendTo(this);
        });
    });
</script>

