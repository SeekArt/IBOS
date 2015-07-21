<link rel="stylesheet" href="<?php echo $assetUrl . '/css/calendar.css'?>">
<style>
	.in-todo-table{}
	.in-todo-table tr td{ height: 39px; color: #58585C; }
	.in-todo-table .in-todo-complete td{ text-decoration: line-through; color: #82939E; }
	.in-todo-table tr:hover .o-todo-uncomplete{ visibility: visible; }
</style>

<table class="table in-todo-table">
	<tbody>
		<?php foreach( $taskList as $task ):?>
		<?php if( empty($task['completetime']) ):?>
		<tr class="bdbs">
			<td width="20">
				<a href="javascript:;" class="o-todo-uncomplete" data-id="<?php echo $task['id']; ?>"></a>
			</td>
			<td>
				<?php echo $task['text'];?>
			</td>
		</tr>
		<?php else:?>
		<tr class="bdbs in-todo-complete">
			<td width="20">
				<a href="javascript:;" class="o-todo-complete"></a>
			</td>
			<td>
				<?php echo $task['text'];?>
			</td>
		</tr>
		<?php endif;?>
		<?php endforeach;?>
	</tbody>
</table>

<script>
	(function() {
		function complete(id, isComplete){
			var url = Ibos.app.url("calendar/task/edit", { op: "complete" });
			return $.post(url, {
				id: id,
				complete: +isComplete,
				formhash: Ibos.app.g("formHash")
			});
		}
		function toggleRowState($elem) {
			$elem.toggleClass("o-todo-complete o-todo-uncomplete").closest("tr").toggleClass("in-todo-complete");
		}
	
		$(".in-todo-table").bindEvents({
			"click .o-todo-uncomplete": function() {
				var $elem = $(this);

				complete($elem.attr("data-id"), true)
				.done(function(res) {
					res.isSuccess && toggleRowState($elem);
				});
			},

			"click .o-todo-complete": function() {
				var $elem = $(this);

				complete($elem.attr("data-id"), false)
				.done(function(res) {
					res.isSuccess && toggleRowState($elem);
				});
			}
		});

	})();
</script>