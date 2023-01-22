<?php

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Response\AjaxJson;
use OfficeMag\ORM\RandomDiscountTable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

/**
 * Компонент для получения случайной скидки
 */
class RandomDiscountsComponent extends CBitrixComponent implements Controllerable
{
    /**
     * Коллекция ошибок
     * @var ErrorCollection
     */
    protected ErrorCollection $errorCollection;

    /**
     * ID текущего пользователя
     * @var int
     */
    protected int $userId;

    /**
     * Время показа старой скидки
     */
    const PERIOD_VIEW_DISCOUNT = 3600;

    /**
     * Период жизни скидки
     */
    const PERIOD_LIFE_DISCOUNT = 10800;

    /**
     * @param null $component
     */
    public function __construct($component = null)
    {
        global $USER;
        parent::__construct($component);

        $this->userId = $USER->GetID();
        $this->errorCollection = new ErrorCollection();
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    /**
     * Конфигурируем ajax методы
     *
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'getDiscount' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'checkDiscount' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    /**
     * Получение уникального кода скидки
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function getDiscountCode(): string
    {
        $isUnique = false;
        $discountCode = '';

        while (!$isUnique) {
            $discountCode = substr(md5(time()), 0, 10);

            $dbRes = RandomDiscountTable::getList(
                [
                    'filter' => [
                        'DISCOUNT_CODE' => $discountCode,
                    ],
                ]
            );

            $isUnique = (!is_array($dbRes->fetch())) ?? true;
        }

        return $discountCode;
    }

    /**
     * Проверить скидку по времени
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws Exception
     */
    private function checkDiscountTime(): array
    {
        $isNoExpiredDiscount = false;
        $discountCode = '';

        $dbRes = RandomDiscountTable::getList(
            [
                'order' => ['TIMESTAMP_X' => 'desc'],
                'filter' => [
                    'USER_ID' => $this->userId
                ],
                'limit' => 1
            ]
        );

        if ($result = $dbRes->fetch()) {
            if ($result['TIMESTAMP_X'] instanceof DateTime) {
                $dateDiff = (new DateTime())->getTimestamp() - $result['TIMESTAMP_X']->getTimestamp();

                if ($dateDiff < self::PERIOD_VIEW_DISCOUNT) {
                    $isNoExpiredDiscount = true;
                    $discountCode = $result['DISCOUNT_CODE'];
                }
            } else {
                throw new Exception(Loc::getMessage('OFFICEMAG_ERROR_CHECK_DISCOUNT'));
            }
        }

        return ['isNoExpiredDiscount' => $isNoExpiredDiscount, 'discountCode' => $discountCode];
    }

    /**
     * Получение скидки пользователя
     * @return AjaxJson
     */
    public function getDiscountAction(): AjaxJson
    {
        try {
            $dataCheckedDiscountTime = $this->checkDiscountTime();

            if ($dataCheckedDiscountTime['isNoExpiredDiscount']) {
                $result = AjaxJson::createSuccess(
                    [
                        'message' => Loc::getMessage('OFFICEMAG_CODE_YOUR_DISCOUNT') . $dataCheckedDiscountTime['discountCode']
                    ]
                );
            } else {
                $discountCode = $this->getDiscountCode();
                $discountValue = rand(1, 50);

                RandomDiscountTable::add(
                    [
                        'DISCOUNT_CODE' => $discountCode,
                        'DISCOUNT_VALUE' => rand(1, 50)
                    ]
                );

                $result = AjaxJson::createSuccess(
                    [
                        'message' => Loc::getMessage(
                            'OFFICEMAG_GETTING_DICSOUNT', ['#DISCOUNT_VALUE#' => $discountValue, '#DISCOUNT_CODE#' => $discountCode]
                        )
                    ]
                );
            }
        } catch (Exception $exception) {
            $this->errorCollection->setError(new Error($exception->getMessage()));
            $result = AjaxJson::createError($this->errorCollection);
        }

        return $result;
    }

    /**
     * Проверить скидку
     * @return AjaxJson
     */
    public function checkDiscountAction(): AjaxJson
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $discountCode = $request->getPost('discountCode');;

        try {
            if (!$discountCode) {
                throw new Exception(Loc::getMessage('OFFICEMAG_ERROR_DISCOUNT_CODE_MUST_NOT_EMPTY'));
            }

            $dbRes = RandomDiscountTable::getList(
                [
                    'select' => ['TIMESTAMP_X', 'DISCOUNT_CODE', 'DISCOUNT_VALUE'],
                    'filter' => [
                        'USER_ID' => $this->userId,
                        'DISCOUNT_CODE' => $discountCode,
                    ],
                ]
            );

            if ($rs = $dbRes->fetch()) {
                if ($rs['TIMESTAMP_X'] instanceof DateTime) {
                    $dateDiff = (new DateTime())->getTimestamp() - $rs['TIMESTAMP_X']->getTimestamp();

                    // Если скидка была получена больше 3х часов назад, то она недоступна
                    if ($dateDiff > self::PERIOD_LIFE_DISCOUNT) {
                        $result = AjaxJson::createSuccess(['message' => Loc::getMessage('OFFICEMAG_DISCOUNT_IS_NOT_AVAIL')]);
                    } else {
                        $result = AjaxJson::createSuccess(
                            [
                                'message' => Loc::getMessage('OFFICEMAG_YOUR_DISCOUNT_VALUE', ['#DISCOUNT_VALUE#' => $rs['DISCOUNT_VALUE']])
                            ]
                        );
                    }
                } else {
                    throw new Exception(Loc::getMessage('OFFICEMAG_ERROR_CHECK_DISCOUNT'));
                }
            } else {
                $result = AjaxJson::createSuccess(['message' => Loc::getMessage('OFFICEMAG_DISCOUNT_IS_NOT_AVAIL')]);
            }
        } catch (Exception $exception) {
            $this->errorCollection->setError(new Error($exception->getMessage()));
            $result = AjaxJson::createError($this->errorCollection);
        }

        return $result;
    }
}