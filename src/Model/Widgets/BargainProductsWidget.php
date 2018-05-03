<?php

namespace SilverCart\Model\Widgets;

use SilverCart\Model\Translation\TranslationTools;
use SilverCart\Model\Widgets\BargainProductsWidgetTranslation;
use SilverCart\Model\Widgets\ProductSliderWidget;
use SilverCart\Model\Widgets\Widget;
use SilverCart\Model\Widgets\WidgetTools;
use SilverCart\View\GroupView\GroupViewHandler;

/**
 * Provides the a view of the bargain products.
 * You can define the number of products to be shown.
 *
 * @package SilverCart
 * @subpackage Model_Widgets
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 29.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class BargainProductsWidget extends Widget {
    
    use ProductSliderWidget;
    
    /**
     * DB attributes of this widget
     * 
     * @var array
     */
    private static $db = array(
        'numberOfProductsToShow'        => 'Int',
        'numberOfProductsToFetch'       => 'Int',
        'fetchMethod'                   => "Enum('random,sortOrderAsc,sortOrderDesc','random')",
        'GroupView'                     => 'Varchar(255)',
        'isContentView'                 => 'Boolean',
        'Autoplay'                      => 'Boolean(1)',
        'autoPlayDelayed'               => 'Boolean(1)',
        'autoPlayLocked'                => 'Boolean(0)',
        'buildArrows'                   => 'Boolean(1)',
        'buildNavigation'               => 'Boolean(1)',
        'buildStartStop'                => 'Boolean(1)',
        'slideDelay'                    => 'Int',
        'stopAtEnd'                     => 'Boolean(0)',
        'transitionEffect'              => "Enum('fade,horizontalSlide,verticalSlide','fade')",
        'useSlider'                     => "Boolean(0)",
        'useRoundabout'                 => "Boolean(0)",
    );
    
    /**
     * 1:1 or 1:n relationships.
     *
     * @var array
     */
    private static $has_many = array(
        'BargainProductsWidgetTranslations' => BargainProductsWidgetTranslation::class
    );
    
    /**
     * Set default values.
     * 
     * @var array
     */
    private static $defaults = array(
        'numberOfProductsToShow'    => 5,
        'numberOfProductsToFetch'   => 5,
        'slideDelay'                => 5000
    );
    
    /**
     * Casted Attributes.
     * 
     * @var array
     */
    private static $casting = array(
        'FrontTitle'   => 'Text',
        'FrontContent' => 'Text',
    );

    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartBargainProductsWidget';
    
    /**
     * Getter for the front title depending on the set language
     *
     * @return string
     */
    public function getFrontTitle() {
        return $this->getTranslationFieldValue('FrontTitle');
    }
    
    /**
     * Getter for the FrontContent depending on the set language
     *
     * @return string The HTML front content
     */
    public function getFrontContent() {
        return $this->getTranslationFieldValue('FrontContent');
    }
    
    /**
     * Returns the input fields for this widget.
     * 
     * @return FieldList
     */
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        
        $fetchMethods = array(
                'random'        => $this->fieldLabel('fetchMethodRandom'),
                'sortOrderAsc'  => $this->fieldLabel('fetchMethodSortOrderAsc'),
                'sortOrderDesc' => $this->fieldLabel('fetchMethodSortOrderDesc'),
        );
        $fetchMethodsField = $fields->dataFieldByName('fetchMethod');
        $fetchMethodsField->setSource($fetchMethods);
        $fields->replaceField('GroupView', GroupViewHandler::getGroupViewDropdownField('GroupView', $this->fieldLabel('GroupView')));
        
        // Temporary disabled slider functions.
        //WidgetTools::getCMSFieldsSliderToggleForSliderWidget($this, $fields);
        $fields->removeByName('numberOfProductsToShow');
        
        return $fields;
    }
    
    /**
     * Returns an array of field/relation names (db, has_one, has_many, 
     * many_many, belongs_many_many) to exclude from form scaffolding in
     * backend.
     * This is a performance friendly way to exclude fields.
     * Excludes all fields that are added in a ToggleCompositeField later.
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>,
     *         Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 19.04.2018
     */
    public function excludeFromScaffolding() {
        $parentExcludes = parent::excludeFromScaffolding();
        
        $excludeFromScaffolding = array_merge(
                $parentExcludes,
                [
                    'Autoplay',
                    'autoPlayDelayed',
                    'autoPlayLocked',
                    'buildArrows',
                    'buildNavigation',
                    'buildStartStop',
                    'slideDelay',
                    'stopAtEnd',
                    'transitionEffect',
                    'useSlider',
                    'useRoundabout'
                ]
        );
        $this->extend('updateExcludeFromScaffolding', $excludeFromScaffolding);
        return $excludeFromScaffolding;
    }

    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.03.2012
     */
    public function fieldLabels($includerelations = true) {
        return array_merge(
                parent::fieldLabels($includerelations),
                WidgetTools::fieldLabelsForProductSliderWidget($this),
                array(
                    'ProductGroupPage'                  => _t(ProductGroupItemsWidget::class . '.STOREADMIN_FIELDLABEL', 'Please choose the product group to display:'),
                    'useSelectionMethod'                => _t(ProductGroupItemsWidget::class . '.USE_SELECTIONMETHOD', 'Selection method for products'),
                    'SelectionMethodProductGroup'       => _t(ProductGroupItemsWidget::class . '.SELECTIONMETHOD_PRODUCTGROUP', 'From product group'),
                    'SelectionMethodProducts'           => _t(ProductGroupItemsWidget::class . '.SELECTIONMETHOD_PRODUCTS', 'Choose manually'),
                    'ProductGroupTab'                   => _t(ProductGroupItemsWidget::class . '.CMS_PRODUCTGROUPTABNAME', 'Product group'),
                    'ProductsTab'                       => _t(ProductGroupItemsWidget::class . '.CMS_PRODUCTSTABNAME', 'Products'),
                    'BargainProductsWidgetTranslations' => _t(TranslationTools::class . '.TRANSLATIONS', 'Translations'),
                )
        );
    }
    
    /**
     * Sets numberOfProductsToFetch to numberOfProductsToShow if it's set to 0.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.03.2016
     */
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if ($this->numberOfProductsToShow == 0) {
            $this->numberOfProductsToShow = $this->numberOfProductsToFetch;
        }
    }
}