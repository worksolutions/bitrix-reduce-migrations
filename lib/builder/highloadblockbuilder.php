<?php

namespace WS\ReduceMigrations\Builder;

use Bitrix\Highloadblock\HighloadBlockTable;
use WS\ReduceMigrations\Builder\Entity\HighLoadBlock;
use WS\ReduceMigrations\Builder\Entity\UserField;

class HighLoadBlockBuilder {

    public function __construct() {
        \CModule::IncludeModule('iblock');
        \CModule::IncludeModule('highloadblock');
    }


    /**
     * @param string $name
     * @param string $tableName
     * @param \Closure $callback
     * @return HighLoadBlock
     * @throws BuilderException
     */
    public function addHLBlock($name, $tableName, $callback) {

        $highLoadBlock = new HighLoadBlock($name, $tableName);
        $callback($highLoadBlock);
        $this->commit($highLoadBlock);
        return $highLoadBlock;
    }

    /**
     * @param string $tableName
     * @param \Closure $callback
     *
     * @return HighLoadBlock
     * @throws BuilderException
     */
    public function updateHLBlock($tableName, $callback) {
        $block = $this->findTable($tableName);
        $highLoadBlock = new HighLoadBlock($block['NAME'], $tableName, $block['ID']);
        $highLoadBlock->markClean();
        $callback($highLoadBlock);
        $this->commit($highLoadBlock);
        return $highLoadBlock;
    }

    /**
     * @var HighLoadBlock $highLoadBlock
     * @throws BuilderException
     */
    private function commit($highLoadBlock) {
        global $DB;
        $DB->StartTransaction();
        try {
            $this->commitHighLoadBlock($highLoadBlock);
            $this->commitFields($highLoadBlock);
        } catch (BuilderException $e) {
            $DB->Rollback();
            throw new BuilderException($e->getMessage());
        }
        $DB->Commit();
    }

    /**
     * @param $tableName
     * @return array|false
     * @throws BuilderException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function findTable($tableName) {
        $hbRes = HighloadBlockTable::getList(array(
            'filter' => array(
                'TABLE_NAME' => $tableName
            )
        ));
        if (!($table = $hbRes->fetch())){
            throw new BuilderException('Cant find block by table name `'.$tableName.'` ');
        }
        return $table;
    }

    /**
     * @var HighLoadBlock $highLoadBlock
     * @throws BuilderException
     * @throws \Bitrix\Main\SystemException
     */
    private function commitHighLoadBlock($highLoadBlock) {
        $isSuccess = true;
        if (!$highLoadBlock->getId()) {
            $hbRes = HighloadBlockTable::add($highLoadBlock->getData());
            $isSuccess = $hbRes->isSuccess();
            $highLoadBlock->setId($hbRes->getId());
        } elseif ($highLoadBlock->isDirty()) {
            $hbRes = HighloadBlockTable::update(
                $highLoadBlock->getId(),
                $highLoadBlock->getData()
            );
            $isSuccess = $hbRes->isSuccess();
        }
        if (!$isSuccess) {
            throw new BuilderException($highLoadBlock->getAttribute('TABLE_NAME') . ' ' . implode(', ', $hbRes->getErrorMessages()));
        }
    }

    /**
     * @var HighLoadBlock $highLoadBlock
     * @throws BuilderException
     */
    private function commitFields($highLoadBlock) {
        global $APPLICATION;

        $gw = new \CUserTypeEntity();
        foreach ($highLoadBlock->getFields() as $field) {
            $res = true;
            if ($field->getId() > 0) {
                $field->isDirty() && $res = $gw->Update($field->getId(), $field->getData());
            } else {
                $res = $gw->Add(array_merge($field->getData(), array(
                    'ENTITY_ID' => 'HLBLOCK_' . $highLoadBlock->getId()
                )));
                if ($res) {
                    $field->setId($res);
                }
            }
            if (!$res) {
                throw new BuilderException($APPLICATION->GetException()->GetString());
            }

            $this->commitEnum($field);
        }
    }

    /**
     * @param UserField $field
     * @throws BuilderException
     */
    private function commitEnum($field) {
        global $APPLICATION;
        $obEnum = new \CUserFieldEnum;
        $values = array();
        foreach ($field->getEnumVariants() as $key => $variant) {
            $key = 'n' . $key;
            if ($variant->getId() > 0) {
                $key = $variant->getId();
            }
            $values[$key] = $variant->getData();
        }
        if (empty($values)) {
            return;
        }
        if (!$obEnum->SetEnumValues($field->getId(), $values)) {
            throw new BuilderException($APPLICATION->GetException()->GetString());
        }
    }

}
