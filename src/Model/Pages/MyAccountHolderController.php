<?php

namespace SilverCart\Model\Pages;

use SilverCart\Admin\Model\Config;
use SilverCart\Dev\Tools;
use SilverCart\Forms\LoginForm;
use SilverStripe\Control\Director;

/**
 * MyAccountHolder Controller class.
 *
 * @package SilverCart
 * @subpackage Model_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 27.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class MyAccountHolderController extends \PageController {

    /**
     * statements to be called on object initialisation
     *
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 18.11.2010
     */
    protected function init() {
        if (Config::EnableSSL()) {
            Director::forceSSL();
        }
        
        Tools::Session()->clear("redirect"); //if customer has been to the checkout yet this is set to direct him back to the checkout after address editing

        parent::init();
    }

    /**
     * Uses the children of MyAccountHolder to render a subnavigation
     * with the SilverCart/Model/Pages/Includes/SubNavigation.ss template.
     * 
     * @param string $identifierCode param only added because it exists on parent::getSubNavigation
     *                               to avoid strict notice
     *
     * @return \SilverStripe\ORM\FieldType\DBHTMLText
     */
    public function getSubNavigation($identifierCode = 'SilvercartProductGroupHolder') {
        $elements = array(
            'SubElementsTitle'  => Tools::PageByIdentifierCode('SilvercartMyAccountHolder')->MenuTitle,
            'SubElements'       => Tools::PageByIdentifierCode('SilvercartMyAccountHolder')->Children(),
        );
        $this->extend('updateSubNavigation', $elements);
        $output = $this->customise($elements)->renderWith(
            array(
                'SilverCart/Model/Pages/Includes/SubNavigation',
            )
        );
        return Tools::string2html($output);
    }

    /**
     * returns the link to the order detail page (without orderID)
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.02.2011
     */
    public function OrderDetailLink() {
        return Tools::PageByIdentifierCode('SilvercartOrderHolder')->Link() . 'detail/';
    }
    
    /**
     * Returns the LoginForm.
     * 
     * @return LoginForm
     */
    public function LoginForm() {
        $form = new LoginForm($this);
        return $form;
    }
    
}