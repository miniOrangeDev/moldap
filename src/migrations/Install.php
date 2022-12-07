<?php
/**
 * moldap plugin for Craft CMS 3.x
 *
 * LDAP/Active Directory Integration with craft cms
 *
 * @link      miniorange.com
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\moldap\migrations;

use miniorangedev\moldap\Moldap;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * moldap Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    miniorange
 * @package   Moldap
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp() {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown() {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables() {
        $emails = Craft::$app->projectConfig->get('email');
        $phone = "N/A";
        $query = "A new customer has installed LDAP/AD Integration plugin on Craft CMS.";
        Moldap::$plugin->moldapService->submitContactUs($emails['fromEmail'], $phone, $query);

        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%mo_ldap_config}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%mo_ldap_config}}',
                [
                    'id' => $this->primaryKey(),
                    'name' => $this->string(),
                    'options' => $this->json(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer()->notNull(),
                ]
            );
        }
        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes() {
        
        $this->createIndex(
            $this->db->getIndexName(
                '{{%mo_ldap_config}}',
                true
            ),
            '{{%mo_ldap_config}}',
            true
        );

        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys() {
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%mo_ldap_config}}', 'siteId'),
            '{{%mo_ldap_config}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData() {
        $site_name = Craft::$app->sites->currentSite->name;

        $settings = array();
        $settings['ldapPort'] = "389";
        $settings['ldapProtocol'] = "ldap";
        $settings['ldapUrl'] = "";
        $settings['userDN'] = "";
        $settings['password'] = "";
        $settings['searchBase'] = "";
        $settings['searchFilter'] = "";
        $settings['emailAttr'] = "";
        $settings['enableLdapLogin'] = 0;

        $prefix = (Craft::$app->version>4)?getenv('CRAFT_DB_TABLE_PREFIX'):getenv('DB_TABLE_PREFIX');
        $insertDefault = Craft::$app->db->createCommand()
            ->upsert($prefix.'mo_ldap_config', array(
                'id' => 1,
                'name' => $site_name,
                'options' => json_encode($settings),
                'siteId'  => 1,
            ))
            ->execute();

        return $insertDefault;
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables() {
        $this->dropTableIfExists('{{%mo_ldap_config}}');
    }
}
