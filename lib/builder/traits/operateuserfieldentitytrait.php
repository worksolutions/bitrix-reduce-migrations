<?php
/**
 * Created by RG. <rg.archuser@gmail.com
 * Date: 09.10.19
 */


namespace WS\ReduceMigrations\Builder\Traits;


use WS\ReduceMigrations\Builder\Entity\UserField;
use WS\ReduceMigrations\Builder\BuilderException;


trait OperateUserFieldEntityTrait
{
    /**
     * @var UserField[] $fields
     * @var string $entity_id
     * @throws BuilderException
     */
    private function commitUserFields($fields, $entity_id) {
        global $APPLICATION;

        $gw = new \CUserTypeEntity();
        foreach ($fields as $field) {
            $res = true;
            if ($field->getId() > 0) {
                $field->isDirty() && $res = $gw->Update($field->getId(), $field->getData());
            } else {
                $res = $gw->Add(array_merge($field->getData(), array(
                    'ENTITY_ID' => $entity_id,
                )));
                if ($res) {
                    $field->setId($res);
                }
            }
            if (!$res) {
                throw new BuilderException($APPLICATION->GetException()->GetString());
            }

            $this->commitUserFieldEnum($field);
        }
    }

    /**
     * @param UserField $field
     * @throws BuilderException
     */
    private function commitUserFieldEnum($field) {
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
