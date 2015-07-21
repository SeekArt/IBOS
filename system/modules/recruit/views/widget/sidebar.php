<?php

use application\core\utils\IBOS;

?>
<li <?php if ( IBOS::app()->controller->id == 'stats' ): ?> class="active" <?php endif; ?>>
	<a href="<?php echo IBOS::app()->createUrl( 'recruit/stats/index' ); ?>">
		<i class="os-statistics"></i>
		<?php echo IBOS::lang( 'Recruit statistics' ); ?>
	</a>
</li>