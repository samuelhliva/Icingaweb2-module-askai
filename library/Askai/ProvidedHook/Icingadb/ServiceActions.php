<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\ProvidedHook\Icingadb;

use Exception;
use Icinga\Module\Icingadb\Hook\ServiceActionsHook;
use Icinga\Module\Icingadb\Model\Service;
use ipl\Web\Widget\Link;
use ipl\Web\Url;
use Icinga\Web\Session;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForObject(Service $service): array
    {
        try {
            return $this->getThem($service);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get the Service Actions with 'Troubleshoot with AI'
     */
    protected function getThem(Service $service): array
    {
        // Check if service is in OK state - no need for troubleshooting
        if (isset($service->state->soft_state) && $service->state->soft_state == 0) {
            return [];
        }

        // Access the host object via the service's host relation
        $host = $service->host;
        
        if ($host === null) {
            return [];
        }

        // Build a compact payload and store it server-side, pass only a token in the URL
        $payload = [
            'service_name'                  => $service->name ?? null,
            'service_display_name'          => $service->display_name ?? null,
            'service_state'                 => $service->state->soft_state ?? null,
            'service_output'                => $service->state->output ?? null,
            'service_perfdata'              => $service->state->performance_data ?? null,
            'service_check_source'          => $service->state->check_source ?? null,
            'service_acknowledged'          => $service->state->is_acknowledged ?? null,
            'service_in_downtime'           => $service->state->in_downtime ?? null,
            'service_notifications_enabled' => $service->state->notifications_enabled ?? null,
            'service_check_command'         => $service->checkcommand_name ?? null,
            
            'host_name'                     => $host->name ?? null,
            'host_address'                  => $host->address ?? null,
            'host_state'                    => $host->state->soft_state ?? null,
            'host_output'                   => $host->state->output ?? null,
            'host_perfdata'                 => $host->state->performance_data ?? null,
            'host_check_source'             => $host->state->check_source ?? null,
            'host_acknowledged'             => $host->state->is_acknowledged ?? null,
            'host_in_downtime'              => $host->state->in_downtime ?? null,
            'host_notifications_enabled'    => $host->state->notifications_enabled ?? null,
            'host_is_reachable'             => $host->state->is_reachable ?? null,
            'check_command'                 => $host->checkcommand_name ?? null,
            'host_os'                       => $host->customvars["host_os"] ?? null,
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
        
        // Persist session changes to ensure data is available in the next request
        Session::getSession()->write();

        // Return array with Link object
        return [
            new Link(
                t('Troubleshoot with AI'),
                Url::fromPath('askai/index/show', ['token' => $token]),
                ['target' => '_next', 'icon' => 'robot']
            )
        ];
    }
}