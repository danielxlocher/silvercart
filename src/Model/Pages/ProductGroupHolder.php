<?php

namespace SilverCart\Model\Pages;

use SilverCart\Dev\SeoTools;
use SilverCart\Dev\Tools;
use SilverCart\Forms\FormFields\FieldGroup;
use SilverCart\Model\Pages\ProductGroupPage;
use SilverCart\View\GroupView\GroupViewHandler;
use SilverStripe\CMS\Model\RedirectorPage;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\Map;

/**
 * Page to display a group of products.
 *
 * @package SilverCart
 * @subpackage Model_Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 28.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ProductGroupHolder extends \Page {
    
    /**
     * Attributes.
     *
     * @var array
     */
    private static $db = array(
        'productGroupsPerPage'          => 'Int',
        'DefaultGroupHolderView'        => 'Varchar(255)',
        'UseOnlyDefaultGroupHolderView' => 'Enum("no,yes,inherit","inherit")',
        'DefaultGroupView'              => 'Varchar(255)',
        'UseOnlyDefaultGroupView'       => 'Enum("no,yes,inherit","inherit")',
        'RedirectToProductGroup'        => 'Boolean(0)',
    );
    
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = array(
        'LinkTo' => SiteTree::class,
    );

    /**
     * DB table name
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductGroupHolder';

    /**
     * Allowed children in site tree
     *
     * @var array
     */
    private static $allowed_children = array(
        ProductGroupPage::class,
        RedirectorPage::class,
    );
    
    /**
     * Icon to use in SiteTree
     *
     * @var string
     */
    private static $icon = "silvercart/silvercart:client/img/page_icons/product_group_holder-file.gif";
    
    /**
     * Indicator to check whether getCMSFields is called
     *
     * @var boolean
     */
    protected $getCMSFieldsIsCalled = false;
    
    /**
     * Cache key parts for this product group
     * 
     * @var array 
     */
    protected $cacheKeyParts = null;
    
    /**
     * Cache key for this product group
     * 
     * @var string
     */
    protected $cacheKey = null;

    /**
     * Singular name for this object
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function singular_name() {
        return Tools::singular_name_for($this);
    }
    
    /**
     * Plural name for this object
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function plural_name() {
        return Tools::plural_name_for($this);
    }

    /**
     * Field labels for display in tables.
     *
     * @param boolean $includerelations A boolean value to indicate if the labels returned include relation fields
     *
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 06.06.2012
     */
    public function fieldLabels($includerelations = true) {
        $fieldLabels = array_merge(
            parent::fieldLabels($includerelations),
            array(
                'productGroupsPerPage'          => _t(ProductGroupPage::class . '.PRODUCTGROUPSPERPAGE', 'Product groups per page'),
                'ProductsPerPageHint'           => _t(ProductGroupPage::class . '.PRODUCTSPERPAGEHINT', 'Set products or product groups per page to 0 (zero) to use the default setting.'),
                'DefaultGroupHolderView'        => _t(ProductGroupPage::class . '.DEFAULTGROUPHOLDERVIEW', 'Default product group view'),
                'UseOnlyDefaultGroupHolderView' => _t(ProductGroupPage::class . '.USEONLYDEFAULTGROUPHOLDERVIEW', 'Allow only default view'),
                'DefaultGroupView'              => _t(ProductGroupPage::class . '.DEFAULTGROUPVIEW', 'Default product view'),
                'DefaultGroupViewInherit'       => _t(ProductGroupPage::class . '.DEFAULTGROUPVIEW_DEFAULT', 'Use view from parent pages'),
                'UseOnlyDefaultGroupView'       => _t(ProductGroupPage::class . '.USEONLYDEFAULTGROUPVIEW', 'Allow only default view'),
                'DisplaySettings'               => _t(ProductGroupPage::class . '.DisplaySettings', 'Display settings'),
                'RedirectionSettings'           => _t(ProductGroupHolder::class . '.RedirectionSettings', 'Redirection'),
                'RedirectToProductGroup'        => _t(ProductGroupHolder::class . '.RedirectToProductGroup', 'Redirect to a product group'),
                'LinkTo'                        => _t(ProductGroupHolder::class . '.LinkTo', 'Product group to redirect to'),
                'Yes'                           => Tools::field_label('Yes'),
                'No'                            => Tools::field_label('No'),
            )
        );

        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Return all fields of the backend.
     *
     * @return FieldList Fields of the CMS
     */
    public function getCMSFields() {
        $this->getCMSFieldsIsCalled = true;
        $fields = parent::getCMSFields();
        
        $useOnlydefaultGroupviewSource  = array(
            'inherit'   => $this->fieldLabel('DefaultGroupViewInherit'),
            'yes'       => $this->fieldLabel('Yes'),
            'no'        => $this->fieldLabel('No'),
        );

        $defaultGroupViewField              = GroupViewHandler::getGroupViewDropdownField('DefaultGroupView', $this->fieldLabel('DefaultGroupView'), $this->DefaultGroupView, $this->fieldLabel('DefaultGroupViewInherit'));
        $useOnlyDefaultGroupViewField       = new DropdownField('UseOnlyDefaultGroupView',  $this->fieldLabel('UseOnlyDefaultGroupView'), $useOnlydefaultGroupviewSource, $this->UseOnlyDefaultGroupView);
        $productGroupsPerPageField          = new TextField('productGroupsPerPage',         $this->fieldLabel('productGroupsPerPage'));
        $defaultGroupHolderViewField        = GroupViewHandler::getGroupViewDropdownField('DefaultGroupHolderView', $this->fieldLabel('DefaultGroupHolderView'), $this->DefaultGroupHolderView, $this->fieldLabel('DefaultGroupView'));
        $useOnlyDefaultGroupHolderViewField = new DropdownField('UseOnlyDefaultGroupHolderView',  $this->fieldLabel('UseOnlyDefaultGroupHolderView'), $useOnlydefaultGroupviewSource, $this->UseOnlyDefaultGroupHolderView);
        $fieldGroup                         = new FieldGroup('FieldGroup', '', $fields);
        $redirectionFieldGroup              = new FieldGroup('RedirectionFieldGroup', '', $fields);
        $redirectToProductGroupField        = new CheckboxField('RedirectToProductGroup', $this->fieldLabel('RedirectToProductGroup'));
        $linkToField                        = new TreeDropdownField('LinkToID', $this->fieldLabel('LinkTo'), SiteTree::class);
        
        $productGroupsPerPageField->setRightTitle($this->fieldLabel('ProductsPerPageHint'));
        
        $fieldGroup->push($defaultGroupViewField);
        $fieldGroup->push($useOnlyDefaultGroupViewField);
        $fieldGroup->breakAndPush($productGroupsPerPageField);
        $fieldGroup->breakAndPush($defaultGroupHolderViewField);
        $fieldGroup->push($useOnlyDefaultGroupHolderViewField);
        
        $redirectionFieldGroup->push($redirectToProductGroupField);
        if ($this->exists()) {
            $linkToField->setTreeBaseID($this->ID);
            $redirectionFieldGroup->breakAndPush($linkToField);
        }
        
        $displaySettingsToggle = ToggleCompositeField::create(
                'DisplaySettingsToggle',
                $this->fieldLabel('DisplaySettings'),
                array(
                    $fieldGroup,
                )
        )->setHeadingLevel(4)->setStartClosed(true);
        
        $redirectionSettingsToggle = ToggleCompositeField::create(
                'RedirectionSettingsToggle',
                $this->fieldLabel('RedirectionSettings'),
                array(
                    $redirectionFieldGroup,
                )
        )->setHeadingLevel(4)->setStartClosed(true);
        
        $fields->insertAfter($redirectionSettingsToggle, 'Content');
        $fields->insertAfter($displaySettingsToggle, 'Content');

        $this->extend('extendCMSFields', $fields);
        return $fields;
    }
    
    /**
     * Returns a dynamic meta description.
     * 
     * @return string
     */
    public function getMetaDescription() {
        $metaDescription = $this->getField('MetaDescription');
        if (!$this->getCMSFieldsIsCalled) {
            if (empty($metaDescription)) {
                $descriptionArray = array($this->Title);
                $children         = $this->Children();
                if ($children->count() > 0) {
                    $map = $children->map();
                    if ($map instanceof Map) {
                        $map = $map->toArray();
                    }
                    $descriptionArray = array_merge($descriptionArray, $map);
                }
                $metaDescription = SeoTools::extractMetaDescriptionOutOfArray($descriptionArray);
            }
            $this->extend('updateMetaDescription', $metaDescription);
        }
        return $metaDescription;
    }

    /**
     * Return the link that we should redirect to.
     * Only return a value if there is a legal redirection destination.
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.070.2014
     */
    public function redirectionLink() {
        $redirectionLink = false;
        if ($this->RedirectToProductGroup) {
            $linkTo = $this->LinkToID ? ProductGroupPage::get()->byID($this->LinkToID) : null;
            if ($linkTo instanceof ProductGroupPage &&
                $linkTo->exists() &&
                $linkTo->ID != $this->ID) {
                $redirectionLink = $linkTo->Link();
            }
        }
        return $redirectionLink;
    }

    /**
     * Checks if ProductGroup has children or products.
     *
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 01.02.2011
     */
    public function hasProductsOrChildren() {
        if (count($this->Children()) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Returns the cache key parts for this product group holder
     * 
     * @return array
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.03.2018
     */
    public function CacheKeyParts() {
        if (is_null($this->cacheKeyParts)) {
            
            $lastEditedChildID = 0;
            if ($this->Children()->Count() > 0) {
                $this->Children()->sort('LastEdited', 'DESC');
                $lastEditedChildID = $this->Children()->First()->ID;
            }
            $ctrl = Controller::curr();
            /* @var $ctrl ProductGroupHolderController */
            
            $cacheKeyParts = array(
                i18n::get_locale(),
                $this->LastEdited,
                $ctrl->getSqlOffsetForProductGroups(),
                GroupViewHandler::getActiveGroupHolderView(),
                $lastEditedChildID,
            );
            $this->extend('updateCacheKeyParts', $cacheKeyParts);
            $this->cacheKeyParts = $cacheKeyParts;
        }
        return $this->cacheKeyParts;
    }
    
    /**
     * Returns the cache key for this product group holder
     * 
     * @return string
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 07.03.2018
     */
    public function CacheKey() {
        if (is_null($this->cacheKey)) {
            $cacheKey = implode('_', $this->CacheKeyParts());
            $this->extend('updateCacheKey', $cacheKey);
            $this->cacheKey = $cacheKey;
        }
        return $this->cacheKey;
    }
    
}