<?php

use application\core\utils\Env;
use application\core\utils\IBOS;
use application\modules\main\utils\Main;
?>

<!-- load css -->
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
                <?php $type = Env::getRequest('type'); ?>
                <li <?php if (!isset($type)): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('default/index', array('catid' => $this->catid)); ?>">
                        <i class="o-art-all"></i>
                        <?php echo $lang['Published']; ?>
                    </a>
                </li>
                <li <?php if ($type == 'new'): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('default/index', array('type' => 'new', 'catid' => $this->catid)); ?>">
                        <i class="o-art-unread"></i>
                        <?php echo $lang['No read']; ?>
                        <?php if ($newCount != 0): ?><span class="bubble"><?php echo $newCount; ?></span><?php endif; ?>
                    </a>
                </li>
                <li <?php if ($type == 'old'): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('default/index', array('type' => 'old', 'catid' => $this->catid)); ?>">
                        <i class="o-art-read"></i>
                        <?php echo $lang['Read']; ?>
                    </a>
                </li>
                <li <?php if ($type == 'notallow'): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('default/index', array('type' => 'notallow', 'catid' => $this->catid)); ?>">
                        <i class="o-art-uncensored"></i>
                        <?php echo IBOS::lang('No verify'); ?>
                        <?php if ($notallowCount != 0): ?><span class="bubble"><?php echo $notallowCount; ?></span><?php endif; ?>
                    </a>
                </li>
                <li <?php if ($type == 'draft'): ?>class="active"<?php endif; ?>>
                    <a href="<?php echo $this->createUrl('default/index', array('type' => 'draft', 'catid' => $this->catid)); ?>">
                        <i class="o-art-draft"></i>
                        <?php echo IBOS::lang('Draft'); ?>
                        <?php if ($draftCount != 0): ?><span class="bubble"><?php echo $draftCount; ?></span><?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="page-list">
            <div class="page-list-header">
                <div class="btn-toolbar pull-left">
                    <button class="btn btn-primary pull-left" onclick="location.href = '<?php echo $this->createUrl('default/add', array('catid' => $this->catid)); ?>'">新建</button>
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
                <form action="<?php echo $this->createUrl('default/index', array('param' => 'search')); ?>" method="post">
                    <div class="search search-config pull-right span3">
                        <input type="text" placeholder="输入标题查询" name="keyword"  id="mn_search" nofocus <?php if (Env::getRequest('param')): ?>value="<?php echo Main::getCookie('keyword');
                                        ; ?>"<?php endif; ?>>
                        <a href="javascript:;">search</a>
                        <input type="hidden" name="type" value="normal_search">
                    </div>
                </form>
            </div>
            <div class="page-list-mainer art-list">
<?php if (count($datas) > 0): ?>
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
                        <tbody>
    <?php foreach ($datas as $data): ?>
                                <tr data-node-type="articleRow" data-id="<?php echo $data['articleid']; ?>">
                                    <td>
                                        <label class="checkbox">
                                            <input type="checkbox" name="article[]" value="<?php echo $data['articleid']; ?>">
                                        </label>
                                    </td>
                                    <td>
                                        <i <?php if ($data['type'] == 0 && $data['votestatus'] == 0): ?>
                                                class="o-art-normal<?php if ($data['readStatus'] == 1): ?>-gray<?php endif; ?>"
                                            <?php elseif ($data['type'] == 1 && $data['votestatus'] == 0): ?>
                                                class="o-art-pic<?php if ($data['readStatus'] == 1): ?>-gray<?php endif; ?>"
                                            <?php elseif ($data['votestatus'] == 1): ?>
                                                class="o-art-vote<?php if ($data['readStatus'] == 1): ?>-gray<?php endif; ?>"
                                            <?php else: ?>
                                                class="o-art-normal<?php if ($data['readStatus'] == 1): ?>-gray<?php endif; ?>"
        <?php endif; ?>
                                            ></i>
                                    </td>
                                    <td>
                                        <a href="<?php echo $this->createUrl('default/index', array('op' => 'show', 'articleid' => $data['articleid'])); ?>"
                                           class="art-list-title" target="_blank"><?php echo $data['subject']; ?></a>
                                        <!-- 当置顶时显示下面图标 -->
                                        <?php if ($data['istop'] == 1) { ?>
                                            <span class="o-art-top"></span>
        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="art-list-modify">
                                            <em><?php echo $data['author']; ?></em>
                                            <span><?php echo $data['uptime']; ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="art-list-clickcount">
        <?php echo $data['clickcount']; ?>
                                        </div>
                                        <div class="art-list-funbar">
                                            <?php if ($data['allowEdit']): ?>
                                                <a href="javascript:;" data-url="<?php echo $this->createUrl('default/edit', array('articleid' => $data['articleid'])); ?>" title="<?php echo IBOS::lang('Edit'); ?>" target="_self" class="cbtn o-edit" data-action="editTip"></a>
                                            <?php endif; ?>
                                            <?php if ($data['allowDel']): ?>
                                                <a href="javascript:;" title="<?php echo IBOS::lang('Delete'); ?>" class="cbtn o-trash" data-action="removeArticle" data-param='{ "id": "<?php echo $data['articleid']; ?>"}'></a>
        <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
    <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data-tip"></div>
            <?php endif; ?>
            </div>
<?php if ($pages->getPageCount() > 1): ?>
                <div class="page-list-footer">
                    <div class="pull-right">
    <?php $this->widget('application\core\widgets\Page', array('pages' => $pages)); ?>
                    </div>
                </div>
<?php endif; ?>
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
<!-- 移动目录 -->
<div id="dialog_art_move" style="width: 400px; display:none;">
    <div class="form-horizontal form-compact">
        <div class="control-group">
            <label class="control-label"><?php echo IBOS::lang('Directory'); ?></label>
            <div class="controls">
                <select name="articleCategory"  id="articleCategory">
<?php echo $categoryOption; ?>
                </select>
            </div>
        </div>
    </div>
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
<!-- load script -->
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article_default_index.js?<?php echo VERHASH; ?>'></script>
<!-- load script end -->
