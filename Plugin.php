<?php

/**
 * Registers Reporting routes, assets, translations and authenticated access.
 *
 * Related test file: tests/ControllerContractTest.php.
 */

namespace Kanboard\Plugin\Reporting;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;

class Plugin extends Base
{
    public function initialize()
    {
        // Reporting records are personal, but every page still requires an authenticated user.
        $this->applicationAccessMap->add('ReportingController', '*', Role::APP_USER);

        // Keep navigation compatible with installations hosted below a URL subdirectory.
        $this->route->addRoute('/reporting/budgets', 'ReportingController', 'ReportingBudget', 'Reporting');
        $this->route->addRoute('/reporting/usage', 'ReportingController', 'ReportingUsed', 'Reporting');
        $this->route->addRoute('/reporting/report', 'ReportingController', 'ReportingReport', 'Reporting');

        $this->template->hook->attach('template:dashboard:sidebar', 'Reporting:dashboard/sidebar');
        $this->hook->on(
            'template:layout:css',
            array('template' => 'plugins/Reporting/assets/css/style.css')
        );
        $this->hook->on(
            'template:layout:js',
            array('template' => 'plugins/Reporting/assets/js/reporting.js')
        );
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__ . '/Locale');
    }

    public function getClasses()
    {
        return array(
            'Plugin\Reporting\Model' => array(
                'ReportingModel',
            ),
        );
    }

    public function getPluginName()
    {
        return 'Reporting';
    }

    public function getPluginAuthor()
    {
        return 'TTJ, maintained by aljawaid';
    }

    public function getPluginVersion()
    {
        return '1.0.1';
    }

    public function getCompatibleVersion()
    {
        // The focused regression matrix targets the supported Kanboard baseline.
        return '>=1.2.54';
    }

    public function getPluginDescription()
    {
        return t('Track personal project budgets, usage entries and printable summaries.');
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/aljawaid/Reporting';
    }
}
