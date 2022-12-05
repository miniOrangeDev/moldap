<?php
/**
 * moldap plugin for Craft CMS 3.x
 *
 * LDAP/Active Directory Integration with craft cms
 *
 * @link      miniorange.com
 * @copyright Copyright (c) 2022 miniorange
 */

namespace miniorangedev\moldap\variables;

use miniorangedev\moldap\models\MoldapConfig;
use miniorangedev\moldap\Moldap;

use Craft;

/**
 * moldap Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.moldap }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    miniorange
 * @package   Moldap
 * @since     1.0.0
 */
class MoldapVariable
{
    private $mo_ldap_config;

    function __construct() {
        $this->mo_ldap_config = new MoldapConfig();
    }

    // Public Methods
    // =========================================================================

    public function getLDAPData() {
        $all_data = $this->mo_ldap_config->getLDAPConfig();
        return $all_data;
    }

}
