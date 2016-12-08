$(function() {
    $.formValidator.initConfig({ formID: 'weibo_form', errorFocus: true });

    $('input[name="wbnums"]').formValidator()
        .functionValidator({
            fun: function(val) {
                val = parseInt(val);

                if (val && val >= 140) {
                    return true;
                }

                return false;
            },
            validateType: "functionValidator"
        });

    $('input[name="wbpostfrequency"]').formValidator()
        .functionValidator({
            fun: function(val) {
                val = parseInt(val);

                if (val && val >= 5) {
                    return true;
                }

                return false;
            },
            validateType: "functionValidator"
        });
});
