$(function(){
	Ibos.evt.add({
		"delRole": function(param, elem){
			var $li = $(this).closest("li"),
				id = $li.data("id"),
				param = {id: id},
				url = Ibos.app.url('dashboard/role/del');
			Ui.confirm(Ibos.l("ORG.SURE_DELETE_ITEM"), function(){
				$.post(url, param, function(res){
					if(res.isSuccess){
						$li.remove();
						Ui.tip(Ibos.l("OPERATION_SUCCESS"));
					}else{
						Ui.tip(Ibos.l("OPERATION_FAILED"), "danger");
					}
				}); 
			});
		}
	});
});