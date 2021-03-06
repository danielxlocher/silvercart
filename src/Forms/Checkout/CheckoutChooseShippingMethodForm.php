<?php

namespace SilverCart\Forms\Checkout;

use SilverCart\Forms\CustomForm;
use SilverCart\Forms\FormFields\ShippingOptionsetField;
use SilverCart\Model\Customer\Address;
use SilverCart\Model\Pages\CheckoutStep;
use SilverCart\Model\Shipment\ShippingMethod;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\Map;

/**
 * Form to choose the shipping method in checkout.
 *
 * @package SilverCart
 * @subpackage Forms_Checkout
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 26.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class CheckoutChooseShippingMethodForm extends CustomForm {
    
    /**
     * Custom extra CSS classes.
     *
     * @var array
     */
    protected $customExtraClasses = [
        'form-horizontal',
    ];
    
    /**
     * List of required fields.
     *
     * @var array
     */
    private static $requiredFields = [
        'ShippingMethod',
    ];

    /**
     * Set of shipping methods
     *
     * @var DataList
     */
    protected $shippingMethods = null;

    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomFields() {
        $this->beforeUpdateCustomFields(function (array &$fields) {
            
            $shippingAddress = $this->getShippingAddress();
            $title = ShippingMethod::singleton()->fieldLabel('ChooseShippingMethod');
            if ($shippingAddress instanceof Address) {
                $title = _t(ShippingMethod::class . '.CHOOSE_SHIPPING_METHOD_TO',
                        'Please choose a shipping method for the delivery to "{country}"',
                        [
                            'country' => $this->getShippingAddress()->Country()->Title,
                        ]
                );
            }
            
            $checkout = $this->getController()->getCheckout();
            /* @var $checkout \SilverCart\Checkout\Checkout */
            $shippingMethodsSelectedValue = $checkout->getDataValue('ShippingMethod');
            $shippingMethodsSource        = [];
            $shippingMethods              = $this->getShippingMethods();
            if ($shippingMethods->exists()) {
                $shippingMethodsSource = $shippingMethods->map('ID', 'TitleWithCarrierAndFee');
                if ($shippingMethodsSource instanceof Map) {
                    $shippingMethodsSource = $shippingMethodsSource->toArray();
                }
                if (is_null($shippingMethodsSelectedValue)) {
                    $shippingMethodsSelectedValue = $shippingMethods->First()->ID;
                }
            }
            
            $fields += [
                ShippingOptionsetField::create('ShippingMethod', $title, $shippingMethodsSource, $shippingMethodsSelectedValue),
            ];
        });
        return parent::getCustomFields();
    }
    
    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomActions() {
        $this->beforeUpdateCustomActions(function (array &$actions) {
            $actions += [
                FormAction::create('submit', CheckoutStep::singleton()->fieldLabel('Forward'))
                    ->setUseButtonTag(true)->addExtraClass('btn-primary')
            ];
        });
        return parent::getCustomActions();
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
     * @since 08.11.2017
     */
    public function doSubmit($data, CustomForm $form) {
        $checkout = $this->getController()->getCheckout();
        /* @var $checkout \SilverCart\Checkout\Checkout */
        $chosenShippingMethod = $checkout->getDataValue('ShippingMethod');
        $currentStep          = $checkout->getCurrentStep();
        if ($chosenShippingMethod != $data['ShippingMethod']) {
            $currentStep->resetNextSteps();
        }
        $checkout->addDataValue('ShippingMethod', $data['ShippingMethod']);
        $currentStep->complete();
        $currentStep->redirectToNextStep();
    }
    
    /**
     * Returns the checkouts current shipping address
     * 
     * @return \SilverCart\Model\Customer\Address
     */
    public function getShippingAddress() {
        return $this->getController()->getShippingAddress();
    }

    /**
     * Returns the shipping methods.
     * 
     * @return DataList
     */
    public function getShippingMethods() {
        if (is_null($this->shippingMethods)) {
            $shippingMethods = ShippingMethod::getAllowedShippingMethods(null, $this->getShippingAddress());
            if (!($shippingMethods instanceof ArrayList) ||
                $shippingMethods->count() == 0) {
                $shippingMethods = ShippingMethod::get()->filter('isActive', true);
                if (!($shippingMethods instanceof DataList)) {
                    $shippingMethods = new DataList();
                }
            }
            $this->shippingMethods = $shippingMethods;
        }
        return $this->shippingMethods;
    }
    
}