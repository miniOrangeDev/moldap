<?php
/**
 * moldap plugin for Craft CMS 3.x
 *
 * LDAP/Active Directory Integration with craft cms
 *
 * @link      miniorange.com
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\moldap\models;

use miniorangedev\moldap\Moldap;

use Craft;
use craft\base\Model;

/**
 * Moldap Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    miniorange
 * @package   Moldap
 * @since     1.0.0
 */
class MoldapConfig extends Model
{

    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */

    /**
     * @var string|null
     */
    public string $ldapUrl;

    /**
     * @var string|null
     */
    public string $userDN;

    /**
     * @var string|null
     */
    public string $subject;


    // Public Methods
    // =========================================================================

    public function attributeLabels() {

    }

    public function getLDAPConfig() {
        $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');

        $config_data = (new craft\db\Query())
            ->select('id, options')
            ->from($prefix.'mo_ldap_config')
            ->where(array(
                'id' => 1
            ))->one();

        $options = json_decode($config_data['options'], true);
        $options['password'] = $this->decrypt($options['password']);

        return $options;
    }

    public function saveLDAPConfig($settings) {
        $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');
        Craft::$app->db->createCommand()->update($prefix.'mo_ldap_config', array('options' => json_encode($settings)), 'id=:id', array(':id' => 1))->execute();
        $settings['password'] = $this->decrypt($settings['password']);
        Moldap::$plugin->moldapService->testConnection($settings);
    }

    public function saveEnableLDAP($settings) {
        $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');
        Craft::$app->db->createCommand()->update($prefix.'mo_ldap_config', array('options' => json_encode($settings)), 'id=:id', array(':id' => 1))->execute();
    }

    public function getEnableLDAP() {
        $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');

        $config_data = (new craft\db\Query())
            ->select('id, options')
            ->from($prefix.'mo_ldap_config')
            ->where(array(
                'id' => 1
            ))->one();

        $options = json_decode($config_data['options'], true);
        return $options['enableLdapLogin'];
    }

    public function testAuthentication($username, $password) {
        $settings = $this->getLDAPConfig();
        return Moldap::$plugin->moldapService->testAuthentication($username, $password, $settings);
    }

    public function getEmailAttribute($username) {
        $settings = $this->getLDAPConfig();
        return Moldap::$plugin->moldapService->getEmailAttribute($username, $settings);
    }

    public function encrypt($str) {
        $method = 'AES-128-ECB';
        $strCrypt = openssl_encrypt ($str, $method, OPENSSL_RAW_DATA||OPENSSL_ZERO_PADDING);
        return base64_encode($strCrypt);
    }

    public function decrypt($value) {
        $strIn = base64_decode($value);
        $method = 'AES-128-ECB';
        $ivSize = openssl_cipher_iv_length($method);
        $data   = substr($strIn,$ivSize);
        return openssl_decrypt ($data, $method, OPENSSL_RAW_DATA||OPENSSL_ZERO_PADDING);
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array {
        return [
            [['ldapUrl', 'userDN', 'searchBase', 'searchFilter', 'password', 'emailAttr' ], 'string'],
        ];
    }
}
