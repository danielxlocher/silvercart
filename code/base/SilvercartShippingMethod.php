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
 * @subpackage Base
 */

/**
 * Theses are the shipping methods the shop offers
 *
 * @package Silvercart
 * @subpackage Base
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright Pixeltricks GmbH
 * @since 20.10.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartShippingMethod extends DataObject implements SilvercartMultilingualInterface {
    
    /**
     * Attributes.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $db = array(
        'isActive' => 'Boolean'
    );
    /**
     * Has-one relationships.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $has_one = array(
        'SilvercartCarrier'   => 'SilvercartCarrier'
    );
    /**
     * Has-many relationship.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $has_many = array(
        'SilvercartOrders' => 'SilvercartOrder',
        'SilvercartShippingFees' => 'SilvercartShippingFee',
        'SilvercartShippingMethodLanguages' => 'SilvercartShippingMethodLanguage'
    );
    /**
     * Many-many relationships.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $many_many = array(
        'SilvercartZones' => 'SilvercartZone'
    );
    /**
     * Belongs-many-many relationships.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $belongs_many_many = array(
        'SilvercartPaymentMethods' => 'SilvercartPaymentMethod'
    );
    /**
     * Summaryfields for display in tables.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $summary_fields = array(
        'Title' => 'Bezeichnung',
        'activatedStatus' => 'Aktiviert',
        'AttributedZones' => 'Für Zonen',
        'SilvercartCarrier.Title' => 'Frachtführer'
    );
    /**
     * Column labels for display in tables.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $field_labels = array(
        'Title' => 'Bezeichnung',
        'activatedStatus' => 'Aktiviert',
        'AttributedZones' => 'Für Zonen'
    );
    /**
     * Virtual database columns.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $casting = array(
        'AttributedCountries' => 'Varchar(255)',
        'activatedStatus' => 'Varchar(255)',
        'Title' => 'Text'
    );
    /**
     * List of searchable fields for the model admin
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $searchable_fields = array(
        'isActive',
        'SilvercartCarrier.ID' => array(
            'title' => 'Frachtführer'
        ),
        'SilvercartZones.ID' => array(
            'title' => 'Für Zonen'
        )
    );
    
    /**
     * Searchable fields
     *
     * @return array
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 5.7.2011
     */
    public function searchableFields() {
        $searchableFields = array(
            'SilvercartShippingMethodLanguages.Title' => array(
                'title' => _t('SilvercartProduct.COLUMN_TITLE'),
                'filter' => 'PartialMatchFilter'
            ),
            'isActive' => array(
                'title' => _t('SilvercartProduct.IS_ACTIVE'),
                'filter' => 'ExactMatchFilter'
            ),
            'SilvercartCarrier.ID' => array(
                'title' => _t('SilvercartCarrier.SINGULARNAME'),
                'filter' => 'ExactMatchFilter'
            ),
            'SilvercartZones.ID' => array(
                'title' => _t('SilvercartShippingMethod.FOR_ZONES', 'for zones'),
                'filter'    => 'ExactMatchFilter'
            )
        );
        $this->extend('updateSearchableFields', $searchableFields);
        return $searchableFields;
    }
    
    /**
     * Sets the field labels.
     *
     * @param bool $includerelations set to true to include the DataObjects relations
     * 
     * @return array
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 05.07.2011
     */
    public function fieldLabels($includerelations = true) {
        return array_merge(
                parent::fieldLabels($includerelations),
                array(
                        'activatedStatus' => _t('SilvercartShopAdmin.PAYMENT_ISACTIVE'),
                        'AttributedZones' => _t('SilvercartShippingMethod.FOR_ZONES', 'for zones'),
                        'isActive' => _t('SilvercartPage.ISACTIVE', 'active'),
                        'SilvercartCarrier' => _t('SilvercartCarrier.SINGULARNAME', 'carrier'),
                        'SilvercartShippingFees' => _t('SilvercartShippingFee.PLURALNAME', 'shipping fees'),
                        'SilvercartZones' => _t('SilvercartZone.PLURALNAME', 'zones'),
                        'SilvercartShippingMethodLanguages' => _t('SilvercartConfig.TRANSLATION')
                    )
                );
    }
    
    /**
     * Sets the summary fields.
     *
     * @return array
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 5.7.2011
     */
    public function summaryFields() {
        return array_merge(
                parent::summaryFields(),
                array(
                        'Title' => _t('SilvercartProduct.COLUMN_TITLE'),
                        'activatedStatus' => _t('SilvercartShopAdmin.PAYMENT_ISACTIVE'),
                        'AttributedZones' => _t('SilvercartShippingMethod.FOR_ZONES', 'for zones'),
                        'SilvercartCarrier.Title' => _t('SilvercartCarrier.SINGULARNAME')
                    )
                );
        
    }
    
    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string The objects singular name 
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 5.7.2011
     */
    public function singular_name() {
        if (_t('SilvercartShippingMethod.SINGULARNAME')) {
            return _t('SilvercartShippingMethod.SINGULARNAME');
        } else {
            return parent::singular_name();
        } 
    }
    
    /**
     * Returns the translated plural name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string the objects plural name
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 5.7.2011 
     */
    public function plural_name() {
        if (_t('SilvercartShippingMethod.PLURALNAME')) {
            return _t('SilvercartShippingMethod.PLURALNAME');
        } else {
            return parent::plural_name();
        }   
    }

    /**
     * customizes the backends fields, mainly for ModelAdmin
     *
     * @return FieldSet the fields for the backend
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 28.10.10
     */
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        //multilingual fields, in fact just the title
        $languageFields = SilvercartLanguageHelper::prepareCMSFields($this->getLanguage());
        foreach ($languageFields as $languageField) {
            $fields->insertBefore($languageField, 'isActive');
        }
        $fields->removeByName('SilvercartCountries');
        $fields->removeByName('SilvercartPaymentMethods');
        $fields->removeByName('SilvercartOrders');
        $fields->removeByName('SilvercartZones');

        $zonesTable = new ManyManyComplexTableField(
            $this,
            'SilvercartZones',
            'SilvercartZone',
            null,
            'getCMSFields_forPopup'
        );
        $fields->addFieldToTab('Root.' . _t('SilvercartZone.PLURALNAME', 'zones'), $zonesTable);

        return $fields;
    }

    /**
     * determins the right shipping fee for a shipping method depending on the cart´s weight
     *
     * @return ShippingFee the most convenient shipping fee for this shipping method
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 9.11.2010
     */
    public function getShippingFee() {
        $fee             = false;
        $cartWeightTotal = Member::currentUser()->SilvercartShoppingCart()->getWeightTotal();
        $fees            = DataObject::get(
            'SilvercartShippingFee',
            sprintf(
                "`SilvercartShippingMethodID` = '%s' AND (`MaximumWeight` >= %d OR `UnlimitedWeight` = 1)",
                $this->ID,
                $cartWeightTotal
            )
        );

        if ($fees) {
            $fees->sort('PriceAmount');
            $fee = $fees->First();
        }
        
        return $fee;
    }
    
    /**
     * getter for the shipping methods title
     *
     * @return string the title in the corresponding front end language 
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 11.01.2012
     */
    public function getTitle() {
        $title = '';
        if ($this->getLanguage()) {
            $title = $this->getLanguage()->Title;
        }
        return $title;
    }

    /**
     * pseudo attribute which can be called with $this->TitleWithCarrierAndFee
     *
     * @return string carrier + title + fee
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 15.11.2010
     */
    public function getTitleWithCarrierAndFee() {
        if ($this->getShippingFee()) {
            $titleWithCarrierAndFee = $this->SilvercartCarrier()->Title . "-" .
                $this->Title . " (+" .
                number_format($this->getShippingFee()->Price->getAmount(), 2, ',', '') .
                $this->getShippingFee()->Price->getSymbol() .
                ")";

            return $titleWithCarrierAndFee;
        } else {
            return false;
        }
    }
    
    /**
     * pseudo attribute
     *
     * @return false|string
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 11.10.2011
     */
    public function getTitleWithCarrier() {
        if ($this->SilvercartCarrier()) {
            return $this->SilvercartCarrier()->Title . "-" . $this->Title;
        }
        return false;
    }

    /**
     * Returns the attributed zones as string (limited to 150 chars).
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function AttributedZones() {
        $attributedZonesStr = '';
        $attributedZones = array();
        $maxLength = 150;

        foreach ($this->SilvercartZones() as $SilvercartZone) {
            $attributedZones[] = $SilvercartZone->Title;
        }

        if (!empty($attributedZones)) {
            $attributedZonesStr = implode(', ', $attributedZones);

            if (strlen($attributedZonesStr) > $maxLength) {
                $attributedZonesStr = substr($attributedZonesStr, 0, $maxLength) . '...';
            }
        }

        return $attributedZonesStr;
    }

    /**
     * Returns the attributed payment methods as string (limited to 150 chars).
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function AttributedPaymentMethods() {
        $attributedPaymentMethodsStr = '';
        $attributedPaymentMethods = array();
        $maxLength = 150;

        foreach ($this->SilvercartPaymentMethods() as $SilvercartPaymentMethod) {
            $attributedPaymentMethods[] = $SilvercartPaymentMethod->Title;
        }

        if (!empty($attributedPaymentMethods)) {
            $attributedPaymentMethodsStr = implode(', ', $attributedPaymentMethods);

            if (strlen($attributedPaymentMethodsStr) > $maxLength) {
                $attributedPaymentMethodsStr = substr($attributedPaymentMethodsStr, 0, $maxLength) . '...';
            }
        }

        return $attributedPaymentMethodsStr;
    }

    /**
     * Returns the activation status as HTML-Checkbox-Tag.
     *
     * @return CheckboxField
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function activatedStatus() {
        $checkboxField = new CheckboxField('isActivated' . $this->ID, 'isActived', $this->isActive);

        return $checkboxField;
    }

    /**
     * Returns allowed shipping methods. Those are active
     *
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 11.07.2011
     */
    public static function getAllowedShippingMethods() {
        $allowedShippingMethods = array();
        $shippingMethods        = DataObject::get('SilvercartShippingMethod', 'isActive = 1');

        if ($shippingMethods) {
            foreach ($shippingMethods as $shippingMethod) {                
                // If there is no shipping fee defined for this shipping
                // method we don't want to show it.
                if ($shippingMethod->getShippingFee() !== false) {
                    $allowedShippingMethods[] = $shippingMethod;
                }
            }
        }
        
        $allowedShippingMethods = new DataObjectSet($allowedShippingMethods);
        
        return $allowedShippingMethods;
    }
    
    /**
     * Getter for the related language object depending on the set language
     * Always returns a SilvercartShippingMethodLanguage
     *
     * @return SilvercartShippingMethodLanguage
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 11.01.2012
     */
    public function getLanguage() {
        if (!isset ($this->languageObj)) {
            $this->languageObj = SilvercartLanguageHelper::getLanguage($this->SilvercartShippingMethodLanguages());
            if (!$this->languageObj) {
                $this->languageObj = new SilvercartShippingMethodLanguage();
                $this->languageObj->Locale = Translatable::get_current_locale();
                $this->languageObj->SilvercartShippingMethodID = $this->ID;
            }
        }
        return $this->languageObj;
    }

}
