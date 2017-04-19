<?php

namespace WS\ReduceMigrations\Builder;


use Bitrix\Main\Application;
use WS\ReduceMigrations\Builder\Entity\Table;

class TableBuilder {

    /**
     * @param $tableName
     * @param \Closure $callback
     *
     * @return Table
     */
    public function create($tableName, $callback) {
        $table = new Table($tableName);
        $callback($table);
        $this->createTable($table);
        return $table;
    }

    /**
     * @param $tableName
     */
    public function drop($tableName) {
        $database = Application::getConnection();
        if (!$database->isTableExists($tableName)) {
            return;
        }
        $database->dropTable($tableName);
    }

    /**
     * @param $tableName
     * @param $columnName
     */
    public function dropColumn($tableName, $columnName) {
        $database = Application::getConnection();
        if (!$database->isTableExists($tableName)) {
            return;
        }
        $database->dropColumn($tableName, $columnName);
    }

    /**
     * @param $tableName
     * @param $columnName
     * @param $type
     */
    public function addColumn($tableName, $columnName, $type) {
        $database = Application::getConnection();
        if (!$database->isTableExists($tableName)) {
            return;
        }
        $columnName = strtoupper($columnName);
        $type = strtoupper($type);
        $sqlHelper = $database->getSqlHelper();
        $database
            ->query('ALTER TABLE '. $sqlHelper->quote($tableName).' ADD '.$sqlHelper->quote($columnName) . ' ' . $type);
    }

    /**
     * @param $tableName
     * @param $columnName
     *
     * @return bool
     */
    public function isColumnExists($tableName, $columnName) {
        $database = Application::getConnection();

        $field = $database->getTableField($tableName, $columnName);

        return $field !== null;
    }

    /**
     * @param Table $table
     */
    private function createTable($table) {
        $database = Application::getConnection();
        $database
            ->createTable($table->name, $table->getPreparedFields(), $table->getPrimary(), $table->getAutoincrement());
    }

}
