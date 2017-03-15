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
                <li data-action="typeSelect" data-module="draft" data-type="draft">
                    <a href="javascript:;">
                        <i class="o-art-draft"></i>
                        <?php echo Ibos::lang('Article Draft'); ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-module="publish" data-type="publish">
                    <a href="javascript:;">
                        <i class="o-art-all"></i>
                        <?php echo Ibos::lang('Article Published'); ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-module="approval" data-type="approval">
                    <a href="javascript:;">
                        <i class="o-art-read"></i>
                        <?php echo Ibos::lang('Article Approval'); ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-module="reback_to" data-type="reback_to">
                    <a href="javascript:;">
                        <i class="o-art-reback"></i>
                        <?php echo Ibos::lang('Article Reback'); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="page-list">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <button class="btn pull-left" style="display:none;"
                            data-action="removeArticles"><?php echo Ibos::lang('Delete'); ?></button>
                    <button class="btn btn-primary pull-left" style="display:none;"
                            data-action="remindApprovers"><?php echo Ibos::lang('Remind'); ?></button>
                </div>
                <form action="javascript:;" method="post">
                    <div class="search pull-right span4">
                        <input type="text" placeholder="输入标题查询" name="keyword" id="art_search" nofocus value="">
                        <a href="javascript:;">search</a>
                    </div>
                </form>
            </div>
            <div class="page-list-mainer art-list">
                <div class="table-panel" data-id="draft" style="display: none;">
                    <table class="table table-hover article-table" id="article_t_draft">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="art_draft[]">
                                </label>
                            </th>
                            <th width="32">
                                <i class="i-lt o-art-list"></i>
                            </th>
                            <th><?php echo Ibos::lang('Title'); ?></th>
                            <th width="100"><?php echo Ibos::lang('Appertaining category'); ?></th>
                            <th width="140"><?php echo Ibos::lang('Last modify time'); ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-panel" data-id="publish" style="display: none;">
                    <table class="table table-hover article-table" id="article_t_publish">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="art_publish[]">
                                </label>
                            </th>
                            <th width="32">
                                <i class="i-lt o-art-list"></i>
                            </th>
                            <th><?php echo Ibos::lang('Title'); ?></th>
                            <th width="100"><?php echo Ibos::lang('View'); ?></th>
                            <th width="140"><?php echo Ibos::lang('Passed time'); ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-panel" data-id="approval" style="display: none;">
                    <table class="table table-hover article-table" id="article_t_approval">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="art_approval[]">
                                </label>
                            </th>
                            <th width="32">
                                <i class="i-lt o-art-list"></i>
                            </th>
                            <th><?php echo Ibos::lang('Title'); ?></th>
                            <th width="100"><?php echo Ibos::lang('Verify step'); ?></th>
                            <th width="140"><?php echo Ibos::lang('Verify time'); ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-panel" data-id="reback_to" style="display: none;">
                    <table class="table table-hover article-table" id="article_t_reback_to">
                        <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="art_reback_to[]">
                                </label>
                            </th>
                            <th width="32">
                                <i class="i-lt o-art-list"></i>
                            </th>
                            <th><?php echo Ibos::lang('Title'); ?></th>
                            <th width="100"><?php echo Ibos::lang('Verify reback step'); ?></th>
                            <th width="140"><?php echo Ibos::lang('Reback time'); ?></th>
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
        'table': 'draft', //默认显示列表
        'type': Ibos.local.get('art_type') || 'draft'
    });
