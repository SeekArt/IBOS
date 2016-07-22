<?php

use application\core\utils\IBOS;
?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/file_cabinets.css?<?php echo VERHASH; ?>">
<!-- Mainer -->
<div class="fmc">
    <div class="page-list">
        <div class="page-list-header clearfix">
            <div class="fc-toolbar">
                <label class="btn checkbox checkbox-inline pull-left">
                    <input type="checkbox" id="fc_checkall"/>
                </label>
                <div class="pull-left" id="fc_folder_toolbar"></div>
                <div class="pull-right">
                    <div class="search">
                        <input type="text" name="keyword" placeholder="搜索文件" id="fc_search" nofocus/>
                        <a href="javascript:;"></a>
                    </div>
                </div>
                <div id="fc_file_toolbar" class="fc-file-toolbar clearfix" style="display:none;"></div>
            </div>
        </div>
        <div class="page-list-mainer">
            <!-- 状态栏 -->
            <div class="fc-topbar clearfix">
                <div class="fc-nav" id="fc_breadcrumb"></div>
                <div class="fc-filterbar">
                    <select name="order" id="fc_order" class="hide">
                        <option value="0"><?php echo $lang['Order_0']; ?></option>
                        <option value="1"><?php echo $lang['Order_1']; ?></option>
                        <option value="2"><?php echo $lang['Order_2']; ?></option>
                        <option value="3"><?php echo $lang['Order_3']; ?></option>
                        <option value="4"><?php echo $lang['Order_4']; ?></option>
                        <option value="5"><?php echo $lang['Order_5']; ?></option>
                    </select>
                    <select name="type" id="fc_filter" class="hide">
                        <option value="all"><?php echo $lang['Type all']; ?></option>
                        <option value="mark"><?php echo $lang['Type mark']; ?></option>
                        <option value="word"><?php echo $lang['Type word']; ?></option>
                        <option value="excel"><?php echo $lang['Type excel']; ?></option>
                        <option value="ppt"><?php echo $lang['Type ppt']; ?></option>
                        <option value="text"><?php echo $lang['Type text']; ?></option>
                        <option value="image"><?php echo $lang['Type image']; ?></option>
                        <option value="package"><?php echo $lang['Type package']; ?></option>
                        <option value="audio"><?php echo $lang['Type audio']; ?></option>
                        <option value="video"><?php echo $lang['Type video']; ?></option>
                    </select>
                </div>
            </div>
            <!-- 文件列表 -->
            <div class="fc-list-cell">
                <ul class="list-thumb clearfix" id="fc_list"></ul>
            </div>
        </div>
        <div class="page-list-footer">
            <div class="pull-right ajax-pagination">
                <div id="fc_pagination"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tempalte: 文件、文件夹模板 -->
<script type="text/template" id="tpl_file_item">
    <div class="fc-icon">
    <!-- 文件 -->
    <% if(type == 0) { %>
    <!-- 图片类型-->
    <% if(thumb) { %>
    <div class="fc-img"><img src="<%= thumb %>" title="<%= name %>" /></div>
    <!-- 其他文件类型 -->
    <% } else if(typeof iconbig !== "undefined") { %>
    <img src="<%= iconbig %>" title="<%= name %>" />
    <% } %>
    <!-- 文件夹 -->
    <% } else { %>
    <i class="file-icon o-folder-normal" title="<%= name %>"></i>
    <% } %>
    </div>

    <div class="xac">
    <% if(openable) { %>
    <a href="javascript:;" class="file-name" title="<%= name %>"><%= name %></a>
    <% } else { %>
    <span class="file-name" title="<%= name %>"><%= name %></span>
    <% } %>
    </div>

    <div class="file-desc">
    <!-- 文件，显示大小 -->
    <% if(type == 0) { %>
    <%= formattedsize %>
    <!-- 文件夹，显示创建时间 -->
    <% } else { %>
    <%= formattedaddtime %>
    <% } %>
    </div>
    <div class="file-opbar">
    <a title="分享" class="o-folder-share"></a>
    <i class="fc-part">|</i>
    <a title="下载" class="o-folder-down"></a>
    <i class="fc-part">|</i>
    <a class="o-folder-dropdown"></a>
    </div>
    <i class="oc-checkbox"></i>
    <!-- 分享图标 -->
    <% if(isShared != 0) { %>
    <i class="o-fc-hand"></i>
    <% } %>
    <!-- 星标，只支持文件 -->
    <% if(type == 0) { %>
    <i class="<%= mark == 0 ? 'o-fc-emptystar': 'o-fc-goldstar' %>"></i>
    <% } %>
</script>

<!-- Tempalte: 面包屑模板 -->
<script type="text/template" id="tpl_file_breadcrumb">
    <% for(var i = 0, len = breadcrumbs.length; i < len; i++){ %>
    <!-- 当前活动项 -->
    <% if(i == len - 1) { %>
    <div class="ellipsis plm prm pull-left" style="max-width:80px;" title="<%= breadcrumbs[i].name %>">
        <a href="<%= breadcrumbs[i].path %>" class="current"><%= breadcrumbs[i].name %></a>
    </div>
    <% } else { %>
    <div class="ellipsis plm prm pull-left" style="max-width:80px;" title="<%= breadcrumbs[i].name %>">
        <a href="<%= breadcrumbs[i].path %>"><%= breadcrumbs[i].name %></a>
    </div>
    <div class="pull-left">
        <i class="o-fc-level mls"></i>
    </div>
    <% } %>
    <% } %>
