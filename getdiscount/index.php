<?php
define('NEED_AUTH', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Форма получения скидки");
?>

<?php
$APPLICATION->IncludeComponent(
    'officemag:randomdiscount',
    '',
    [], false
);
?>

<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>