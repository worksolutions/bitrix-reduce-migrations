<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace WS\ReduceMigrations\Processes;

use WS\ReduceMigrations\ApplyResult;
use WS\ReduceMigrations\ChangeDataCollector\CollectorFix;
use WS\ReduceMigrations\Entities\AppliedChangesLogModel;
use WS\ReduceMigrations\Module;
use WS\ReduceMigrations\SubjectHandlers\BaseSubjectHandler;

abstract class BaseProcess {

    public function getLocalization() {
        return Module::getInstance()->getLocalization('processes');
    }

    static public function className() {
        return get_called_class();
    }

    abstract public function getName();

    /**
     * @param BaseSubjectHandler $subjectHandler
     * @param CollectorFix $fix
     * @param AppliedChangesLogModel $log
     * @return ApplyResult
     */
    abstract public function update(BaseSubjectHandler $subjectHandler, CollectorFix $fix, AppliedChangesLogModel $log);

    /**
     * @param BaseSubjectHandler $subjectHandler
     * @param AppliedChangesLogModel $log
     * @return ApplyResult
     */
    abstract public function rollback(BaseSubjectHandler $subjectHandler, AppliedChangesLogModel $log);
}