</script>

<!-- Tempalte: 文件操作栏模板 -->
<script type="text/template" id="tpl_file_toolbar">
    <div class="pull-left">
    <% if(multiple) { %>
    <button type="button" class="btn btn-warning" file-act="download">打包下载</button>
    <button type="button" class="btn mlm" file-act="share">共享</button>
    <% } else { %>
    <button type="button" class="btn btn-warning" file-act="download">下载</button>
    <button type="button" class="btn mlm" file-act="share">共享</button>
    <button type="button" class="btn mlm" file-act="rename">重命名</button>
    <% } %>
    <button type="button" class="btn mlm" file-act="remove">删除</button>
    </div>
    <div class="pull-right select-info">
    <% if(multiple) { %>
    已选中 <span class="xco xwb"><%= length %></span> 个文件，大小 <span class="xco xwb"><%= totalSize %></span>
    <% } else { %>
    已选中 <span class="xco xwb"><%= name %></span>，大小 <span class="xco xwb"><%= totalSize %></span>，<%= isFolder ? '创建于' : '上传于' %> <span><%= formattedaddtime %></span>
    <% } %>
    </div>
</script>

<!-- Tempalte: 文件夹操作栏模板 -->
<script type="text/template" id="tpl_folder_toolbar">
    <% if(fileCreatable) { %>
    <button type="button" class="btn btn-primary mlm" id="upload_file_btn">上传附件</button>
    <% } %>
    <div class="btn-group mlm">
    <% if(folderCreatable) { %>
    <button type="button" class="btn btn-narrow" id="add_file_btn">
    <i class="o-add-folder"></i>
    <span class="dib">新建</span>
    </button>
    <% } %>
    <!--
    <% if(fileCreatable) { %>
    <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
    <li><a href="javascript:;">Word</a></li>
    <li><a href="javascript:;">Excel</a></li>
    <li><a href="javascript:;">PPT</a></li>
    </ul>
    <% } %>
    -->
    </div>
</script>

<!-- Template: 文件操作菜单 -->
<script type="text/template" id="tpl_file_menu">
    <% if( /txt|office|image/.test(filetype) ){ %>
    <li>
    <a href="javascript:;" file-act="open">
    <i class="o-drop-open"></i> 打开
    </a>
    </li>
    <% } %>
    <li>
    <a href="javascript:;" file-act="download">
    <i class="o-drop-down"></i> 下载
    </a>
    </li>
    <li<% if(filetype != "office"){ %> class="hide" <% } %>>
    <a href="javascript:;" file-act="edit">
    <i class="o-drop-edit"></i> 编辑
    </a>
    </li>
    <li>
    <a href="javascript:;" file-act="copy">
    <i class="o-drop-copy"></i> 复制
    </a>
    </li>
    <li>
    <a href="javascript:;" file-act="cut">
    <i class="o-drop-scissors"></i> 剪切
    </a>
    </li>
    <li>
    <a href="javascript:;" file-act="rename">
    <i class="o-drop-rename"></i> 重命名
    </a>
    </li>
    <li>
    <a href="javascript:;" file-act="remove">
    <i class="o-drop-delete"></i> 删除
    </a>
    </li>
</script>

<!-- Template: 右键菜单 -->
<script type="text/template" id="tpl_context_menu">
    <li <% if(!fileCreatable) { %>class="disabled"<% } %>>
    <a href="javascript:;" file-act="upload">上传文件</a>
    </li>
    <li <% if(!folderCreatable) { %>class="disabled"<% } %>>
    <a href="javascript:;" file-act="newFolder">新建文件夹</a>
    </li>
    <li <% if(selectedFileCount <= 0) { %> class="hide" <% } %>>
    <a href="javascript:;" file-act="copy">复制</a>
    </li>
    <li <% if(selectedFileCount <= 0) { %> class="hide" <% } %>>
    <a href="javascript:;" file-act="cut">剪切</a>
    </li>
    <li <% if(clipboardFileCount == 0 || !fileCreatable) { %>class="disabled"<% } %>>
    <a href="javascript:;" file-act="paste">
    粘贴<% if(clipboardFileCount > 0) { %>(<%= clipboardFileCount %>个文件) <% } %>
    </a>
    </li>
</script>

<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/underscore/underscore.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/backbone/backbone.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/jquery.pagination.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.pSelect.js?<?php echo VERHASH; ?>'></script>

<script src='<?php echo $this->getAssetUrl(); ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $this->getAssetUrl(); ?>/js/cabinet.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $this->getAssetUrl(); ?>/js/cabinet_personal.js?<?php echo VERHASH; ?>'></script>

<script>
    Ibos.app.s({
        "pid": <?php echo $pid; ?>,
        "cabinetType": "personal",
        "isAdministrator": <?php echo IBOS::app()->user->isadministrator; ?>
    })
</script>
