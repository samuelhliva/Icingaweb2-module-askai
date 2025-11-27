<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\ProvidedHook\Monitoring;

use Exception;
use Icinga\Module\Monitoring\Hook\ServiceActionsHook;
use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Web\Url;
use Icinga\Web\Session;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForService(MonitoredObject $service): array
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
    protected function getThem(MonitoredObject $service): array
    {
        $actions = array();
        $service->fetch();
        if ($service->service_state == 0)
            return $actions;

        // Build a compact payload and store server-side, pass only a short token in the URL
        $payload = [
            'host_name'                     => $service->host_name,
            'host_address'                  => $service->host_address,
            'host_state'                    => $service->host_state,
            'host_acknowledged'             => $service->host_acknowledged,
            'host_in_downtime'              => $service->host_in_downtime,
            'host_notifications_enabled'    => $service->host_notifications_enabled,

            'service_acknowledged'          => $service->service_acknowledged,
            'service_check_source'          => $service->service_check_source,
            'service_in_downtime'           => $service->service_in_downtime,
            'service_notes_url'             => $service->service_notes_url,
            'service_output'                => $service->service_output,
            'service_state'                 => $service->service_state,
            
            'check_command'                 => $service->service_check_command
        ];

        $token = bin2hex(random_bytes(16));
        
        $session = Session::getSession()->getNamespace('askai');
        $payloads = $session->get('payloads', []);
        $payloads[$token] = [
            'data' => $payload,
            'exp'  => time() + 600, // 10 minutes TTL
        ];
        $session->set('payloads', $payloads);

        $actions['Troubleshoot with AI'] = Url::fromPath('askai/index/show')->setParams([
            'token' => $token,
        ]);
        return $actions;
    }
}