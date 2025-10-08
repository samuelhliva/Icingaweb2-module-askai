# Installation

## Requirements

* Icinga Web 2 (>= 2.9.0)
* PHP (>= 7.2)
* Access to an AI service provider (configuration required)

## Installation from Git

Clone the repository into the Icinga Web 2 modules directory:

```bash
cd /usr/share/icingaweb2/modules
git clone https://github.com/samuelhliva/icingaweb2-module-askai.git askai
```

## Enable the Module

Enable the module using the Icinga Web 2 CLI:

```bash
icingacli module enable askai
```

Or enable it in the Icinga Web 2 frontend under `Configuration` → `Modules` → `askai` → `Enable`.

## Configuration

After installation, configure the module by navigating to `Configuration` → `Modules` → `askai` → `Configuration`.

See [Configuration](03-Configuration.md) for detailed configuration options.