<?php 

use application\core\utils\Convert;
use application\core\utils\IBOS;
use application\core\utils\String;

?>
<!-- Mainer -->
<div class="mc mcf clearfix">
	<?php echo $this->getHeader( $lang ); ?>
	<div>
		<div>
			<ul class="nav nav-tabs nav-tabs-large nav-justified nav-special">
				<li class="active"><a href="<?php echo $this->createUrl( 'home/index', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Home page']; ?></a></li>
				<?php if ( $this->getIsWeiboEnabled() ): ?><li><a href="<?php echo IBOS::app()->urlManager->createUrl( 'weibo/personal/index', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Weibo']; ?></a></li><?php endif; ?>
				<?php if ( $this->getIsMe() ): ?>
					<li><a href="<?php echo $this->createUrl( 'home/credit' ); ?>"><?php echo $lang['Credit']; ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo $this->createUrl( 'home/personal', array( 'uid' => $this->getUid() ) ); ?>"><?php echo $lang['Profile']; ?></a></li>
			</ul>
		</div>
	</div>
</div>
<div class="pc-container special-container clearfix">
    <div class="row mb">
        <div class="span6">
            <div class="mbox">
                <div class="mbox-header adjust-box-header">
                    <span><?php echo $lang['Credit situation']; ?></span>
                </div>
				<div class="mbox-body pc-hb">
					<div class="fill-nn bglg">
						<div>
							<!-- Status 1 start-->
							<div class="mbs">
								<div class="dib">
									<div class="mb xwb">
										<i class="lv lv<?php echo $user['level']; ?>"></i>
										<span class="dib mlm fss"><?php echo $user['group_title']; ?></span>
									</div>	
									<span><?php if ( $user['upgrade_percent'] > 90 ): ?><?php echo $lang['Credit Encourage 1']; ?><?php else: ?><?php echo $lang['Credit Encourage 2']; ?><?php endif; ?></span>
								</div>
								<span class="exp-val">
									<em><?php echo $user['credits']; ?></em>/<?php echo $user['next_group_credit']; ?>
								</span>
							</div>
							<div class="progress" id="exp_info">
								<div class="progress-bar <?php if ( $user['upgrade_percent'] > 90 ): ?>progress-bar-danger<?php else: ?>progress-bar-success<?php endif; ?>" style="width: <?php echo $user['upgrade_percent']; ?>%;"></div>
							</div>
							<div>
								<div class="dib">
									<span><?php echo $lang['Current credits']; ?>&nbsp;:&nbsp;</span><span class="xwb"><?php echo $user['credits']; ?></span>
								</div>
								<div class="dib mlf">
									<span><?php echo $lang['Upgrade needed']; ?>&nbsp;:&nbsp;</span><span class="xwb"><?php echo (int) ($user['next_group_credit'] - $user['credits']); ?></span>
								</div>
								<div class="dib mlf">
									<span><?php echo $lang['Online time']; ?>&nbsp;:&nbsp;</span><span class="xwb"><?php echo $userCount['oltime']; ?><?php echo $lang['Hour']; ?></span>
								</div>
							</div>
						</div>
					</div>
					<?php if(is_array($extcredits)): ?>
					<div class="mb">
						<table class="profile-info-table">
							<tbody>
								<tr>
									<?php 
									foreach ( $extcredits as $index => $credits ): ?>
										<?php if(!empty($credits)): ?>
										<td>
											<div><?php echo $credits['name']; ?></div>
											<strong><?php echo $userCount['extcredits' . $index]; ?></strong>
										</td>
										<?php endif; ?>
									<?php endforeach; ?>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="xar integral-tip">
						<i class="o-doubt-img"></i>
						<a target="_blank" href="<?php echo $this->createUrl( 'home/credit', array( 'op' => 'rule' ) ); ?>"><?php echo $lang['How to obtain the credit']; ?>？</a>
					</div>
					<?php endif;?>
				</div>
            </div>
        </div>
        <div class="span6">
            <div class="mbox">
				<div class="mbox-header adjust-box-header">
					<span><?php if ( $this->getIsMe() ): ?><?php echo $lang['My contacts']; ?><?php else: ?><?php echo $lang['Other contacts']; ?><?php endif; ?></span>
				</div>
				<div class="mbox-body">
					<div id="contacts" class="pc-hb bglb"></div>
				</div>
            </div>
        </div>
    </div>
    <div class="mb">
		<div class="mbox">
			<div class="mbox-header adjust-box-header">
				<span>
					<?php echo $lang['My ranking']; ?></span>
			</div>
			<div class="mbox-body">
				<table class="point-myranking-table">
					<tbody>
						<tr>
							<td rowspan="2" width="319">
								<div class="point-myranking-total">
									<div class="fsl mb">
										<?php echo $lang['Credit total']; ?></div> <em class="xco"><?php echo $user['credits']; ?></em>
								</div>
							</td>
							<td width="319" height="130">
								<div class="point-myranking-detail xac">
									<div class="fsl mb">
										<?php echo $lang['Ranking']; ?></div>
									<div> <em class="fsf xcbu"><?php echo $curRanking; ?></em>
										<span class="fsl tcm">
											/
											<?php echo $totalRanking; ?></span>
									</div>
								</div>
							</td>
							<td rowspan="2">
								<div>
									<table class="table table-striped table-npd mbz">
										<tbody>
											<?php foreach ( $ranklist as $index => $rank ): ?>
												<tr>
													<td>
														<?php if ( $index == 0 ): ?>    
															<table class="t-point-ranking-top">
																<tbody>
																	<tr>
																		<td width="80" class="fill-zn">
																			<a href="<?php echo $rank['space_url']; ?>
																			   " class="avatar-box pull-left posr">
																				<span class="avatar-circle">
																					<img width="56" height="56" class="mbm" src="<?php echo $rank['avatar_middle']; ?>" /></span>
																				<span class="top-flag">1</span>
																			</a>
																		</td>
																		<td>
																			<div class="mbs">
																				<a href="<?php echo $rank['space_url']; ?>"><?php echo $rank['realname']; ?></a>
																			</div>
																			<div class="tcm">
																				<?php echo $rank['posname']; ?>
																			</div>
																		</td>
																		<td class="xar fill-zn">
																			<span class="fsg xcbu"><?php echo $rank['credits']; ?></span>
																		</td>
																	</tr>
																</tbody>
															</table>
														<?php else: ?>    
															<span class="xwb xco dib">
																<?php echo ($index + 1); ?></span>
															<a href="<?php echo $rank['space_url']; ?>" class="avatar-circle-small dib mlm" title="<?php echo $rank['realname']; ?>">
																<img src="<?php echo $rank['avatar_middle']; ?>"></a>
															<span class="ilsep dib mlm">
																<a target="_blank" href="<?php echo $rank['space_url']; ?>"><?php echo $rank['realname']; ?></a>
															</span>
															<span class="xwb pull-right"><?php echo $rank['credits']; ?></span>
														<?php endif; ?>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</td>
						</tr>
						<tr>
							<td height="130">
								<div class="xac">
									<?php if ( $isTop ): ?>    
										<div class="fsl"><?php echo $lang['Credit top 1'] ?>！</div>
									<?php else: ?>    
										<div class="fsl mb"><?php echo $lang['More than']; ?></div>
										<div>
											<em class="fsf xcbu"><?php echo $rankPercent; ?>%</em>
											<span class="fsl"><?php echo $lang['Other colleagues']; ?></span>
										</div>
									<?php endif; ?>    
									<?php if ( !$isTop && $rankPercent > 90 ): ?>
										<div class="fsl mb">
											<?php echo $lang['So good']; ?>！</div>
									<?php endif; ?>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
    </div>
	<?php if ( $this->getIsMe() ): ?>
		<div class="row">
			<div class="span6">
				<div class="mbox">
					<div class="mbox-header adjust-box-header">
						<span><?php echo $lang['Security Rating']; ?></span>
					</div>
					<div class="mbox-body">
						<!-- status 1 start -->    
						<div class="fill-nn bglg">
							<div class="pc-safe-point pull-right fsf xar xco">
								<span id="security_point"></span>
								<?php echo $lang['Point']; ?>
							</div>
							<div>
								<div id="security_point_desc" class="mb xco"></div>
								<div class="progress" id="security_progress">
									<div class="progress-bar progress-bar-warning"></div>
								</div>
							</div>
							<div class="xac fill-sn">
								<button id="recheck" type="button" data-loading-text="<?php echo $lang['Rechecking']; ?>..." autocomplete="off" class="btn btn-primary">
									<?php echo $lang['Recheck']; ?></button>
							</div>
						</div>
						<?php if ( !$securityRating !== 100 ): ?>    
							<div class="fill-sn">
								<div class="mb">
									<?php echo $lang['You can']; ?>：</div>
								<ul>
									<li class="clearfix mbs">
										<?php if ( $user['validationemail'] == 1 ): ?> <i class="o-small-mail-bind"></i>
											<?php echo $lang['Email address']; ?>    
											：
											<?php echo $user['email']; ?>    
											<span class="tcm">
												<?php echo $lang['Already bind']; ?></span>
											<a href="javascript:;" data-action="bind" data-param='{"type": "email"}' class="btn btn-small pull-right">
												<?php echo $lang['Modify']; ?></a>
										<?php else: ?>    
											<!-- 已绑定样式类为o-mail-bind --> <i class="o-small-mail-unbind"></i>
											<?php echo $lang['Email address']; ?>    
											：
											<?php echo $user['email']; ?>    
											<span class="tcm">
												<?php echo $lang['Unbind']; ?></span>
											<a href="javascript:;" data-action="bind" data-param='{"type": "email"}' class="btn btn-small pull-right">
												<?php echo $lang['Bind']; ?></a>
										<?php endif; ?></li>
									<li class="clearfix">
										<?php if ( $user['validationmobile'] == 1 ): ?>    
											<i class="o-small-phone-bind"></i>
											<?php echo $lang['Mobile number']; ?>    
											：
											<?php echo $user['mobile']; ?>    
											<span class="tcm">
												<?php echo $lang['Already bind']; ?></span>
											<a href="javascript:;" data-action="bind" data-param='{"type": "mobile"}' class="btn btn-small pull-right">
												<?php echo $lang['Modify']; ?></a>
										<?php else: ?>    
											<i class="o-small-phone-unbind"></i>
											<?php echo $lang['Mobile number']; ?>    
											：
											<?php echo $user['mobile']; ?>    
											<span class="tcm">
												<?php echo $lang['Unbind']; ?></span>
											<a href="javascript:;" data-action="bind" data-param='{"type": "mobile"}' class="btn btn-small pull-right">
												<?php echo $lang['Bind']; ?></a>
										<?php endif; ?>
									</li>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="span6">
				<div class="mbox mbox-stable">
					<div class="mbox-header adjust-box-header">
						<span>
							<?php echo $lang['Login record']; ?></span>
					</div>
					<div class="mbox-body">
						<table class="table table-striped mbz">
							<tbody>
								<?php
								$terminal = array( 'web' =>
									$lang['Web login'], 'app' => $lang['App login'] )
								?>
								<?php foreach ( $history as $log ): ?>    
									<?php $row = json_decode( $log['message'], true ); ?>    
									<tr>
										<td>
											<?php echo Convert::formatDate( $log['logtime'], 'u' ); ?></td>
										<td>
											<?php echo String::cutStr( !empty( $row['address'] ) ? $row['address'] : Convert::convertIp( $row['ip'] ), 7 ); ?></td>
										<td>
											<?php echo $row['ip']; ?></td>
										<td>
											<?php echo $terminal[$row['terminal']]; ?></td>
									</tr>
								<?php endforeach; ?></tbody>
						</table>
						<div class="mbox-layer">
							<div class="xac fill-nn">
								<a href="<?php
								echo $this->
										createUrl( 'home/personal', array( 'op' => 'history' ) );
								?>" class="link-more">
									<i class="cbtn o-more"></i>
									<span class="ilsep">
										<?php echo $lang['See more history']; ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>    
</div>
<script>
	Ibos.app.s({
		<?php if ( !empty( $contacts ) ): ?>
		seriesData: [{
			nodes: [
				<?php foreach ( $contacts as $index => $contact ) : ?>
				{category: <?php if ( $index <= 2 ): ?><?php echo $index; ?><?php else: ?>2<?php endif; ?>, name: '<?php echo $contact['realname']; ?>', value: <?php echo rand( 1, 40 ); ?>},
				<?php endforeach; ?>
			],
			links: [
				<?php foreach ( $contacts as $index => $contact ) : ?>
				{source: <?php echo $index + 1; ?>, target: 0, weight: <?php echo rand( 1, 3 ); ?>},
				<?php endforeach; ?>
			]
		}],
		<?php endif; ?>
		"currentUid": "<?php echo $this->getUid(); ?>",
		"securityRating": "<?php echo isset($securityRating) ? $securityRating : ''; ?>"
	});
</script>
<script src='<?php echo $assetUrl; ?>/js/user.js?<?php echo VERHASH; ?>'></script>
<script src='<?php echo $assetUrl; ?>/js/user_home_index.js?<?php echo VERHASH; ?>'></script>
