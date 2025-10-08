<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\ProvidedHook\Monitoring;

use Exception;
use Icinga\Module\Monitoring\Hook\ServiceActionsHook;
use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Web\Url;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForService(MonitoredObject $service)
    {
        try {
            return $this->getThem($service);
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * Get the Service Actions with 'Troubleshoot with AI'
     */
    protected function getThem(MonitoredObject $service)
    {
        $actions = array();
        $service->fetch();
        $actions['Troubleshoot with AI'] = Url::fromPath('askai/index/show')->setParams([
            'service' => $service->service,
            'state' => $service->service_state,
            'output' => $service->service_output
        ]);
        return $actions;
    }
}