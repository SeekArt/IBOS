<?php 

use application\core\utils\Ibos;

?>
<div class="ct">
	<div class="clearfix">
		<h1 class="mt"><?php echo IBOS::lang( 'Information center' ); ?></h1>
	</div>
	<div>
		<form action="<?php echo $this->createUrl( 'dashboard/add' ); ?>" class="form-horizontal" method="post">
			<!-- 邮件发送设置 start -->
			<div class="ctb">
				<h2 class="st"><?php echo IBOS::lang( 'Officialdoc setting' ); ?></h2>
				<div class="">
					<div class="control-group">
						<label for="" class="control-label"><?php echo IBOS::lang( 'Comment' ); ?></label>
						<div class="controls">
							<input type="checkbox" name="commentSwitch" value='1' id="" data-toggle="switch" class="visi-hidden" <?php if ( $commentSwitch ): ?>checked<?php endif; ?>>
						</div>
					</div>
					<div>
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th width="120"><?php echo IBOS::lang( 'NO.' ); ?></th>
									<th><?php echo IBOS::lang( 'Template name' ); ?></th>
									<th width="120"><?php echo IBOS::lang( 'Operating' ); ?></th>
								</tr>
							</thead>
							<tbody id="mal_setup_tbody">
								<!-- 显示行 查改删-->
								<?php foreach ( $data as $key => $rcType ) { ?>
									<tr>
										<td>
											<?php echo $key + 1; ?><input type="hidden" name="rcid_<?php echo $rcType['rcid']; ?>" value="<?php echo $rcType['rcid']; ?>">
										</td>
										<td>
											<input type="text" class="input-small" name="old_<?php echo $rcType['rcid']; ?>" value="<?php echo $rcType['name']; ?>">
										</td>
										<td>
											<a href="<?php echo $this->createUrl( 'dashboard/edit', array( 'rcid' => $rcType['rcid'] ) ); ?>" class="cbtn o-edit"></a>
											<a href="javascript:delete(<?php echo $rcType['rcid']; ?>);" class="cbtn o-trash"></a>
										</td>
									</tr>
								<?php } ?>
								<!-- 增加行 -->
							</tbody>
							<tfoot>
								<tr>
									<td colspan="7">
										<a href="javascript:;" id="add_mal_item" data-number="<?php echo count( $data ); ?>"  class="cbtn o-plus"></a>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="control-group">
						<label for="" class="control-label"></label>
						<div class="controls">
							<button class="btn btn-primary btn-large btn-submit" type="submit"><?php echo IBOS::lang( 'Submit' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/ibos-template" id="mal_setup_template">
			<tr>
				<td>
					<%=number%>
				</td>
				<td>
					<input type="text" name="<%=name%>" class="input-small">
				</td>
				<td>
					<a href="#" class="cbtn o-trash"></a>
				</td>
			</tr>
</script>
<script>
	(function() {
		var malTbody = $("#mal_setup_tbody");
		// 新增项
		$("#add_mal_item").on("click", function() {
			var number = parseInt($('#add_mal_item').attr('data-number'), 10) + 1;
			var malTemp = $.template("mal_setup_template", {number: number, name: 'new_' + number});
			//将模板文本生成节点，并对其中的复选框初始化，然后插入表格
			$(malTemp).find("input[type='checkbox']").label().end().appendTo(malTbody);
			$('#add_mal_item').attr('data-number', number);
		});
		// 删除项
		malTbody.on("click", ".o-trash", function() {
			$(this).parents("tr").first().remove();
		});
	})();
</script>