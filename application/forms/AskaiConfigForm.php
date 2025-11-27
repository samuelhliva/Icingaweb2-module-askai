<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\Forms;

use Icinga\Forms\ConfigForm;
use Icinga\Module\Askai\Web\Form\Validator\ExternalUrlValidator;

/**
 * Configuration form for AskAI module
 * 
 * Allows administrators to configure AI API settings including
 * endpoint, authentication, model selection, and custom instructions.
 */
class AskaiConfigForm extends ConfigForm
{

    /**
     * Initialize the form
     */
    public function init(): void
    {
        $this->setName('askai_config');
        $this->setSubmitLabel($this->translate('Save Changes'));
    }

    /**
     * Create form elements
     * 
     * @param array $formData Current form data for conditional fields
     */
    public function createElements(array $formData): void
    {
        $this->addElement('text', 'ai_endpoint', [
            'label'         => $this->translate('API Endpoint'),
            'description'   => $this->translate('Full URL to the AI API endpoint (e.g., https://openrouter.ai/api/v1/chat/completions)'),
            'required'      => true,
            'validators'    => [
                new ExternalUrlValidator()
            ]
        ]);

        $this->addElement('password', 'ai_apikey', [
            'label'             => $this->translate('API Key'),
            'description'       => $this->translate('Authentication key for the AI API'),
            'required'          => true,
            'preserveDefault'   => true,
            'renderPassword'    => true
        ]);

        $this->addElement('select', 'ai_model', [
            'label'           => $this->translate('AI Model'),
            'description'     => $this->translate('Select the AI model to use for troubleshooting. There are pre-defined free models provided by the OpenRouter.'),
            'required'        => true,
            'multiOptions'    => [
                'x-ai/grok-4.1-fast:free'           => 'X/Grok 4.1 Fast',
                'openai/gpt-oss-20b:free'           => 'OpenAI/GPT-OSS 20b',
                'tngtech/tng-r1t-chimera:free'      => 'TNG/R1T Chimera',
                'custom'                            => 'Custom Model'
            ],
            'multiple'        => false,
            'autosubmit'      => true
        ]);

        $customModelEnabled = isset($formData['ai_model']) && $formData['ai_model'] == 'custom';

        if ($customModelEnabled) {
            $this->addElement('text', 'ai_custom_model', [
                'label'         => $this->translate('Custom Model'),
                'description'   => $this->translate('Enter the custom model'),
                'required'      => true
            ]);
        }

        $this->addElement('checkbox', 'ai_custom_instructions', [
            'label'         => $this->translate('Enable Custom Instructions'),
            'description'   => $this->translate('Use custom system instructions instead of the default.'),
            'autosubmit'    => true,
            'default'       => false
        ]);

        $customInstructionsEnabled = isset($formData['ai_custom_instructions']) && $formData['ai_custom_instructions'];
        
        if ($customInstructionsEnabled) {
            $this->addElement('textarea', 'ai_custom_instructions_content', [
                'label'         => $this->translate('Custom Instructions'),
                'description'   => $this->translate('Enter the System prompt for the AI assistant'),
                'rows'          => 5,
                'required'      => false,
                'style'        => 'resize: none'
            ]);
        }

        $this->addElement('checkbox', 'ai_enable_debug', [
            'label'         => $this->translate('Enable Debug Mode'),
            'description'   => $this->translate('Display diagnostic information including instructions, prompt, and model details'),
            'default'       => false
        ]);
    }
}