<?php 
use application\core\utils\IBOS;
use application\modules\vote\components\Vote;
?>
<!-- load css -->
<link rel="stylesheet" href="<?php echo $assetUrl; ?>/css/article.css">
<link rel="stylesheet" href="<?php echo STATICURL; ?>/css/emotion.css?<?php echo VERHASH; ?>">
<!-- Mainer -->

    <div class="mc clearfix">
        <!-- Sidebar -->
        <?php echo $this->getSidebar( $this->catid ); ?>
        <!-- Sidebar end -->

        <!-- Mainer right -->
        <div class="mcr">
            <form id="article_form" action="<?php echo $this->createUrl( 'default/edit' , array('op'=>'update') ); ?>" method="post" class="form-horizontal" enctype="multipart/form-data">
                <div class="ct ctform">
                    <!-- Row 1 -->
                    <div class="row">
                        <div class="span8">
                            <div class="control-group">
                                <label for=""><?php echo IBOS::lang( 'News title'); ?></label>
                                <input type="text" id="subject" name="subject" value="<?php echo $data['subject']; ?>">
                            </div>
                        </div>
                        <div class="span4">
                            <div class="control-group">
                                <label for=""><?php echo IBOS::lang( 'Appertaining category'); ?></label>
                                <select name="catid"  id="edit_articleCategory">
                                    <?php echo $categoryOption; ?>
                                </select>
                                <script>$('#edit_articleCategory').val(<?php echo $data['catid']; ?>);</script>
                            </div>
                        </div>
                    </div>
                    <!-- Row 2 -->
                    <div class="row">
                        <div class="span8">
                            <div class="control-group">
                                <label for=""><?php echo $lang['Publishing permissions']; ?></label>
                                <input type="text" name="publishScope" value="<?php echo $data['publishScope']; ?>" id="publishScope">
                                <div id="publishScope_box"></div>
                            </div>
                        </div>
                        <div class="span4">
                            <div class="control-group">
                                <div class="stand stand-label">
                                    <div class="pull-right">
                                        <input type="checkbox" id="msgRemind" name="msgRemind" <?php if ( !$dashboardConfig['articlemessageenable'] ): ?>
                                               disabled title="<?php echo $lang['Message is not enabled']; ?>"
                                           <?php else: ?>
                                               checked
                                           <?php endif; ?> data-toggle="switch" class="visi-hidden">
                                    </div>
                                    <!--小图标 -->
                                    <i class="o-clock"></i>
                                    <?php echo $lang['Message reminding']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Row 3 Tab -->
                    <div class="mb">
                        <div>
                            <ul class="nav nav-tabs nav-tabs-large nav-justified" id="content_type">
                                <input type="hidden" name="type" id="content_type_value" 
                                       value="<?php echo $data['type']; ?>">
                                <li>
                                    <a href="#type_article" data-toggle="tab" data-value="0">
                                        <i class="o-art-text"></i>
                                        <?php echo IBOS::lang( 'Article content'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#type_pic" data-toggle="tab" data-value="1">
                                        <i class="o-art-picm"></i>
                                        <?php echo IBOS::lang( 'Picture content'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="#type_url" data-toggle="tab" data-value="2">
                                        <i class="o-art-link"></i>
                                        <?php echo IBOS::lang( 'Hyperlink address'); ?>
                                    </a>
                                </li>
                            </ul>
                            <div class="nav-content tab-content bdrb">
                                <!-- 文字内容 -->
                                <div id="type_article" class="tab-pane active">
                                    <div class="bdbs">
                                        <script id="article_add_editor" name="content" type="text/plain"><?php echo $data['content']; ?></script>
                                    </div>
                                    <div class="att">
                                        <div class="attb">
                                            <span id="upload_btn"></span>
                                            <!-- 文件柜 -->
                                            <button type="button" class="btn btn-icon vat" data-action="selectFile" data-param='{"target": "#file_target", "input": "#attachmentid"}'>
                                                <i class="o-folder-close"></i>
                                            </button>
                                            <input type="hidden" name="attachmentid" id="attachmentid" value="<?php echo $data['attachmentid']; ?>">
											<span><?php echo $lang['File size limit'] ?><?php echo $uploadConfig['max']/1024; ?>MB</span>
										</div>
                                        <div class="attl" id="file_target">
                                            <?php if(isset($attach)): ?>
                                                <?php foreach ($attach as $value): ?>
                                                <div class="attl-item" data-node-type="attachItem">
                                                    <a href="javascript:;" title="删除附件" class="cbtn o-trash" data-node-type="attachRemoveBtn" data-id="<?php echo $value['aid']; ?>" ></a>
                                                    <i class="atti"><img width="44" height="44" src="<?php echo $value['iconsmall']; ?>" alt="<?php echo $value['filename']; ?>" title="<?php echo $value['filename']; ?>"></i>
                                                    <div class="attc"><?php echo $value['filename']; ?></div> 
                                                    <span class="fss mlm">
    													<a href="<?php echo $value['downurl']; ?>" class="anchor">下载</a>&nbsp;&nbsp;
    		                                            <?php if (isset($value['officereadurl'])): ?>
    		                                                <a href="javascript:;" data-action="viewOfficeFile" data-param='{"href": "<?php echo $value['officereadurl']; ?>"}' title="<?php echo $lang['View']; ?>">
    		                                                    <?php echo $lang['View']; ?>
    		                                                </a>
    		                                            <?php endif; ?>&nbsp;&nbsp;
    		                                            <?php if (isset($value['officeediturl'])): ?>
    		                                                <a href="javascript:;" data-action="editOfficeFile" data-param='{"href": "<?php echo $value['officeediturl']; ?>"}' title="<?php echo $lang['Edit']; ?>">
    		                                                    <?php echo $lang['Edit']; ?>
    		                                                </a>
    		                                            <?php endif; ?>
    												</span>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div> 
                                </div>

                                <!-- 图片内容 -->
                                <div id="type_pic" class="tab-pane">
                                    <div class="fill-nn">
                                        <div class="btn-group pull-right">
                                            <button type="button" id="pic_moveup" class="btn btn-fix" style="display: none;"><i class="glyphicon-arrow-up"></i></button>
                                            <button type="button" id="pic_movedown" class="btn btn-fix" style="display: none;"><i class="glyphicon-arrow-down"></i></button>
                                        </div>
                                        <label class="btn checkbox checkbox-inline"><input type="checkbox" data-name="pic" id=""></label>
                                        <span>
                                            <i id="pic_upload"></i>
                                        </span>
                                        <button type="button" class="btn btn-fix" id="pic_remove" style="display: none;">
                                            <i class="glyphicon-trash"></i>
                                        </button>
                                    </div>
                                    <div>
                                        <div id="pic_list" class="art-pic-list">
                                            <?php if(isset($pictureData)): ?>
                                                <?php foreach ($pictureData as $picture): ?>
                                                    <div class="attl-item" id="pic_item_<?php echo $picture['aid']; ?>" data-node-type="attachItem">
                                                        <label class="checkbox">
                                                            <input type="checkbox" name="pic" value="<?php echo $picture['aid']; ?>" >
                                                        </label>
                                                        <a href="javascript:;" title="删除附件" class="cbtn o-trash" data-id="<?php echo $picture['aid']; ?>"  data-node-type="attachRemoveBtn"></a>
                                                        <img class="pull-left" width="100" src="<?php echo $picture['filepath']; ?>">
                                                        <div class="attc"><?php echo $picture['filename']; ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <input type="hidden" name="picids" id="picids" value="<?php if(isset($picids)): ?><?php echo $picids; ?><?php endif; ?>"/>
                                </div>

                                <!-- 链接内容 -->
                                <div id="type_url" class="tab-pane ct fill">
                                    <input type="text" id="article_link_url" name="url" value="<?php echo $data['url']; ?>" placeholder="<?php $lang['Enter the link address']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Row 4 -->
                    <div class="row">
                        <div class="span4">
                            <div class="control-group">
                                <label for="status"><?php echo IBOS::lang( 'Information Status'); ?></label>
                                <div>
                                    <div class="btn-group btn-group-justified" data-toggle="buttons-radio" id="article_status">
                                        <label class="btn <?php if( $aitVerify == 0 && $data['status'] != 3): ?>active<?php endif; ?>" <?php if($aitVerify != 0): ?>style="display:none"<?php endif;?>>
                                            <input type="radio" name="status" value="2" <?php if( $aitVerify == 0 ): ?>checked<?php endif; ?>>
                                            <?php echo IBOS::lang( 'Wait verify'); ?>
                                        </label>
										<label class="btn <?php if( $aitVerify == 1 && $data['status'] != 3): ?>active<?php endif; ?>" <?php if($aitVerify != 1): ?>style="display:none"<?php endif;?>>
                                            <input type="radio" name="status" value="1" <?php if( $aitVerify == 1 ): ?>checked<?php endif; ?>>
                                            <?php echo IBOS::lang( 'Publish'); ?>
                                        </label>
                                        <label class="btn <?php if( $data['status'] == 3 ): ?>active<?php endif; ?>">
                                            <input type="radio" name="status" value="3" <?php if( $data['status'] == 3 ): ?>checked<?php endif; ?>>
                                            <?php echo IBOS::lang( 'Draft'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="span4">
                            <div class="control-group">
                                <div class="stand stand-label">
                                    <!-- 判断开关初始状态 -->
                                    <div class="pull-right">
                                        <input type="checkbox" value="1" id="commentStatus" 
                                        <?php if ( !$dashboardConfig['articlecommentenable'] ): ?>
                                        disabled title="<?php echo IBOS::lang( 'Comments module is not installed or enabled'); ?>"
                                        <?php elseif( $data['commentstatus'] ): ?>
                                            checked
                                        <?php else: ?>
                                            
                                        <?php endif; ?>
                                        name="commentstatus" data-toggle="switch" class="visi-hidden">
                                    </div>
                                    <i class="o-comment"></i>
                                    <?php echo IBOS::lang( 'Comment'); ?>
                                </div>
                            </div>
        
                        </div>
                        <div class="span4">
                            <div class="control-group">
                                <div class="stand stand-label">
                                    <div class="pull-right">
                                        <input type="checkbox" id="voteStatus" value="<?php echo $data['votestatus']; ?>" 
                                        <?php if ( !$this->getVoteInstalled() || !$dashboardConfig['articlevoteenable'] ): ?>
                                        disabled title="<?php echo IBOS::lang( 'Votes module is not installed or enabled'); ?>"
                                        <?php elseif( !$data['votestatus'] ): ?>
                                        
                                        <?php else: ?>
                                        checked
                                        <?php endif; ?>
                                        name="votestatus" data-toggle="switch" class="visi-hidden">
                                    </div>
                                    <i class="o-vote"></i>
                                    <?php echo IBOS::lang( 'Vote'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Row 5 Tab -->
                    <?php if ( $this->getVoteInstalled() && $dashboardConfig['articlevoteenable'] ): ?>
                        <?php echo Vote::getView( 'articleEdit'); ?>
                    <?php endif; ?>
                    <!-- Row 6 Button -->   
                    <div id="submit_bar" class="clearfix">
                        <button type="button" class="btn btn-large btn-submit pull-left" onclick="history.back(-1);"><?php echo IBOS::lang( 'Return'); ?></button>
                        <div class="pull-right">
                            <button type="button" id="prewiew_submit" class="btn btn-large btn-submit"><?php echo IBOS::lang( 'Preview'); ?></button>
                            <button type="submit" class="btn btn-large btn-submit btn-primary"><?php echo IBOS::lang( 'Submit'); ?></button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="articleid" value="<?php echo $data['articleid']; ?>">
                <input type="hidden" name="relatedmodule" value="article" />
                <input type="hidden" name="relatedid" value="<?php echo $data['articleid']; ?>">
            </form> 
        </div>
    </div>

<script>
    Ibos.app.setPageParam({
        'publishType': '<?php echo $data["type"] ?>',
        'voteStatus': '<?php echo $data['votestatus']; ?>',
        'voteInstalled': '<?php echo $this->getVoteInstalled(); ?>',
        'voteEnable': '<?php echo $dashboardConfig['articlevoteenable']; ?>'
    })
</script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/swfupload.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/SWFUpload/handlers.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_config.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/ueditor/editor_all_min.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/formValidator/formValidator.packaged.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/app/ibos.treeCategory.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo STATICURL; ?>/js/lib/introjs/intro.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/lang/zh-cn.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/article_default_add.js?<?php echo VERHASH; ?>'></script>
<script>
    $(function() {
        var publishType = Ibos.app.g("publishType");
        $("#content_type [data-toggle='tab'][data-value='" + publishType + "']").tab("show");
        
        //投票状态为假是不显示投票
        window.setTimeout(function(){
            var votestatus = Ibos.app.g('voteStatus'),
                voteInstalled = Ibos.app.g('voteInstalled'),
                voteEnable = Ibos.app.g('voteEnable');
            if(!parseInt(votestatus, 10)){
                if( parseInt(voteInstalled, 10) && parseInt(voteEnable,10) ){
                    $('#vote').hide();
                }
            }
        },100);
    
		$("#edit_articleCategory").on("change", function() {
			var uid = Ibos.app.g("uid"),
					catid = this.value,
					url = Ibos.app.url("article/default/add", {op: "checkIsAllowPublish"});
			$.get(url, {catid: catid, uid: uid}, function(res) {
				$("#article_status label").eq(1).toggle(res.isSuccess).end().eq(+res.isSuccess).trigger("click");
			}, 'json');
		});

    });
</script>