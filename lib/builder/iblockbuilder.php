<?php

namespace WS\ReduceMigrations\Builder;

use Bitrix\Main\Application;
use WS\ReduceMigrations\Builder\Entity\Iblock;
use WS\ReduceMigrations\Builder\Entity\Property;

class IblockBuilder {

    public function __construct() {
        \CModule::IncludeModule('iblock');
    }

    /**
     * @param string $iblockType
     * @param string $name
     * @param \Closure $callback
     *
     * @throws BuilderException
     * @return $id
     */
    public function createIblock($iblockType, $name, $callback) {
        $iblock = Iblock::create($iblockType, $name);
        $callback($iblock);
        $result = $this->commitIblock($iblock);

        return $result;
    }


    /**
     * @param integer $id
     * @param \Closure $callback
     *
     * @return array
     * @throws BuilderException
     */
    public function updateIblock($id, $callback) {
        $iblockData = \CIBlock::GetByID($id)->Fetch();
        if (!$iblockData) {
            throw new BuilderException("Iblock `{$id}` not found");
        }
        $iblock = new Iblock();
        $iblock->setId($iblockData['ID']);

        $callback($iblock);

        $result = $this->commitIblock($iblock);

        return $result;
    }

    /**
     * @param Iblock $iblock
     *
     * @return array
     * @throws BuilderException
     */
    private function commitIblock($iblock) {
        $result = array();
        $connection = Application::getConnection();
        try {
            $connection->startTransaction();
            $data = $iblock->getData();
            $cIblock = new \CIBlock();
            if ($iblock->getId() > 0) {
                $isSuccess = $cIblock->Update($iblock->getId(), $data);
                $iblockId = $iblock->getId();
            } else {
                $isSuccess = $cIblock->Add($data);
                $iblockId = $isSuccess;
            }

            if (!$isSuccess) {
                throw new BuilderException($cIblock->LAST_ERROR);
            }

            $iblock->setId($iblockId);
            $result['IBLOCK_ID'] = $iblockId;
            $result['PROPERTIES'] = $this->commitProperties($iblock);
            $connection->commitTransaction();
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
            throw new BuilderException($e->getMessage());
        }

        return $result;
    }

    /**
     * @param Iblock $iblock
     *
     * @return mixed
     * @throws BuilderException
     */
    private function commitProperties($iblock) {
        $result = array();
        foreach ($iblock->getDeleteProperties() as $property) {
            \CIBlockProperty::Delete($property->getId());
        }
        $propertyGateway = new \CIBlockProperty();
        foreach ($iblock->getUpdateProperties() as $property) {
            $isSuccess = $propertyGateway->Update($property->getId(), $property->getData());
            if (!$isSuccess) {
                throw new BuilderException($propertyGateway->LAST_ERROR);
            }
            $this->commitEnum($property);
        }
        foreach ($iblock->getProperties() as $property) {
            $property->setAttribute('IBLOCK_ID', $iblock->getId());
            $propertyId = $propertyGateway->Add($property->getData());
            if (!$propertyId) {
                throw new BuilderException($propertyGateway->LAST_ERROR);
            }
            $result[$property->getName()] = $propertyId;
            $property->setId($propertyId);
            $this->commitEnum($property);
        }

        return $result;
    }

    /**
     * @param Property $property
     * @throws BuilderException
     */
    private function commitEnum($property) {
        $obEnum = new \CIBlockPropertyEnum;
        foreach ($property->getEnumVariants() as $key => $variant) {
            if ($variant->needToDelete() == 'Y' && $variant->getId() > 0) {
                $obEnum->Delete($variant->getId());
                continue;
            }
            if ($variant->getId() > 0) {
                if (!$obEnum->Update($variant->getId(), $variant->getData())) {
                    throw new BuilderException("Failed to update enum '{$variant->getAttribute('VALUE')}'");
                }
                continue;
            }

            if (!$obEnum->Add(array_merge($variant->getData(), array('PROPERTY_ID' => $property->getId())))) {
                throw new BuilderException("Failed to add enum '{$variant->getAttribute('VALUE')}'");
            }
        }

    }

    /**
     * @param $iblockType
     * @param $name
     *
     * @return bool
     * @throws BuilderException
     */
    public function removeIblock($iblockType, $name) {
        $dbRes = \CIBlock::GetList(null, array(
            'NAME' => $name,
            'TYPE' => $iblockType,
        ));
        $count = $dbRes->SelectedRowsCount();
        if ($count == 0) {
            throw new BuilderException('Iblock not found');
        }

        if ($count > 1) {
            throw new BuilderException("There is a {$count} iblock with type={$iblockType} name={$name}");
        }
        $iblock = $dbRes->Fetch();

        return $this->removeIblockById($iblock['ID']);
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws BuilderException
     */
    public function removeIblockById($id) {

        $result = \CIBlock::Delete($id);
        if (!$result) {
            throw new BuilderException("Error occurring delete iblock with id={$id}");
        }
        return true;
    }

}
