<?php

namespace WS\ReduceMigrations\Builder;

use WS\ReduceMigrations\Builder\Entity\Iblock;
use WS\ReduceMigrations\Builder\Entity\IblockSection;
use WS\ReduceMigrations\Builder\Entity\IblockType;
use WS\ReduceMigrations\Builder\Entity\Property;

class IblockBuilder {
    /** @var  Iblock */
    private $iblock;
    /** @var  Property[] */
    private $properties;
    /** @var  IblockType */
    private $iblockType;
    /** @var  IblockSection[] */
    private $sections;

    public function __construct() {
        \CModule::IncludeModule('iblock');
    }

    public function reset() {
        $this->iblock = null;
        $this->iblockType = null;
        $this->properties = array();
        $this->sections = array();
    }

    /**
     * @param $type
     * @return IblockType
     * @throws BuilderException
     */
    public function addIblockType($type) {
        if ($this->iblockType) {
            throw new BuilderException('IblockType already set');
        }
        $this->iblockType = new IblockType($type);
        return $this->iblockType;
    }

    /**
     * @param $type
     * @return IblockType
     * @throws BuilderException
     */
    public function getIblockType($type) {
        if ($this->iblockType) {
            throw new BuilderException('IblockType already set');
        }
        if (!$data = $this->findIblockType($type)) {
            throw new BuilderException("Can't find iblockType with type {$type}");
        }
        $this->iblockType = new IblockType($type, $data);
        return $this->iblockType;
    }

    /**
     * @param $name
     * @return Iblock
     * @throws \Exception
     */
    public function addIblock($name) {
        if ($this->iblock) {
            throw new BuilderException('Iblock already set');
        }
        $this->iblock = new Iblock($name);
        return $this->iblock;
    }

    /**
     * @param $name
     * @return Iblock
     * @throws \Exception
     */
    public function getIblock($name) {
        if ($this->iblock) {
            throw new BuilderException('Iblock already set');
        }
        if (!$data = $this->findIblock($name)) {
            throw new BuilderException("Can't find iblock with name {$name}");
        }
        $this->iblock = new Iblock($name, $data);
        return $this->iblock;
    }

    /**
     * @param $name
     * @return Property
     */
    public function addProperty($name) {
        $prop = new Property($name);
        $this->properties[] = $prop;
        return $prop;
    }

    /**
     * @param $name
     * @return Property
     * @throws BuilderException
     */
    public function getProperty($name) {
        if (!$this->iblock->getId()) {
            throw new BuilderException("Iblock not initialized");
        }
        if (!$data = $this->findProperty($name)) {
            throw new BuilderException("Can't find property with name {$name}");
        }
        $prop = new Property($name, $data);
        $this->properties[] = $prop;
        return $prop;
    }

    /**
     * @param $name
     * @return IblockSection
     * @throws BuilderException
     */
    public function addSection($name) {
        if (!$this->iblock) {
            throw new BuilderException("Iblock not initialized");
        }
        $args = func_get_args();
        if (empty($args)) {
            throw new BuilderException("Missing arguments");
        }
        $section = new IblockSection($name);
        array_shift($args);
        $root = $section;
        foreach ($args as $sectionName) {
            $root = $root->addChild($sectionName);
        }
        $this->sections[] = $section;
        return $section;
    }

    /**
     * @param $name
     * @return IblockSection
     * @throws BuilderException
     */
    public function getSection($name) {
        $args = func_get_args();
        if (empty($args)) {
            throw new BuilderException("Missing arguments");
        }
        if (!$data = $this->findSection($name)) {
            throw new BuilderException("Can't find section with name {$name}");
        }
        $section = new IblockSection($name, $data);

        array_shift($args);
        $root = $section;
        foreach ($args as $sectionName) {
            $root = $root->getChild($sectionName);
        }
        $this->sections[] = $section;
        return $section;
    }

    /**
     * @return Iblock
     */
    public function getCurrentIblock() {
        return $this->iblock;
    }

    /**
     * @throws BuilderException
     */
    public function commit() {
        global $DB;
        $DB->StartTransaction();
        try {
            $this->commitIblockType();

            $this->commitIblock();

            $this->commitProperties();

            $this->commitSections();

        } catch (\Exception $e) {
            $DB->Rollback();
            throw new BuilderException($e->getMessage());
        }
        $DB->Commit();
    }

    /**
     * @throws BuilderException
     */
    private function commitIblockType() {
        if (!$this->iblockType) {
            return;
        }
        $ibType = new \CIBlockType();
        if ($this->iblockType->iblockTypeId) {
            if (!$ibType->Update($this->iblockType->id, $this->iblockType->getSaveData())) {
                throw new BuilderException('IblockType was not updated. ' . $ibType->LAST_ERROR);
            }
        } else {
            $id = $ibType->Add($this->iblockType->getSaveData());
            $this->iblockType->setId($id);
        }
        if (!$this->iblockType->getId()) {
            throw new BuilderException('IblockType was not created. ' . $ibType->LAST_ERROR);
        }
    }

