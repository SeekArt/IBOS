<?php 

use application\core\utils\IBOS;
?>
<div class="vote vote-text well well-lightblue">
	<div class="plate-item media">
		<div class="pull-left">
			<div class="plate">
				<span class="plate-title"><?php echo IBOS::lang( 'Number of participants' , 'vote.default' ); ?></span>
				<em id="voter_num"><?php echo $votePeopleNumber; ?></em>
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
				| <?php if($voteData['vote']['ismulti']==0){ echo IBOS::lang( 'Single select' , 'vote.default' );}else{echo IBOS::lang( 'Multi select' , 'vote.default' ).' | '.IBOS::lang( 'Max select number' ,'vote.default' ).$voteData['vote']['maxselectnum'].IBOS::lang( 'Item' ,'vote.default' );} ?>
			</div>
		</div>
	</div>
	<div class="vote-body" id="vote_text">
		<!--如果投票状态为有效且用户已经投票，显示用户投票数据，提示用户已投票-->
		<?php if(($voteStatus==1||$voteStatus==0) && $userHasVote==true){ ?>
			<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
				<div class="vote-item clearfix">
					<label>
						<?php echo $voteItem['content']; ?>
					</label>
					<div class="pgb">
						<div class="pgbr" style="width: <?php echo $voteItem['percentage']; ?>; background-color: <?php echo $voteItem['color_style']; ?>;"></div>
						<div class="pgbs">
							<?php echo $voteItem['number']; ?>(<?php echo $voteItem['percentage']; ?>)
						</div>
					</div>
				</div>
			<?php } ?>
			<?php echo IBOS::lang( 'Thank you for participating' , 'vote.default' ); ?>
		<!--判断投票结果查看权限  如果所有人可见：显示投票结果，显示投票按钮； 如果为投票后可见，不显示投票结果，投票后显示-->
		<?php }else if($voteStatus==1 && $userHasVote==false){ ?>
			<!--如果所有人可见：显示投票结果-->
			<?php if($voteData['vote']['isvisible']==0){ ?>
				<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
					<div class="vote-item clearfix">
						<?php if($voteData['vote']['ismulti']==0){ ?>
							<label class="radio">
								<input type="radio" name="itemid" data-type="vote" value="<?php echo $voteItem['itemid']; ?>">
								<?php echo $voteItem['content']; ?>
							</label>
						<?php }else{ ?>
							<label class="checkbox">
								<input type="checkbox" name="itemids" data-type="vote" value="<?php echo $voteItem['itemid']; ?>">
								<?php echo $voteItem['content']; ?>
							</label>
						<?php } ?>
						<div class="pgb">
							<div class="pgbr" style="width: <?php echo $voteItem['percentage']; ?>; background-color: <?php echo $voteItem['color_style']; ?>;"></div>
							<div class="pgbs">
								<?php echo $voteItem['number']; ?>(<?php echo $voteItem['percentage']; ?>)
							</div>
						</div>
					</div>
				<?php } ?>
				<button id="vote_submit" type="button" class="btn btn-primary"><?php echo IBOS::lang( 'Vote' , 'vote.default' ); ?></button>
			<!--如果为投票后可见，不显示投票结果，投票后显示-->
			<?php }else if($voteData['vote']['isvisible']==1){ ?>
				<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
					<div class="vote-item clearfix">
						<?php if($voteData['vote']['ismulti']==0){ ?>
							<label class="radio">
								<input type="radio" name="itemid" data-type="vote" value="<?php echo $voteItem['itemid']; ?>">
								<?php echo $voteItem['content']; ?>
							</label>
						<?php }else{ ?>
							<label class="checkbox">
								<input type="checkbox" name="itemids" data-type="vote" value="<?php echo $voteItem['itemid']; ?>" >
								<?php echo $voteItem['content']; ?>
							</label>
						<?php } ?>
					</div>
				<?php } ?>
				<button id="vote_submit" type="button" class="btn btn-primary"><?php echo IBOS::lang( 'Vote' , 'vote.default' ); ?></button>
			<?php } ?>
		<?php } ?>
		<!--如果投票状态为已结束且用户未投票，显示用户投票数据-->
		<?php if($voteStatus==0 && $userHasVote==false){ ?>
			<?php foreach ( $voteData['voteItemList'] as $voteItem ) { ?>
				<div class="vote-item clearfix">
					<label>
						<?php echo $voteItem['content']; ?>
					</label>
					<div class="pgb">
						<div class="pgbr" style="width: <?php echo $voteItem['percentage']; ?>; background-color: <?php echo $voteItem['color_style']; ?>;"></div>
						<div class="pgbs" style="left: <?php echo $voteItem['percentage']; ?>">
							<?php echo $voteItem['number']; ?>(<?php echo $voteItem['percentage']; ?>)
						</div>
					</div>
				</div>
			<?php } ?>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">

		// @Todo: 代码待整理
		(function(){
			$vote = $("#vote_text");
			var max = "<?php echo $voteData['vote']['maxselectnum']; ?>";
			var voteText = function($ctx, maxNum){
				var getChecked = function(){
						return $vote.find('[data-type="vote"]:checked');
					},
					getValue = function(){
						var arr = [];
						var $checked = getChecked();
						$checked.each(function(){
							arr.push(this.value);
						});
						return arr.join(",");
					},
					check = function(id){
						$vote.find('[data-type="vote"]').filter('[value="' + id + '"]').label("check");
					},
					uncheck = function(id){
						$vote.find('[data-type="vote"]').filter('[value="' + id + '"]').label("uncheck");
					},
					lastId;

				$vote.on("change", '[data-type="vote"]', function(){
					var id = this.value,
						checkNum = getChecked().length;
					if(checkNum > max){
						lastId && uncheck(lastId);
					}
					lastId = id;
				});
				return {
					val: getValue,
					check: check,
					uncheck: uncheck
				}

			}
			var vote = voteText($vote, max);


			function voteSubmit(){
				var relatedmodule = $('#relatedmodule').val();
					relatedid = $('#relatedid').val();
					voteItemids = vote.val();

				if(!voteItemids){
					$.jGrowl('<?php echo IBOS::lang( 'Min select num description' , 'vote.default'); ?>', { theme: "warning" });
					return false;
				}
				var url="<?php echo $this->createUrl('default/index',array('op'=>'clickVote')); ?>";
				$.post(url, {
					relatedmodule: relatedmodule,
					relatedid:relatedid,
					voteItemids:voteItemids
				}, function(data) {

					if(isNaN(data)){
						var str = '',
							voteItemList = data.voteItemList;
						
						for(var i=0; i< voteItemList.length; i++){

							str +="<div class='vote-item clearfix'>"+
								"<label>"+
									voteItemList[i]['content']+
								"</label>"+
								"<div class='pgb'>"+
									"<div class='pgbr' style='width: "+voteItemList[i]['percentage']+"; background-color: "+voteItemList[i]['color_style']+";'></div>"+
									"<div class='pgbs' style='left: "+voteItemList[i]['percentage']+"'>"+
										voteItemList[i]['number']+"("+voteItemList[i]['percentage']+")"+
									"</div>"+
								"</div>"+
							"</div>";

						}
						str+="<?php echo IBOS::lang( 'Thank you for participating' , 'vote.default' ) ?>";
						$vote.html(str);

						var url="<?php echo $this->createUrl('default/index',array('op'=>'getVoteCount')); ?>";
						$.post(url, {relatedmodule: relatedmodule,relatedid:relatedid}, function(data) {
							$('#voter_num').html(data);
						});
					}else{
						//@Todo: 提示用户已经点击过了
						if(data=== -1){

						}
					}
				});
			}
			$('#vote_submit').click(voteSubmit);

		})();

</script>