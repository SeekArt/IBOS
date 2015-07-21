<?php

use application\core\utils\Env;
use application\core\utils\String;
?>
<div class="aside" id="aside">
	<div class="sbb sbbl sbbf">
		<ul class="nav nav-strip nav-stacked">
			<li class="active">
				<a href="<?php echo $this->createUrl( 'default/index' ); ?>">
					<i class="o-company-cl"></i>
					<?php echo $lang['Company contact']; ?>
				</a>
				<div>
					<table class="org-dept-table">
						<tbody>
							<?php $op = Env::getRequest( 'op' ); ?>
							<tr <?php if ( $this->id == "constant" ): ?>class="dep-active"<?php endif; ?>>
								<td>
									<a href='<?php echo $this->createUrl( 'constant/index', array( 'op' => $op ) ); ?>' class='org-dep-name'><i class="o-common-users"></i> <?php echo $lang['Regular contact']; ?></a>
								</td>
							</tr>
							<tr data-id='0' data-pid='0'>
								<td>
									<a href='<?php echo $this->createUrl( 'default/index', array( 'op' => $op ) ); ?>' class='org-dep-name'><i class='os-company'></i><?php echo isset( $unit['fullname'] ) ? $unit['fullname'] : ''; ?></a>
								</td>
							</tr>
							<?php
							$str = "
						<tr data-id='\$deptid' data-pid='\$pid'>
							<td>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; \$spacer<a href='" . $this->createUrl( "default/index&op={$op}&deptid=" ) . "\$deptid' class='org-dep-name'><i class='os-department'></i>\$deptname</a>
							</td>
						</tr>";
							$categorys = String::getTree( $dept, $str );
							echo $categorys;
							?>
						</tbody>
					</table>
				</div>
			</li>
		</ul>
	</div>
</div>
<script>
	$(function() {
		var deptid = "<?php echo Env::getRequest( 'deptid' ); ?>",
			controller = "<?php echo $this->id ?>";
		if (!deptid && controller == "default"){
			deptid = 0;
		}
		$('tr[data-id="' + deptid + '"]').addClass("dep-active");
	});
</script>