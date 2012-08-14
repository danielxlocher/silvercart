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
 * abstract for an order status
 *
 * @package Silvercart
 * @subpackage Order
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @copyright 2010 pixeltricks GmbH
 * @since 22.11.2010
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartOrderStatus extends DataObject {

    /**
     * attributes
     *
     * @var array
     */
    public static $db = array(
        'Code' => 'VarChar'
    );

    /**
     * 1:n relations
     *
     * @var array
     */
    public static $has_many = array(
        'SilvercartOrders'      => 'SilvercartOrder',
        'SilvercartOrderStatusLanguages' => 'SilvercartOrderStatusLanguage'
    );

    /**
     * n:m relations
     *
     * @var array
     */
    public static $many_many = array(
        'SilvercartShopEmails'  => 'SilvercartShopEmail'
    );

    /**
     * n:m relations
     *
     * @var array
     */
    public static $belongs_many_many = array(
        'SilvercartPaymentMethodRestrictions' => 'SilvercartPaymentMethod'
    );
    
    /**
     * Castings
     *
     * @var array 
     */
    public static $casting = array(
        'Title' => 'VarChar(255)'
    );
    
    /**
     * Default sort
     *
     * @var string 
     */
    public static $default_sort = "`SilvercartOrderStatusLanguage`.`Title`";

    /**
     * Returns the translated singular name of the object. If no translation exists
     * the class name will be returned.
     * 
     * @return string The objects singular name 
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.07.2012
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
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.07.2012
     */
    public function plural_name() {
        return SilvercartTools::plural_name_for($this);
    }  
    
    /**
     * retirieves title from related language class depending on the set locale
     * Title is a very common attribute and is therefore located in the decorator
     *
     * @return string 
     */
    public function getTitle() {
        return $this->getLanguageFieldValue('Title');
    }

    /**
     * Get any user defined searchable fields labels that
     * exist. Allows overriding of default field names in the form
     * interface actually presented to the user.
     *
     * The reason for keeping this separate from searchable_fields,
     * which would be a logical place for this functionality, is to
     * avoid bloating and complicating the configuration array. Currently
     * much of this system is based on sensible defaults, and this property
     * would generally only be set in the case of more complex relationships
     * between data object being required in the search interface.
     *
     * Generates labels based on name of the field itself, if no static property
     * {@link self::field_labels} exists.
     *
     * @param boolean $includerelations a boolean value to indicate if the labels returned include relation fields
     *
     * @return array|string Array of all element labels if no argument given, otherwise the label of the field
     *
     * @uses $field_labels
     * @uses FormField::name_to_label()
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.02.2011
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
            parent::fieldLabels($includerelations),
            array(
                'Title'                                 => _t('SilvercartPage.TITLE'),
                'Code'                                  => _t('SilvercartOrderStatus.CODE'),
                'SilvercartOrders'                      => _t('SilvercartOrder.PLURALNAME'),
                'SilvercartPaymentMethodRestrictions'   => _t('SilvercartPaymentMethod.PLURALNAME'),
                'SilvercartOrderStatusLanguages'        => _t('SilvercartOrderStatusLanguage.PLURALNAME'),
                'ShopEmailsTab'                         => _t('SilvercartOrderStatus.ATTRIBUTED_SHOPEMAILS_LABEL_TITLE'),
                'ShopEmailLabelField'                   => _t('SilvercartOrderStatus.ATTRIBUTED_SHOPEMAILS_LABEL_DESC'),
            )
        );
        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }

    /**
     * remove attribute Code from the CMS fields
     *
     * @return FieldSet all CMS fields related
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.07.2012
     */
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeByName('SilvercartShopEmails');
        
        $languageFields = SilvercartLanguageHelper::prepareCMSFields($this->getLanguage(true));
        foreach ($languageFields as $languageField) {
            $fields->addFieldToTab('Root.Main', $languageField);
        }
        // Add shop email field
        $shopEmailLabelField = new LiteralField(
            'ShopEmailLabelField',
            sprintf(
                "<br /><p>%s</p>",
                $this->fieldLabel('ShopEmailLabelField')
            )
        );
        $shopEmailField = new ManyManyComplexTableField(
            $this,
            'SilvercartShopEmails',
            'SilvercartShopEmail'
        );
        $shopEmailField->setPageSize(20);

        $fields->findOrMakeTab('Root.shopEmails', $this->fieldLabel('ShopEmailsTab'));
        
        $fields->addFieldToTab('Root.shopEmails', $shopEmailLabelField);
        $fields->addFieldToTab('Root.shopEmails', $shopEmailField);

        $this->extend('updateCMSFields', $fields);

        return $fields;
    }
    
    /**
     * Sends a mail with the given SilvercartOrder object as data provider.
     * 
     * @param SilvercartOrder $order The order object that is used to fill the
     *                               mail template variables.
     * 
     * @return void
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.10.2011
     */
    public function sendMailFor(SilvercartOrder $order) {
        $shopEmails = $this->SilvercartShopEmails();
        
        if ($shopEmails) {
            foreach ($shopEmails as $shopEmail) {
                SilvercartShopEmail::send(
                    $shopEmail->Identifier,
                    $order->CustomersEmail,
                    array(
                        'SilvercartOrder'   => $order,
                        'FirstName'         => $order->SilvercartInvoiceAddress()->FirstName,
                        'Surname'           => $order->SilvercartInvoiceAddress()->Surname,
                        'Salutation'        => $order->SilvercartInvoiceAddress()->Salutation
                    )
                );
            }
        }
        
        $this->extend('updateSendMailFor', $order);
    }

    /**
     * returns array with StatusCode => StatusText
     *
     * @return DataObjectSet
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 23.11.2010
     */
    public static function getStatusList() {
        $statusList = DataObject::get(
            'SilvercartOrderStatus'
        );

        return $statusList;
    }
    
    /**
     * Summaryfields for display in tables.
     *
     * @return array
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.07.2012
     */
    public function summaryFields() {
        $summaryFields = array(
            'Code'  => $this->fieldLabel('Code'),
            'Title' => $this->fieldLabel('Title'),
        );

        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
}
