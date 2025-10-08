<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

namespace Icinga\Module\Askai\Controllers;

use Icinga\Module\Askai\Forms\AskaiConfigForm;

use Icinga\Application\Config;
use Icinga\Web\Widget\Tab;
use Icinga\Web\Widget\Tabs;
use Icinga\Web\Notification;
use Icinga\Web\Controller;

class ConfigController extends Controller
{
    public function init()
    {
        $this->assertPermission('askai/config');
        parent::init();
    }

    public function indexAction()
    {
        $form = (new AskaiConfigForm())
            ->setIniConfig(Config::module('askai'));

        $form->handleRequest();

        $this->view->tabs = $this->Module()->getConfigTabs()->activate('configuration');
        $this->view->form = $form;
    }
}
