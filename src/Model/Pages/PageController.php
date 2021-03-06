<?php

namespace SilverCart\Model\Pages;

use SilverCart\Admin\Model\Config;
use SilverCart\Admin\Model\CookiePolicyConfig;
use SilverCart\Checkout\Checkout;
use SilverCart\Dev\Tools;
use SilverCart\Forms\ChangeLanguageForm;
use SilverCart\Forms\LoginForm;
use SilverCart\Forms\QuickLoginForm;
use SilverCart\Forms\QuickSearchForm;
use SilverCart\Model\Customer\Country;
use SilverCart\Model\Customer\Customer;
use SilverCart\Model\Order\Order;
use SilverCart\Model\Order\ShoppingCart;
use SilverCart\Model\Pages\CheckoutStep;
use SilverCart\Model\Pages\CheckoutStepController;
use SilverCart\Model\Pages\MetaNavigationHolder;
use SilverCart\Model\Pages\Page;
use SilverCart\Model\Pages\ProductGroupHolder;
use SilverCart\Model\Payment\PaymentMethod;
use SilverCart\Model\Widgets\Widget;
use SilverCart\View\Requirements_Backend;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ErrorPage\ErrorPage;
use SilverStripe\ErrorPage\ErrorPageController;
use SilverStripe\i18n\i18n;
use SilverStripe\i18n\Messages\Symfony\FlushInvalidatedResource;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

