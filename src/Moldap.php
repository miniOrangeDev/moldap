<?php
/**
 * moldap plugin for Craft CMS 3.x
 *
 * LDAP/Active Directory Integration with craft cms
 *
 * @link      miniorange.com
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\moldap;

use miniorangedev\moldap\models\MoldapConfig;
use miniorangedev\moldap\services\MoldapService as MoldapServiceService;
use miniorangedev\moldap\variables\MoldapVariable;
use miniorangedev\moldap\twigextensions\MoldapTwigExtension;
use miniorangedev\moldap\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\web\twig\variables\Cp;
use craft\helpers\UrlHelper;

use yii\base\Event;

/**
 * @author    miniorange
 * @package   Moldap
 * @since     1.0.0
 *
 * @property  MoldapServiceService $moldapService
 * @property  Settings $settings
 * @property  MoldapConfig $moldapconfig
 * @method    Settings getSettings()
 */
class Moldap extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Moldap::$plugin
     *
     * @var Moldap
     */
    public static Plugin $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Moldap::$plugin
     *
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new MoldapTwigExtension());

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'miniorangedev\moldap\console\controllers';
        }

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['moldap'] = 'moldap/load';
                $event->rules['ldap/login'] = 'moldap/login';
                $event->rules['ldap/login/create'] = 'moldap/login/ldaplogin';
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('moldap', MoldapVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'moldap',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem(): ?array {
        $item = parent::getCpNavItem();
        $item['label'] = 'LDAP/Active Directory Integration';
        $item['subnav'] = [
            'ldapConfig' => ['label' => 'LDAP Configuration', 'url' => 'moldap'],
            'signSettings' => ['label' => 'Sign-in Settings', 'url' => 'moldap/signsettings'],
            'contactUs' => ['label' => 'Contact Us', 'url' => 'moldap/contactus'],
            'licensing' => ['label' => 'Upgrade to Premium', 'url' => 'moldap/licensing'],
        ];
        return $item;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel(): ?craft\base\Model {
        return new MoldapConfig();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string {

        return Craft::$app->view->renderTemplate(
            'moldap',
            [
                'redirect' => Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('moldap'))->send()
            ]
        );
    }
}
