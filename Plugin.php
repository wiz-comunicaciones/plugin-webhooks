<?php namespace Wiz\Webhooks;

use Backend;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

/**
 * Webhook Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'wiz.webhooks::lang.plugin.name',
            'description' => 'wiz.webhooks::lang.plugin.description',
            'author'      => 'Scott Bedard, Gonzalo Henríquez (Wiz Comunicaciones)',
            'icon'        => 'icon-code',
        ];
    }

    /**
     * Returns plugin settings
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'webhooks' => [
                'label'       => 'wiz.webhooks::lang.plugin.name',
                'description' => 'wiz.webhooks::lang.plugin.description',
                'category'    => 'wiz.webhooks::lang.plugin.name',
                'icon'        => 'icon-code',
                'url'         => Backend::url('wiz/webhooks/hooks'),
                'permissions' => ['wiz.webhooks.manage_hooks'],
            ],
        ];
    }

    /**
     * Returns plugin permissions
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'wiz.webhooks.manage_hooks' => [
                'tab' => 'wiz.webhooks::lang.permissions.tab',
                'label' => 'wiz.webhooks::lang.permissions.manage_hooks',
            ],
        ];
    }

    public function register(){
        $this->registerConsoleCommand('wiz:webhooks.example', 'Wiz\Webhooks\Console\Example');
    }
}
