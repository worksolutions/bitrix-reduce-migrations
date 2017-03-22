<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Processes;


use WS\ReduceMigrations\ChangeDataCollector\CollectorFix;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Module;
use WS\ReduceMigrations\SubjectHandlers\BaseSubjectHandler;
use WS\ReduceMigrations\SubjectHandlers\IblockSectionHandler;

class DeleteProcess extends BaseProcess {
    private $_beforeChangesSnapshots = array();

    public function update(BaseSubjectHandler $subjectHandler, CollectorFix $fix, AppliedChangesLogModel $log) {
        $id = $fix->getUpdateData();
        $originalData = $subjectHandler->getSnapshot($id, $fix->getDbVersion());

        $result = $subjectHandler->delete($id, $fix->getDbVersion());

        $log->description = $fix->getName();
        $log->originalData = $originalData;
        $log->updateData = $id;
        return $result;
    }

    public function rollback(BaseSubjectHandler $subjectHandler, AppliedChangesLogModel $log) {
        return $subjectHandler->applySnapshot($log->originalData);
    }

    public function beforeChange(BaseSubjectHandler $subjectHandler, $data) {
        $id = $subjectHandler->getIdByChangeMethod(Module::FIX_CHANGES_BEFORE_DELETE_KEY, $data);
        $this->_beforeChangesSnapshots[$id] = $snapshot = $subjectHandler->getSnapshot($id);
    }

    public function afterChange(BaseSubjectHandler $subjectHandler, CollectorFix $fix, $data) {
        $id = $subjectHandler->getIdByChangeMethod(Module::FIX_CHANGES_AFTER_DELETE_KEY, $data);
        $fix
            ->setOriginalData($this->_beforeChangesSnapshots[$id])
            ->setUpdateData($id);
        $subjectHandler->registerDelete($id);
        return true;
    }

    public function getName() {
        return $this->getLocalization()->getDataByPath('delete');
    }
}