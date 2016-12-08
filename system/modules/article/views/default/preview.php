<?php

use application\core\utils\File;

?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->
<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Sidebar -->

    <!-- Mainer right -->
    <div class="mcr">
        <form action="" class="form-horizontal">
            <!-- 文章 -->
            <div class="ct ctview ctview-art"></div>
        </form>
    </div>
</div>
<script type="text/template" id="art_preview">
    <div class="art">
        <div class="art-container">
            <h1 class="art-title"><%= subject %></h1>
            <div class="art-ct mb editor-content">
                <% if (type == 1) { %>
                <div id="gallery" class="ad-gallery">
                    <div class="ad-image-wrapper"></div>
                    <!-- <div class="ad-controls"></div> -->
                    <div class="ad-nav">
                        <div class="ad-thumbs">
                            <ul class="ad-thumb-list">
                                <% for (var i = 0, len = pics.length; i < len; i += 1) { %>
                                <li>
                                    <a href="<%= pics[i].url %>">
                                        <img
                                            src="<%= pics[i].url %>"
                                            alt="<%= pics[i].name %>"/>
                                        <!-- 此处输出索引和总张数 -->
                                        <span><em><%= i + 1 %> / <%= len %></em></span>
                                    </a>
                                </li>
                                <% } %>
                            </ul>
                        </div>
                    </div>
                </div>
                <% } else { %>
                <%= content %>
                <% } %>
            </div>
        </div>
    </div>
</script>
<script>
    Ibos.app.setPageParam({
        "articleType": 1
    });
</script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/src/emotion.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article_default_show.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
    (function () {
        var $tmpl, data = JSON.parse(window.sessionStorage.getItem('preview.article'));

        if (!data) {
            Ui.tip('预览失效，请重新尝试', 'warning');
            setTimeout(function () {
                window.close();
            }, 1000);
        }

        $tmpl = $.tmpl('art_preview', data);
        $('.ctview-art').append($tmpl);
    })();
</script>
