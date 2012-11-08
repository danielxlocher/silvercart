<?php
/**
 * Copyright 2012 pixeltricks GmbH
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
 * @package SilverCart
 * @subpackage Translation
 */

/**
 * Translation object of SilvercartProductGroupItemsWidget
 *
 * @package SilverCart
 * @subpackage Translation
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright Pixeltricks GmbH
 * @since 23.10.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductGroupManufacturersWidgetLanguage extends DataObject {

    /**
     * Attributes.
     *
     * @var array
     */
    public static $db = array(
        'FrontTitle'                    => 'VarChar(255)',
        'FrontContent'                  => 'Text'
    );

    /**
     * 1:1 or 1:n relationships.
     *
     * @var array
     */
    public static $has_one = array(
        'SilvercartProductGroupManufacturersWidget' => 'SilvercartProductGroupManufacturersWidget'
    );

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
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2012 pixeltricks GmbH
     * @since 27.01.2012
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
                parent::fieldLabels($includerelations),
                array(
                    'FrontTitle'                                => _t('SilvercartWidget.FRONTTITLE'),
                    'FrontContent'                              => _t('SilvercartWidget.FRONTCONTENT'),
                    'SilvercartProductGroupManufacturersWidget' => _t('SilvercartProductGroupManufacturersWidget.SINGULARNAME')
                )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }

    /**
     * Summary fields
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.04.2012
     */
    public function summaryFields() {
        $summaryFields = array_merge(
                parent::summaryFields(),
                array(
                    'FrontTitle' => $this->fieldLabel('FrontTitle'),
                )
        );

        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
}
