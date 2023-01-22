<?php

namespace Sprint\Migration;

use OfficeMag\ORM\RandomDiscountTable;

/**
 * Миграция для создания таблицы для хранения случайной скидкаи
 */
class randomDiscountTable20230121154034 extends Version
{
    protected $description = "Таблица для хранения случайной скидки";

    protected $moduleVersion = "4.2.4";

    /**
     * Создание таблицы
     */
    public function up(): void
    {
        RandomDiscountTable::createTable();
    }

    /**
     * Удаление таблицы
     * @return bool|void
     */
    public function down(): void
    {
        RandomDiscountTable::dropTable();
    }
}
