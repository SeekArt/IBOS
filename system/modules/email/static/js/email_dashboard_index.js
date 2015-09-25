var positionData = Ibos.data.get("position");
(function() {
	$("#roleallocation").userSelect({
		box: $("#roleallocation_box"),
		type: 'position',
		data: positionData,
		maximumSelectionSize: 1
	});
	$('#email_option_add').on('click', function() {
		var date = new Date();
		var id = date.getTime();
		var data = {
			name: 'role[' + id + '][positionid]',
			id: 'roleallocation_' + id,
			boxid: 'roleallocation_' + id + '_box',
			size: 'role[' + id + '][size]'
		},
		temp = $.template('email_template', data);
		$('.email-controls').append(temp);

		$('#' + data.id).userSelect({
			box: $('#' + data.boxid),
			type: 'position',
			data: positionData
		});
	});
})();
$(function() {
	$('.email-controls input[type=text]').each(function() {
		var dataId = $(this).data('id'), id = 'roleallocation_' + dataId, boxId = 'roleallocation_' + dataId + '_box';
		$('#' + id).userSelect({
			box: $('#' + boxId),
			type: 'position',
			data: positionData,
			maximumSelectionSize: 1
		});
	});
});