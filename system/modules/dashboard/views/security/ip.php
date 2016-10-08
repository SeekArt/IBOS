<?php 
use application\core\utils\Ibos;
?>

<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo $lang['Security setting']; ?></h1>
		<ul class="mn">
			<li>
				<a href="<?php echo $this->createUrl( 'security/setup' ); ?>"><?php echo $lang['Account security setup']; ?></a>
			</li>
			<li>
				<a href="<?php echo $this->createUrl( 'security/log' ); ?>"><?php echo $lang['Run log']; ?></a>
			</li>
			<li>
				<span><?php echo $lang['Disabled ip']; ?></span>
			</li>
		</ul>
	</div>
	<div>
		<form id="sys_security_form" action="<?php echo $this->createUrl( 'security/ip' ); ?>" method="post" class="form-horizontal">
			<!-- IP设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo $lang['Disabled ip']; ?></h2>
				<div class="alert trick-tip">
					<div class="trick-tip-title">
						<i></i>
						<strong><?php echo $lang['Skills prompt']; ?></strong>
					</div>
					<div class="trick-tip-content">
						<?php echo $lang['Disabled ip tip']; ?>
					</div>
				</div>
				<div class="page-list b">
					<div class="page-list-header">
						<button type="button" data-act="del" class="btn"><?php echo $lang['Delete select ip']; ?></button>
					</div>
					<div class="page-list-mainer">
						<table class="table table-striped table-operate" id="ip_rec_table">
							<thead>
								<tr>
									<th width="30">
										<label class="checkbox">
											<input type="checkbox" data-name="ip">
										</label>
									</th>
									<th><?php echo $lang['Ip address']; ?></th>
									<th><?php echo $lang['Geographical location']; ?></th>
									<th><?php echo $lang['Operator']; ?></th>
									<th><?php echo $lang['Start time']; ?></th>
									<th><?php echo $lang['End time']; ?></th>
								</tr>
							</thead>
							<tbody id="ip_rec_tbody">
								<?php if ( !empty( $list ) ): ?>
									<?php foreach ( $list as $banned ) : ?>
										<tr>
											<td>
												<label class="checkbox">
													<input type="checkbox" value="<?php echo $banned['id']; ?>" name="id[]" data-check="ip">
												</label>
											</td>
											<td><?php echo $banned['display']; ?></td>
											<td><?php echo $banned['scope']; ?></td>
											<td><?php echo $banned['admin']; ?></td>
											<td><?php echo $banned['dateline']; ?></td>
											<td>
												<input type="text" name="expiration[<?php echo $banned['id']; ?>]" value="<?php echo $banned['expiration']; ?>" class="input-small">
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="6">
										<a href="javascript:;" id="add_ip_rec" class="operate-group">
											<i class="cbtn o-plus"></i>
											<?php echo $lang['Add disabled ip']; ?>
										</a>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<div>
					<input type="hidden" name="act" id="form_act" />
					<input type="hidden" name="securitySubmit" value='1' />
					<button type="button" data-act="clear" class="btn btn-large btn-submit"><?php echo $lang['Clear all']; ?></button>
					<button type="submit" class="btn btn-primary btn-large btn-submit mls"><?php echo $lang['Submit']; ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- “禁止IP”表格模板 -->
<script type="text/ibos-template" id="ip_rec_template">
	<tr>
	<td></td>
	<td colspan="3">
	<input type="text" name="ip[<%=id%>][ip1]" class="input-small w40">
	<span>.</span>
	<input type="text" name="ip[<%=id%>][ip2]" class="input-small w40">
	<span>.</span>
	<input type="text" name="ip[<%=id%>][ip3]" class="input-small w40">
	<span>.</span>
	<input type="text" name="ip[<%=id%>][ip4]" class="input-small w40">
	<a href="javascript:;" title="<?php echo $lang['Del']; ?>" class="cbtn o-trash mls"></a>
	</td>
	<td colspan="2">
	<?php echo $lang['Period of validity']; ?>
	<input type="text" name="ip[<%=id%>][validitynew]" class="input-small w40" value="30">
	<?php echo $lang['Day']; ?>
	</td>
	</tr>
</script>
<script src="<?php echo $assetUrl; ?>/js/db_security.js"></script>