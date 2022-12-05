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
 * Default Controller
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

class DefaultController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/moldap/default
     *
     * @return mixed
     */
    public function actionIndex() {
        Craft::$app->session->removeAllFlashes();
        $request = Craft::$app->getRequest();

        $model = new MoldapConfig();
        $settings = $model->getLDAPConfig();

        $settings['ldapPort'] = $request->getBodyParam('ldapPort');
        $settings['ldapProtocol'] = $request->getBodyParam('ldapProtocol');
        $settings['ldapUrl'] = $request->getBodyParam('ldapUrl');
        $settings['userDN'] = $request->getBodyParam('userDN');
        $settings['password'] = $model->encrypt($request->getBodyParam('password'));
        $settings['searchBase'] = $request->getBodyParam('searchBase');
        $settings['searchFilter'] = $request->getBodyParam('searchFilter');
        $settings['emailAttr'] = $request->getBodyParam('emailAttr');

        if(empty($settings['ldapUrl']) || empty($settings['userDN']) || empty($settings['password']) || empty($settings['searchBase']) || empty($settings['searchFilter']) || empty($settings['emailAttr'])) {
            $message = "Please Enter all the required fields.";
            Craft::$app->session->setFlash('error', $message, true);
        }
        else {
            $model->saveLDAPConfig($settings);
        }

        return $this->redirectToPostedUrl($model);
    }

    public function actionTestAuthentication() {
        Craft::$app->session->removeAllFlashes();
        $request = Craft::$app->getRequest();

        $model = new MoldapConfig();
        $username = ($request->getBodyParam('testUsername'));
        $password = ($request->getBodyParam('testPassword'));
        if(empty($username) || empty($password)) {
            $message = "Please Enter all the required fields.";
            Craft::$app->session->setFlash('error', $message, true);
        }
        else {
            $model->testAuthentication($username, $password);
        }

        return $this->redirectToPostedUrl($model);
    }

}