</script>
<script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/art_list.js?<?php echo VERHASH; ?>'></script>
<script type="text/javascript">
    (function () {
        // 草稿箱
        ArtTable['draft'] = Article.createTable('#article_t_draft', {
            url: Ibos.app.url('article/data/index'),
            columns: [
                // 复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="art_draft[]" value="' + row.articleid + '"/></label>';
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
                // 所属分类
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span>' + row.categoryname + '</span>';
                    }
                },
                // 最近修改
                {
                    "data": "uptime",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span class="art-list-hide">' + row.uptime + '</span>' +
                            '<div class="art-list-funbar">' +
                            (row.allowEdit ? '<a href="javascript:;" title="编辑" class="cbtn o-edit" data-action="editArticle" data-id=' + row.articleid + '></a>' : '') +
                            (row.allowDel ? '<a href="javascript:;" title="删除" class="cbtn o-trash" data-action="removeArticle" data-id=' + row.articleid + '></a>' : '') +
                            '</div>';
                    }
                }
            ]
        });
        // 已发布
        ArtTable['publish'] = Article.createTable('#article_t_publish', {
            url: Ibos.app.url('article/data/index'),
            columns: [
                // 复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="art_publish[]" value="' + row.articleid + '"/></label>';
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
                // 查看次数
                {
                    "data": "clickcount",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span>' + row.clickcount + '</span>';
                    }
                },
                // 通过时间
                {
                    "data": "opentime",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span class="art-list-hide">' + row.opentime + '</span>' +
                            '<div class="art-list-funbar">' +
                            (row.allowEdit ? '<a href="javascript:;" title="编辑" class="cbtn o-edit" data-action="editArticle" data-id=' + row.articleid + '></a>' : '') +
                            (row.allowDel ? '<a href="javascript:;" title="删除" class="cbtn o-trash" data-action="removeArticle" data-id=' + row.articleid + '></a>' : '') +
                            '</div>';
                    }
                }
            ]
        });
        // 审核中
        ArtTable['approval'] = Article.createTable('#article_t_approval', {
            url: Ibos.app.url('article/data/index'),
            columns: [
                // 复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="art_approval[]" value="' + row.articleid + '"/></label>';
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
                // 当前步骤
                {
                    "data": "current",
                    "orderable": false,
                    "render": function (data, type, row) {
                        var tmpl = '<a href="' + Ibos.app.url('article/default/show', {articleid: row.articleid}) + '#verifylog">' +
                                '<span class="label">' + row.current.step + '</span>' +
                                '<span class="fss mlm">' + row.current.currentName + '</span>' +
                                '</div>';
                        return tmpl;
                    }
                },
                // 停留时间
                {
                    "data": "stoptime",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span class="art-list-hide">' + row.stoptime + '</span>' +
                            '<div class="art-list-funbar">' +
                            '<a href="javascript:;" title="催办" class="cbtn o-remind" data-action="remindApprover" data-id=' + row.articleid + '></a>' +
                            '<a href="javascript:;" title="撤销" class="cbtn o-cancel" data-action="pushBack" data-id=' + row.articleid + '></a>' +
                            '</div>';
                    }
                }
            ]
        });
        // 被退回
        ArtTable['reback_to'] = Article.createTable('#article_t_reback_to', {
            url: Ibos.app.url('article/data/index'),
            columns: [
                // 复选框
                {
                    "data": "",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<label class="checkbox"><input type="checkbox" name="art_reback_to[]" value="' + row.articleid + '"/></label>';
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
                // 退回步骤
                {
                    "data": "backstep",
                    "orderable": false,
                    "render": function (data, type, row) {
                        var tmpl = '<div class="art-step-info">' +
                            '<span class="label">' + row.backstep.step + '</span>' +
                            '<span class="fss mlm">' + row.backstep.backname + '</span>' +
                            '<div class="art-step-all">' +
                            '<i class="art-step-num warn">' + row.backstep.step + '</i>' +
                            '<span class="mls xcr">' + row.backstep.backname + '</span>' +
                            '<p class="xcn mts">' + row.backreason + '</p>' +
                            '</div>' +
                            '</div>';
                        return tmpl;
                    }
                },
                // 退回时间
                {
                    "data": "backtime",
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<span class="art-list-hide">' + row.backtime + '</span>' +
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