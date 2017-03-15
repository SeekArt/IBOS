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
                <li data-action="typeSelect" data-module="index" data-type="all">
                    <a href="javascript:;">
                        <i class="o-art-all"></i>
                        <?php echo Ibos::lang('All'); ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-module="index" data-type="unread">
                    <a href="javascript:;">
                        <i class="o-art-unread"></i>
                        <?php echo Ibos::lang('No read'); ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-module="index" data-type="read">
                    <a href="javascript:;">
                        <i class="o-art-read"></i>
                        <?php echo Ibos::lang('Read'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="page-list">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <button class="btn btn-primary pull-left" style="display:none;"
                            data-action="allRead"><?php echo Ibos::lang('All Read'); ?></button>
                    <?php if (Ibos::app()->user->isadministrator) : ?>
                        <div class="btn-group" id="art_more" style="display:none;">
                            <button class="btn dropdown-toggle" data-toggle="dropdown">
                                <?php echo Ibos::lang('More Operating'); ?>
                                <i class="caret"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="javascript:;" data-action="moveArticles">
                                        <i class="o-menu-move"></i>
                                        <?php echo Ibos::lang('Move'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-action="topArticles">
                                        <i class="o-menu-top"></i>
                                        <?php echo Ibos::lang('Top'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-action="highlightArticles">
                                        <i class="o-menu-light"></i>
                                        <?php echo Ibos::lang('Highlight'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-action="removeArticles">
                                        <i class="o-menu-trash"></i>
                                        <?php echo Ibos::lang('Delete'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <form action="javascript:;" method="post">
                    <div class="search pull-right span4">
                        <input type="text" placeholder="输入标题查询" name="keyword" id="art_search" nofocus value="">
                        <a href="javascript:;">search</a>
                    </div>
                </form>
            </div>
            <div class="page-list-mainer art-list">
                <div class="table-panel" data-id="index" style="display: none;">
                    <table class="table table-hover article-table" id="article_t_index">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="art_index[]">
                                </label>
                            </th>
                            <th width="32">
                                <i class="i-lt o-art-list"></i>
                            </th>
                            <th><?php echo Ibos::lang('Title'); ?></th>
                            <th width="100"><?php echo Ibos::lang('Publisher'); ?></th>
                            <th width="140"><?php echo Ibos::lang('Last modify time'); ?></th>
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
        'table': 'index', //默认显示列表
        'type': Ibos.local.get('art_type') || 'all'
    });
</script>
<script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/art_list.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
    (function () {
        ArtTable['index'] = Article.createTable('#article_t_index', {
            url: Ibos.app.url('article/data/index'),
            columns: [
                // 复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="art_index[]" value="' + row.articleid + '"/></label>';
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
                        return '<a href="' + Ibos.app.url('article/default/show', {articleid: row.articleid}) + '" class="art-list-title ellipsis" target="' + (row.type == 2 ? '_blank' : '_self') + '">' + row.subject + '</a>' +
                            (row.istop == 1 ? '<i class="o-art-top"></i>' : '') +
                            (row.votestatus == 1 ? '<i class="o-art-vote"></i>' : '');
                    }
                },
                // 发布者
                {
                    "data": "author",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span>' + row.author + '</span>';
                    }
                },
                // 最近修改
                {
                    "data": "uptime",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span class="art-list-hide ' + ((!row.allowEdit && !row.allowDel) ? 'art-list-show' : '') + '">' + row.uptime + '</span>' +
                            '<div class="art-list-funbar">' +
                            (row.allowEdit ? '<a href="javascript:;" title="编辑" class="cbtn o-edit" data-action="editArticle" data-id=' + row.articleid + '></a>' : '') +
                            (row.allowDel ? '<a href="javascript:;" title="删除" class="cbtn o-trash" data-action="removeArticle" data-id=' + row.articleid + '></a>' : '') +
                            '</div>';
                    }
                }
            ]
        });
    })();
</script>