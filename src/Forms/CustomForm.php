<?php

namespace SilverCart\Forms;

use ReflectionClass;
use SilverCart\Forms\CustomRequiredFields;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\Validator;

/**
 * custom form definition.
 *
 * @package SilverCart
 * @subpackage Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 03.11.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class CustomForm extends Form {

    /**
     * Default form Name property
     */
    const DEFAULT_NAME = 'CustomForm';

    /**
     * Custom extra CSS classes.
     *
     * @var array
     */
    protected $customExtraClasses = [];
    
    /**
     * Don't enable Security token for this type of form because we'll run
     * into caching problems when using it.
     * 
     * @var boolean
     */
    protected $securityTokenEnabled = true;
    
    /**
     * List of required fields.
     *
     * @var array
     */
    private static $requiredFields = [];

    /**
     * Create a new form, with the given fields an action buttons.
     *
     * @param RequestHandler $controller Optional parent request handler
     * @param string         $name       The method on the controller that will return this form object.
     * @param FieldList      $fields     All of the fields in the form - a {@link FieldList} of {@link FormField} objects.
     * @param FieldList      $actions    All of the action buttons in the form - a {@link FieldLis} of {@link FormAction} objects
     * @param Validator      $validator  Override the default validator instance (Default: {@link RequiredFields})
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.11.2017
     */
    public function __construct(RequestHandler $controller = null, $name = self::DEFAULT_NAME, FieldList $fields = null, FieldList $actions = null, Validator $validator = null) {
        if (!is_null($controller)) {
            $this->setController($controller);
        }
        if ($name == self::DEFAULT_NAME) {
            $reflection = new ReflectionClass($this);
            $name = $reflection->getShortName();
        }
        if (is_null($fields)) {
            $fields = FieldList::create();
        }
        if (is_null($actions)) {
            $actions = FieldList::create();
        }
        if (is_null($validator)) {
            $requiredFields    = $this->getRequiredFields();
            $requiredCallbacks = [];
            if (!empty($requiredFields)) {
                $requiredFieldNames = [];
                foreach ($requiredFields as $key => $value) {
                    if (is_array($value)) {
                        $requiredFieldNames[]    = $key;
                        $requiredCallbacks[$key] = $value;
                    } else {
                        $requiredFieldNames[]      = $value;
                        $requiredCallbacks[$value] = [
                            'isFilledIn' => true,
                        ];
                    }
                }
                $validator = new CustomRequiredFields($requiredFieldNames);
                $validator->setRequiredCallbacks($requiredCallbacks);
            }
        }
        foreach ($this->customExtraClasses as $extraClass) {
            $this->addExtraClass($extraClass);
        }
        if (!$this->securityTokenEnabled) {
            $this->disableSecurityToken();
        }
        parent::__construct($controller, $name, $fields, $actions, $validator);
    }
    
    /**
     * Returns the required fields.
     * 
     * @return array
     */
    public function getRequiredFields() {
        $requiredFields = self::config()->get('requiredFields');
        $this->extend('updateRequiredFields', $requiredFields);
        return $requiredFields;
    }

    /**
     * Flush persistant form state details
     *
     * @return $this
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.11.2017
     */
    public function clearFormState() {
        $this
            ->getSession()
            ->clear("FormInfo.{$this->FormName()}.successMessage");
        return parent::clearFormState();
    }

    /**
     * Populate this form with messages from the given ValidationResult.
     * Note: This will not clear any pre-existing messages
     *
     * @param ValidationResult $result Validation result
     * 
     * @return CustomForm
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.11.2017
     */
    public function loadMessagesFrom($result) {
        $messages = [];
        // Set message on either a field or the parent form
        foreach ($result->getMessages() as $message) {
            $fieldName = $message['fieldName'];
            if ($fieldName) {
                $owner = $this->fields->dataFieldByName($fieldName) ?: $this;
            } else {
                $owner = $this;
            }
            $messages[]  = $errorMessage = $message['message'];
            $messageType = $message['messageType'];
            $messageCast = $message['messageCast'];
            $this->markFieldValidationError($fieldName, $errorMessage, $messageType, $messageCast);
        }
        if (!empty($messages)) {
            $this->setMessage(implode(PHP_EOL, $messages), $messageType, $messageCast);
        }
        return $this;
    }
    
    /**
     * Sets a manual error message.
     * 
     * @param string $message Error message
     * 
     * @return void
     */
    public function setErrorMessage($message) {
        $this->sessionMessage($message, 'error');
    }
    
    /**
     * Sets the success message.
     * 
     * @param string $message Success message
     * 
     * @return void
     */
    public function setSuccessMessage($message) {
        $this->sessionMessage($message, 'success');
    }
    
    /**
     * Sets the default success message.
     * 
     * @return void
     */
    public function setDefaultSuccessMessage() {
        $this->setSuccessMessage(_t(CustomForm::class . '.DefaultSuccessMessageSave', 'Your data was successfully saved.'));
    }
    
    /**
     * Sets the default success message.
     * 
     * @return void
     */
    public function setDefaultSuccessMessageSent() {
        $this->setSuccessMessage(_t(CustomForm::class . '.DefaultSuccessMessageSend', 'Your data was successfully sent.'));
    }
    
    /**
     * Marks the field with the given name as validation error field.
     * 
     * @param string $fieldName    Field name
     * @param string $errorMessage Error message
     * @param string $messageType  Message type
     * @param string $messageCast  Message cast
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.11.2017
     */
    protected function markFieldValidationError($fieldName, $errorMessage, $messageType, $messageCast) {
        $messageType .= ' error';
        $field = $this->Fields()->dataFieldByName($fieldName);
        $field->addExtraClass('error');
        $field->setMessage($errorMessage, $messageType, $messageCast);
    }


    /**
     * Sets the form template by the given suffix.
     * 
     * @param string $suffix Template suffix
     * 
     * @return void
     */
    public function setTemplateBySuffix($suffix) {
        if (empty($suffix)) {
            return;
        }
        $templates      = $this->getTemplates();
        $templateBase   = array_shift($templates);
        $customTemplate = $templateBase . $suffix;
        $this->setTemplate($customTemplate);
    }

    /**
     * Allows user code to hook into CustomForm::Fields prior to updateFields
     * being called on extensions
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.11.2017
     */
    protected function beforeUpdateFields($callback) {
        $this->beforeExtending('updateFields', $callback);
    }

    /**
     * Returns the form fields.
     * 
     * @return FieldList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.11.2017
     */
    public function Fields() {
        $fields = parent::Fields();
        if ($fields->fieldPosition('action_submit') === false) {
            $fields->push(HiddenField::create('action_submit'));
            foreach ($this->getCustomFields() as $field) {
                if ($fields->fieldPosition($field) === false) {
                    $fields->push($field);
                }
            }
            $this->extend('updateFields', $fields);
        }
        return $fields;
    }

    /**
     * Allows user code to hook into CustomForm::Actions prior to updateActions
     * being called on extensions
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.11.2017
     */
    protected function beforeUpdateActions($callback) {
        $this->beforeExtending('updateActions', $callback);
    }
    
    /**
     * Returns the form actions.
     * 
     * @return FieldList
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.11.2017
     */
    public function Actions() {
        $actions = parent::Actions();
        foreach ($this->getCustomActions() as $action) {
            if ($actions->fieldPosition($action) === false) {
                $actions->push($action);
            }
        }
        $this->extend('updateActions', $actions);
        return $actions;
    }

    /**
     * Allows user code to hook into CustomForm::getCustomFields prior to updateCustomFields
     * being called on extensions
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.11.2017
     */
    protected function beforeUpdateCustomFields($callback) {
        $this->beforeExtending('updateCustomFields', $callback);
    }
    
    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomFields() {
        $fields = [];
        $this->extend('updateCustomFields', $fields);
        return $fields;
    }

    /**
     * Allows user code to hook into CustomForm::getCustomActions prior to updateCustomActions
     * being called on extensions
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 03.11.2017
     */
    protected function beforeUpdateCustomActions($callback) {
        $this->beforeExtending('updateCustomActions', $callback);
    }
    
    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomActions() {
        $actions = [];
        $this->extend('updateCustomActions', $actions);
        return $actions;
    }

    /**
     * Allows user code to hook into CustomForm::submit() prior to onBeforeSubmit
     * being called on extensions
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.11.2017
     */
    protected function beforeOnBeforeSubmit($callback) {
        $this->beforeExtending('onBeforeSubmit', $callback);
    }

    /**
     * Allows user code to hook into CustomForm::submit() prior to onAfterSubmit
     * being called on extensions
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.11.2017
     */
    protected function beforeOnAfterSubmit($callback) {
        $this->beforeExtending('onAfterSubmit', $callback);
    }
    
    /**
     * Submits the form.
     * 
     * @param array      $data Submitted data
     * @param CustomForm $form Form
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.11.2017
     */
    public function submit($data, CustomForm $form) {
        $this->prepareSubmittedData($data);
        $this->extend('onBeforeSubmit', $data, $form);
        $this->doSubmit($data, $form);
        $this->extend('onAfterSubmit', $data, $form);
    }
    
    /**
     * Method to use for a CustomForm extensions submission.
     * 
     * @param array      $data Submitted data
     * @param CustomForm $form Form
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.11.2017
     */
    public function doSubmit($data, CustomForm $form) {
        
    }

    /**
     * Allows user code to hook into CustomForm::fieldLabels() prior to updateFieldLabels
     * being called on extensions
     *
     * @param callable $callback The callback to execute
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.11.2017
     */
    protected function beforeUpdateFieldLabels($callback) {
        $this->beforeExtending('updateFieldLabels', $callback);
    }

    /**
     * Returns the field labels for this form.
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.11.2017
     */
    public function fieldLabels() {
        $fieldLabels = [];
        $this->extend('updateFieldLabels', $fieldLabels);
        return $fieldLabels;
    }
    
    /**
     * Returns the field label for the given field name.
     * 
     * @param string $fieldName Name to get label for
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.11.2017
     */
    public function fieldLabel($fieldName) {
        $fieldLabel  = $fieldName;
        $fieldLabels = $this->fieldLabels();
        if (!array_key_exists($fieldName, $fieldLabels)) {
            $fieldLabel = _t(static::class . '.' . $fieldName, _t(self::class . '.' . $fieldName, $fieldName));
        }
        return $fieldLabel;
    }
    
    /**
     * Prepares the submitted data.
     * Fills not checked checkbox fields with FALSE.
     * 
     * @param boolean $data Data to prepare
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.04.2018
     */
    public function prepareSubmittedData(&$data) {
        foreach ($this->Fields() as $field) {
            /* @var $field \SilverStripe\Forms\FormField */
            if (!array_key_exists($field->getName(), $data)) {
                $data[$field->getName()] = false;
            }
        }
    }
    
}