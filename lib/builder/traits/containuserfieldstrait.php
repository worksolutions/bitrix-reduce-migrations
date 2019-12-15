<?php
/**
 * Created by RG. <rg.archuser@gmail.com
 * Date: 09.10.19
 */


namespace WS\ReduceMigrations\Builder\Traits;


use WS\ReduceMigrations\Builder\Entity\UserField;
use WS\ReduceMigrations\Builder\BuilderException;


trait ContainUserFieldsTrait
{
    /** @var UserField[] */
    private $user_fields;

    /**
     * @param $code
     * @return UserField
     */
    private function addUserField($code) {
        $field = new UserField($code);
        $this->user_fields[] = $field;

        return $field;
    }

    /**
     * @param string $code
     * @param string $entity_id
     * @return UserField
     * @throws BuilderException
     */
    private function updateUserField($code, $entity_id) {
        $data = $this->findUserField($code, $entity_id);
        $field = new UserField($code);
        $field->setId($data['ID']);
        $field->markClean();
        $this->user_fields[] = $field;

        return $field;
    }

    /**
     * @param string $code
     * @param string $entity_id
     * @return array
     * @throws BuilderException
     */
    private function findUserField($code, $entity_id) {
        $field = \CUserTypeEntity::GetList(null, array(
            'FIELD_NAME' => $code,
            'ENTITY_ID' => $entity_id,
        ))->Fetch();

        if (empty($field)) {
            throw new BuilderException("Field for `$code` not found");
        }

        return $field;
    }

    /**
     * @return UserField[]
     */
    private function getUserFields() {
        return $this->user_fields;
    }
}
