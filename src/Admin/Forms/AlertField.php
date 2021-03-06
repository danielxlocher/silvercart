<?php

namespace SilverCart\Admin\Forms;

use SilverStripe\Forms\DatalessField;

/**
 * Dataless form field to display information alerts.
 *
 * @package SilverCart
 * @subpackage Admin_Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 25.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class AlertField extends DatalessField {
    
    /**
     * Alert field title
     *
     * @var string
     */
    protected $alertTitle = null;
    
    /**
     * Alert type (info/warning/danger/success)
     *
     * @var string
     */
    protected $alertType = 'info';
    
    /**
     * Alert content
     *
     * @var string
     */
    protected $content = null;
    
    /**
     * Creates a new field.
     * 
     * @param string $name    Field name
     * @param string $content Field content to display
     * @param string $title   Field title to display
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 22.07.2016
     */
    public function __construct($name, $content = null, $title = null) {
        parent::__construct($name, $title);
        $this->setContent($content);
        $this->setAlertTitle($title);
    }
    
    /**
     * Returns the alert title.
     * 
     * @return string
     */
    public function getAlertTitle() {
        return $this->alertTitle;
    }

    /**
     * Sets the alert title.
     * 
     * @param string $alertTitle Alert title
     * 
     * @return void
     */
    public function setAlertTitle($alertTitle) {
        $this->alertTitle = $alertTitle;
    }
    
    /**
     * Returns the alert type.
     * 
     * @return string
     */
    public function getAlertType() {
        return $this->alertType;
    }

    /**
     * Sets the alert type.
     * 
     * @param string $alertType Alert type
     * 
     * @return void
     */
    public function setAlertType($alertType) {
        $this->alertType = $alertType;
    }
    
    /**
     * Returns the content.
     * 
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Sets the content.
     * 
     * @param string $content Content
     * 
     * @return void
     */
    public function setContent($content) {
        $this->content = $content;
    }
    
}