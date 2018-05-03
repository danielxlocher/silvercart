<?php

namespace SilverCart\Dev;

use ReflectionClass;
use SilverCart\Admin\Model\Config;
use SilverCart\Model\Customer\Address;
use SilverCart\Model\Pages\Page;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\Session;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Security\Permission;
use SilverStripe\View\Parsers\Transliterator;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

/**
 * Provides methods for common tasks in SilverCart.
 *
 * @package SilverCart
 * @subpackage Dev
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 25.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class Tools {
    
    use \SilverStripe\Core\Extensible;
    use \SilverStripe\Core\Injector\Injectable;
    
    /**
     * The base url segment
     *
     * @var string
     */
    public static $baseURLSegment = null;
    
    /**
     * Indicates whether the installation is completed or not
     *
     * @var bool 
     */
    public static $isInstallationCompleted = null;
    
    /**
     * Indicates whether the current request is in an isolated environment like
     * dev/build, dev/test, installation, ...
     *
     * @var bool 
     */
    public static $isIsolatedEnvironment = null;
    
    /**
     * Indicates whether the current request is in backend
     *
     * @var bool 
     */
    public static $isBackendEnvironment = null;

    /**
     * Cache for the page hierarchy model.
     *
     * @var ArrayList
     */
    public static $pageHierarchy = array();

    /**
     * List of already called pages
     *
     * @var array
     */
    protected static $pagesByIdentifierCode = array();
    
    /**
     * locale to restore.
     *
     * @var string
     */
    public static $localeToRestore = null;
    
    /**
     * Set this to true to disable checking for updates.
     *
     * @var boolean
     */
    public static $disableUpdateCheck = false;

    /**
     * Initializes silvercart specific session data.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.10.2013
     */
    public static function initSession() {
        $silvercartSession = self::Session()->get('Silvercart');
        if (is_null($silvercartSession)) {
            self::Session()->set('Silvercart', array());
            self::saveSession();
            $silvercartSession = array();
        }
        if (!array_key_exists('errors', $silvercartSession)) {
            self::Session()->set('Silvercart.errors', array());
            self::saveSession();
        }
    }
    
    /**
     * Returns the current Session.
     * 
     * @return Session
     */
    public static function Session() {
        return Controller::curr()->getRequest()->getSession();
    }
    
    /**
     * Returns the current Session.
     * 
     * @return Session
     */
    public static function saveSession() {
        return self::Session()->save(Controller::curr()->getRequest());
    }

    /**
     * Returns the base URL segment that's used for inclusion of css and
     * javascript files via Requirements.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 16.02.2012
     */
    public static function getBaseURLSegment() {
        if (is_null(self::$baseURLSegment)) {
            $baseUrl = Director::baseUrl();

            if ($baseUrl === '/') {
                $baseUrl = '';
            }

            if (!empty($baseUrl) &&
                 substr($baseUrl, -1) != '/') {

                $baseUrl .= '/';
            }
            self::$baseURLSegment = $baseUrl;
        }

        return self::$baseURLSegment;
    }
    
    /**
     * Takes the given string and puts it into a DBHTMLText object to render properly in a 
     * template.
     * 
     * @param string $string String to convert.
     * 
     * @return DBHTMLText
     */
    public static function string2html($string) {
        $html = new DBHTMLText();
        $html->setValue($string);
        return $html;
    }

    /**
     * Remove chars from the given string that are not appropriate for an url
     *
     * @param string $originalString String to convert
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.04.2014
     */
    public static function string2urlSegment($originalString) {
        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($originalString);
        } else {
            $string = strtolower($originalString);
        }
        $transliterator = new Transliterator();
        $string         = $transliterator->toASCII($string);
        $string         = str_replace('&amp;', '-and-', $string);
        $string         = str_replace('&', '-and-', $string);
        $string         = preg_replace('/[^A-Za-z0-9]+/', '-', $string);

        if (!$string || $string == '-' || $string == '-1') {
            if (function_exists('mb_strtolower')) {
                $string = mb_strtolower($originalString);
            } else {
                $string = strtolower($originalString);
            }
        }
        $string = trim($string, '-');
        self::replace_special_chars($string);
        self::replace_cyrillic_chars($string);
        return urlencode($string);
    }
    
    /**
     * Replaces special chars.
     * 
     * @param string &$string String reference to replace special chars for
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.03.2014
     */
    public static function replace_special_chars(&$string) {
        $remove  = array('ä',  'ö',  'ü',  'Ä',  'Ö',  'Ü',  '/', '?', '&', '#', '.', ',', ' ', '%', '"', "'", '<', '>');
        $replace = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', '-', '-', '-', '-', '-', '-', '-', '',  '',  '',  '',  '');
        $string  = str_replace($remove, $replace, $string);
    }
    
    /**
     * Replaces cyrillic chars with latin chars
     * 
     * @param string &$string String reference to replace cyrillic chars for
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.03.2014
     */
    public static function replace_cyrillic_chars(&$string) {
        $remove  = array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $replace = array('a', 'b', 'v', 'g', 'd', 'e', 'yo', 'zh', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'shh', '-', 'y', '-', 'e-', 'yu', 'ya');
        $string  = str_replace($remove, $replace, $string);
    }

    /**
     * Writes a log entry
     *
     * @param string $context  the context for the log entry
     * @param string $text     the text for the log entry
     * @param string $filename filename to log into
     *
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 20.04.2018
     */
    public static function Log($context, $text, $filename = 'default') {
        $filePath = SILVERCART_LOG_PATH . DIRECTORY_SEPARATOR . $filename . '.log';
        $logText = sprintf(
                "%s - %s - %s" . PHP_EOL,
                date('Y-m-d H:i:s'),
                $context,
                $text
        );
        file_put_contents($filePath, $logText, FILE_APPEND);
    }

    /**
     * Returns the attributed DataObjects as string (limited to 150 chars) by
     * the given DataList.
     * 
     * @param DataList $dataList  DataList to get list for
     * @param string   $dbField   Db field to use to display
     * @param int      $maxLength Maximum string length
     *
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2012
     */
    public static function AttributedDataObject($dataList, $dbField = "Title", $maxLength = 150) {
        $attributedDataObjectStr    = '';
        $attributedDataObjects      = array();

        foreach ($dataList as $component) {
            $attributedDataObjects[] = $component->{$dbField};
        }
        
        if (!empty($attributedDataObjects)) {
            $attributedDataObjectStr = implode(', ', $attributedDataObjects);

            if (strlen($attributedDataObjectStr) > $maxLength) {
                $attributedDataObjectStr = substr($attributedDataObjectStr, 0, $maxLength) . '...';
            }
        }

        return $attributedDataObjectStr;
    }

    /**
     * returns a single page by IdentifierCode
     * used to retrieve links dynamically
     *
     * @param string $identifierCode the classes name
     * @param bool   $force          Set to true to force a database access
     * 
     * @return SiteTree
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.07.2016
     */
    public static function PageByIdentifierCode($identifierCode = "SilvercartFrontPage", $force = false) {
        if (!array_key_exists($identifierCode, self::$pagesByIdentifierCode) ||
            $force) {
            self::$pagesByIdentifierCode[$identifierCode] = Page::get()->filter('IdentifierCode', $identifierCode)->first();
        }
        return self::$pagesByIdentifierCode[$identifierCode];
    }

    /**
     * returns a page link by IdentifierCode
     *
     * @param string $identifierCode the DataObjects IdentifierCode
     * @param bool   $force          Set to true to force a database access
     *
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.07.2016
     */
    public static function PageByIdentifierCodeLink($identifierCode = "SilvercartFrontPage", $force = false) {
        $page = self::PageByIdentifierCode($identifierCode, $force);
        if ($page === false ||
            is_null($page)) {
            return '';
        }
        return $page->Link();
    }
    
    /**
     * Returns the translated singular name of the given object. If no 
     * translation exists the class name will be returned.
     * 
     * @param DataObject $dataObject DataObject to get singular name for
     * @param string     $default    Default string to use as fallback
     * 
     * @return string The objects singular name 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.05.2012
     */
    public static function singular_name_for($dataObject, $default = '') {
        if (empty($default)) {
            $reflection = new ReflectionClass($dataObject);
            $default = ucwords(trim(strtolower(preg_replace('/_?([A-Z])/', ' $1', $reflection->getShortName()))));
        }
        return _t($dataObject->ClassName . '.SINGULARNAME', $default);
    }


    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @param DataObject $dataObject DataObject to get plural name for
     * @param string     $default    Default string to use as fallback
     * 
     * @return string the objects plural name
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.05.2012
     */
    public static function plural_name_for($dataObject, $default = '') {
        if (empty($default)) {
            $plural_name = self::singular_name_for($dataObject);
            if (substr($plural_name,-1) == 'e') {
                $plural_name = substr($plural_name,0,-1);
            } elseif (substr($plural_name,-1) == 'y') {
                $plural_name = substr($plural_name,0,-1) . 'ie';
            }
            $default = ucfirst($plural_name . 's');
        }
        return _t($dataObject->ClassName . '.PLURALNAME', $default);
    }
    
    /**
     * Returns the default field labels for the given DataObject.
     * 
     * @param string $objectName Object name to get field labels for
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 09.04.2014
     */
    public static function field_labels_for($objectName) {
        
        $fieldLabels = array();
        
        $params = array('db', 'casting', 'has_one', 'has_many', 'many_many');
        foreach ($params as $param) {
            $source = \SilverStripe\Core\Config\Config::inst()->get($objectName, $param);
            if (is_array($source)) {
                foreach (array_keys($source) as $fieldname) {
                    $fieldLabels[$fieldname]          = _t($objectName . '.' . $fieldname);
                    $fieldLabels[$fieldname . 'Desc'] = _t($objectName . '.' . $fieldname . 'Desc');
                }
            }
        }
        
        return $fieldLabels;
    }

    /**
     * Get a list of i18n field labels.
     *
     * @return array
     */
    public static function field_labels() {
        $labels = array(
            'DATE'         => _t(Tools::class . '.DATE', 'Date'),
            'DateFormat'   => _t(Tools::class . '.DATEFORMAT', 'm/d/Y'),
            'No'           => _t(Tools::class . '.NO', 'No'),
            'PleaseChoose' => _t(Tools::class . '.PLEASECHOOSE', '--please choose--'),
            'Priority'     => _t(Tools::class . '.PRIORITY', 'Priority (the higher the more important)'),
            'To'           => _t(Tools::class . '.To', 'To'),
            'Yes'          => _t(Tools::class . '.YES', 'Yes'),
        );
        self::singleton()->extend('updateFieldLabels', $labels);
        return $labels;
    }

    /**
     * Get a human-readable label for a single field,
     * see {@link field_labels()} for more details.
     *
     * @uses field_labels()
     * @uses FormField::name_to_label()
     *
     * @param string $name Name of the field
     * 
     * @return string Label of the field
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 19.10.2017
     */
    public static function field_label($name) {
        $labels = self::field_labels();
        return (isset($labels[$name])) ? $labels[$name] : FormField::name_to_label($name);
    }

    /**
     * Checks if the installation is complete. We assume a complete
     * installation if the Member table has the field "ShoppingCartID"
     * that is decorated via "Customer".
     * 
     * @return boolean
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.11.2012
     */
    public static function isInstallationCompleted() {
        if (is_null(self::$isInstallationCompleted)) {
            $installationComplete   = false;

            if ((array_key_exists('SCRIPT_NAME', $_SERVER) && strpos($_SERVER['SCRIPT_NAME'], 'install.php') !== false) ||
                (array_key_exists('QUERY_STRING', $_SERVER) && strpos($_SERVER['QUERY_STRING'], 'successfullyinstalled') !== false) ||
                (array_key_exists('QUERY_STRING', $_SERVER) && strpos($_SERVER['QUERY_STRING'], 'deleteinstallfiles') !== false) ||
                (array_key_exists('REQUEST_URI', $_SERVER) && strpos($_SERVER['REQUEST_URI'], 'successfullyinstalled') !== false) ||
                (array_key_exists('REQUEST_URI', $_SERVER) && strpos($_SERVER['REQUEST_URI'], 'deleteinstallfiles') !== false)) {
                $installationComplete = false;
            } else {
                $memberFieldList        = array();
                $queryRes               = DB::query("SHOW TABLES");
                if ($queryRes->numRecords() > 0) {
                    $queryRes               = DB::query("SHOW COLUMNS FROM Member");

                    foreach ($queryRes as $key => $value) {
                        $memberFieldList[] = $value['Field'];
                    }

                    if (in_array('ShoppingCartID', $memberFieldList)) {
                        $installationComplete = true;
                    }
                }
            }
            self::$isInstallationCompleted = $installationComplete;
        }
        return self::$isInstallationCompleted;
    }
    
    /**
     * Checks whether the current request is a special, isolated environment
     *
     * @return boolean 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.01.2013
     */
    public static function isIsolatedEnvironment() {
        if (is_null(self::$isIsolatedEnvironment)) {
            self::$isIsolatedEnvironment = false;
            if ((array_key_exists('url', $_REQUEST) && (strpos($_REQUEST['url'], '/Security/login') !== false || strpos($_REQUEST['url'], 'dev/build') !== false || self::isInstallationCompleted() == false)) ||
                (array_key_exists('QUERY_STRING', $_SERVER) && (strpos($_SERVER['QUERY_STRING'], 'dev/tests') !== false || strpos($_SERVER['QUERY_STRING'], 'dev/build') !== false)) ||
                (array_key_exists('SCRIPT_NAME', $_SERVER) && strpos($_SERVER['SCRIPT_NAME'], 'install.php') !== false) ||
                ($_SERVER['SCRIPT_NAME'] === FRAMEWORK_DIR.'/cli-script.php' ||
                 $_SERVER['SCRIPT_NAME'] === '/' . FRAMEWORK_DIR.'/cli-script.php')) {
                self::$isIsolatedEnvironment = true;
            }
        }
        return self::$isIsolatedEnvironment;
    }
    
    /**
     * Checks whether the current request is a CMS preview
     *
     * @return boolean 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.04.2018
     */
    public static function is_cms_preview() {
        $request      = Controller::curr()->getRequest();
        $isCMSPreview = (bool) $request->getVar('CMSPreview');
        $isAdmin      = Permission::check('ADMIN');
        return $isAdmin && $isCMSPreview;
    }
    
    /**
     * Prepares a given email address to use for request handling.
     * CAUTION: This is used for EVERY post requested variable named 'Email'
     * and called in _config.php
     *
     * @param string $emailAddress Email address to prepare
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.07.2012
     */
    public static function prepareEmailAddress($emailAddress) {
        $preparedEmailAddress = str_replace('/', '', $emailAddress);
        return $preparedEmailAddress;
    }
    
    /**
     * Checks whether the current url location is in backend
     * 
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.11.2012
     */
    public static function isBackendEnvironment() {
        if (is_null(self::$isBackendEnvironment)) {
            $isBackendEnvironment = false;

            $controller = Controller::curr();
            $request    = $controller->getRequest();

            if (strpos($request->getURL(), 'admin/') === 0 ||
                strpos($request->getURL(), '/admin/') === 0) {
                $isBackendEnvironment = true;
            }
            self::$isBackendEnvironment = $isBackendEnvironment;
        }
        return self::$isBackendEnvironment;
    }

    /**
     * Returns a flat array containing the IDs of all child pages of the given page.
     *
     * @param int $pageId The root page ID
     *
     * @return array
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.08.2012
     */
    public static function getFlatChildPageIDsForPage($pageId) {
        $pageIDs = array($pageId);
        $pageObj = SiteTree::get()->byID($pageId);
        
        if ($pageObj) {
            foreach ($pageObj->Children() as $pageChild) {
                $pageIDs = array_merge($pageIDs, self::getFlatChildPageIDsForPage($pageChild->ID));
            }
        }
        
        return $pageIDs;
    }

    /**
     * Builds a hierarchy from the current page to the top product group page
     * or holder.
     *
     * @param SiteTree $currPage The page to start from
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 18.10.2012
     */
    public static function getPageHierarchy($currPage) {
        if (!array_key_exists('SiteTree_'.$currPage->ID, self::$pageHierarchy)) {
            $level      = 0;
            $hierarchy  = array(
                'SiteTree_'.$currPage->ID => array(
                    'Page'  => $currPage,
                    'Level' => $level
                )
            );

            while ($currPage->hasMethod('getParent') &&
                $currPage->getParent()) {

                $parent = $currPage->getParent();

                if ($parent) {
                    $level++;
                    $hierarchy['SiteTree_'.$parent->ID] = array(
                        'Page'  => $parent,
                        'Level' => $level
                    );
                    $currPage = $parent;
                } else {
                    break;
                }
            }

            self::$pageHierarchy['SiteTree_'.$currPage->ID] = array();

            foreach ($hierarchy as $pageID => $pageInfo) {
                self::$pageHierarchy['SiteTree_'.$currPage->ID][$pageID] = array(
                    'Page'  => $pageInfo['Page'],
                    'Level' => ($pageInfo['Level'] - $level) * -1
                );
            }
        }

        return self::$pageHierarchy['SiteTree_'.$currPage->ID];
    }

    /**
     * Returns the localized salutation string.
     * 
     * @param string $salutation Enum value for salutation to get i18n for
     *
     * @return string
     */
    public static function getSalutationText($salutation) {
        if ($salutation == 'Herr') {
            $salutationText = Address::singleton()->fieldLabel('Mister');
        } elseif ($salutation == 'Frau') {
            $salutationText = Address::singleton()->fieldLabel('Misses');
        } else {
            $salutationText = Address::singleton()->fieldLabel(strtoupper($salutation));
        }
        return $salutationText;
    }

    /**
     * Removes a prefix from a checkout address data array.
     *
     * @param string $prefix Prefix
     * @param array  $data   Checkout address data
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.04.2014
     */
    public static function extractAddressDataFrom($prefix, $data) {
        $addressData = array();

        foreach ($data as $key => $value) {
            if (strpos($key, $prefix . '_') === 0) {
                $dataFieldName = str_replace($prefix . '_', '', $key);
                if ($dataFieldName == 'Country') {
                    $dataFieldName = 'CountryID';
                }
                $addressData[$dataFieldName] = $value;
            }
        }

        if (array_key_exists('TaxIdNumber', $addressData) &&
            array_key_exists('Company', $addressData) &&
            !empty($addressData['TaxIdNumber']) &&
            !empty($addressData['Company'])) {

            $addressData['isCompanyAddress'] = true;
        } else {
            $addressData['isCompanyAddress'] = false;
        }

        return $addressData;
    }

    /**
     * Tries to find the given page ID in the page hierarchy structure.
     *
     * @param int $searchPageID The page ID to find
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 18.10.2012
     */
    public static function findPageIdInHierarchy($searchPageID) {
        $foundPageId = false;
        $hierarchy   = self::getPageHierarchy(Controller::curr());

        foreach ($hierarchy as $pageID => $pageInfo) {
            if ($pageInfo['Page']->ID === $searchPageID) {
                $foundPageId = true;
                break;
            }
        }

        return $foundPageId;
    }

    /**
     * Tries to find the given page ID in the page hierarchy structure and
     * returns the corresponding page.
     *
     * @param int $searchPageID The page ID to find
     *
     * @return SiteTree
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 18.10.2012
     */
    public static function getPageLevelByPageId($searchPageID) {
        $level     = false;
        $hierarchy = self::getPageHierarchy(Controller::curr());

        foreach ($hierarchy as $pageID => $pageInfo) {
            if ($pageInfo['Page']->ID == $searchPageID) {
                $level = $pageInfo['Level'];
                break;
            }
        }

        return $level;
    }

    /**
     * Checks if the given page IDs are siblings of the same level.
     *
     * @param int $checkPageID1 The first page ID to check
     * @param int $checkPageID2 The second page ID to check
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 18.10.2012
     */
    public static function pageIsSiblingOf($checkPageID1, $checkPageID2) {
        $isSibling = false;

        $level1 = self::getPageLevelByPageId($checkPageID1);
        $level2 = self::getPageLevelByPageId($checkPageID2);

        if ($level1 === $level2) {
            $isSibling = true;
        }

        return $isSibling;
    }

    /**
     * Checks on silvercart.org whether there is an update available.
     * 
     * @return boolean
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.01.2013
     */
    public static function checkForUpdate() {
        if (self::$disableUpdateCheck) {
            return false;
        }
        $updateAvailable = false;
        try {
            $checkForUpdateUrl = sprintf(
                    'http://www.silvercart.org/scsc/checkForUpdate/%s.%s',
                    Config::SilvercartVersion(),
                    Config::SilvercartMinorVersion()
            );
            $ch = curl_init($checkForUpdateUrl);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_REFERER, Director::absoluteBaseURL());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $exc) {
            $result = 0;
        }
        if ((int) $result == 1) {
            // update available
            $updateAvailable = true;
        }
        return $updateAvailable;
    }
    
    /**
     * Redirects to the given URL with status "303 See other".
     * 
     * @param string $url Relative or absolute URL to redirect to
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.10.2013
     */
    public static function redirectPermanentlyTo($url) {
        header("HTTP/1.1 303 See Other");
        header('Location: ' . Director::absoluteURL($url));
        exit();
    }
    
    /**
     * Returns the given date with time in a nice format
     * 
     * @param string $date Date to format
     * 
     * @return string
     */
    public static function getDateWithTimeNice($date) {
        $dateNice           = self::getDateNice($date);
        $dateTimestamp      = strtotime($date);
        $timeNiceFormat     = '%H:%M';
        $timeNice           = strftime($timeNiceFormat, $dateTimestamp) . ' ' .  _t(Tools::class . '.Oclock', "o'clock");
        $dateWithTimeNice   = $dateNice . ' ' . $timeNice;
        return $dateWithTimeNice;
    }
    
    /**
     * Returns the given date in a nice format
     * 
     * @param string $date          Date to format
     * @param bool   $fullMonthName Set to true to show the full month name
     * @param bool   $forceYear     Set to true to force showing the year
     * @param bool   $withWeekDay   Set to true to show the name of the week day
     * 
     * @return string
     */
    public static function getDateNice($date, $fullMonthName = false, $forceYear = false, $withWeekDay = false) {
        self::switchLocale(false);
        if ($fullMonthName) {
            $month = '%B';
        } else {
            $month = '%b.';
        }
        $dateTimestamp  = strtotime($date);
        $dateNiceFormat = '%d. ' . $month;
        if (date('Y', $dateTimestamp) != date('Y') ||
            $forceYear) {
            $dateNiceFormat = '%d. ' . $month . ' %Y';
        } elseif (date('m-d', $dateTimestamp) == date('m-d')) {
            $dateNiceFormat = ucfirst(_t(Tools::class . '.TODAY', 'today'));
        } elseif (date('m-d', $dateTimestamp) == date('m-d', time() - 24*60*60)) {
            $dateNiceFormat = ucfirst(_t(Tools::class . '.YESTERDAY', 'yesterday'));
        }
        if ($withWeekDay) {
            $dateNiceFormat = '%A, ' . $dateNiceFormat;
        }
        $dateNice = strftime($dateNiceFormat, $dateTimestamp);
        self::switchLocale();
        return $dateNice;
    }
    
    /**
     * Returns a map of month number and name to use in a drop down.
     * 
     * @param string $emptyString Optional string to use instead of default empty string.
     * 
     * @return array
     */
    public static function getMonthMap($emptyString = null) {
        if (is_null($emptyString)) {
            $emptyString = Tools::field_label('PleaseChoose');
        }
        $monthMap = array(
            ''   => $emptyString,
            '1'  => Page::singleton()->fieldLabel('January'),
            '2'  => Page::singleton()->fieldLabel('February'),
            '3'  => Page::singleton()->fieldLabel('March'),
            '4'  => Page::singleton()->fieldLabel('April'),
            '5'  => Page::singleton()->fieldLabel('May'),
            '6'  => Page::singleton()->fieldLabel('June'),
            '7'  => Page::singleton()->fieldLabel('July'),
            '8'  => Page::singleton()->fieldLabel('August'),
            '9'  => Page::singleton()->fieldLabel('September'),
            '10' => Page::singleton()->fieldLabel('October'),
            '11' => Page::singleton()->fieldLabel('November'),
            '12' => Page::singleton()->fieldLabel('December'),
        );
        return $monthMap;
    }
    
    /**
     * Returns a map of salutation system text and i18n name to use in a drop down.
     * 
     * @param string $emptyString Optional string to use instead of default empty string.
     * 
     * @return array
     */
    public static function getSalutationMap($emptyString = null) {
        if (is_null($emptyString)) {
            $emptyString = Tools::field_label('PleaseChoose');
        }
        $salutationMap = array(
            ''   => $emptyString,
            'Frau'  => self::getSalutationText('Frau'),
            'Herr'  => self::getSalutationText('Herr'),
        );
        return $salutationMap;
    }

    /**
     * Switchs the locale from default to the current SS locale and back.
     * This method is called in constructor and destructor.
     * 
     * @param bool $doRestore Should this call restore the locale to the default value?
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.05.2011
     */
    public static function switchLocale($doRestore = true) {
        if (!$doRestore &&
            !is_null(self::$localeToRestore)) {
            return;
        }
        if (is_null(self::$localeToRestore)) {
            self::$localeToRestore  = setlocale(LC_ALL, null);
            $currentLocale          = i18n::get_locale();
        } else {
            $currentLocale          = self::$localeToRestore;
            self::$localeToRestore  = null;
        }
        // it's a kind of dirty, because this will not match every possible
        // system locale... It works for plain and utf8 locales.
        setlocale(LC_ALL, $currentLocale . '.utf8', $currentLocale . '.UTF-8', $currentLocale);
    }

    /**
     * Returns the current locale.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.04.2018
     */
    public static function current_locale() {
        $locale = FluentState::singleton()->getLocale();
        if (empty($locale)) {
            $locale = i18n::get_locale();
        }
        return $locale;
    }

    /**
     * Sets the current locale.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.04.2018
     */
    public static function set_current_locale($locale) {
        return FluentState::singleton()->setLocale($locale);
    }

    /**
     * Returns the available content locales.
     * 
     * @return \SilverStripe\ORM\ArrayList
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.04.2018
     */
    public static function content_locales() {
        return Locale::getCached();
    }

    /**
     * Returns the default locale.
     * 
     * @param string|null|bool $domain If provided, the default locale for the given domain will be returned.
     *                                 If true, then the current state domain will be used (if in domain mode).
     * 
     * @return Locale
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.04.2018
     */
    public static function default_locale($domain = null) {
        return Locale::getDefault($domain);
    }

    /**
     * Returns the translation with the given locale.
     * 
     * @param DataObject $original Original DataObject to get translation for
     * @param string     $locale   Locale to get translation for
     * 
     * @return DataObject
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.04.2018
     */
    public static function get_translation($original, $locale) {
        $originalLocale = Tools::current_locale();
        Tools::set_current_locale($locale);
        $translation = DataObject::get($original->ClassName)->byID($original->ID);
        Tools::set_current_locale($originalLocale);
        return $translation;
    }

    /**
     * Returns all translations of the given record.
     * 
     * @param DataObject $original Original DataObject to get translations for
     * 
     * @return DataObject
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.04.2018
     */
    public static function get_translations($original) {
        if ($original instanceof \SilverStripe\CMS\Controllers\ContentController) {
            $original = $original->data();
        }
        $translations = [];
        if ($original->hasMethod('Locales')) {
            $locales = $original->Locales();
            /* @var $locales \SilverStripe\ORM\ArrayList */
            foreach ($locales as $locale) {
                /* @var $locale ArrayData */
                $translation = self::get_translation($original, $locale->Locale);
                if ($translation instanceof DataObject &&
                    $translation->exists()) {
                    $translations[] = $translation;
                }
            }
        }
        return new ArrayList($translations);
    }

    /**
     * Returns whether the translation with the given locale exists for the given record.
     * 
     * @param DataObject $original Original DataObject to check translation for
     * @param string     $locale   Locale to check translation for
     * 
     * @return bool
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.04.2018
     */
    public static function has_translation($original, $locale) {
        $translation = self::get_translation($original, $locale);
        return $translation instanceof DataObject && $translations->exists();
    }
    
    /**
     * Returns the redirect back url.
     * 
     * @return string
     */
    public static function get_redirect_back_url() {
        $request = Controller::curr()->getRequest();
        if ($request->requestVar('_REDIRECT_BACK_URL')) {
            $url = $request->requestVar('_REDIRECT_BACK_URL');
        } elseif ($request->getHeader('Referer')) {
            $url = $request->getHeader('Referer');
        } else {
            $url = Director::baseURL();
        }
        return $url;
    }
    
    /**
     * Returns the DB table name for the given class.
     * 
     * @param string $class Class
     * 
     * @return string
     */
    public static function get_table_name($class) {
        return \SilverStripe\Core\Config\Config::inst()->get($class, 'table_name');
    }
}