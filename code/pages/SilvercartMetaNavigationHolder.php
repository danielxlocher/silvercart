<?php
/**
 * Copyright 2010, 2011 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * SilverCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SilverCart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with SilverCart.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Silvercart
 * @subpackage Pages
 */

/**
 * This site is not visible in the frontend.
 * Its purpose is to gather the meta navigation sites in the backend for better usability.
 * Now a shop admin has a correspondence between front end site order and backend tree structure.
 *
 * @package Silvercart
 * @subpackage Pages
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright 2010 pixeltricks GmbH
 * @since 23.10.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartMetaNavigationHolder extends Page {
    
    /**
     * allowed children in site tree
     *
     * @var array
     */
    public static $allowed_children = array(
        'SilvercartContactFormPage',
        'SilvercartNewsletterPage',
        'SilvercartMetaNavigationPage',
        'SilvercartPaymentMethodsPage',
        'SilvercartShippingFeesPage',
        'SilvercartSiteMapPage',
    );
    
    /**
     * We set a custom icon for this page type here
     *
     * @var string
     */
    public static $icon = "silvercart/images/page_icons/metanavigation_holder";

/**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string The objects singular name 
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 13.07.2012
     */
    public function singular_name() {
        return SilvercartTools::singular_name_for($this);
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
    public function plural_name() {
        return SilvercartTools::plural_name_for($this); 
    }
}

/**
 * correlating controller
 *
 * @package Silvercart
 * @subpackage Pages
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @since 23.10.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2010 pixeltricks GmbH
 */
class SilvercartMetaNavigationHolder_Controller extends Page_Controller {

    /**
     * Uses the children of SilvercartMetaNavigationHolder to render a subnavigation
     * with the SilvercartSubNavigation.ss template.
     *
     * @return string
     */
    public function getSubNavigation() {
        $root   = $this->dataRecord;
        $output = '';
        if ($root->ClassName != 'SilvercartMetaNavigationHolder') {
            while ($root->ClassName != 'SilvercartMetaNavigationHolder') {
                $root = $root->Parent();
                if ($root->ParentID == 0) {
                    $root = null;
                    break;
                }
            }
        }
        if (!is_null($root)) {
            $elements = array(
                'SubElements' => $root->Children(),
            );
            $output = $this->customise($elements)->renderWith(
                array(
                    'SilvercartSubNavigation',
                )
            );
        }
        return $output;
    }
}