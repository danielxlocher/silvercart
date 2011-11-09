<?php
/**
 * Copyright 2011 pixeltricks GmbH
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
 * @subpackage Widgets
 */

/**
 * Contains an arbitrary number of widgets.
 *
 * @package Silvercart
 * @subpackage Widgets
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @since 27.05.2011
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartWidgetSet extends DataObject {
    
    /**
     * singular name for backend
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.05.2011
     */
    public static $singular_name = "Widget set";

    /**
     * plural name for backend
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.05.2011
     */
    public static $plural_name = "Widget sets";
    
    /**
     * Attributes
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.05.2011
     */
    public static $db = array(
        'Title' => 'VarChar(255)'
    );
    
    /**
     * Has-one relationships
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.05.2011
     */
    public static $has_one = array(
        'WidgetArea' => 'WidgetArea'
    );
    
    /**
     * Has-many relationships
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.05.2011
     */
    public static $belongs_many_many = array(
        'SilvercartPages' => 'SilvercartPage'
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
        if (_t('SilvercartWidgetSet.SINGULARNAME')) {
            return _t('SilvercartWidgetSet.SINGULARNAME');
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
        if (_t('SilvercartWidgetSet.PLURALNAME')) {
            return _t('SilvercartWidgetSet.PLURALNAME');
        } else {
            return parent::plural_name();
        }   
    }
    
    /**
     * Returns the GUI fields for the storeadmin.
     * 
     * @param array $params Additional parameters
     * 
     * @return FieldSet
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.05.2011
     */
    public function getCMSFields($params = null) {
        $fields = parent::getCMSFields($params);
        
        if ($this->ID > 0) {
            $fields->removeByName('WidgetAreaID');
            $fields->removeFieldFromTab('Root', 'SilvercartPages');
            $availableWidgets = array();

            $classes = ClassInfo::subclassesFor('Widget');
            array_shift($classes);
            foreach($classes as $class) {
                if ($class == 'SilvercartWidget') {
                    continue;
                }
                $widgetClass        = singleton($class);
                $availableWidgets[] = array($widgetClass->ClassName, $widgetClass->Title());
            }

            $widgetAreaField = new SilvercartHasManyOrderField(
                $this->WidgetArea(),
                'Widgets',
                'WidgetArea',
                'Widget Konfiguration',
                $availableWidgets
            );

            $fields->addFieldToTab('Root.Main', $widgetAreaField);
        } else {
            $fields->removeByName('WidgetAreaID');
        }
        
        return $fields;
    }
    
    /**
     * Summary fields for display in tables.
     * 
     * @return array
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.05.2011
     */
    public function summaryFields() {
        $fields = array(
            'Title' => 'Title'
        );
        
        return $fields;
    }
    
    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 02.10.2011
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
                parent::fieldLabels($includerelations),             array(
                    'Title' => _t('SilvercartAvailabilityStatus.TITLE')
                )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * We have to create a WdgetArea object if there's none attributed yet.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 20.10.2011
     */
    public function onAfterWrite() {
        parent::onAfterWrite();
        
        if ($this->WidgetAreaID == 0) {
            $widgetArea = new WidgetArea();
            $widgetArea->write();
            
            $this->WidgetAreaID = $widgetArea->ID;
            $this->write();
        }
    }
    
    /**
     * We want to delete all attributed WidgetAreas and Widgets before deletion.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 20.10.2011
     */
    public function onBeforeDelete() {
        parent::onBeforeDelete();
        
        foreach ($this->WidgetArea()->Widgets() as $widget) {
            $widget->delete();
        }
        
        $this->WidgetArea()->delete();
    }
}
