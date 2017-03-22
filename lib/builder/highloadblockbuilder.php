<?php

namespace WS\ReduceMigrations\Builder;

use Bitrix\Highloadblock\HighloadBlockTable;
use WS\ReduceMigrations\Builder\Entity\HighLoadBlock;
use WS\ReduceMigrations\Builder\Entity\UserField;

class HighLoadBlockBuilder {
    /** @var  HighLoadBlock */
    private $highLoadBlock;
    /** @var  UserField[] */
    private $fields;

    public function __construct() {
        \CModule::IncludeModule('iblock');
        \CModule::IncludeModule('highloadblock');
    }

    public function reset() {
        $this->highLoadBlock = null;
        $this->fields = array();
    }

    /**
     * @param $name
     * @param $tableName
     * @return HighLoadBlock
     * @throws BuilderException
     */
    public function addHLBlock($name, $tableName) {
        if ($this->highLoadBlock) {
            throw new BuilderException('reset builder data for continue');
        }
        $this->highLoadBlock = new HighLoadBlock($name, $tableName);
        return $this->highLoadBlock;
    }

    /**
     * @param $tableName
     * @return HighLoadBlock
     * @throws BuilderException
     */
    public function getHLBlock($tableName) {
        if ($this->highLoadBlock) {
            throw new BuilderException('reset builder data for continue');
        }
        $block = $this->findTable($tableName);
        $this->highLoadBlock = new HighLoadBlock($block['NAME'], $tableName, $block['ID']);
        return $this->highLoadBlock;
    }

    /**
     * @param $code
     * @return UserField
     */
    public function addField($code) {
        $field = new UserField($code);
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @param $code
     * @return UserField
     * @throws BuilderException
     */
    public function getField($code) {
        $data = $this->findField($code);
        $field = new UserField($code, $data);
        $this->fields[] = $field;
        return $field;
    }

    /**
     * @throws BuilderException
     */
    public function commit() {
        global $DB;
        $DB->StartTransaction();
        try {
            $this->commitHighLoadBlock();
            $this->commitFields();
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
     * @return HighLoadBlock
     */
    public function getCurrentHighLoadBlock() {
        return $this->highLoadBlock;
    }

    /**
     * @throws BuilderException
     * @throws \Bitrix\Main\SystemException
     */
    private function commitHighLoadBlock() {
        if (!$this->highLoadBlock->getId()) {
            $hbRes = HighloadBlockTable::add($this->highLoadBlock->getSaveData());
        } else {
            $hbRes = HighloadBlockTable::update(
                $this->highLoadBlock->getId(),
                $this->highLoadBlock->getSaveData()
            );
        }
        if (!$hbRes->isSuccess()) {
            throw new BuilderException($this->highLoadBlock->tableName . ' ' . implode(', ', $hbRes->getErrorMessages()));
        }
        $this->highLoadBlock->setId($hbRes->getId());
    }

    /**
     * @throws BuilderException
     */
    private function commitFields() {
        global $APPLICATION;
        if (!$this->getCurrentHighLoadBlock()->getId()) {
            throw new BuilderException('Set highLoadBlock before');
        }
        $gw = new \CUserTypeEntity();
        foreach ($this->fields as $field) {
            if ($field->getId() > 0) {
                $res = $gw->Update($field->getId(), $field->getSaveData());
            } else {
                $res = $gw->Add(array_merge($field->getSaveData(), array(
                    'ENTITY_ID' => 'HLBLOCK_' . $this->getCurrentHighLoadBlock()->getId()
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
            $values[$key] = $variant->getSaveData();
        }
        if (empty($values)) {
            return;
        }
        if (!$obEnum->SetEnumValues($field->getId(), $values)) {
            throw new BuilderException($APPLICATION->GetException()->GetString());
        }
    }

    /**
     * @param $code
     * @return array
     * @throws BuilderException
     */
    private function findField($code) {
        if (!$this->highLoadBlock) {
            throw new BuilderException('set higloadBlock for continue');
        }
        $field = \CUserTypeEntity::GetList(null, array(
            'FIELD_NAME' => $code,
            'ENTITY_ID' => "HLBLOCK_" . $this->getCurrentHighLoadBlock()->getId(),
        ))->Fetch();

        if (empty($field)) {
            throw new BuilderException('Field for update not found');
        }
        return $field;
    }

}
