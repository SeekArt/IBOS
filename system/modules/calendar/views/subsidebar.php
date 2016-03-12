<?php 
use application\core\utils\Env;
use application\core\utils\IBOS;
?>
<!-- Sidebar -->
<div class="aside" id="aside">
	<div class="sbb sbbl sbbf">
		<ul class="nav nav-strip nav-stacked">
			<li>
				<a href="<?php echo $this->createUrl( 'schedule/index' ); ?>">
					<i class="o-cal-personal"></i>
					<?php echo IBOS::lang( 'Personal' ); ?>
				</a>
			</li>
			<li class="active">
				<a href="<?php echo $this->createUrl( 'schedule/subschedule' ); ?>">
					<i class="o-cal-underling"></i>
					<?php echo IBOS::lang( 'Subordinate' ); ?>
				</a>
				<div>
                    <ul class="mng-list" id="mng_list">
						<?php if(!empty($deptArr)): ?>
						<?php foreach ( $deptArr as $dept ): ?>
							<li>
								<div class="mng-item mng-department active" data-action="toggleUnderlingsList">
									<span class="o-caret dept"><i class="caret"></i></span>
									<a href="javascript:;">
										<i class="o-org"></i>
										<?php echo $dept['deptname']; ?>
									</a>
								</div>
								<ul class="mng-scd-list cal-underling-list">
									<?php foreach ( $dept['user'] as $user ): ?>
										<li>
											<div class="mng-item sub">
												<span class="o-caret g-sub" data-action="toggleSubUnderlingsList" data-param='{"uid":"<?php echo $user['uid']; ?>"}'><?php if ( $user['hasSub'] ): ?><i class="caret"></i><?php endif; ?></span>
												<a href="<?php echo $this->createUrl( 'schedule/subschedule', array( 'uid' => $user['uid'] ) ); ?>" <?php if ( Env::getRequest( 'uid' ) == $user['uid'] ): ?>style="color:#3497DB;"<?php endif; ?>>
													<img src="static.php?type=avatar&uid=<?php echo $user['uid']; ?>&size=middle&engine=<?php echo ENGINE; ?>" alt="">
													<?php echo $user['realname']; ?>
												</a>
												<div class="pull-right">
													<a href="<?php echo $this->createUrl( 'task/subtask', array( 'uid' => $user['uid'] ) ); ?>" class="o-cal-todo mlm" title="<?php echo IBOS::lang( 'Task' ); ?>"></a>
													<a href="<?php echo $this->createUrl( 'schedule/subschedule', array( 'uid' => $user['uid'] ) ); ?>" class="o-cal-calendar" title="<?php echo IBOS::lang( 'Schedule' ); ?>"></a>
												</div>
											</div>
											<!--下属资料,ajax调用生成-->
										</li>
									<?php endforeach; ?>
								</ul>
							</li>
						<?php endforeach; ?>
						<?php endif; ?>
                    </ul>
                </div>
			</li>
			<?php if ( $hasShareUid !== FALSE ): ?>
				<li>
					<a href="<?php echo $this->createUrl( 'schedule/shareschedule' ); ?>">
						<i class="o-cal-shareme"></i>
						<?php echo IBOS::lang( 'Share' ); ?>
					</a>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</div>

<script>
	$(function(){
		// 侧栏伸缩
		var $mngList = $("#mng_list");
		$mngList.on("click", ".view-all", function() {		
			var $el = $(this);
		 	$.get(Ibos.app.url("calendar/schedule/subSchedule", { op: "getsubordinates", item: "99999" }), {
		 		uid: $el.attr('data-uid')
		 	}, function(res){
				$el.parent().replaceWith(res);
		 	});
		});
		
		Ibos.evt.add({
			// 展开/收起下属列表
			"toggleUnderlingsList": function(param, elem){
				$(elem).toggleClass("active").next("ul").toggle();
			},

			// 展开/收起下属的下属列表
			"toggleSubUnderlingsList": function(param, elem){
				var $elem = $(elem),
					$item = $elem.closest(".mng-item");

				if(!$elem.data("init")) {
					$.get(Ibos.app.url('calendar/schedule/subSchedule', {op: 'getsubordinates'}), { uid: param.uid }, function(res){
						$elem.data("init", "1").parent().after(res);
					}, "html")
				}
				$item.toggleClass("active").next("ul").toggle();
			}
		})
	});	
</script>