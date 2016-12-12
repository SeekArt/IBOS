<?php

use application\core\utils\Ibos;
use application\modules\vote\model\Vote as VoteModel;

?>
<link rel="stylesheet"
      href="<?php echo Ibos::app()->assetManager->getAssetsUrl('vote'); ?>/css/vote.css?<?php echo VERHASH; ?>">


<div id="vote" class="vote  form-compact">
    <!-- 文字投票 -->
    <div class="ct" id="vote_list"></div>
    <div class="control-group">
        <a href="javascript:;" class="add-one" id="vote_add">
            <i></i> 添加新题目
        </a>
    </div>
    <!-- 截止时间 -->
    <div class="row">
        <div class="span6">
            <label>截止时间</label>
            <div class="datepicker dib ml" id="vot_deadline_date">
                <input type="text" name="vote[endtime]" id="endtime" class="datepicker-input"
                       value="<?php if (empty($vote['endtimestr'])) {
                           echo date('Y-m-d H:i', time() + 86400);
                       } else {
                           echo @$vote['endtimestr'];
                       } ?>">
                <a href="javascript:;" class="datepicker-btn"></a>
            </div>
        </div>
        <div class="pull-right">
            <div class="control-group">
                <label class="control-label mr">投票结果</label>
                <label class="radio radio-inline checked"><span class="icon"></span><span
                        class="icon-to-fade"></span>
                    <?php $isVisible = @(int)$vote['isvisible']; ?>
                    <input type="radio" name="vote[isvisible]"
                           value="1" <?php in_array($isVisible, array(null, VoteModel::AFTER_VOTE_VISIBLE)) && print 'checked'; ?>>投票后可见
                </label>
                <label class="radio radio-inline mr"><span class="icon"></span><span class="icon-to-fade"></span>
                    <input type="radio" name="vote[isvisible]"
                           value="0" <?php $isVisible === VoteModel::ALL_VISIBLE && print 'checked'; ?>>
                    任何人可见
                </label>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="vote_project_tpl">
    <li class="control-group <% if(type == 'image'){ %>vote-image-item<% } %>" data-index="<%= id %>">
        <label class="control-label">
            <span class="badge"><%= itemid+1 %></span>
        </label>
        <div class="controls">
            <% if( type == 'text' ){ %>
            <input type="text" name="vote[topics][<%= voteid %>][<%= itemid %>][content]" maxlength="20" <% if( index ==
            1 ){ %>placeholder="至少2项,每项最多20字"<% } %> value="<%= content %>">
            <input type="hidden" name="vote[topics][<%= voteid %>][<%= itemid %>][picpath]" value="<%= picpath %>">
            <% if(index > 2){ %>
            <a href="javascript:;" class="o-vote-remove" data-item-remove="<%=id%>"></a>
            <% } %>
            <a href="javascript:;" class="o-vote-add" data-item-add="<%=id%>"></a>
            <% } if( type == "image" ){ %>
            <div class="media">
                <div class="pull-left img-upload">
                    <!-- 初始 -->
                    <div class="votepic-upload">
                        <i class="cbtn o-plus"></i>
                        <p>添加图片</p>
                    </div>
                    <div class="votepic-upload-error">上传失败</div>
                    <!-- 重新上传 -->
                    <div class="img-reupload">
                        <div class="img-reupload-bg"></div>
                        <div class="img-reupload-text">重新上传</div>
                    </div>
                    <!-- 上传按钮 -->
                    <span id="vote_pic_upload_<%=id%>"></span>
                    <!-- 遮罩 -->
                    <div class="img-upload-cover"></div>
                    <!-- 进条 -->
                    <div class="img-upload-progress"></div>
                    <!-- 图片预览层 -->
                    <div class="img-upload-imgwrap">
                        <% if( picpath ){ %>
                        <img src="<%= picpath %>">
                        <% } %>
                    </div>
                    <input type="hidden" name="vote[topics][<%= voteid %>][<%= itemid %>][picpath]" data-picpath
                           value="<%= picpath %>">
                </div>
                <div class="media-body">
                    <input type="text" name="vote[topics][<%= voteid %>][<%= itemid %>][content]" maxlength="20"
                           value="<%= content %>">
                </div>
            </div>
            <% } %>
            <% if(index > 2){ %>
            <a href="javascript:;" title="" class="o-vote-remove" data-item-remove="<%=id%>"></a>
            <% } %>
            <a href="javascript:;" title="" class="o-vote-add" data-item-add="<%=id%>"></a>
        </div>
    </li>
</script>
<script type="text/template" id="vote_tpl">
    <div class="vote-project" data-id="<%= id %>">
        <% if( index != 1 ){ %>
        <a href="javascript:" title="删除题目" class="vote-close" data-id="<%= id %>"></a>
        <% } %>
        <div class="control-group">
            <label class="control-label">题目</label>
            <div class="controls">
                <input class="vote_subject" name="vote[topics][<%= voteid %>][subject]" value="<%= subject %>"
                       type="text" maxlength="20">
            </div>
        </div>
        <!-- 最大可选项 -->
        <div class="control-group">
            <label class="control-label">单选/多选</label>
            <div class="controls">
                <div class="row">
                    <div class="span3">
                        <select name="vote[topics][<%= voteid %>][maxselectnum]" id="vote_max_select_<%= id %>">
                            <option
                            <% if(maxselectnum == 1){ %>selected<% } %> value="1">单选</option>
                            <option
                            <% if(maxselectnum == 2){ %>selected<% } %> value="2">最多选择2项</option>
                            <option
                            <% if(maxselectnum == 3){ %>selected<% } %> value="3">最多选择3项</option>
                        </select>
                    </div>
                    <div class="span4 ml">
                        <div class="stand">
                            <!--判断开关初始状态 -->
                            <div class="pull-right">
                                <input type="checkbox" <% if( type == "image" ){ %>checked<%}%> value="1"
                                data-toggle="switch" data-id="<%= id %>">
                            </div>
                            <i class="o-image"></i> 添加图片
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="vote_ismulti" name="vote[ismulti]" value="0"/>
        </div>
        <div>
            <ul class="custom-list" id="vote_project_<%= id %>"></ul>
        </div>
        <!-- 此字段用以判断投票类型 "1"为文字投票 "2"为图片投票-->
        <input type="hidden" class="topic-type vote-item-<%= id %>" name="vote[topics][<%= voteid %>][topic_type]"
               value="<% if(type =='text'){ %>1<% }else{ %>2<% } %>">
    </div>
</script>
<script>
    Ibos.app.setPageParam({
        voteUploadSettings: {
            upload_url: "<?php echo Ibos::app()->urlManager->createUrl('main/attach/upload', array('uid' => Ibos::app()->user->uid, 'hash' => $uploadConfig['hash'], 'type' => 'vote')); ?>",
            file_size_limit: "<?php echo $uploadConfig['max']; ?>",
            file_types: "<?php echo $uploadConfig['attachexts']['ext']; ?>",
            file_types_description: "<?php echo $uploadConfig['attachexts']['depict']; ?>"
        },
        voteEditData: <?php echo json_encode($topics); ?>
    });
    $(function(){
        $('.checkbox input, .radio input').label();
    })
</script>
<script src="<?php echo Ibos::app()->assetManager->getAssetsUrl('vote'); ?>/js/vote.js?<?php echo VERHASH; ?>"></script>
<script
    src="<?php echo Ibos::app()->assetManager->getAssetsUrl('vote'); ?>/js/vote_default_articleadd.js?<?php echo VERHASH; ?>"></script>