    /**
     * @throws BuilderException
     */
    private function commitIblock() {
        if (!$this->iblock) {
            return;
        }
        $ib = new \CIBlock();
        if ($this->iblock->getId()) {
            if (!$ib->Update($this->iblock->id, $this->iblock->getSaveData())) {
                throw new BuilderException('Iblock was not updated. ' . $ib->LAST_ERROR);
            }
        } else {
            $iblockId = $ib->Add($this->iblock->getSaveData());
            $this->iblock->setId($iblockId);
        }
        if (!$this->iblock->getId()) {
            throw new BuilderException('Iblock was not created. ' . $ib->LAST_ERROR);
        }
    }

    private function commitSections() {
        if (!$this->sections) {
            return;
        }
        if (!$this->iblock->getId()) {
            throw new BuilderException("Iblock didn't set");
        }
        $gw = new \CIBlockSection();
        foreach ($this->sections as $section) {
            $this->saveSectionRecursive($section, false, $gw);
        }
    }

    /**
     * @param IblockSection $section
     * @param $parentSectionId
     * @param \CIBlockSection $gw
     * @throws BuilderException
     */
    private function saveSectionRecursive($section, $parentSectionId, $gw) {
        $data = $section->getSaveData();
        $data['IBLOCK_SECTION_ID'] = $parentSectionId;
        $data['IBLOCK_ID'] = $this->iblock->getId();
        if ($section->getId() > 0) {
            $res = $gw->Update($section->getId(), $data);
            if (!$res) {
                throw new BuilderException("Section '{$section->name}' wasn't updated. " . $gw->LAST_ERROR);
            }
        } else {
            $res = $gw->Add($data);
            if (!$res) {
                throw new BuilderException("Section '{$section->name}' wasn't created. " . $gw->LAST_ERROR);
            }
            $section->setId($res);
        }
        foreach ($section->getChildren() as $child) {
            $this->saveSectionRecursive($child, $section->getId(), $gw);
        }
    }

    /**
     * @throws BuilderException
     */
    private function commitProperties() {
        $propertyGw = new \CIBlockProperty();
        if (empty($this->properties)) {
            return;
        }
        if (!$this->iblock->getId()) {
            throw new BuilderException("Iblock didn't set");
        }

        foreach ($this->properties as $property) {
            if ($property->getId() > 0) {
                continue;
            }
            $id = $propertyGw->Add(array_merge($property->getSaveData(), array('IBLOCK_ID' => $this->iblock->getId())));
            if (!$id) {
                throw new BuilderException("Property was {$property->name} not created. " . $propertyGw->LAST_ERROR);
            }
            $property->setId($id);

            $this->commitEnum($property);
        }

        foreach ($this->properties as $property) {
            if (!$property->getId()) {
                continue;
            }
            $id = $propertyGw->Update($property->id, array_merge($property->getSaveData(), array('IBLOCK_ID' => $this->iblock->getId())));
            if (!$id) {
                throw new BuilderException("Property was {$property->name} not updated. " . $propertyGw->LAST_ERROR);
            }
            $this->commitEnum($property);
        }
    }

    /**
     * @param Property $property
     * @throws BuilderException
     */
    private function commitEnum($property) {
        $obEnum = new \CIBlockPropertyEnum;
        foreach ($property->getEnumVariants() as $key => $variant) {
            if ($variant->del == 'Y' && $variant->getId() > 0) {
                $obEnum->Delete($variant->getId());
                continue;
            }
            if ($variant->getId() > 0) {
                if (!$obEnum->Update($variant->getId(), $variant->getSaveData())) {
                    throw new BuilderException("Failed to update enum '{$variant->value}'");
                }
                continue;
            }

            if (!$obEnum->Add(array_merge($variant->getSaveData(), array('PROPERTY_ID' => $property->getId())))) {
                throw new BuilderException("Failed to add enum '{$variant->value}'");
            }
        }

    }

    /**
     * @param $type
     * @return array
     */
    private function findIblockType($type) {
        return \CIBlockType::GetList(null, array(
            'ID' => $type
        ))->Fetch();
    }

    /**
     * @param $name
     * @return array
     */
    private function findIblock($name) {
        return \CIBlock::GetList(null, array(
            '=NAME' => $name
        ))->Fetch();
    }

    private function findProperty($name) {
        return \CIBlockProperty::GetList(null, array(
            'NAME' => $name,
            'IBLOCK_ID' => $this->iblock->getId()
        ))->Fetch();
    }

    private function findSection($name) {
        return \CIBlockSection::GetList(null, array(
            '=NAME' => $name,
            'SECTION_ID' => false,
            'IBLOCK_ID' => $this->iblock->getId()
        ))->Fetch();
    }
}
