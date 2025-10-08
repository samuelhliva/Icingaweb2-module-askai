<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

use Icinga\Module\Askai\Auth\Permission;

$this->providePermission(Permission::ALL_PERMISSIONS, $this->translate('Allow unrestricted access to AskAI Module'));
$this->providePermission(Permission::CONFIGURATION, $this->translate('Allow configuration access to AskAI Module'));
$this->providePermission(Permission::ACTION, $this->translate('Allow AI Invoke access to AskAI Module'));

$this->provideConfigTab(
    'configuration',
    [
        'title' => $this->translate('Configuration'),
        'label' => $this->translate('Configuration'),
        'url'   => 'config/index'
    ]
);