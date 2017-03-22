<?php

namespace WS\ReduceMigrations\Builder;

use WS\ReduceMigrations\Builder\Entity\Agent;

class AgentBuilder {
    /**
     * @var Agent
     */
    private $agent;

    public function reset() {
        $this->agent = null;
    }

    /**
     * @param $callback
     * @return Agent
     * @throws BuilderException
     */
    public function addAgent($callback) {
        if ($this->agent) {
            throw new BuilderException('reset builder data for continue');
        }
        $this->agent = new Agent($callback);
        return $this->agent;
    }

    /**
     * @param $callback
     * @return Agent
     * @throws BuilderException
     */
    public function getAgent($callback) {
        if ($this->agent) {
            throw new BuilderException('reset builder data for continue');
        }
        $data = $this->findAgent($callback);
        $this->agent = new Agent($callback, $data);
        return $this->agent;
    }

    /**
     * @throws BuilderException
     */
    public function commit() {
        global $DB, $APPLICATION;
        $DB->StartTransaction();
        try {
            $agent = $this->agent;
            if ($agent->getId() > 0) {
                $res = \CAgent::Update($agent->getId(), $agent->getSaveData());
                if (!$res) {
                    throw new BuilderException("Agent wasn't updated");
                }
            } else {
                $res = \CAgent::AddAgent(
                    $agent->callback,
                    $agent->module,
                    $agent->isPeriod,
                    $agent->interval,
                    '',
                    $agent->active,
                    $agent->nextExec,
                    $agent->sort,
                    $agent->userId
                );
                if (!$res) {
                    throw new BuilderException("Agent wasn't created: " . $APPLICATION->GetException()->GetString());
                }
                $agent->setId($res);
            }

        } catch (BuilderException $e) {
            $DB->Rollback();
            throw new BuilderException($e->getMessage());
        }
        $DB->Commit();
    }

    /**
     * @return Agent
     */
    public function getCurrentAgent() {
        return $this->agent;
    }

    /**
     * @param $callback
     * @return array
     * @throws BuilderException
     */
    private function findAgent($callback) {
        $agent = \CAgent::GetList(null, array(
            'NAME' => $callback,
        ))->Fetch();
        if (empty($agent)) {
            throw new BuilderException("Agent {$callback} not found");
        }
        return $agent;
    }

}
