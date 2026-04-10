<?php
/* Icinga Web 2 - AskAI Module | (c) 2025 Samuel Hliva | GPLv2+ */

/** @var $this \Icinga\Application\Modules\Module */
use Icinga\Module\Grafana\ProvidedHook\Icingadb\IcingadbSupport;

if ($this->exists('monitoring')){
    $this->provideHook('Monitoring/HostActions');
    $this->provideHook('Monitoring/ServiceActions');
}

if ($this->exists('icingadb')){
    $this->provideHook('icingadb/HostActions');
    $this->provideHook('icingadb/ServiceActions');
}