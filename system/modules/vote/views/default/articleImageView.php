<?php 
use application\core\utils\Ibos;
?>
<div class="vote vote-pic well well-lightblue">
	<div class="plate-item media">
		<div class="pull-left">
			<div class="plate">
				<span class="plate-title"><?php echo Ibos::lang( 'Number of participants' , 'vote.default' ); ?></span>
				<em><?php echo $votePeopleNumber; ?></em>
			</div>
		</div>
		<div class="media-body">
			<h4 class="media-heading"><?php echo $voteData['vote']['subject']; ?></h4>
            <div class="tcm">
				<?php if($voteStatus==1){ ?>
					<?php if($voteData['vote']['remainTime']==0){ ?>
						<?php echo Ibos::lang( 'No end time' , 'vote.default' ); ?>
					<?php }else if(  is_array( $voteData['vote']['remainTime'] )){ ?>
						<?php echo Ibos::lang( 'Distance vote end time' , 'vote.default' ); ?><?php echo $voteData['vote']['remainTime']['day']; ?><?php echo Ibos::lang( 'Day','date' ); ?><?php echo $voteData['vote']['remainTime']['hour']; ?><?php echo Ibos::lang('Hour', 'date' ); ?><?php echo $voteData['vote']['remainTime']['minute']; ?><?php echo Ibos::lang('Min', 'date' ); ?><?php echo $voteData['vote']['remainTime']['second']; ?><?php echo Ibos::lang ('Sec', 'date' ); ?>
					<?php } ?>
				<?php }else if($voteStatus==0){ ?>
					<?php echo Ibos::lang( 'Closed' , 'vote.default' ); ?>
				<?php } ?>
				| <?php if($voteData['vote']['ismulti']==0){ echo Ibos::lang( 'Single select' , 'vote.default' );}else{echo Ibos::lang( 'Multi select' , 'vote.default' ).' | '.Ibos::lang( 'Max select number' , 'vote.default' ).$voteData['vote']['maxselectnum'].Ibos::lang( 'Item' , 'vote.default' );} ?>
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
   		<p><?php echo Ibos::lang( 'Thank you for participating' , 'vote.default' ); ?></p>
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
  		<button id="vote_submit" type="button" class="btn btn-primary"><?php echo Ibos::lang( 'Vote' , 'vote.default' ); ?></button>
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
var VoteImage = {
	max : <?php echo $voteData['vote']['maxselectnum']  ?>
};
</script>
<script src="<?php echo Ibos::app()->assetManager->getAssetsUrl( 'vote' ); ?>/js/vote_default_imageview.js?<?php echo VERHASH; ?>"></script>



