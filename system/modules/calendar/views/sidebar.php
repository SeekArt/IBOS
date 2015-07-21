<?php 
use application\core\utils\IBOS;
?>
<!-- Sidebar -->
<div class="aside" id="aside">
	<div class="sbb sbbl sbbf">
		<ul class="nav nav-strip nav-stacked">
			<li class="active">
				<a href="<?php echo $this->createUrl( 'schedule/index' ); ?>">
					<i class="o-cal-personal"></i>
					<?php echo $lang['Personal']; ?>
				</a>
				<div class="sbb sbbf">
					<ul class="aside-list">
						<li <?php if ( IBOS::app()->getController()->getId() == 'schedule' ): ?>class="active"<?php endif; ?>>
							<a href="<?php echo $this->createUrl( 'schedule/index' ); ?>"><i class="o-cal-calendar"></i>
								<?php echo $lang['Schedule']; ?>
							</a>
						</li>
						<li <?php if ( IBOS::app()->getController()->getId() == 'task' ): ?>class="active"<?php endif; ?>>
							<a href="<?php echo $this->createUrl( 'task/index' ); ?>"><i class="o-cal-todo"></i>
								<?php echo $lang['Task']; ?>
							</a>
						</li>
						<li <?php if ( IBOS::app()->getController()->getId() == 'loop' ): ?>class="active"<?php endif; ?>>
							<a href="<?php echo $this->createUrl( 'loop/index' ); ?>"><i class="o-cal-affairs"></i>
								<?php echo $lang['Periodic affairs']; ?>
							</a>
						</li>
					</ul>
				</div>
			</li>
			<?php if ( $hasSubUid ): ?>
				<li>
					<a href="<?php echo $this->createUrl( 'schedule/subschedule' ); ?>">
						<i class="o-cal-underling"></i>
						<?php echo $lang['Subordinate']; ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</div>
