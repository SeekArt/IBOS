<?php

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\main\utils\Main;
?>

<!-- load css -->
<link rel="stylesheet" href="<?php echo STATICURL; ?>/js/lib/dataTable/css/jquery.dataTables_ibos.min.css?<?php echo VERHASH; ?>">
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- load css end-->

<!-- Mainer -->
<div class="mc clearfix">
    <!-- Sidebar -->
    <?php echo $this->getSidebar($this->catid); ?>
    <!-- Sidebar end -->

    <!-- Mainer right -->
    <div class="mcr">
        <div class="mc-header">
            <!-- Mainer nav -->
            <ul class="mnv nl clearfix">
                <li class="active" data-action="typeSelect" data-type="done">
                    <a href="javascript:;">
                        <i class="o-art-all"></i>
                        <?php echo $lang['Published']; ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-type="new">
                    <a href="javascript:;">
                        <i class="o-art-unread"></i>
                        <?php echo $lang['No read']; ?>
                        <?php if ($newCount != 0): ?><span class="bubble"><?php echo $newCount; ?></span><?php endif; ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-type="old">
                    <a href="javascript:;">
                        <i class="o-art-read"></i>
                        <?php echo $lang['Read']; ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-type="notallow">
                    <a href="javascript:;">
                        <i class="o-art-uncensored"></i>
                        <?php echo IBOS::lang('No verify'); ?>
                        <?php if ($notallowCount != 0): ?><span class="bubble"><?php echo $notallowCount; ?></span><?php endif; ?>
                    </a>
                </li>
                <li data-action="typeSelect" data-type="draft">
                    <a href="javascript:;">
                        <i class="o-art-draft"></i>
                        <?php echo IBOS::lang('Draft'); ?>
                        <?php if ($draftCount != 0): ?><span class="bubble"><?php echo $draftCount; ?></span><?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="page-list" id="article_base">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <button class="btn btn-primary pull-left" onclick="location.href = '<?php echo $this->createUrl('default/add'); ?>&catid='+ (Ibos.local.get('catid') || 0);">新建</button>
                    <div class="btn-group" id="art_more" style="display:none;">
                        <button class="btn dropdown-toggle" data-toggle="dropdown">
                            <?php echo IBOS::lang('More Operating'); ?>
                            <i class="caret"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:;" data-action="moveArticle">
                                    <i class="o-menu-move"></i>
                                    <?php echo IBOS::lang('Move'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" data-action="topArticle">
                                    <i class="o-menu-top"></i>
                                    <?php echo IBOS::lang('Top'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" data-action="highlightArticle">
                                    <i class="o-menu-light"></i>
                                    <?php echo IBOS::lang('Highlight'); ?>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:;" data-action="removeArticles">
                                    <i class="o-menu-trash"></i>
                                    <?php echo IBOS::lang('Delete'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <form action="javascript:;" method="post">
                    <div class="search search-config pull-right span3">
                        <input type="text" placeholder="输入标题查询" name="keyword"  id="mn_search" nofocus <?php if (Env::getRequest('param')): ?>value="<?php echo Main::getCookie('keyword');
                                        ; ?>"<?php endif; ?>>
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
            </div>
            <div class="page-list-mainer art-list">
                    <table class="table table-hover article-table" id="article_table">
                        <thead>
                            <tr>
                                <th width="20">
                                    <label class="checkbox">
                                        <input type="checkbox" data-name="article[]">
                                    </label>
                                </th>
                                <th width="32">
                                    <i class="i-lt o-art-list"></i>
                                </th>
                                <th><?php echo IBOS::lang('Title'); ?></th>
                                <th width="120"><?php echo IBOS::lang('Last update'); ?></th>
                                <th width="80"><?php echo IBOS::lang('View'); ?></th>
                            </tr>
                        </thead>
                    </table>
            </div>
        </div>
        <div class="page-list" id="article_approval" style="display:none;">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">     
                    <button class="btn btn-primary pull-left" data-action="verifyArticle">审核通过</button>
                    <button class="btn pull-left" data-action="backArticle">退回</button>
                </div>
                <form action="javascript:;" method="post">
                    <div class="search search-config pull-right span3">
                        <input type="text" placeholder="输入标题查询" name="keyword"  id="an_search" nofocus <?php if(Env::getRequest( 'param')): ?>value="<?php echo Main::getCookie( 'keyword' ); ?>"<?php endif; ?>>
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
            </div>
            <div class="page-list-mainer art-list">
                <table class="table table-hover article-table" id="approval_table">
                    <thead>
                        <tr>
                            <th width="20">
                                <label class="checkbox">
                                    <input type="checkbox" data-name="approval[]">
                                </label>
                            </th>
                            <th><?php echo IBOS::lang( 'Title'); ?></th>
                            <th width="110">审核流程</th>
                            <th width="110">发布者</th>
                            <th width="70">操作</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!-- Mainer content -->
    </div>
</div>

<!-- 高级搜索 -->
<div id="mn_search_advance" style="width: 400px; display:none;">
    <form id="mn_search_advance_form" action="<?php echo $this->createUrl('default/index', array('param' => 'search')); ?>" method="post">
        <div class="form-horizontal form-compact">
            <div class="control-group">
                <label for="" class="control-label"><?php echo IBOS::lang('Keyword'); ?></label>
                <div class="controls">
                    <input type="text" id="keyword" name="search[keyword]">
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo IBOS::lang('Start time'); ?></label>
                <div class="controls">
                    <div class="datepicker" id="date_start">
                        <a href="javascript:;" class="datepicker-btn"></a>
                        <input type="text" class="datepicker-input" name="search[starttime]">
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label for="" class="control-label"><?php echo IBOS::lang('End time'); ?></label>
                <div class="controls">
                    <div class="datepicker" id="date_end">
                        <a href="javascript:;" class="datepicker-btn"></a>
                        <input type="text" class="datepicker-input" name="search[endtime]">
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="type" value="advanced_search">
    </form>
</div>

<!-- 设置置顶 -->
<div id="dialog_art_top" class="form-horizontal form-compact" style="width: 400px; display:none;">
    <form action="javascript:;">
        <div class="control-group">
            <label class="control-label" id="test"><?php echo IBOS::lang('Expired time'); ?></label>
            <div class="controls">
                <div class="datepicker" id="date_time_top">
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="topEndTime" value="<?php echo date('Y-m-d'); ?>" />
                </div>
            </div>
        </div>
    </form>
</div>
<!-- 高亮对话框  -->
<div id="dialog_art_highlight" class="form-horizontal form-compact" style="width: 400px; display:none;">
    <form action="javascript:;">
        <div class="control-group">
            <label class="control-label" id="test"><?php echo IBOS::lang('Expired time'); ?></label>
            <div class="controls">
                <div class="datepicker" id="date_time_highlight">
                    <a href="javascript:;" class="datepicker-btn"></a>
                    <input type="text" class="datepicker-input" name="highlightEndTime" value="<?php echo date('Y-m-d'); ?>" />
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls" id="simple_editor"></div>
            <input type="hidden" id="highlight_color" name="highlight_color" value="00b050">
            <input type="hidden" id="highlight_bold" name="highlight_bold" value="0">
            <input type="hidden" id="highlight_italic" name="highlight_italic" value="0">
            <input type="hidden" id="highlight_underline" name="highlight_underline" value="0">
        </div>
    </form>
</div>
<!-- 退回 -->
<div id="rollback_reason" style="display:none;">
    <form action="javascript:;" method="post" id="rollback_form">
        <textarea rows="8" cols="60" id="rollback_textarea" name="reason" placeholder="退回理由...."></textarea>
    </form>
</div>
<!-- load script -->
<script src="<?php echo STATICURL; ?>/js/lib/dataTable/js/jquery.dataTables.js?<?php echo VERHASH; ?>"></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article_default_index.js?<?php echo VERHASH; ?>'></script>
<!-- load script end -->