/**
 * Standard Controller
 *
 * @package SilverCart
 * @subpackage Model_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 28.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class PageController extends ContentController
{
    /**
     * Prevents recurring rendering of this page's controller.
     *
     * @var array
     */
    public static $instanceMemorizer = [];
    /**
     * Contains HTML code from modules that shall be inserted on the Page.ss
     * template.
     *
     * @var array
     */
    protected static $moduleHtmlInjections = [];
    
    /**
     * Constructor.
     *
     * @param array $dataRecord Data record
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 17.10.2017
     */
    public function __construct($dataRecord = null)
    {
        i18n::config()->merge('default_locale', Tools::current_locale());
        i18n::set_locale(Tools::current_locale());
        parent::__construct($dataRecord);
    }
    
    /**
     * Loads all PHP side SilverCart JS requirements.
     * Additional JS files can still be loaded elsewhere.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.07.2018
     */
    public function RequireFullJavaScript() : void
    {
        if (Tools::isIsolatedEnvironment()) {
            return;
        }
        $this->extend('onBeforeRequireFullJavaScript');
        Requirements::set_write_js_to_body(true);
        Requirements::javascript('silvercart/silvercart:client/gdpr/jquery.1.9.1.min.js');
        Requirements::javascript('silvercart/silvercart:client/gdpr/jquery-ui.1.10.1.min.js');
        $this->RequireI18nJavaScript();
        $this->RequireCoreJavaScript();
        $this->RequireExtendedJavaScript();
        $this->RequireCookieBannerJavaScript();
        $this->extend('onAfterRequireFullJavaScript');
    }
    
    /**
     * Loads SilverStripe framework i18n.js and registers the SilverCart i18n JS
     * folder.
     * 
     * @param bool $force Force requirement
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.07.2018
     */
    public function RequireI18nJavaScript(bool $force = false) : void
    {
        if (!$force
         && Tools::isIsolatedEnvironment()
        ) {
            return;
        }
        $this->extend('onBeforeRequireI18nJavaScript');
        Requirements::set_write_js_to_body(true);
        Requirements::javascript('silverstripe/admin:client/dist/js/i18n.js');
        Requirements::add_i18n_javascript('silvercart/silvercart:client/javascript/lang');
        $this->extend('onAfterRequireI18nJavaScript');
    }
    
    /**
     * Loads the SilverCart core (default) JS requirements.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.07.2018
     */
    public function RequireCoreJavaScript() : void
    {
        if (Tools::isIsolatedEnvironment()) {
            return;
        }
        $jsFilesBefore = [];
        $jsFilesWidget = [];
        $this->extend('onBeforeRequireCoreJavaScript', $jsFilesBefore);
        if (Widget::$use_anything_slider) {
            $jsFilesWidget = [
                'silvercart/silvercart:client/javascript/anythingslider/js/jquery.anythingslider.min.js',
                'silvercart/silvercart:client/javascript/anythingslider/js/jquery.anythingslider.fx.min.js',
                'silvercart/silvercart:client/javascript/anythingslider/js/jquery.easing.1.2.js',
            ];
        }
        $jsFilesCore = array_merge(
                $jsFilesBefore,
                [
                    'silvercart/silvercart:client/javascript/jquery.pixeltricks.tools.js',
                    'silvercart/silvercart:client/javascript/jquery.cookie.js',
                    'silvercart/silvercart:client/javascript/bootstrap.min.js',
                    'silvercart/silvercart:client/javascript/jquery.flexslider-min.js',
                    'silvercart/silvercart:client/javascript/jquery.cycle2.min.js',
                    'silvercart/silvercart:client/javascript/jquery.cycle2.carousel.min.js',
                    'silvercart/silvercart:client/javascript/jquery.cycle2.swipe.min.js',
                    'silvercart/silvercart:client/javascript/fancybox/jquery.fancybox.js',
                    'silvercart/silvercart:client/javascript/custom.js',
                    'silvercart/silvercart:client/javascript/silvercart.js',
                ],
                $jsFilesWidget
        );
        $this->extend('updateRequireCoreJavaScript', $jsFilesCore);
        
        if (count($jsFilesCore) > 0) {
            foreach ($jsFilesCore as $file) {
                Requirements::javascript($file);
            }
        }
        $this->extend('onAfterRequireCoreJavaScript', $jsFilesCore);
    }
    
    /**
     * Loads the SilverCart extended JS requirements.
     * Extended JS files are loaded by modules or custom project extensions
     * using the updateRequireExtendedJavaScript hook.
     * 
     * @param bool $force Force requirement
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.07.2018
     */
    public function RequireExtendedJavaScript(bool $force = false) : void
    {
        if (!$force
         && Tools::isIsolatedEnvironment()
        ) {
            return;
        }
        $jsFilesExt = [];
        $this->extend('updateRequireExtendedJavaScript', $jsFilesExt);
        
        if (count($jsFilesExt) > 0) {
            foreach ($jsFilesExt as $file) {
                Requirements::javascript($file);
            }
        }
    }
    
    /**
     * Loads the SilverCart cookie policy (banner) JS requirements.
     * 
     * @param bool $force Force requirement
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.07.2018
     */
    public function RequireCookieBannerJavaScript(bool $force = false) : void
    {
        if (!$force
         && Tools::isIsolatedEnvironment()
        ) {
            return;
        }
        CookiePolicyConfig::load_requirements();
    }
    
    /**
     * Requires the color scheme CSS.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.06.2014
     */
    public function RequireColorSchemeCSS() : void
    {
        if (!is_null(Config::getConfig()->ColorScheme)) {
            Requirements::themedCSS('client/css/color_' . Config::getConfig()->ColorScheme);
        }
    }
    
    /**
     * Returns custom HTML code to place within the <head> tag, injected by
     * extensions.
     * 
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.08.2018
     */
    public function HeadCustomHtmlContent() : DBHTMLText
    {
        $headCustomHtmlContent = '';
        $this->extend('updateHeadCustomHtmlContent', $headCustomHtmlContent);
        return Tools::string2html($headCustomHtmlContent);
    }
    
    /**
     * Returns custom HTML code to place right after the <body> tag, injected by
     * extensions.
     * 
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.08.2018
     */
    public function HeaderCustomHtmlContent() : DBHTMLText
    {
        $headerCustomHtmlContent = '';
        $this->extend('updateHeaderCustomHtmlContent', $headerCustomHtmlContent);
        return Tools::string2html($headerCustomHtmlContent);
    }
    
    /**
     * Returns custom HTML code to place right before the footer (first line in
     * Footer.ss) injected by extensions.
     * 
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.08.2018
     */
    public function BeforeFooterContent() : DBHTMLText
    {
        $beforeFooterContent = '';
        $this->extend('updateBeforeFooterContent', $beforeFooterContent);
        return Tools::string2html($beforeFooterContent);
    }
    
    /**
     * Returns custom HTML code to place right before the closing </body> tag, 
     * injected by extensions.
     * 
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 31.08.2018
     */
    public function FooterCustomHtmlContent() : DBHTMLText
    {
        $footerCustomHtmlContent = '';
        $this->extend('updateFooterCustomHtmlContent', $footerCustomHtmlContent);
        return Tools::string2html($footerCustomHtmlContent);
    }

    /**
     * standard page controller
     *
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.11.2014
     */
    protected function init()
    {
        if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            if (Config::isUserAgentBlacklisted($_SERVER['HTTP_USER_AGENT'])) {
                exit();
            }
        }

        if (array_key_exists($this->ID, self::$instanceMemorizer)) {
            parent::init();
            return;
        }
        Requirements_Backend::config()->set('force_combine_files', true);
        
        Tools::initSession();
        $controller = Controller::curr();
        
        if ($controller == $this
         || $controller->forceLoadOfWidgets
        ) {
            $this->loadWidgetControllers();
        }
        
        $allParams = Controller::curr()->getRequest()->allParams();
        $customer  = Security::getCurrentUser();
        if (Controller::curr() instanceof Security
         && array_key_exists('Action', $allParams)
         && strtolower($allParams['Action']) == 'lostpassword'
         && $customer instanceof Member
        ) {
            $customer->logOut();
        }
        if (!($customer instanceof Member)) {
            Tools::Session()->set('loggedInAs', 0);
            Tools::saveSession();
        }
        $registeredCustomer = Customer::currentRegisteredCustomer();
        if ($registeredCustomer instanceof Member
         && $registeredCustomer->exists()
         && !$registeredCustomer->RegistrationOptInConfirmed
         && !($this instanceof RegistrationPageController)
        ) {
            $registrationPage = RegistrationPage::get()->first();
            if ($registrationPage instanceof RegistrationPage
             && $registrationPage->exists()
            ) {
                $this->redirect($registrationPage->Link('optinpending'));
            }
        }

        // check the SilverCart configuration
        if (!Tools::isIsolatedEnvironment()) {
            Config::Check();
        }
        if (Director::isDev()) {
            $this->getResponse()->addHeader('X-Robots-Tag', Page::config()->robots_tag_noindex);
        }
        if (array_key_exists('flushi18n', $this->getRequest()->getVars())
         && (Director::isDev()
          || ($customer instanceof Member
           && $customer->isAdmin()))
        ) {
            FlushInvalidatedResource::flush();
        }
        if (array_key_exists('flushrequirements', $this->getRequest()->getVars())
         && (Director::isDev()
          || ($customer instanceof Member
           && $customer->isAdmin()))
        ) {
            Requirements::flush();
        }

        // Decorator can use this method to add custom forms and other stuff
        $this->extend('updateInit');
        
        self::$instanceMemorizer[$this->ID] = true;
        parent::init();
    }
    
    /**
     * Returns the ChangeLanguageForm.
     * 
     * @return ChangeLanguageForm|null
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.11.2017
     */
    public function ChangeLanguageForm() : ?ChangeLanguageForm
    {
        $form         = null;
        $translations = Tools::get_translations($this->data());
        if ($translations instanceof \SilverStripe\ORM\SS_List
         && $translations->exists()
        ) {
            $form = ChangeLanguageForm::create($this);
        }
        return $form;
    }
    
    /**
     * Returns the error response for the given status code.
     * Workaround to include CSS requirements into response HTML code.
     * 
     * @param string $statusCode Status code
     * 
     * @return ContentController|null
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.05.2015
     */
    public static function error_response_for($statusCode) : ?ContentController
    {
        $response  = null;
        $errorPage = ErrorPage::get()->filter([
            "ErrorCode" => $statusCode
        ])->first(); 

        if ($errorPage) {
            $response = ModelAsController::controller_for($errorPage)->handleRequest(new HTTPRequest('GET', ''));
        }
        
        return $response;
    }

    /**
     * Returns the protocol for the current page.
     *
     * @return string
     */
    public function getProtocol() : string
    {
        return Director::protocol();
    }

    /**
     * Returns HTML code that has been created by SilverCart modules.
     *
     * @return string
     */
    public function ModuleHtmlInjections() : string
    {
        $injections = '';
        foreach (self::$moduleHtmlInjections as $injectionId => $injectionCode) {
            $injections .= $injectionCode;
        }
        return $injections;
    }

    /**
     * Saves HTML code for injection on the Page.ss template.
     *
     * @param string $identifier The identifier for the injection
     * @param string $code       The code to inject
     *
     * @return void
     */
    public static function injectHtmlCode(string $identifier, $code) : void
    {
        self::$moduleHtmlInjections[$identifier] = $code;
    }

    /**
     * Indicates wether the site is in live mode.
     * 
     * @return bool
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 18.01.2012
     */
    public function isLive() : bool
    {
        return Director::isLive();
    }
    
    /**
     * template function: returns customers orders
     * 
     * @param int $limit Limit
     *
     * @return DataList|null
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 27.10.10
     */
    public function CurrentMembersOrders(int $limit = null) : ?DataList
    {
        $customer = Security::getCurrentUser();
        if ($customer instanceof Member) {
            if ($limit) {
                $orders = Order::get()->filter('MemberID', $customer->ID)->limit($limit);
            } else {
                $orders = Order::get()->filter('MemberID', $customer->ID);
            }
            return $orders;
        }
    }
    
    /**
     * Returns the HTML Code of SilverCart errors and clears the error list.
     *
     * @return string
     * 
     * @author Sascha koehler <skoehler@pixeltricks.de>,
     *         Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.04.2013
     */
    public function ShopErrors() : string
    {
        $errorStr = '';
        $silvercartSessionErrors = Tools::Session()->get('Silvercart.errors');
        if (is_array($silvercartSessionErrors)) {
            foreach ($silvercartSessionErrors as $error) {
                $errorStr .= '<p>'.$error.'</p>';
            }
            Tools::Session()->set('Silvercart.errors', []);
            Tools::saveSession();
        }
        return $errorStr;
    }

    /**
     * Provide permissions
     * 
     * @return array configuration of API permissions
     */
    public function providePermissions() : array
    {
        return [
            'API_VIEW'   => Page::singleton()->fieldLabel('APIView'),
            'API_CREATE' => Page::singleton()->fieldLabel('APICreate'),
            'API_EDIT'   => Page::singleton()->fieldLabel('APIEdit'),
            'API_DELETE' => Page::singleton()->fieldLabel('APIDelete'),
        ];
    }

    /**
     * Function similar to Security::getCurrentUser(); Determines if we deal with a
     * registered customer who has opted in. Returns the member object or false.
     *
     * @return Member|null
     */
    public function CurrentRegisteredCustomer() : ?Member
    {
        $member = Customer::currentRegisteredCustomer();
        if (!$member) {
            $member = null;
        }
        return $member;
    }
    
    /**
     * Returns the logout URL.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.08.2018
     */
    public function logoutURL() : string
    {
        return Security::logout_url();
    }

    /**
     * returns a single page by IdentifierCode
     * used to retrieve links dynamically
     *
     * @param string $identifierCode the classes name
     * 
     * @return SiteTree|null
     */
    public static function PageByIdentifierCode(string $identifierCode = Page::IDENTIFIER_FRONT_PAGE) : ?SiteTree
    {
        return Tools::PageByIdentifierCode($identifierCode);
    }

    /**
     * returns a page link by IdentifierCode
     *
     * @param string $identifierCode the DataObjects IdentifierCode
     *
     * @return string
     */
    public static function PageByIdentifierCodeLink(string $identifierCode = Page::IDENTIFIER_FRONT_PAGE) : string
    {
        return Tools::PageByIdentifierCodeLink($identifierCode);
    }

    /**
     * Uses the children of ProductGroupHolder to render a subnavigation
     * with the SilverCart/Model/Pages/Includes/SubNavigation.ss template. This is the default sub-
     * navigation.
     *
     * @param string $identifierCode The code of the parent page.
     *
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function getSubNavigation(string $identifierCode = Page::IDENTIFIER_PRODUCT_GROUP_HOLDER) : DBHTMLText
    {
        $output = '';
        $this->extend('updateSubNavigation', $output);
        if (empty($output)) {
            $isInformationPage = false;
            $page = $this->data();
            do {
                if ($page instanceof MetaNavigationHolder) {
                    $isInformationPage = true;
                } else {
                    $page = $page->Parent();
                }
            } while ($page->Parent()->exists()
                  && !$isInformationPage);
            if ($isInformationPage) {
                $output = (string) ModelAsController::controller_for($page)->getSubNavigation();
            } else {
                $items            = [];
                $productGroupPage = Tools::PageByIdentifierCode($identifierCode);

                if ($productGroupPage) {
                    foreach ($productGroupPage->Children() as $child) {
                        if ($child->hasmethod('hasProductsOrChildren')
                         && $child->hasProductsOrChildren()
                        ) {
                            $items[] = $child;
                        }
                    }
                    $elements = [
                        'SubElements' => ArrayList::create($items),
                    ];
                    $output = $this->customise($elements)->renderWith('SilverCart/Model/Pages/Includes/SubNavigation');
                }
            }
        }
        return Tools::string2html($output);
    }

    /**
     * Returns whether the prices should be shown with the given type (gross/net).
     *
     * @param string $type Price type (gross/net) to check
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.09.2017
     */
    public function showPrices(string $type) : bool
    {
        $pricetype = Config::Pricetype();
        $member    = Customer::currentUser();
        
        if ($member instanceof Member
         && $member->doesNotHaveToPayTaxes()
        ) {
            $pricetype = 'net';
        }
        
        return $pricetype == $type;
    }

    /**
     * Returns whether the prices should be shown gross (including taxes).
     *
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.09.2017
     */
    public function showPricesGross() : bool
    {
        return $this->showPrices('gross');
    }

    /**
     * Returns whether the prices should be shown net (excluding taxes).
     *
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 28.09.2017
     */
    public function showPricesNet() : bool
    {
        return $this->showPrices('net');
    }
    
    /**
     * Returns the shoppingcart of the current user or false if there's no
     * member object registered.
     * 
     * @return ShoppingCart
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Sascha Koehler <skoehler@pixeltricks.de>
     * @since 15.11.2014
     */
    public function ShoppingCart()
    {
        $controller = Controller::curr();

        if (get_class($this) == get_class($controller)
         && !Tools::isIsolatedEnvironment()
         && !Tools::isBackendEnvironment()
        ) {
            $member = Customer::currentUser();
            if (!$member) {
                return false;
            }
            return $member->getCart();
        } else {
            return false;
        }
    }
    
    /**
     * Alias for self::ShoppingCart().
     * 
     * @return ShoppingCart
     */
    public function getCart()
    {
        return $this->ShoppingCart();
    }
    
    /**
     * Builds an associative array of ProductGroups to use in GroupedDropDownFields.
     *
     * @param SiteTree $parent      Expects a ProductGroupHolder or a ProductGroupPage
     * @param boolean  $allChildren All children or only visible children?
     * @param boolean  $withParent  Add parent page to the list?
     *
     * @return array
     * 
     * @deprecated no uses found. remove before release.
     */
    public static function getRecursivePagesForGroupedDropdownAsArray($parent = null, $allChildren = false, $withParent = false) : array
    {
        $pages = [];
        
        if (is_null($parent)) {
            $pages[''] = '';
            $parent    = Tools::PageByIdentifierCode('SilverCartPageHolder');
        }
        
        if ($parent) {
            if ($withParent) {
                $pages[$parent->ID] = $parent->Title;
            }
            if ($allChildren) {
                $children = $parent->AllChildren();
            } else {
                $children = $parent->Children();
            }
            foreach ($children as $child) {
                $pages[$child->ID] = $child->Title;
                $subs              = self::getRecursivePagesForGroupedDropdownAsArray($child);
                
                if (!empty ($subs)) {
                    $pages[_t(ProductGroupHolder::class . '.SUBGROUPS_OF','Subgroups of ') . $child->Title] = $subs;
                }
            }
        }
        return $pages;
    }
    
    /**
     * Returns all payment methods
     *
     * @return DataList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 14.05.2012
     */
    public function PaymentMethods()
    {
        return PaymentMethod::getAllowedPaymentMethodsFor($this->ShippingCountry(), ShoppingCart::singleton(), true);
    }
    
    /**
     * Returns the current shipping country
     *
     * @return Country
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.11.2014
     */
    public function ShippingCountry() : ?Country
    {
        $customer        = Customer::currentUser();
        $shippingCountry = null;
        if ($customer) {
            $shippingCountry = $customer->ShippingAddress()->Country();
        }
        if (is_null($shippingCountry)
         || $shippingCountry->ID == 0
        ) {
            $shippingCountry = Country::get()->filter([
                'ISO2'   => substr(Tools::current_locale(), 3),
                'Active' => 1,
            ])->first();
        }
        return $shippingCountry;
    }
    
    /**
     * Returns the footer columns.
     * 
     * @return DataList
     */
    public function getFooterColumns() : DataList
    {
        return MetaNavigationHolder::get()->filter('ClassName', MetaNavigationHolder::class);
    }
    
    /**
     * Returns the year range starting from the given year to the current year.
     * Example:
     * $year = 2019, current year is 2020
     * -> '2019-2020'
     * $year = 2020, current year is 2020
     * -> '2020'
     * 
     * @param int $year Year
     * 
     * @return string
     */
    public function getYearRangeFrom(int $year) : string
    {
        $range = $year;
        $currentYear = (int) date('Y');
        if ($year < $currentYear) {
            $range = "{$year}-{$currentYear}";
        }
        return $range;
    }
    
    /**
     * Returns the link to lost password form dependent on the current locale.
     * 
     * @return string
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.07.2014
     */
    public function LostPasswordLink() : string
    {
        return Director::baseURL() . 'Security/lostpassword/?locale=' . Tools::current_locale();
    }
    
    /**
     * Get the error message out of session and delete it (from session).
     *
     * @return DBHTMLText
     */
    public function getErrorMessage() : DBHTMLText
    {
        $errorMessage = Tools::Session()->get('Silvercart.errorMessage');
        Tools::Session()->clear('Silvercart.errorMessage');
        Tools::saveSession();
        return DBHTMLText::create()->setValue($errorMessage);
    }

    /**
     * Set the error message into the session.
     *
     * @param string $errorMessage Error message
     * 
     * @return void
     */
    public function setErrorMessage(string $errorMessage) : PageController
    {
        Tools::Session()->set('Silvercart.errorMessage', $errorMessage);
        Tools::saveSession();
        return $this;
    }
    
    /**
     * Get the success message out of session and delete it (from session).
     *
     * @return DBHTMLText
     */
    public function getSuccessMessage() : DBHTMLText
    {
        $successMessage = Tools::Session()->get('Silvercart.successMessage');
        Tools::Session()->clear('Silvercart.successMessage');
        Tools::saveSession();
        return DBHTMLText::create()->setValue($successMessage);
    }

    /**
     * Set the success message into the session.
     *
     * @param string $successMessage Success message
     * 
     * @return void
     */
    public function setSuccessMessage(string $successMessage) : PageController
    {
        Tools::Session()->set('Silvercart.successMessage', $successMessage);
        Tools::saveSession();
        return $this;
    }
    
    /**
     * Returns the QuickSearchForm.
     * 
     * @param string $htmlID Optional HTML ID to avoid duplicate IDs when using 
     *                       a form multiple times.
     * 
     * @return QuickSearchForm
     */
    public function QuickSearchForm(string $htmlID = null) : QuickSearchForm
    {
        $form = QuickSearchForm::create($this);
        if (!is_null($htmlID)) {
            $form->setHTMLID($htmlID);
        }
        return $form;
    }
    
    /**
     * Returns the QuickLoginForm.
     * 
     * @param string $htmlID Optional HTML ID to avoid duplicate IDs when using 
     *                       a form multiple times.
     * 
     * @return QuickLoginForm
     */
    public function QuickLoginForm(string $htmlID = null) : QuickLoginForm
    {
        $form = QuickLoginForm::create($this);
        if (!is_null($htmlID)) {
            $form->setHTMLID($htmlID);
        }
        return $form;
    }
    
    /**
     * Returns the LoginForm.
     * 
     * @return LoginForm
     */
    public function LoginForm() : LoginForm
    {
        return LoginForm::create($this);
    }
}