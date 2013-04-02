<?php
/**
 * Copyright 2013 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package Silvercart
 * @subpackage ModelAdmins
 */

/**
 * ModelAdmin for SilvercartNumberRanges.
 * 
 * @package Silvercart
 * @subpackage ModelAdmins
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2013 pixeltricks GmbH
 * @since 16.01.2012
 * @license see license file in modules root directory
 */
class SilvercartNumberRangeAdmin extends ModelAdmin {

    /**
     * The code of the menu under which this admin should be shown.
     * 
     * @var string
     */
    public static $menuCode = 'orders';

    /**
     * The section of the menu under which this admin should be grouped.
     * 
     * @var string
     */
    public static $menuSortIndex = 30;

    /**
     * The URL segment
     *
     * @var string
     */
    public static $url_segment = 'silvercart-number-ranges';

    /**
     * The menu title
     *
     * @var string
     */
    public static $menu_title = 'Number Ranges';

    /**
     * Managed models
     *
     * @var array
     */
    public static $managed_models = array(
        'SilvercartNumberRange',
    );
    
    /**
     * Provides hook for decorators, so that they can overwrite css
     * and other definitions.
     * 
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 01.08.2011
     */
    public function init() {
        parent::init();
        $this->extend('updateInit');
    }
    
    /**
     * title in the top bar of the CMS
     *
     * @return string 
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 17.08.2012
     */
    public function SectionTitle() {
        return _t('SilvercartNumberRange.PLURALNAME');
    }
}


