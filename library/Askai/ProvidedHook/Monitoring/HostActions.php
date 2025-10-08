<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\ProvidedHook\Monitoring;

use Exception;
use Icinga\Module\Monitoring\Hook\HostActionsHook;
use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Web\Url;

class HostActions extends HostActionsHook
{
    public function getActionsForHost(MonitoredObject $host)
    {
        try {
            return $this->getThem($host);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Get the Host Actions with 'Troubleshoot with AI'
     */
    protected function getThem(MonitoredObject $host)
    {
        $actions = array();
        $host->fetch();
        $actions['Troubleshoot with AI'] = Url::fromPath('askai/index/show')->setParams([
            'service' => $host->host_check_command,
            'state' => $host->host_state,
            'output' => $host->host_output
        ]);
        return $actions;
    }
}
