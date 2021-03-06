<?php

namespace SilverCart\Forms\FormFields;

/** 
 * Fixes the tabIndex for TextareaField.
 *
 * @package SilverCart
 * @subpackage Forms_FormFields
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 26.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class TextareaField extends \SilverStripe\Forms\TextareaField {
    
    /**
     * Placeholder to set
     *
     * @var string
     */
    protected $placeholder = '';
    
    /**
     * Returns the placeholder
     *
     * @return string
     */
    public function getPlaceholder() {
        return $this->placeholder;
    }

    /**
     * Sets the placeholder
     *
     * @param string $placeholder Placeholder to set
     * 
     * @return void
     */
    public function setPlaceholder($placeholder) {
        $this->placeholder = $placeholder;
    }

    /**
     * Returns the attributes
     * 
     * @return array
     */
    public function getAttributes() {
        return array_merge(
            parent::getAttributes(),
            [
                'placeholder' => $this->getPlaceholder(),
            ]
        );
    }

}