<?php

namespace WS\ReduceMigrations\Tests\Cases;

use Bitrix\Main\Application;
use WS\ReduceMigrations\Builder\Entity\Table;
use WS\ReduceMigrations\Builder\TableBuilder;
use WS\ReduceMigrations\Tests\AbstractCase;

class TableBuilderCase extends AbstractCase {

    public function name() {
        return $this->localization->message('name');
    }

    public function description() {
        return $this->localization->message('description');
    }

    public function close() {

    }

    public function testAlgorithm() {
        $this->add();
        $this->update();
        $this->drop();
    }


    public function add() {
        $tableBuilder = new TableBuilder();
        $tableBuilder->create('test_reducemigrations_table', function (Table $table) {
            $table->integer('ID')
                ->autoincrement(true)
                ->primary(true);
            $table->string('NAME');
            $table->text('ABOUT');
            $table->date('BIRTHDAY');
        });

        $tableInfo = $this->getTableInfo('test_reducemigrations_table');

        $this->assertNotEmpty($tableInfo);
        $this->assertEquals($tableInfo['ID']['Type'], 'int(11)');
        $this->assertEquals($tableInfo['ID']['Key'], 'PRI');
        $this->assertEquals($tableInfo['ID']['Extra'], 'auto_increment');
        $this->assertEquals($tableInfo['NAME']['Type'], 'varchar(255)');
        $this->assertEquals($tableInfo['ABOUT']['Type'], 'text');
        $this->assertEquals($tableInfo['BIRTHDAY']['Type'], 'date');
    }


    public function update() {
        $tableBuilder = new TableBuilder();
        $tableBuilder->addColumn('test_reducemigrations_table', 'TEST_COLUMN', 'LONGTEXT');
        $tableBuilder->dropColumn('test_reducemigrations_table', 'BIRTHDAY');

        $tableInfo = $this->getTableInfo('test_reducemigrations_table');

        $this->assertNotEmpty($tableInfo);
        $this->assertEquals($tableInfo['ID']['Type'], 'int(11)');
        $this->assertEquals($tableInfo['ID']['Key'], 'PRI');
        $this->assertEquals($tableInfo['ID']['Extra'], 'auto_increment');
        $this->assertEquals($tableInfo['NAME']['Type'], 'varchar(255)');
        $this->assertEquals($tableInfo['ABOUT']['Type'], 'text');
        $this->assertEquals($tableInfo['TEST_COLUMN']['Type'], 'longtext');
        $this->assertTrue(!isset($tableInfo['BIRTHDAY']));
    }

    public function drop() {
        $tableBuilder = new TableBuilder();
        $tableBuilder->drop('test_reducemigrations_table');

        try {
            $this->getTableInfo('test_reducemigrations_table');
            $this->assertTrue(false);//table wasn't deleted
        } catch (\Bitrix\Main\DB\SqlQueryException $e) {
            //everything ok
        }
    }

    private function getTableInfo($tableName) {
        $database = Application::getConnection();
        $res = $database->query("DESCRIBE $tableName");
        $info = array();
        while ($item = $res->fetch()) {
            $info[$item['Field']] = $item;
        }

        return $info;
    }

}