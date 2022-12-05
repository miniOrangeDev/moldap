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
use miniorangedev\moldap\models\Settings;
use miniorangedev\moldap\Moldap;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;

/**
 * Contactus Controller
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

class ContactusController extends Controller
{
    public function actionSendQuery() {
        $email = ($this->request->getBodyParam('email'));
        $phone = ($this->request->getBodyParam('contact'));
        $query = ($this->request->getBodyParam('query'));

        if(Moldap::$plugin->moldapService->submitContactUs($email, $phone, $query)) {
            $message = "Query sent Successfully";
            Craft::$app->session->setFlash('success', $message, true);
        } else {
            $message = "Could not send the query. Please contact info@xecurify.com or ldapsupport@xecurify.com if the problem persists.";
            Craft::$app->session->setFlash('error', $message, true);
        }
        return $this->redirect($this->request->absoluteUrl);
    }

}
