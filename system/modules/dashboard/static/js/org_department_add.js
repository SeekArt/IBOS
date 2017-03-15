$(function() {
    var $pid = $('#dept_pid');

    $("#dep_manager, #superior_manager, #superior_branched_manager").userSelect({
        data: Ibos.data.get("user"),
        type: "user",
        maximumSelectionSize: "1"
    });

    $.formValidator.initConfig({
        formID: "add_dept_form",
        errorFocus: true
    });

    $pid.formValidator()
        .functionValidator({
            fun: function(val) {
                var res = $.grep(val.split(','), function(val, idx) {
                        return val && $.trim(val);
                    }),
                    flag = true;

                if (res.length === 0) {
                    flag = false;
                    Ui.tip(Ibos.l('ORG.DEPARTMENT_PID_CANNOT_BE_EMPTY'), 'warning');
                }

                return flag;
            },
            onError: function() {
                return false;
            }
        });

    $("#dept_name").formValidator()
        .regexValidator({
            regExp: "notempty",
            dataType: "enum",
            onError: Ibos.l("ORG.DEPARTMENT_NAME_CANNOT_BE_EMPTY")
        });

    $pid.userSelect({
        data: Ibos.data.get('department'),
        type: 'department',
        maximumSelectionSize: "1"
    });
});