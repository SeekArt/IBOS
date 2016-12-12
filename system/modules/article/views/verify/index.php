<?php

use application\core\utils\Env;
use application\core\utils\Ibos;
use application\modules\main\utils\Main;

?>

<!-- load css -->
<link rel="stylesheet"
      href="<?php echo STATICURL; ?>/js/lib/dataTable/css/jquery.dataTables_ibos.min.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->

<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar(); ?>
    <!-- Sidebar end -->
    <div class="mcr">
        <div class="mc-header">
            <!-- Mainer nav -->
            <ul class="mnv nl clearfix">
                <li data-action="typeSelect" data-module="verify" data-type="wait">
                    <a href="javascript:;">
                        <i class="o-art-wait"></i>
                        <?php echo Ibos::lang('Wait to verify'); ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-module="passed" data-type="passed">
                    <a href="javascript:;">
                        <i class="o-art-passed"></i>
                        <?php echo Ibos::lang('Passed by me'); ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-module="passed" data-type="reback_from">
                    <a href="javascript:;">
                        <i class="o-art-reback"></i>
                        <?php echo Ibos::lang('Reback by me'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="page-list">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <button class="btn btn-primary pull-left" style="display:none;"
                            data-action="passArticles"><?php echo Ibos::lang('Pass'); ?></button>
                    <button class="btn btn-primary pull-left" style="display:none;"
                            data-action="getBack"><?php echo Ibos::lang('Getback'); ?></button>
                </div>
                <form action="javascript:;" method="post">
                    <div class="search pull-right span4">
                        <input type="text" placeholder="输入标题查询" name="keyword" id="art_search" nofocus value="">
                        <a href="javascript:;">search</a>
                    </div>
                </form>
            </div>
            <div class="page-list-mainer art-list">
                <div class="table-panel" data-id="verify" style="display: none;">
                    <table class="table table-hover article-table" id="article_t_verify">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="art_verify[]">
                                </label>
                            </th>
                            <th width="32">
                                <i class="i-lt o-art-list"></i>
                            </th>
                            <th><?php echo Ibos::lang('Title'); ?></th>
                            <th width="100"><?php echo Ibos::lang('Verify applier'); ?></th>
                            <th width="140"><?php echo Ibos::lang('Verify time'); ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-panel" data-id="passed" style="display: none;">
                    <table class="table table-hover article-table" id="article_t_passed">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="art_passed[]">
                                </label>
                            </th>
                            <th width="32">
                                <i class="i-lt o-art-list"></i>
                            </th>
                            <th><?php echo Ibos::lang('Title'); ?></th>
                            <th width="100"><?php echo Ibos::lang('Verify applier'); ?></th>
                            <th width="140"><?php echo Ibos::lang('Submit time'); ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    Ibos.app.s({
        'table': 'verify', //默认显示列表
        'type': Ibos.local.get('art_type') || 'wait'
    });
</script>
<script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/art_list.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
    (function () {
        ArtTable['verify'] = Article.createTable('#article_t_verify', {
            columns: [
                // 复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="art_verify[]" value="' + row.articleid + '"/></label>';
                    }
                },
                // 图标
                {
                    "data": "type",
                    "orderable": false,
                    "render": function (data, type, row) {
                        var readStatus = row.readStatus,
                            type = row.type,
                            className;

                        className = type == 0 ? 'o-art-normal' : ( type == 1 ? 'o-art-pic' : 'o-art-href');
                        if (readStatus == 1) className += '-gray';

                        return '<i class="' + className + '"></i>';
                    }
                },
                // 标题
                {
                    "data": "subject",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<a href="' + Ibos.app.url('article/default/show', {articleid: row.articleid}) + '" class="art-list-title ellipsis" target="_self">' + row.subject + '</a>' +
                            (row.istop == 1 ? '<i class="o-art-top"></i>' : '') +
                            (row.votestatus == 1 ? '<i class="o-art-vote"></i>' : '');
                    }
                },
                // 申请人
                {
                    "data": "author",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span>' + row.author + '</span>';
                    }
                },
                // 停留时间
                {
                    "data": "stoptime",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span class="art-list-hide">' + row.stoptime + '</span>' +
                            '<div class="art-list-funbar">' +
                            '<a href="javascript:;" title="通过" class="cbtn o-pass" data-action="passArticle" data-id=' + row.articleid + '></a>' +
                            '<a href="javascript:;" title="退回" class="cbtn o-reback" data-action="reback" data-id=' + row.articleid + '></a>' +
                            '</div>';
                    }
                }
            ]
        });
        // 我已通过/被我退回
        ArtTable['passed'] = Article.createTable('#article_t_passed', {
            columns: [
                // 复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="art_passed[]" value="' + row.articleid + '"/></label>';
                    }
                },
                // 图标
                {
                    "data": "type",
                    "orderable": false,
                    "render": function (data, type, row) {
                        var readStatus = row.readStatus,
                            type = row.type,
                            className;

                        className = type == 0 ? 'o-art-normal' : ( type == 1 ? 'o-art-pic' : 'o-art-href');
                        if (readStatus == 1) className += '-gray';

                        return '<i class="' + className + '"></i>';
                    }
                },
                // 标题
                {
                    "data": "subject",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<a href="' + Ibos.app.url('article/default/show', {articleid: row.articleid}) + '" class="art-list-title ellipsis" target="_self">' + row.subject + '</a>' +
                            (row.istop == 1 ? '<i class="o-art-top"></i>' : '') +
                            (row.votestatus == 1 ? '<i class="o-art-vote"></i>' : '');
                    }
                },
                // 申请人
                {
                    "data": "author",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span>' + row.author + '</span>';
                    }
                },
                // 停留时间
                {
                    "data": "passtime",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span class="art-list-hide art-list-show">' + row.passtime + '</span>';
                    }
                }
            ]
        });
    })();
</script>