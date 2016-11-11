<?php

namespace Kanboard\Plugin\Reporting;

use Kanboard\Core\Plugin\Base;

class Plugin extends Base
{
    public function initialize()
    {

	$this->template->hook->attach('template:dashboard:sidebar', 'Reporting:dashboard/sidebar');

    }

    public function getClasses()
    {
        return array(
            'Plugin\Reporting\Model' => array(
                'ReportingModel'
             )
         );
    }

    public function getPluginName()
    {
        return 'Reporting';
    }
    public function getPluginAuthor()
    {
        return 'TTJ';
    }
    public function getPluginVersion()
    {
        return '0.0.1';
    }
    public function getPluginDescription()
    {
        return 'Reporting';
    }
    public function getPluginHomepage()
    {
        return 'https://gitlab.com/ThomasTJ/Reporting';
    }
}
