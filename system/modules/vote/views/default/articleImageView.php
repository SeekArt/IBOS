<?php 
use application\core\utils\IBOS;
?>
<div class="vote vote-pic well well-lightblue">
	<div class="plate-item media">
		<div class="pull-left">
			<div class="plate">
				<span class="plate-title"><?php echo IBOS::lang( 'Number of participants' , 'vote.default' ); ?></span>
				<em><?php echo $votePeopleNumber; ?></em>
			</div>
		</div>
		<div class="media-body">
			<h4 class="media-heading"><?php echo $voteData['vote']['subject']; ?></h4>
            <div class="tcm">
				<?php if($voteStatus==1){ ?>
					<?php if($voteData['vote']['remainTime']==0){ ?>
						<?php echo IBOS::lang( 'No end time' , 'vote.default' ); ?>
					<?php }else if(  is_array( $voteData['vote']['remainTime'] )){ ?>
						<?php echo IBOS::lang( 'Distance vote end time' , 'vote.default' ); ?><?php echo $voteData['vote']['remainTime']['day']; ?><?php echo IBOS::lang( 'Day','date' ); ?><?php echo $voteData['vote']['remainTime']['hour']; ?><?php echo IBOS::lang('Hour', 'date' ); ?><?php echo $voteData['vote']['remainTime']['minute']; ?><?php echo IBOS::lang('Min', 'date' ); ?><?php echo $voteData['vote']['remainTime']['second']; ?><?php echo IBOS::lang ('Sec', 'date' ); ?>
					<?php } ?>
				<?php }else if($voteStatus==0){ ?>
					<?php echo IBOS::lang( 'Closed' , 'vote.default' ); ?>
				<?php } ?>
				| <?php if($voteData['vote']['ismulti']==0){ echo IBOS::lang( 'Single select' , 'vote.default' );}else{echo IBOS::lang( 'Multi select' , 'vote.default' ).' | '.IBOS::lang( 'Max select number' , 'vote.default' ).$voteData['vote']['maxselectnum'].IBOS::lang( 'Item' , 'vote.default' );} ?>
			</div>
		</div>
	</div>
    <!--如果投票状态为有效且用户已经投票，显示用户投票数据，提示用户已投票-->
	<?php if(($voteStatus==1||$voteStatus==0) && $userHasVote==true){ ?>
		<div class="vote-body">
			<ul class="vote-pic-list clearfix" id="vote">
				<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
					<li>
						<a href="javascript:;">
							<div class="vote-pic-img">
								<img src="<?php echo $voteItem['picpath']; ?>" alt="<?php echo $voteItem['content']; ?>">
								<i class="o-checked"></i>
							</div>
							<p class="vote-pic-desc"><?php echo $voteItem['content']; ?></p>
							<input type="checkbox" class="hide" name="vote[]">
						</a>
						<div class="pgb">
							<div class="pgbr" style="width:<?php echo $voteItem['percentage']; ?>; background-color: #91CE31;"></div>
						</div>
						<p><?php echo $voteItem['number']; ?>(<?php echo $voteItem['percentage']; ?>)</p>
					</li>
				<?php } ?>
			</ul>
		</div>
   		<p><?php echo IBOS::lang( 'Thank you for participating' , 'vote.default' ); ?></p>
	<!--判断投票结果查看权限  如果所有人可见：显示投票结果，显示投票按钮； 如果为投票后可见，不显示投票结果，投票后显示-->
	<?php }else if($voteStatus==1 && $userHasVote==false){ ?>
        <!--如果所有人可见：显示投票结果-->
		<?php if($voteData['vote']['isvisible']==0){ ?>
	    <div class="vote-body">
			<ul class="vote-pic-list clearfix" id="vote">
				<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
					<li data-id="<?php echo $voteItem['itemid']; ?>">
						<a href="javascript:;">
							<div class="vote-pic-img">
								<img src="<?php echo $voteItem['picpath']; ?>" alt="<?php echo $voteItem['content']; ?>">
								<i class="o-checked"></i>
							</div>
							<p  class="vote-pic-desc"><?php echo $voteItem['content']; ?></p>
							<input type="checkbox" class="hide" name="vote[]">
						</a>
						<div class="pgb">
							<div class="pgbr" style="width:<?php echo $voteItem['percentage']; ?>; background-color: #91CE31;"></div>
						</div>
						<p><?php echo $voteItem['number']; ?>(<?php echo $voteItem['percentage']; ?>)</p>
					</li>
				<?php } ?>
			</ul>
		</div>
    	<!--如果为投票后可见，不显示投票结果，投票后显示-->
		<?php }else if($voteData['vote']['isvisible']==1){ ?>
	  	<div class="vote-body">
			<ul class="vote-pic-list clearfix" id="vote">
				<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
					<li data-id="<?php echo $voteItem['itemid']; ?>">
						<a href="javascript:;">
							<div class="vote-pic-img">
								<img src="<?php echo $voteItem['picpath']; ?>" alt="<?php echo $voteItem['content']; ?>">
								<i class="o-checked"></i>
							</div>
							<p class="vote-pic-desc"><?php echo $voteItem['content']; ?></p>
							<input type="checkbox" class="hide" name="vote[]">
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
    	<?php } ?>
  		<button id="vote_submit" type="button" class="btn btn-primary"><?php echo IBOS::lang( 'Vote' , 'vote.default' ); ?></button>
	<?php } ?>
	<!--如果投票状态为已结束且用户未投票，显示用户投票数据-->
	<?php if($voteStatus==0 && $userHasVote==false){ ?>
	   	<div class="vote-body">
			<ul class="vote-pic-list clearfix" id="vote">
				<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
					<li>
						<a href="javascript:;">
							<div class="vote-pic-img">
								<img src="<?php echo $voteItem['picpath']; ?>" alt="<?php echo $voteItem['content']; ?>">
								<i class="o-checked"></i>
							</div>
							<p class="vote-pic-desc"><?php echo $voteItem['content']; ?></p>
							<input type="checkbox" class="hide" name="vote[]" checked>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</div>

<!-- li -->
<script type="text/ibos-template" id="vote_pic_template">
	<li>
		<a href="javascript:;">
			<div class="vote-pic-img">
				<img src="<%=picpath%>" alt="<%=content%>">
				<i class="o-checked"></i>
			</div>
			<p  class="vote-pic-desc"><%=content%></p>
			<input type="checkbox" class="hide" name="vote[]" checked>
		</a>
		<div class="pgb">
			<div class="pgbr" style="width:<%=percentage%>; background-color: #91CE31;"></div>
		</div>
		<p><%=number%>(<%=percentage%>)</p>
	</li>
</script>

<script type="text/javascript">
	(function(){
		// Vote 投票模块 JS， 单独放入vote.js
		var votePic = function($ctx, selector, maxNum){

			selector = selector || "[data-type='voteitem']";
			$ctx = ($ctx && $ctx.length) ? $ctx : $("#vote");
			maxNum = maxNum || 1;

			var selCheckbox = "input[type='checkbox']",
				lastId;

			var _getChecked = function(){
				return $ctx.find(selector + ".active");
			}
			var _getCheckedNum = function(){
				return _getChecked().length;
			}
			var getCheckedValue = function(){
				var $checked = _getChecked();
				var arr = [];
				$checked.each(function(){
					arr.push($.attr(this, "data-id"));
				});
				return arr.join(",");
			}

			var uncheck = function(id){
				$ctx.find(selector).filter("[data-id='" + id + "']").removeClass("active");
			}
			var check = function(id){
				var checkedNum = _getCheckedNum($ctx, selector);
				// 如果选项小于最大可选数
				if(checkedNum < maxNum){
					$ctx.find(selector).filter("[data-id='" + id + "']").addClass("active");
					// 记录上次选中的id
					lastId = id;
				// 大于最大可选数时，当前选中的会替代上个选中的选项
				} else {
					if(lastId){
						// 取消上次选中项
						uncheck(lastId);
						check(id)
					}
				}
			}
			var _bind = function(){
				$ctx.on("click.vote", selector, function(){
					var id = $.attr(this, "data-id");
					if(!id){
						return false;
					}
					// 此处有些性能浪费
					if($(this).hasClass("active")){
						uncheck(id);
					}else{
						check(id);
					}
				})
			};

			_bind();
			return {
				val: function(){
					return getCheckedValue();
				},
				check: check,
				uncheck: uncheck,
				enable: function(){
					_bind();
				},
				disable: function(){
					$ctx.off("click.vote");
				}
			}
		}

		var $vote = $("#vote"),
			max = <?php echo $voteData['vote']['maxselectnum']  ?>,
			vote = votePic($vote, 'li', max);

		 $('#vote_submit').on('click',function(){
             var $elem = $(this),
             	 relatedmodule = $('#relatedmodule').val(),
                 relatedid = $('#relatedid').val(),
                 voteItemids = vote.val();

				if(!voteItemids){
                    Ui.tip(U.lang('SELECT_AT_LEAST_ONE_ITEM'), 'warning');
					return false;
				}
				var url="<?php echo $this->createUrl('default/index',array('op'=>'clickVote')); ?>";
				$.post(url, {
					relatedmodule: relatedmodule,
					relatedid:relatedid,
					voteItemids:voteItemids
				}, function(data) {
					if(typeof data === 'object'){
						var voteItemList = data.voteItemList,
							htmlStr = "";
                        for(var i = 0; i < voteItemList.length; i++){
                             var data = {
	                                picpath: voteItemList[i].picpath,
	                                content: voteItemList[i].content,
	                                percentage:voteItemList[i].percentage,
	                                number: voteItemList[i].number
	                            };

                            htmlStr += $.template('vote_pic_template', data);
                        }
                        $vote.html(htmlStr)
                        .parent().after("<p><?php echo IBOS::lang( 'Thank you for participating' , 'vote.default' ) ?></p>");

                        $elem.remove();
                        // 已投过则禁止投票
                        vote.disable();
                    }
						
					window.setTimeout(function(){
						var url="<?php echo $this->createUrl('default/index',array('op'=>'getVoteCount')) ?>";
						$.post(url, {relatedmodule: relatedmodule,relatedid:relatedid}, function(data) {
							$('.plate em').html(data);
						});
					},100);
				});
         });			
	})();

</script>



