<?php 

use application\core\utils\Convert;
use application\core\utils\IBOS;

?>
<!-- Mainer -->
<div class="mc mcf clearfix">
	<?php echo $this->getHeader( $lang ); ?>
	<div>
		<div>
			<ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
				<li><a href="<?php echo $this->createUrl( 'home/index', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Home page']; ?></a></li>
				<?php if ( $this->getIsWeiboEnabled() ): ?><li><a href="<?php echo IBOS::app()->urlManager->createUrl( 'weibo/personal/index', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Weibo']; ?></a></li><?php endif; ?>
				<?php if ( $this->getIsMe() ): ?>
					<li><a href="<?php echo $this->createUrl( 'home/credit', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Credit']; ?></a></li>
				<?php endif; ?>
				<li class="active"><a href="<?php echo $this->createUrl( 'home/personal', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Profile']; ?></a></li>
			</ul>
		</div>
	</div>
</div>
<div class="pc-header clearfix">
	<ul class="nav nav-skid">
		<li>
			<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'profile', 'uid' => $this->getUid() ) ); ?>">
				<?php echo $lang['My profile']; ?>
			</a>
		</li>
		<?php if ( $this->getIsMe() ): ?>
			<li>
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'avatar', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Upload avatar']; ?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'password', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Change password']; ?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'remind', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Remind setup']; ?></a>
			</li>
			<li class="active">
				<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'history', 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Login history']; ?></a>
			</li>
		<?php endif; ?>
	</ul>
</div>
<div class="alert alert-main mtn xac"> 
	<strong><?php echo $lang['History tip']; ?></strong>
	<a href="<?php echo $this->createUrl( 'home/personal', array( 'op' => 'password', 'uid' => $this->getUid() ) ); ?>" class="anchor">
		<?php echo $lang['Change password']; ?>
	</a>
</div>
<div class="pc-container clearfix">
	<div class="">
		<table class="table table-bordered table-striped">
			<thead>
				<tr>
					<th width="120">
						<?php echo $lang['Time']; ?></th>
					<th width="100">
						<?php echo $lang['Login ip']; ?></th>
					<th>
						<?php echo $lang['Location']; ?></th>
					<th width="120">
						<?php echo $lang['Login type']; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( !empty( $history ) ): ?>
					<?php $terminal = array( 'web' => $lang['Web login'], 'app' => $lang['App login'], 'bqqsso' => $lang['Bqq sso login'] ); ?>
					<?php foreach ( $history as $log ): ?>
						<?php $row = json_decode( $log['message'], true ); ?>
						<tr>
							<td><?php echo $log['logtime']; ?></td>
							<td><?php echo $row['ip']; ?></td>
							<td><?php echo!empty( $row['address'] ) ? $row['address'] : Convert::convertIp( $row['ip'] ); ?></td>
							<td class="fss"><?php echo $terminal[$row['terminal']]; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
			<?php if ( !empty( $pages ) ): ?>
				<tfoot>
					<tr>
						<td colspan="4">
							<div class="pull-right"><?php $this->widget( 'application\core\widgets\Page', array( 'pages' => $pages ) ); ?></div>
						</td>
					</tr>
				</tfoot>
			<?php endif; ?></table>
	</div>
</div>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>