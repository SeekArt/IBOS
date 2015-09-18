<?php

use application\core\utils\IBOS;
?>
<link rel="stylesheet" href="<?php echo IBOS::app()->assetManager->getAssetsUrl( 'vote' ); ?>/css/vote.css?<?php echo VERHASH; ?>">

<div id="vote" class="vote mb" style="display: none;">
    <ul class="nav nav-tabs nav-tabs-large nav-justified" id="vote_tab">
        <li class="active">
            <a href="javascript:;" data-target="#vote_text" data-value="1">
                <i class="o-art-text"></i>
                <?php echo IBOS::lang( 'Initiated text vote', 'vote.default' ); ?>
            </a>
        </li>
        <li>
            <a href="javascript:;" data-target="#vote_pic" data-value="2">
                <i class="o-art-picm"></i>
                <?php echo IBOS::lang( 'Initiated image vote', 'vote.default' ); ?>
            </a>
        </li>
    </ul>
    <div class="nav-content bdrb">
        <!-- 文字投票 -->
        <div class="ct ctform form-compact" id="vote_text">
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Create title', 'vote.default' ); ?></label>
                <div class="controls">
                    <input id="vote_subject" name="vote[subject]" type="text" maxlength="20">
                </div>
            </div>
            <div class="control-group">
                <div class="controls"><?php echo IBOS::lang( 'Vote option description', 'vote.default' ); ?></div>
            </div>
            <div>
                <!-- 投票列表: 内容由JS动态生成 -->
                <ul class="custom-list" id="vote_text_list"></ul>
                <div class="control-group">
                    <div class="controls">
                        <a href="javascript:;" class="add-one" id="vote_text_add">
                            <i class="cbtn o-plus"></i>
                            <?php echo IBOS::lang( 'Add option', 'vote.default' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <!-- 最大可选项 -->
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Single or multi select', 'vote.default' ); ?></label>
                <div class="controls">
                    <div class="row">
                        <div class="span3">
                            <select name="vote[maxselectnum]" id="vote_max_select">
                                <option value="1">单选</option>
                                <option value="2">最多选择2项</option>
                                <option value="3">最多选择3项</option>
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="vote_ismulti" name="vote[ismulti]" value="0" />
            </div>
            <!-- 截止时间 -->
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Deadline', 'vote.default' ); ?></label>
                <div class="controls">
                    <div class="row">
                        <div class="span3">
                            <select name="vote[deadlineType]" id="vote_txt_deadline">
                                <option value="0"><?php echo IBOS::lang( 'Custom', 'vote.default' ); ?></option>
                                <option value="1"><?php echo IBOS::lang( 'One week', 'date' ) ?></option>
                                <option value="2"><?php echo IBOS::lang( 'One month', 'date' ) ?></option>
                                <option value="3"><?php echo IBOS::lang( 'Half of a year', 'date' ) ?></option>
                                <option value="4"><?php echo IBOS::lang( 'One year', 'date' ) ?></option>
                            </select>
                        </div>
                        <div class="span6">
                            <div class="datepicker" id="vote_txt_deadline_date">
                                <input type="text" name="vote[endtime]" class="datepicker-input" value="<?php echo date( 'Y-m-d' ); ?>">
                                <a href="javascript:;" class="datepicker-btn"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 投票结果 -->
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Vote result', 'vote.default' ); ?></label>
                <div class="controls">
                    <label class="radio radio-inline">
                        <input type="radio" name="vote[isvisible]" value="1" checked>
                        <?php echo IBOS::lang( 'After the vote visible', 'vote.default' ); ?>
                    </label>
                    <label class="radio radio-inline">
                        <input type="radio" name="vote[isvisible]" value="0">
                        <?php echo IBOS::lang( 'Any visible', 'vote.default' ); ?>
                    </label>
                </div>
            </div>
        </div>
        <!-- 图片投票 -->
        <div id="vote_pic" class="ct ctform form-compact" style="display:none;">
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Create title', 'vote.default' ); ?></label>
                <div class="controls">
                    <input name="imageVote[subject]" id="imageVote_subject" type="text" maxlength="20">
                </div>
            </div>
            <div class="control-group">
                <div class="controls"><?php echo IBOS::lang( 'Vote option description', 'vote.default' ); ?></div>
            </div>
            <div>
                <!-- 投票列表内容由JS动态生成 -->
                <ul class="custom-list" id="vote_pic_list"></ul>
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="controls">
                        <a href="javascript:;" class="add-one" id="vote_pic_add">
                            <i class="cbtn o-plus o-plus"></i>
                            <?php echo IBOS::lang( 'Add option', 'vote.default' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Single or multi select', 'vote.default' ); ?></label>
                <div class="controls">
                    <div class="row">
                        <div class="span3">
                            <select name="imageVote[maxselectnum]" id="picvote_max_select">
                                <option data-id="1" value="1">单选</option>
                                <option data-id="2" value="2">最多选择2项</option>
                                <option data-id="3" value="3">最多选择3项</option>
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="picvote_ismulti" name="imageVote[ismulti]" value="0" />
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Deadline', 'vote.default' ); ?></label>
                <div class="controls">
                    <div class="row">
                        <div class="span3">
                            <select name="imageVote[deadlineType]" id="vote_pic_deadline">
                                <option value="0"><?php echo IBOS::lang( 'Custom', 'vote.default' ); ?></option>
                                <option value="1"><?php echo IBOS::lang( 'One week', 'date' ) ?></option>
                                <option value="2"><?php echo IBOS::lang( 'One month', 'date' ) ?></option>
                                <option value="3"><?php echo IBOS::lang( 'Half of a year', 'date' ) ?></option>
                                <option value="4"><?php echo IBOS::lang( 'One year', 'date' ) ?></option>
                            </select>
                        </div>
                        <div class="span6">
                            <div class="datepicker" id="vote_pic_deadline_date">
                                <input type="text" name="imageVote[endtime]" class="datepicker-input" value="<?php echo date( 'Y-m-d' ); ?>">
                                <a href="javascript:;" class="datepicker-btn"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"><?php echo IBOS::lang( 'Vote result', 'vote.default' ); ?></label>
                <div class="controls">
                    <label class="radio radio-inline">
                        <input type="radio" name="imageVote[isvisible]" value="1" checked>
                        <?php echo IBOS::lang( 'After the vote visible', 'vote.default' ); ?>
                    </label>
                    <label class="radio radio-inline">
                        <input type="radio" name="imageVote[isvisible]" value="0">
                        <?php echo IBOS::lang( 'Any visible', 'vote.default' ); ?>
                    </label>
                </div>
            </div>
        </div>
        <!-- 此字段用以判断投票类型 "1"为文字投票 "2"为图片投票-->
        <input type="hidden" id="vote_type" name="voteItemType" value="1">
    </div>
</div>

<!-- 新增文本投票项模板 -->
<script type="text/ibos-template" id="vote_text_tpl">
    <li class="control-group">
    <label class="control-label">
    <span data-item-index="<%=index%>" class="badge"><%=index%></span>
    </label>
    <div class="controls">
    <input type="text" name="vote[voteItem][]" class="input-small" maxlength="20">
    <a href="javascript:;" title="<?php echo IBOS::lang( 'Delete', 'vote.default' ); ?>" class="o-ra" data-item-remove="<%=id%>"></a>
    </div>
    </li>
</script>

<!-- 新增图片投票项模板 -->
<script type="text/template" id="vote_pic_tpl">
    <li class="control-group">
    <label class="control-label">
    <span data-item-index="<%=index%>" class="badge"><%=index%></span>
    </label>
    <div class="controls">
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
    <div class="img-upload-imgwrap"></div>
    <input type="hidden" name="imageVote[picpath][<%=id%>]" data-picpath>
    </div>
    <div class="media-body">
    <input type="text" name="imageVote[voteItem][<%=id%>]" class="input-small" maxlength="20">
    </div>
    </div>
    <a href="javascript:;" title="<?php echo IBOS::lang( 'Delete', 'vote.default' ); ?>" class="o-ra" data-item-remove="<%=id%>"></a>
    </div>
    </li>
</script>
<script>
    Ibos.app.setPageParam({
        voteUploadSettings: {
            upload_url: "<?php echo IBOS::app()->urlManager->createUrl( 'main/attach/upload', array( 'uid' => IBOS::app()->user->uid, 'hash' => $uploadConfig['hash'], 'type' => 'vote' ) ); ?>",
            file_size_limit: "<?php echo $uploadConfig['max']; ?>",
            file_types: "<?php echo $uploadConfig['attachexts']['ext']; ?>",
            file_types_description: "<?php echo $uploadConfig['attachexts']['depict']; ?>"
        }
    })
</script>
<script src="<?php echo IBOS::app()->assetManager->getAssetsUrl( 'vote' ); ?>/js/vote.js?<?php echo VERHASH; ?>"></script>
<script src="<?php echo IBOS::app()->assetManager->getAssetsUrl( 'vote' ); ?>/js/vote_default_articleadd.js?<?php echo VERHASH; ?>"></script>

