<?php
if (!defined('B_PROLOG_INCLUDED') || (B_PROLOG_INCLUDED !== true)) die();

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

Extension::load('ui.dialogs.messagebox');
Extension::load('ui.buttons');
Extension::load('ui.forms');
?>

<div class="b-coupon-generator">
    <div class="b-getdiscount">
        <button class="ui-btn ui-btn-success ui-btn-sm get_discount"><?= Loc::getMessage('OFFICEMAG_GET_DISCOUNT')?></button>
    </div>

    <div class="b-check-discountcode">
        <div class="ui-ctl ui-ctl-textbox"> <!-- 1. Основной контейнер -->
            <input id="discount_code" type="text" class="ui-ctl-element form-control" placeholder="<?= Loc::getMessage('OFFICEMAG_ENTER_DISCOUNT_CODE')?>">

            <button class="ui-btn ui-btn-sm btn-check-discountcode check-discountcode"><?= Loc::getMessage('OFFICEMAG_CHECK_DISCOUNT_CODE')?></button>
        </div>
    </div>
</div>

<script>
    BX.message({
        OFFICEMAG_UNDEFINED_ERROR_MESSAGE: '<?= Loc::getMessage("OFFICEMAG_UNDEFINED_ERROR_MESSAGE")?>',
        OFFICEMAG_ENTER_DISCOUNT_CODE: '<?= Loc::getMessage("OFFICEMAG_ENTER_DISCOUNT_CODE")?>',
    });
</script>