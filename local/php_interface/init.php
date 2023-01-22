<?php
require(__DIR__ . "/../include/vendor/autoload.php");

//TODO насторйки для ручной интеграции с 1с, после автоматической насторйки убрать!
COption::SetOptionString("catalog", "DEFAULT_SKIP_SOURCE_CHECK", "Y");
\Bitrix\Main\Config\Option::set("catalog", "DEFAULT_SKIP_SOURCE_CHECK", "Y");
COption::SetOptionString("sale", "secure_1c_exchange", "N");

define('VUEJS_DEBUG', true);