<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Processes;


use WS\ReduceMigrations\ChangeDataCollector\CollectorFix;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Module;
use WS\ReduceMigrations\SubjectHandlers\BaseSubjectHandler;

class UpdateProcess extends BaseProcess {

    private $_beforeChangesSnapshots = array();

    public function update(BaseSubjectHandler $subjectHandler, CollectorFix $fix, AppliedChangesLogModel $log) {
        $data = $fix->getUpdateData();
        $id = $subjectHandler->getIdBySnapshot($data);
        $originalData = $subjectHandler->getSnapshot($id, $fix->getDbVersion());
        $result = $subjectHandler->applyChanges($data, $fix->getDbVersion());

        $log->description = $fix->getName();
        $log->originalData = $originalData;
        $log->updateData = $data;
        return $result;
    }

    public function rollback(BaseSubjectHandler $subjectHandler, AppliedChangesLogModel $log) {
        return $subjectHandler->applySnapshot($log->originalData);
    }

    public function beforeChange(BaseSubjectHandler $subjectHandler, $data) {
        $id = $subjectHandler->getIdByChangeMethod(Module::FIX_CHANGES_BEFORE_CHANGE_KEY, $data);
        $this->_beforeChangesSnapshots[$id] = $snapshot = $subjectHandler->getSnapshot($id);
    }

    public function afterChange(BaseSubjectHandler $subjectHandler, CollectorFix $fix, $data) {
        $id = $subjectHandler->getIdByChangeMethod(Module::FIX_CHANGES_AFTER_CHANGE_KEY, $data);
        $originalData = $this->_beforeChangesSnapshots[$id];
        $actualData = $subjectHandler->getSnapshot($id);
        $data = $subjectHandler->analysisOfChanges($actualData, $this->_beforeChangesSnapshots[$id]);
        $data && $fix
            ->setOriginalData($originalData)
            ->setUpdateData($data);
        return true;
    }

    public function getName() {
        return $this->getLocalization()->getDataByPath('update');
    }
}