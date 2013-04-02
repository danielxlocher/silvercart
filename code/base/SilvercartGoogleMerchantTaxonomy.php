<?php
/**
 * Copyright 2013 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * @package Silvercart
 * @subpackage Base
 */

/**
 * Definition for Google merchant product groups (Froogle)
 *
 * @package Silvercart
 * @subpackage Base
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @since 08.08.2011
 * @license see license file in modules root directory
 * @copyright 2013 pixeltricks GmbH
 */
class SilvercartGoogleMerchantTaxonomy extends DataObject {
    
    /**
     * The cache key for storing the complete result set of all breadcrumbs
     * for display in HTML dropdown fields.
     *
     * @var string
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 09.08.2011
     */
    public static $cacheKey = 'SilvercartGoogleMerchantTaxonomyBreadcrumbs';
    
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
    
    /**
     * Attributes
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 08.08.2011
     */
    public static $db = array(
        'CategoryLevel1' => 'VarChar(100)',
        'CategoryLevel2' => 'VarChar(100)',
        'CategoryLevel3' => 'VarChar(100)',
        'CategoryLevel4' => 'VarChar(100)',
        'CategoryLevel5' => 'VarChar(100)',
        'CategoryLevel6' => 'VarChar(100)'
    );
    
    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2013 pixeltricks GmbH
     * @since 11.01.2013
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
                parent::fieldLabels($includerelations),             array(
                    'SilvercartProductGroupPages' => _t('SilvercartProductGroupPage.PLURALNAME'),
                    'CategoryLevel1' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL1'),
                    'CategoryLevel2' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL2'),
                    'CategoryLevel3' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL3'),
                    'CategoryLevel4' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL4'),
                    'CategoryLevel5' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL5'),
                    'CategoryLevel6' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL6')
                )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Has-many relationships.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 08.08.2011
     */
    public static $has_many = array(
        'SilvercartProductGroupPages' => 'SilvercartProductGroupPage'
    );
    
    /**
     * Summaryfields for display in tables.
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 08.08.2011
     */
    public function summaryFields() {
        $summaryFields = array(
            'CategoryLevel1' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL1'),
            'CategoryLevel2' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL2'),
            'CategoryLevel3' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL3'),
            'CategoryLevel4' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL4'),
            'CategoryLevel5' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL5'),
            'CategoryLevel6' => _t('SilvercartGoogleMerchantTaxonomy.LEVEL6')
        );
        
        return $summaryFields;
    }
    
    /**
     * Returns a breadcrumb string for the full path of this object.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 09.08.2011
     */
    public function BreadCrumb() {
        $breadcrumbs            = '';
        $breadcrumbSeparator    = ' > ';

        foreach ($this->db() as $dbFieldName => $dbFieldDefinition) {
            if (substr($dbFieldName, 0, 13) == 'CategoryLevel' &&
                !empty($this->$dbFieldName)) {

                if (!empty($breadcrumbs)) {
                    $breadcrumbs .= $breadcrumbSeparator;
                }
                $breadcrumbs .= $this->$dbFieldName;
            }
        }
        
        return $breadcrumbs;
    }
}