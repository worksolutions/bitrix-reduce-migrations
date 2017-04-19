<?php

namespace WS\ReduceMigrations\Builder;

use WS\ReduceMigrations\Builder\Entity\Agent;

class AgentBuilder {
    /**
     * @var Agent
     */
    private $agent;

    /**
     * @param string $agentFunction
     * @param \Closure $callback
     * @return Agent
     * @throws BuilderException
     */
    public function addAgent($agentFunction, $callback) {
        $agent = Agent::create($agentFunction);
        $callback($agent);
        $this->commit($agent);
        return $agent;
    }

    /**
     * @param string $agentFunction
     * @param \Closure $callback
     * @return Agent
     * @throws BuilderException
     */
    public function updateAgent($agentFunction, $callback) {
        $data = $this->findAgent($agentFunction);
        $agent = new Agent($agentFunction);
        $agent->setId($data['ID']);
        $agent->markClean();
        $callback($agent);
        $this->commit($agent);
        return $agent;
    }

    /**
     * @var Agent $agent
     * @throws BuilderException
     */
    private function commit($agent) {
        global $DB, $APPLICATION;
        $DB->StartTransaction();
        $gw = new \CAgent();
        try {
            if ($agent->getId() > 0) {
                if ($agent->isDirty()) {
                    $res = $gw->Update($agent->getId(), $agent->getData());
                    if (!$res) {
                        throw new BuilderException("Agent wasn't updated");
                    }
                }
            } else {
                $res = $gw->AddAgent(
                    $agent->getAttribute('NAME'),
                    $agent->getAttribute('MODULE_ID'),
                    $agent->getAttribute('IS_PERIOD'),
                    $agent->getAttribute('AGENT_INTERVAL'),
                    '',//bitrix doesn't use this parameter
                    $agent->getAttribute('ACTIVE'),
                    $agent->getAttribute('NEXT_EXEC'),
                    $agent->getAttribute('SORT'),
                    $agent->getAttribute('USER_ID')
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
