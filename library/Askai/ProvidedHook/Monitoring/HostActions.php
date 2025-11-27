<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\ProvidedHook\Monitoring;

use Exception;
use Icinga\Module\Monitoring\Hook\HostActionsHook;
use Icinga\Module\Monitoring\Object\MonitoredObject;
use Icinga\Web\Url;
use Icinga\Web\Session;

class HostActions extends HostActionsHook
{
    public function getActionsForHost(MonitoredObject $host): array
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
    protected function getThem(MonitoredObject $host): array
    {
        $actions = array();
        $host->fetch();
        if ($host->host_state == 0)
            return $actions;

        // Build a compact payload and store it server-side, pass only a token in the URL
        $payload = [
            'host_name'                     => $host->host_name,
            'host_address'                  => $host->host_address,
            'host_state'                    => $host->host_state,
            'host_output'                   => $host->host_output,
            'host_perfdata'                 => $host->host_perfdata,
            'host_check_source'             => $host->host_check_source,
            'host_acknowledged'             => $host->host_acknowledged,
            'host_in_downtime'              => $host->host_in_downtime,
            'host_notifications_enabled'    => $host->host_notifications_enabled,
            'host_is_reachable'             => $host->host_is_reachable,
            
            'check_command'                 => $host->host_check_command
        ];

        // Generate a random token and store payload in session with a short TTL marker
        $token = bin2hex(random_bytes(16));
        
        $session = Session::getSession()->getNamespace('askai');
        $payloads = $session->get('payloads', []);
        $payloads[$token] = [
            'data' => $payload,
            'exp'  => time() + 600, // expire after 10 minutes
        ];
        $session->set('payloads', $payloads);

        $actions['Troubleshoot with AI'] = Url::fromPath('askai/index/show')->setParams([
            'token' => $token,
        ]);

        return $actions;
    }
}