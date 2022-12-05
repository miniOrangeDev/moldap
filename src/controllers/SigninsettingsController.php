<?php
/**
 * moldap plugin for Craft CMS 3.x
 *
 * LDAP/Active Directory Integration with craft cms
 *
 * @link      miniorange.com
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\moldap\controllers;

use miniorangedev\moldap\models\MoldapConfig;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;

/**
 * Signinsettings Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    miniorange
 * @package   Moldap
 * @since     1.0.0
 *
 */

class SigninsettingsController extends Controller
{
    public function actionEnableLdapLogin() {
        Craft::$app->session->removeAllFlashes();

        $enableLdapLogin = $this->request->getBodyParam('enableLdapLogin');
        $model = new MoldapConfig();
        $settings = $model->getLDAPConfig();
        $settings['enableLdapLogin'] = $enableLdapLogin;
        $settings['password'] = $model->encrypt($settings['password']);
        $model->saveEnableLDAP($settings);

        if($enableLdapLogin == 1) {
            $message = "Login with LDAP is enabled";
            Craft::$app->session->setFlash('success', $message, true);
        } else {
            $message = "Login with LDAP is disabled";
            Craft::$app->session->setFlash('error', $message, true);
        }

        return $this->redirect($this->request->absoluteUrl);
    }

}
