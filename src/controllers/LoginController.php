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
use craft\elements\User;
use craft\helpers\User as UserHelper;
use craft\events\LoginFailureEvent;
use craft\base\Element;
use craft\elements\Asset;
use craft\elements\Entry;
use craft\errors\UploadFailedException;
use craft\errors\UserLockedException;
use craft\events\DefineUserContentSummaryEvent;
use craft\events\InvalidUserTokenEvent;
use craft\events\RegisterUserActionsEvent;
use craft\events\UserEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\helpers\Html;
use craft\helpers\Image;
use craft\helpers\Session;
use craft\i18n\Locale;
use craft\models\UserGroup;
use craft\services\Users;
use craft\web\assets\edituser\EditUserAsset;
use craft\web\Request;
use craft\web\ServiceUnavailableHttpException;
use craft\web\UploadedFile;
use craft\web\View;
use DateTime;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;



/**
 * Login Controller
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

class LoginController extends Controller
{
    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = ['index', 'ldaplogin'];

    const EVENT_LOGIN_FAILURE = 'loginFailure';


    public function actionIndex() {

    }

    public function actionLdaplogin(): ?Response {
        $userSession = Craft::$app->getUser();
        $request = Craft::$app->getRequest();
        $user_name = $request->getRequiredBodyParam('loginName');
        $password = $request->getRequiredBodyParam('password');
        $rememberMe = (bool)$request->getBodyParam('rememberMe');
        $email = $user_name;

        $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($user_name);
        $model = new MoldapConfig();
        $enableLdapLogin = $model->getEnableLDAP() == 1 ? true : false;
        $authenticated = false;

        if((!$user || $user->password === null) && $enableLdapLogin) {
            $user = new User;
            $mail = $model->getEmailAttribute($user_name);

            if(!empty($mail)) {
                $email = $mail;
            }
            // Authenticate with LDAP
            if($model->testAuthentication($user_name, $password)) {
                $authenticated = true;

                // check if user already exists, create user if not exist
                $user_info = User::find()->email($email)->all();
                if(isset($user_info[0]["admin"]) && $user_info[0]["admin"] == 1) {
                    exit('No Such Email Address Found');
                }

                if(empty($user_info)) {
                    if(Craft::$app->getUser()->getIdentity()) {
                        return null;
                    }
                    $user->username = $user_name;
                    $user->email = $email;
                    $user->password = substr(md5(mt_rand()), 0, 10);

                    if ($user->validate(null, false)) {
                        Craft::$app->getElements()->saveElement($user, false);
                    }
                }
            }
        }
        else if ($enableLdapLogin && !$user->password === null ) {
            Craft::$app->getSecurity()->validatePassword('p@ss1w0rd', '$2y$13$nj9aiBeb7RfEfYP3Cum6Revyu14QelGGxwcnFUKXIrQUitSodEPRi');
            return $this->_handleLoginFailure(User::AUTH_INVALID_CREDENTIALS);
        }
        $user = Craft::$app->getUsers()->getUserByUsernameOrEmail($email);

        if(!$user) {
            Craft::$app->getSecurity()->validatePassword('p@ss1w0rd', '$2y$13$nj9aiBeb7RfEfYP3Cum6Revyu14QelGGxwcnFUKXIrQUitSodEPRi');
            return $this->_handleLoginFailure(User::AUTH_INVALID_CREDENTIALS);
        }

        if(!$authenticated && !$user->authenticate($password)) {
            return $this->_handleLoginFailure($user->authError, $user);
        }

        $generalConfig = Craft::$app->getConfig()->getGeneral();
        if($rememberMe && $generalConfig->rememberedUserSessionDuration !== 0) {
            $duration = $generalConfig->rememberedUserSessionDuration;
        }
        else {
            $duration = $generalConfig->userSessionDuration;
        }

        if(!$userSession->login($user, $duration)) {
            return $this->_handleLoginFailure(null, $user);
        }

        return $this->_handleSuccessfulLogin();
    }

    /**
     * Handles a failed login attempt.
     *
     * @param string|null $authError
     * @param User|null $user
     * @return Response|null
     * @throws ServiceUnavailableHttpException
     */
    private function _handleLoginFailure(?string $authError, ?User $user = null): ?Response
    {
        // Delay randomly between 0 and 1.5 seconds.
        usleep(random_int(0, 1500000));

        $message = UserHelper::getLoginFailureMessage($authError, $user);

        // Fire a 'loginFailure' event
        $event = new LoginFailureEvent([
            'authError' => $authError,
            'message' => $message,
            'user' => $user,
        ]);
        $this->trigger(self::EVENT_LOGIN_FAILURE, $event);

        return $this->asFailure(
            $event->message,
            data: [
                'errorCode' => $authError,
            ],
            routeParams: [
                'loginName' => $this->request->getBodyParam('loginName'),
                'rememberMe' => (bool)$this->request->getBodyParam('rememberMe'),
                'errorCode' => $authError,
                'errorMessage' => $event->message,
            ]
        );
    }

    /**
     * Redirects the user after a successful login attempt, or if they visited the Login page while they were already
     * logged in.
     *
     * @return Response
     */
    private function _handleSuccessfulLogin(): Response
    {
        // Get the return URL
        $userSession = Craft::$app->getUser();
        $returnUrl = $userSession->getReturnUrl();

        // Clear it out
        $userSession->removeReturnUrl();

        // If this was an Ajax request, just return success:true
        if ($this->request->getAcceptsJson()) {
            $return = [
                'returnUrl' => $returnUrl,
            ];

            if (Craft::$app->getConfig()->getGeneral()->enableCsrfProtection) {
                $return['csrfTokenValue'] = $this->request->getCsrfToken();
            }

            return $this->asSuccess(data: $return);
        }

        return $this->redirectToPostedUrl($userSession->getIdentity(), $returnUrl);
    }

}
