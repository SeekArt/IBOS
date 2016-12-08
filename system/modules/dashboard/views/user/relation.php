<?php ?>
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/organization.css?<?php echo VERHASH; ?>">
<div id="relationship_dialog">
    <!-- <ul class="nav nav-skid type-nav-skid">
        <li class="active">
            <a href="javascript:;" data-toggle="tab">按员工</a>
        </li>
    </ul>  -->
    <div class="list-content" id="list_content">
        <div class="fill-nn clearfix">
            <div class="pull-left content-left">
                <p class="mb">用鼠标拖拽员工以调整上下级别关系</p>
                <div class="mbs">
                    <div class="search">
                        <input type="text" name="keyword" placeholder="查找姓名" id="r_search" nofocus>
                        <a href="javascript:;">search</a>
                    </div>
                </div>
                <div class="relation-list-content scroll">
                    <ul id="rtree" class="ztree relation-ztree"></ul>
                </div>
            </div>
            <div class="pull-left content-right ml">
                <p class="mb">未指定直属上级的员工</p>
                <div class="noexist-list-content">
                    <div class="noexist-list-wrap" id="noexist_list_wrap">
                    </div>
                    <div class="opt-toolbar">
                        <div class="btn-group">
                            <button type="button" class="btn disabled" id="page_prev" data-action="prevPage">
                                <i class="o-opt-prev"></i>
                            </button>
                            <button type="button" class="btn disabled" id="page_next" data-action="nextPage">
                                <i class="o-opt-next"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/template" id="tpl_user_list">
    <ul class="noexist-list" id="noexist_list">
        <% if(data.length){ %>
        <% for( var i = 0; i < data.length; i++) { %>
        <li data-id="<%= data[i].uid %>" class="noexist-list-li">
            <div class="clearfix">
	<span class="avatar-circle pull-left">
	<img class="image-wrap" src="static.php?type=avatar&uid=<%= data[i].uid %>>&size=small&engine=LOCAL"/>
	</span>
                <span class="pull-left fss mls user-name"><%= data[i].name %></span>
                <span class="pull-right fss user-position"><%= data[i].position %></span>
            </div>
        </li>
        <% } %>
        <% } %>
    </ul>
</script>
<script>
    Ibos.app.setPageParam({
        'upUsers': <?php echo CJSON::encode($upUsers); ?>
    });
</script>
<script src='<?php echo $assetUrl; ?>/js/org_relationship_show.js?<?php echo VERHASH; ?>'></script>