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
				<?php }else if($voteStatus==2){ ?>
					<?php echo Ibos::lang( 'Closed' , 'vote.default' ); ?>
				<?php } ?>
				| <?php if($voteData['vote']['ismulti']==0){ echo IBOS::lang( 'Single select' , 'vote.default' );}else{echo IBOS::lang( 'Multi select' , 'vote.default' ).' | '.IBOS::lang( 'Max select number' ,'vote.default' ).$voteData['vote']['maxselectnum'].IBOS::lang( 'Item' ,'vote.default' );} ?>
			</div>
		</div>
	</div>
	<div class="vote-body" id="vote_text">
		<!--如果投票状态为有效且用户已经投票，显示用户投票数据，提示用户已投票-->
		<?php if(($voteStatus==1||$voteStatus==2) && $userHasVote==true){ ?>
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
		<?php if($voteStatus==2 && $userHasVote==false){ ?>
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
<script>
var VoteTextView = {
	max : "<?php echo $voteData['vote']['maxselectnum']; ?>"
};
</script>
<script src="<?php echo IBOS::app()->assetManager->getAssetsUrl( 'vote' ); ?>/js/vote_default_textview.js?<?php echo VERHASH; ?>"></script>