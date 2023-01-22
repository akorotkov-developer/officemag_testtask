<?php

namespace OfficeMag\ORM;

use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\AddResult;

class RandomDiscountTable extends Entity\DataManager
{
    /**
     * Коннект к базе данных
     * @var string
     */
    private static $connect = '';

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return 't_random_discount';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Entity\IntegerField('USER_ID', [
                'required' => true,
            ]),
            new Entity\IntegerField('DISCOUNT_VALUE', [
                'required' => true,
            ]),
            new Entity\StringField('DISCOUNT_CODE', [
                'required' => true,
            ]),
            new Entity\DatetimeField('TIMESTAMP_X', [
                'required' => true,
            ]),
        ];
    }

    /**
     * Записывает коннект к БД
     */
    private static function initConnect(): void
    {
        if (empty(self::$connect)) {
            self::$connect = Main\Application::getConnection();
        }
    }

    /**
     * Создает таблицу
     */
    public static function createTable(): void
    {
        self::initConnect();
        if (!self::$connect->isTableExists(self::getTableName())) {
            self::getEntity()->createDbTable();
        }
    }

    /**
     * Удаляет таблицу
     */
    public static function dropTable(): void
    {
        self::initConnect();
        if (self::$connect->isTableExists(self::getTableName())) {
            self::$connect->dropTable(self::getTableName());
        }
    }

    /**
     * Подготовка данных к записи
     * Поле USER_ID и TIMESTAMP_X формируются автоматически
     * @param array $arData
     */
    private static function prepareAdd(array &$arData)
    {
        global $USER;

        $arData['USER_ID'] = $USER->GetID() ?? null;
        $arData['TIMESTAMP_X'] = new \Bitrix\Main\Type\DateTime();
    }

    /**
     * Добавление в таблицу
     * @param array $arData
     * @return AddResult
     * @throws \Exception
     */
    public static function add(array $arData): AddResult
    {
        self::prepareAdd($arData);
        $result = parent::add($arData);

        return $result;
    }
}