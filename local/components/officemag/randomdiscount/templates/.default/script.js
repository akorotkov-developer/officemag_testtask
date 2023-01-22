BX.ready(function(){
    /**
     * Получение скидки пользователя
     */
    BX.bindDelegate(
        document.body, 'click', {className: 'get_discount' },
        function(e) {

            BX.ajax.runComponentAction('officemag:randomdiscount', 'getdiscount',{
                mode: 'class',
                data: {},
            }).then(function (response) {
                BX.UI.Dialogs.MessageBox.alert(response.data.message);
            }).catch(function(response) {
                if (response.errors[0].message !== '') {
                    BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + response.errors[0].message + '</span>');
                } else {
                    BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + BX.message('OFFICEMAG_UNDEFINED_ERROR_MESSAGE') + '</span>');
                }
            });
        }
    );

    /**
     * Проверка скидки
     */
    BX.bindDelegate(
        document.body, 'click', {className: 'check-discountcode' },
        function(e) {
            let discountCode = BX("discount_code").value;

            if (discountCode != '') {
                BX.ajax.runComponentAction('officemag:randomdiscount', 'checkdiscount', {
                    mode: 'class',
                    data: {
                        'discountCode': discountCode
                    },
                }).then(function (response) {
                    BX.UI.Dialogs.MessageBox.alert(response.data.message);
                }).catch(function (response) {
                    if (response.errors[0].message !== '') {
                        BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + response.errors[0].message + '</span>');
                    } else {
                        BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + BX.message('OFFICEMAG_UNDEFINED_ERROR_MESSAGE') + '</span>');
                    }
                });
            } else {
                BX.UI.Dialogs.MessageBox.alert('<span class="b-error">' + BX.message('OFFICEMAG_ENTER_DISCOUNT_CODE') + '</span>');
            }
        }
    );
});