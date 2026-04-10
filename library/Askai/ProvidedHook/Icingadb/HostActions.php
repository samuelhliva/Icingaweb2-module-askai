<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\ProvidedHook\Icingadb;

use Exception;
use Icinga\Module\Icingadb\Hook\HostActionsHook;
use Icinga\Module\Icingadb\Model\Host;
use ipl\Web\Widget\Link;
use ipl\Web\Url;
use Icinga\Web\Session;

class HostActions extends HostActionsHook
{
    public function getActionsForObject(Host $host): array
    {
        try {
            return $this->getThem($host);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get the Host Actions with 'Troubleshoot with AI'
     */
    protected function getThem(Host $host): array
    {
        // Check if host is in OK state - no need for troubleshooting
        if (isset($host->state->soft_state) && $host->state->soft_state == 0) {
            return [];
        }
            
        // Build a compact payload and store it server-side, pass only a token in the URL
        $payload = [
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