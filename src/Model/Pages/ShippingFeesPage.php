<?php

namespace SilverCart\Model\Pages;

use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\MetaNavigationHolder;
use SilverCart\Model\Shipment\Carrier;

/**
 * shows the shipping fee matrix.
 *
 * @package SilverCart
 * @subpackage Model_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 28.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ShippingFeesPage extends MetaNavigationHolder
{
    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartShippingFeesPage';
    /**
     * allowed children on site tree
     *
     * @var array
     */
    private static $allowed_children = 'none';
    /**
     * We set a custom icon for this page type here
     *
     * @var string
     */
    private static $icon = "silvercart/silvercart:client/img/page_icons/metanavigation_page-file.gif";
    
    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string The objects singular name 
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 13.07.2012
     */
    public function singular_name()
    {
        return Tools::singular_name_for($this);
    }


    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string the objects plural name
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 13.07.2012
     */
    public function plural_name()
    {
        return Tools::plural_name_for($this); 
    }

    /**
     * get all carriers; for the frontend
     *
     * @return DataList all carriers or empty DataList
     * 
     * @since 18.11.10
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     */
    public function Carriers()
    {
        $carriers = Carrier::get()->sort(['priority' => 'DESC']);
        return $carriers;
    }
}