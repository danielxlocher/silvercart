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
 * @subpackage Order
 */

/**
 * A carrier has many shipping fees.
 * They mainly depend on the freights weight.
 *
 * @package Silvercart
 * @subpackage Order
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright Pixeltricks GmbH
 * @since 06.11.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartShippingFee extends DataObject {

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
        'MaximumWeight'     => 'Int',   //gramms
        'UnlimitedWeight'  => 'Boolean',
        'Price'             => 'Money',
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
        'SilvercartZone'              => 'SilvercartZone',
        'SilvercartShippingMethod'    => 'SilvercartShippingMethod',
        'SilvercartTax'               => 'SilvercartTax'
    );

    /**
     * Has-many Relationship.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $has_many = array(
        'SilvercartOrders' => 'SilvercartOrder'
    );

    /**
     * Virtual database fields.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $casting = array(
        'PriceFormatted'                => 'Varchar(20)',
        'AttributedShippingMethods'     => 'Varchar(255)',
        'MaximumWeightLimitedOrNot'    => 'Varchar(255)',
    );
    
    
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
        if (_t('SilvercartShippingFee.SINGULARNAME')) {
            return _t('SilvercartShippingFee.SINGULARNAME');
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
        if (_t('SilvercartShippingFee.PLURALNAME')) {
            return _t('SilvercartShippingFee.PLURALNAME');
        } else {
            return parent::plural_name();
        }   
    }

    /**
     * i18n for summary fields
     *
     * @return array
     * 
     * @author Seabstian Diel <sdiel@pixeltricks.de>
     * @since 28.04.2011
     */
    public function summaryFields() {
        return array_merge(
                parent::summaryFields(),
                array(
                    'SilvercartZone.Title'      => _t('SilvercartShippingMethod.FOR_ZONES'),
                    'AttributedShippingMethods' => _t('SilvercartShippingFee.ATTRIBUTED_SHIPPINGMETHOD', 'attributed shipping method'),
                    'MaximumWeightLimitedOrNot' => _t('SilvercartShippingFee.MAXIMUM_WEIGHT', 'maximum weight (g)', null, 'Maximalgewicht (g)'),
                    'PriceFormatted'            => _t('SilvercartShippingFee.COSTS', 'costs')
                )
        );
    }

    /**
     * i18n for field labels
     *
     * @param bool $includerelations a boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     * 
     * @author Seabstian Diel <sdiel@pixeltricks.de>
     * @since 28.04.2011
     */
    public function fieldLabels($includerelations = true) {
        return array_merge(
                parent::fieldLabels($includerelations),
                array(
                    'MaximumWeight'             => _t('SilvercartShippingFee.MAXIMUM_WEIGHT'),
                    'UnlimitedWeight'           => _t('SilvercartShippingFee.UNLIMITED_WEIGHT_LABEL'),
                    'Price'                     => _t('SilvercartShippingFee.COSTS'),
                    'SilvercartZone.Title'      => _t('SilvercartShippingMethod.FOR_ZONES'),
                    'AttributedShippingMethods' => _t('SilvercartShippingFee.ATTRIBUTED_SHIPPINGMETHOD'),
                    'SilvercartTax'             => _t('SilvercartTax.SINGULARNAME', 'tax'),
                    ''
                )
        );
    }
    
    /**
     * Returns the maximum weight or a hint, that this fee is unlimited.
     *
     * @return string
     */
    public function getMaximumWeightLimitedOrNot() {
        $maximumWeightLimitedOrNot = $this->MaximumWeight;
        if ($this->UnlimitedWeight) {
            $maximumWeightLimitedOrNot = _t('SilvercartShippingFee.UNLIMITED_WEIGHT');
        }
        return $maximumWeightLimitedOrNot;
    }

    
    /**
     * Set a custom search context for fields like "greater than", "less than",
     * etc.
     *
     * @return SearchContext
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function getDefaultSearchContext() {
        $fields     = $this->scaffoldSearchFields();
        $filters    = array(
            'MaximumWeight'             => new LessThanFilter('MaximumWeight')
        );
        return new SearchContext(
            $this->class,
            $fields,
            $filters
        );
    }

    /**
     * Customizes the backends fields, mainly for ModelAdmin
     *
     * @return FieldSet the fields for the backend
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 8.11.10
     */
    public function getCMSFields() {
       $fields = parent::getCMSFields();

       /**
        * only the carriers zones must be selectable
        */
       $fields->removeByName('SilvercartZone');
       $filter  = sprintf("`SilvercartCarrierID` = %s", $this->SilvercartShippingMethod()->SilvercartCarrier()->ID);
       $zones   = DataObject::get('SilvercartZone', $filter);
       if ($zones) {
           $fields->addFieldToTab(
                "Root.Main",
                new DropdownField(
                    'SilvercartZoneID',
                    _t('SilvercartShippingFee.ZONE_WITH_DESCRIPTION', 'zone (only carrier\'s zones available)'),
                   $zones->toDropDownMap('ID', 'Title', _t('SilvercartShippingFee.EMPTYSTRING_CHOOSEZONE', '--choose zone--'))
                )
           );
        }

       return $fields;
    }

    /**
     * Returns the Price formatted by locale.
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function PriceFormatted() {
        return $this->Price->Nice();
    }

    /**
     * Returns the attributed shipping methods as string (limited to 150 chars).
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function AttributedShippingMethods() {
        $attributedShippingMethodsStr = '';
        $attributedShippingMethods    = array();
        $maxLength          = 150;

        foreach ($this->SilvercartShippingMethod() as $shippingMethod) {
            $attributedShippingMethods[] = $shippingMethod->Title;
        }

        if (!empty($attributedShippingMethods)) {
            $attributedShippingMethodsStr = implode(', ', $attributedShippingMethods);

            if (strlen($attributedShippingMethodsStr) > $maxLength) {
                $attributedShippingMethodsStr = substr($attributedShippingMethodsStr, 0, $maxLength).'...';
            }
        }

        return $attributedShippingMethodsStr;
    }

    /**
     * returns the tax amount included in $this
     *
     * @return float
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 07.02.2011
     */
    public function getTaxAmount() {
        $taxRate = $this->Price->getAmount() - ($this->Price->getAmount() / (100 + $this->SilvercartTax()->getTaxRate()) * 100);

        return $taxRate;
    }
    
    /**
     * helper method for displaying a fee in a dropdown menu
     *
     * @return false|string [carrier] - [shipping method] (+[fee amount][currency])
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 10.10.2011
     */
    public function getFeeWithCarrierAndShippingMethod() {
        if ($this->SilvercartShippingMethod()) {
            $carrier = "";
            if ($this->SilvercartShippingMethod()->SilvercartCarrier()) {
                $carrier = $this->SilvercartShippingMethod()->SilvercartCarrier()->Title;
            }
            $shippingMethod = $this->SilvercartShippingMethod()->Title;
            $shippingFeeAmountAsString = number_format($this->Price->getAmount(), 2, ',', '') .$this->Price->getCurrency();
            
            return $carrier . "-" . $shippingMethod . "(+" . $shippingFeeAmountAsString . ")";
        }
        return false;
    }
}

