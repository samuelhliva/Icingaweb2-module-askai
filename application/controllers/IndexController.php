<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\Controllers;

use Icinga\Web\Controller;
use Icinga\Application\Config;
use Icinga\Data\ConfigObject;
use Icinga\Web\Session;

class IndexController extends Controller
{
    /** @var string Instruction set for the LLM */
    protected $instructions;

    /** @var ConfigObject Module configuration section */
    protected $config;

    /** @var string URL to establish a connection with the LLM */
    protected $apiUrl;

    /** @var string Authentication key for the REST API communication */
    protected $apiKey;

    /** @var string LLM Model to be used */
    protected $model;

    /** @var bool Allow the debug print */
    protected $debug;

    /** @var int Sets the HTTP request timeout */
    protected $timeout = 60;

    public function init(): void
    {
        $this->assertPermission('askai/index');
        // Load the configuration file
        $this->config = $this->getConfig();
        if ($this->config === null) {
            throw new \Exception('Module configuration not found. Please configure the askai module.');
        }

        // Initialize instructions with default or custom
        if ($this->config->get('custom_instructions')) {
            $this->instructions = $this->config->get('custom_instructions_content', $this->getDefaultInstructions());
        } else {
            $this->instructions = $this->getDefaultInstructions();
        }

        // Setup the API communication properties
        $this->apiUrl   = $this->config->get('endpoint');
        $this->apiKey   = $this->config->get('apikey');
        $this->model    = $this->config->get('model');
        $this->debug    = $this->config->get('enable_debug', false);

        // Error handling
        if ($this->apiUrl === null) {
            throw new \Exception('API Endpoint is not configured. Please setup in the module configuration.');
        }

        if ($this->apiKey === null) {
            throw new \Exception('API Key is not configured. Please setup in the module configuration.');
        }

        if ($this->model === null) {
            throw new \Exception('AI Model is not configured. Please setup in the module configuration.');
        }

        if ($this->model === 'custom') {
            $this->model = $this->config->get('custom_model');
        }

        // Pass interface's tabs to the View model
        $this->view->tabs = $this->getTabs()->add('askai', [
            'title' => $this->translate('Ask AI'),
            'label' => $this->translate('Ask AI'),
            'url'   => $this->getRequest()->getUrl()
        ])->activate('askai');
    }

    public function showAction(): void
    {
        // Prefer tokenized payload passed via session to avoid long/sensitive query strings
        $payload = null;
        $token = $this->params->get('token');
        
        if (! $token) {
            throw new \Exception(
                'This page cannot be accessed directly. Please use the "Troubleshoot with AI" action from a host or service details page. (Token not found)'
            );
        }

        $session = Session::getSession()->getNamespace('askai');
        $payloads = $session->get('payloads', []);

        if (isset($payloads[$token])) {
            $entry = $payloads[$token];
            // Remove expired entries
            if (! empty($entry['exp']) && $entry['exp'] < time()) {
                unset($payloads[$token]);
            } else {
                $payload = $entry['data'] ?? null;
                // One-time use: drop immediately for safety
                unset($payloads[$token]);
            }
        } else {
            throw new \Exception(
                'Failed to retrieve the payload information. Please, reload the Host/Service tab and try again.'
            );
        }
        
        // Opportunistic cleanup of any other expired payloads
        foreach ($payloads as $k => $v) {
            if (! empty($v['exp']) && $v['exp'] < time()) {
                unset($payloads[$k]);
            }
        }
        
        // Save cleaned payloads back to session
        $session->set('payloads', $payloads);

        // Set parameters directly from payload
        $this->parameters = $payload;

        // Pre-craft the AI prompt
        $prompt = "Troubleshoot the issue based on the provided information below: \n" . 
        json_encode($payload);

        // Execute the function to get the AI response
        $response = $this->sendToAi($prompt);

        // Pass interface-related variables
        $this->view->debug          = $this->debug;
        $this->view->instructions   = $this->instructions;
        $this->view->prompt         = $prompt;
        $this->view->model          = $this->model;
        $this->view->response       = $response;
    }

    private function sendToAi(string $prompt): string
    {
        // HTTP Data Payload
        $data = [
            "model" => $this->model,
            "messages" => [
                [
                    "role" => "system",
                    "content" => $this->instructions
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ]
        ];

        // HTTP Header + Payload
        $opts = [
            "http" => [
                "method" => "POST",
                "header" => [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $this->apiKey
                ],
                "content" => json_encode($data),
                "timeout" => $this->timeout
            ]
        ];

        $context = stream_context_create($opts);
        $result = @file_get_contents($this->apiUrl, false, $context);

        if ($result === false) {
            return "Error: Failed to contact AI API. Please check your configuration and network connectivity.";
        }

        $json = json_decode($result, true);

        // Extract only the LLM response.
        return $json['choices'][0]['message']['content'] ?? "No AI response";
    }

    private function getConfig(): mixed
    {
        $moduleConfig = Config::module('askai');

        if ($moduleConfig->isEmpty() === false) {
            return $moduleConfig->getSection('ai');
        }

        return null;
    }

    private function getDefaultInstructions(): string
    {
        return "You are an expert Icinga monitoring troubleshooting assistant with deep knowledge of distributed monitoring architectures, service checks, and infrastructure diagnostics.

        CONTEXT:
        - You are analyzing monitoring data from Icinga Web 2
        - All configurations are managed through Icinga Director module
        - You do NOT have access to configuration files on the filesystem

        YOUR ROLE:
        Provide precise, actionable troubleshooting steps based on the service state and output provided. Focus on identifying root causes and suggesting practical solutions.

        RESPONSE GUIDELINES:
        1. Format: Write in plain text only - NO markdown, NO formatting symbols
        2. Structure: Provide step-by-step troubleshooting in numbered format (1. 2. 3.)
        3. Clarity: Be concise but thorough - each step should be actionable
        4. Scope: Only suggest actions that can be performed through:
        - Icinga Director (configuration changes)
        - Service/host checks (re-checks, acknowledgments)
        - Network/infrastructure investigation
        - Application-level debugging

        ANALYSIS APPROACH:
        1. Interpret the service state (OK=0, WARNING=1, CRITICAL=2, UNKNOWN=3)
        2. Analyze the output message for error patterns, error codes, timeouts, connection issues
        3. Consider distributed monitoring architecture - could this be a satellite communication issue?
        4. Identify whether the issue is: network-related, service-related, configuration-related, or resource-related
        5. Suggest immediate actions first, then deeper investigation steps

        COMMON SCENARIOS TO RECOGNIZE:
        - Connection timeouts: Check network connectivity, firewall rules, service availability
        - Authentication failures: Verify credentials, certificates, API keys in Icinga Director
        - Permission denied: Check user permissions, file ownership (if applicable to service)
        - Resource exhaustion: Investigate CPU, memory, disk space on target system
        - Service not responding: Verify service is running, check logs, restart if needed
        - Check plugin errors: Review plugin syntax, parameters, and thresholds in Director
        - Satellite communication issues: Check master-satellite connectivity, zone configuration

        PROHIBITED ACTIONS:
        - Do NOT suggest editing configuration files directly (use Icinga Director instead)
        - Do NOT provide generic advice - be specific to the error message
        - Do NOT suggest actions requiring filesystem access

        OUTPUT FORMAT:
        1. First step describing immediate action
        2. Second step for verification
        3. Third step for deeper investigation
        4. Additional steps as needed based on complexity

        Begin each troubleshooting response directly with step 1.";
    }
}
