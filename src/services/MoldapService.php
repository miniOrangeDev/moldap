<?php
/**
 * moldap plugin for Craft CMS 3.x
 *
 * LDAP/Active Directory Integration with craft cms
 *
 * @link      miniorange.com
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\moldap\services;

use miniorangedev\moldap\Moldap;

use Craft;
use craft\base\Component;

/**
 * MoldapService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    miniorange
 * @package   Moldap
 * @since     1.0.0
 */
class MoldapService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     Moldap::$plugin->moldapService->exampleService()
     *
     * @return mixed
     */
    public function exampleService() {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (Moldap::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }

    public function getConnection($ldapUrl) {
        $ldapconn = ldap_connect($ldapUrl);
        if($ldapconn) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                ldap_set_option($ldapconn, LDAP_OPT_NETWORK_TIMEOUT, 5);
            }

            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
            return $ldapconn;
        }
        return null;
    }

    public function testConnection($settings) {
        $ldapurl = $settings['ldapProtocol'] . "://" . $settings['ldapUrl'] . ":" . $settings['ldapPort'];
        $ldapconn = $this->getConnection($ldapurl);

        if($ldapconn) {
            //Add TLS option here
            $bind = @ldap_bind($ldapconn, $settings['userDN'], $settings['password']);
            $error_no = ldap_errno($ldapconn);
            $err = ldap_error($ldapconn);
            if ($error_no == -1) {
                $message = "Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address. If there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your WordPress site IP address and below specified port number.";
                Craft::$app->session->setFlash('error', $message, true);
            } else if (strtolower($err) != 'success') {
                $message = "Connection to your LDAP server is successful but unable to make authenticated bind to LDAP server. Make sure you have provided correct username or password.";
                Craft::$app->session->setFlash('error', $message, true);
            } else {
                $message = "LDAP Connection Successful";
                Craft::$app->session->setFlash('success', $message, true);
            }
        } else {
            $message = "Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address. If there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your WordPress site IP address and below specified port number.";
            Craft::$app->session->setFlash('error', $message, true);
        }
    }

    public function testAuthentication($username, $password, $settings) {
        $ldapurl = $settings['ldapProtocol'] . "://" . $settings['ldapUrl'] . ":" . $settings['ldapPort'];
        $ldapconn = $this->getConnection($ldapurl);

        if($ldapconn) {
            //Add TLS option here
            $bind = @ldap_bind($ldapconn, $settings['userDN'], $settings['password']);
            $error_no = ldap_errno($ldapconn);
            $err = ldap_error($ldapconn);
            if ($error_no == -1) {
                $message = "Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address. If there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your WordPress site IP address and below specified port number.";
                Craft::$app->session->setFlash('error', $message, true);
            }
            else if (strtolower($err) != 'success') {
                $message = "Connection to your LDAP server is successful but unable to make authenticated bind to LDAP server. Make sure you have provided correct username or password.";
                Craft::$app->session->setFlash('error', $message, true);
            }
            else {
                $user_search_result = null;
                $info = null;
                $username_attr = explode(";", $settings['searchFilter']);
                $filter = "(&(objectClass=*)(|";
                foreach ($username_attr as $attr) {
                    $filter = $filter . "(" . trim($attr) . "=?)";
                    break;
                }
                $filter = $filter . "(" . trim($settings['emailAttr']) . "=?)";
                $filter = $filter . "))";

                $filter = str_replace('?', $username, $filter);

                $user_search_result = ldap_search($ldapconn, $settings['searchBase'], $filter, $username_attr);
                if($user_search_result) {
                    $info = ldap_first_entry($ldapconn, $user_search_result);
                    $entry = ldap_get_entries($ldapconn, $user_search_result);
                }

                if($info) {
                    $dn = ldap_get_dn($ldapconn, $info);
                    $ldap_bind_user = @ldap_bind($ldapconn, $dn, $password);
                    $bind_error_no = ldap_errno($ldapconn);
                    $bind_err = ldap_error($ldapconn);
                    if($bind_error_no == -1) {
                        $message = "LDAP server is not reachable.";
                        Craft::$app->session->setFlash('error', $message, true);
                        return;
                    } elseif(strtolower($bind_err) != "success") {
                        $message = "User found in LDAP server. Password entered is incorrect.";
                        Craft::$app->session->setFlash('error', $message, true);
                        return;
                    } else {
                        $message = "Test authentication was successful. You can now enable LDAP Login.";
                        Craft::$app->session->setFlash('success', $message, true);
                        return true;
                    }
                } else {
                    $message = "Cannot find user " . $username . " in the LDAP Server. Possible reasons:" . "\n" . "1. The search base DN is typed incorrectly." . "\n" . "2. User is not present in configured search base." . "\n" . "3. Username Attribute is incorrect.";
                    Craft::$app->session->setFlash('error', $message, true);
                    return;
                }
            }
        } else {
            $message = "Cannot connect to LDAP Server. Make sure you have entered correct LDAP server hostname or IP address. If there is a firewall, please open the firewall to allow incoming requests to your LDAP server from your WordPress site IP address and below specified port number.";
            Craft::$app->session->setFlash('error', $message, true);
        }
    }

    public function getEmailAttribute($username, $settings) {
        $mail_attribute = $settings['emailAttr'];
        $attr = array($mail_attribute);
        $ldapconn = $this->getConnection($settings['ldapUrl']);

        if($ldapconn) {
            $bind = @ldap_bind($ldapconn, $settings['userDN'], $settings['password']);
            if($bind) {
                $username_attr = explode(";", $settings['searchFilter']);
                $filter = "(&(objectClass=*)(|";
                foreach ($username_attr as $uattr) {
                    $filter = $filter . "(" . trim($uattr) . "=?)";
                    break;
                }
                $filter = $filter . "))";

                $filter = str_replace('?', $username, $filter);

                $user_search_result = ldap_search($ldapconn, $settings['searchBase'], $filter, $attr);
                $info = ldap_first_entry($ldapconn, $user_search_result);
                $entry = ldap_get_entries($ldapconn, $user_search_result);
                $dn = "";
                if($info) {
                    $dn = ldap_get_dn($ldapconn, $info);
                } if(!empty($dn) && isset($entry[0][$mail_attribute])) {
                    return $entry[0][$mail_attribute][0];
                }
            }
        }
        return "";
    }

    public function submitContactUs($email, $phone, $query) {
        $query = '[CraftCMS LDAP/AD Integration Plugin] ' . $query;

        $fields = array (
            'email' => $email,
            'ccEmail'=> 'ldapsupport@xecurify.com',
            'phone' => $phone,
            'query' => $query
        );

        $field_string = json_encode( $fields );

        $url = "https://login.xecurify.com/moas/rest/customer/contact-us";

        $ch = curl_init ( $url );
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt ( $ch, CURLOPT_ENCODING, "" );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $ch, CURLOPT_AUTOREFERER, true );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false ); // required for https urls
        curl_setopt ( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
            'Content-Type: application/json',
            'charset: UTF-8',
            'Authorization: Basic'
        ) );
        curl_setopt ( $ch, CURLOPT_POST, true );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $field_string );

        $content = curl_exec ( $ch );

        if (curl_errno ( $ch )) {
            return false;
        }

        curl_close ( $ch );

        return true;
    }
}