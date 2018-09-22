<?php

namespace SilverCart\Forms;

use SilverCart\Admin\Model\Config;
use SilverCart\Dev\Tools;
use SilverCart\Forms\CustomForm;
use SilverCart\Forms\Checkout\CheckoutFormStep2;
use SilverCart\Forms\FormFields\TextField;
use SilverCart\Model\Customer\Address;
use SilverCart\Model\Customer\Country;
use SilverCart\Model\Customer\Customer;
use SilverCart\Model\Newsletter\AnonymousNewsletterRecipient;
use SilverCart\Model\Newsletter\Newsletter;
use SilverCart\Model\Pages\CheckoutStep;
use SilverCart\Model\Pages\Page;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\Security;

/**
 * Form for registration of a regular customer.
 *
 * @package SilverCart
 * @subpackage Forms
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 26.09.2017
 * @copyright 2017 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class RegisterRegularCustomerForm extends CustomForm {
    
    /**
     * List of required fields.
     *
     * @var array
     */
    private static $requiredFields = [
        'Salutation',
        'FirstName' => [
            'isFilledIn'   => true,
            'hasMinLength' => 3,
        ],
        'Surname' => [
            'isFilledIn'   => true,
            'hasMinLength' => 3,
        ],
        'Street',
        'StreetNumber',
        'Postcode',
        'City',
        'Country',
        'Phone' => [
            'isFilledIn'    => true,
            'isPhoneNumber' => true,
        ],
        'Email' => [
            'isEmailAddress' => true,
            'isFilledIn'     => true,
            'doesEmailExist' => false,
        ],
        'EmailCheck' => [
            'isFilledIn' => true,
            'mustEqual'  => 'Email',
        ],
        'Password' => [
            'isFilledIn'    => true,
            'hasMinLength'  => 6,
            'mustNotEqual'  => 'Email',
        ],
        'PasswordCheck' => [
            'mustEqual' => 'Password',
        ],
    ];
    
    /**
     * Optional backlink to overwrite the default redirection after a successful submission.
     *
     * @var string
     */
    protected $backLink = '';

    /**
     * Returns the required fields.
     * 
     * @return array
     */
    public function getRequiredFields() {
        if ($this->demandBirthdayDate()) {
            $requiredFields = self::config()->get('requiredFields');
            $requiredFields += [
                'BirthdayDay' => [
                    'isFilledIn' => true,
                ],
                'BirthdayMonth',
                'BirthdayYear' => [
                    'isFilledIn'    => true,
                    'isNumbersOnly' => true,
                    'hasLength'     => 4,
                ],
            ];
            if ($this->UseMinimumAgeToOrder()) {
                $requiredFields['BirthdayDay']['hasMinAge'] = Config::MinimumAgeToOrder();
            }
            self::config()->set('requiredFields', $requiredFields);
        }
        if ($this->EnableBusinessCustomers()) {
            $requiredFields = self::config()->get('requiredFields');
            $requiredFields += [
                'TaxIdNumber' => [
                    'isFilledInDependentOn' => [
                        'field'     => 'IsBusinessAccount',
                        'hasValue'  => '1'
                    ],
                ],
                'Company' => [
                    'isFilledInDependentOn' => [
                        'field'     => 'IsBusinessAccount',
                        'hasValue'  => '1'
                    ],
                ],
            ];
            self::config()->set('requiredFields', $requiredFields);
        }
        return parent::getRequiredFields();
    }

    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomFields() {
        $this->beforeUpdateCustomFields(function (array &$fields) {
            $fields = array_merge(
                    $fields,
                    $this->getBirthdayFields(),
                    $this->getBusinessFields(),
                    [
                        DropdownField::create('Salutation', Address::singleton()->fieldLabel('Salutation'), Tools::getSalutationMap()),
                        TextField::create('AcademicTitle', Address::singleton()->fieldLabel('AcademicTitle')),
                        TextField::create('FirstName', Address::singleton()->fieldLabel('FirstName')),
                        TextField::create('Surname', Address::singleton()->fieldLabel('Surname')),
                        TextField::create('Addition', Address::singleton()->fieldLabel('Addition')),
                        TextField::create('Street', Address::singleton()->fieldLabel('Street')),
                        TextField::create('StreetNumber', Address::singleton()->fieldLabel('StreetNumber'), '', 10),
                        TextField::create('Postcode', Address::singleton()->fieldLabel('Postcode'), '', 10),
                        TextField::create('City', Address::singleton()->fieldLabel('City')),
                        DropdownField::create('Country', Address::singleton()->fieldLabel('Country'), Country::getPrioritiveDropdownMap(true, _t(CheckoutFormStep2::class . '.EMPTYSTRING_COUNTRY', '--country--'))),
                        TextField::create('Phone', Address::singleton()->fieldLabel('Phone')),
                        TextField::create('Fax', Address::singleton()->fieldLabel('Fax')),
                        EmailField::create('Email', Address::singleton()->fieldLabel('Email')),
                        EmailField::create('EmailCheck', Address::singleton()->fieldLabel('EmailCheck')),
                        PasswordField::create('Password', Page::singleton()->fieldLabel('Password')),
                        PasswordField::create('PasswordCheck', Page::singleton()->fieldLabel('PasswordCheck')),
                        $newsletterField = CheckboxField::create('SubscribedToNewsletter', CheckoutStep::singleton()->fieldLabel('SubscribeNewsletter')),
                        HiddenField::create('backlink', 'backlink', $this->getBackLink()),
                    ]
            );
            $newsletterField->setDescription(Newsletter::singleton()->fieldLabel('OptInNotFinished'));
        });
        return parent::getCustomFields();
    }
    
    /**
     * Returns the birthday fields if enabled.
     * 
     * @return array
     */
    protected function getBirthdayFields() {
        $birthdayFields = [];
        if ($this->demandBirthdayDate()) {
            $birthdayDays = [
                '' => Tools::field_label('PleaseChoose')
            ];
            for ($idx = 1; $idx < 32; $idx++) {
                $birthdayDays[$idx] = $idx;
            }

            $birthdayFields = [
                DropdownField::create('BirthdayDay', Page::singleton()->fieldLabel('Day'), $birthdayDays),
                DropdownField::create('BirthdayMonth', Page::singleton()->fieldLabel('Month'), Tools::getMonthMap()),
                TextField::create('BirthdayYear', Page::singleton()->fieldLabel('Year'), '', 4),
            ];
        }
        return $birthdayFields;
    }
    
    /**
     * Returns the business fields if enabled.
     * 
     * @return array
     */
    protected function getBusinessFields() {
        $businessFields = [];
        if ($this->EnableBusinessCustomers()) {
            $businessFields = [
                CheckboxField::create('IsBusinessAccount', Member::singleton()->fieldLabel('IsBusinessAccount')),
                TextField::create('TaxIdNumber', Address::singleton()->fieldLabel('TaxIdNumber'), '', 30),
                TextField::create('Company', Address::singleton()->fieldLabel('Company'), '', 50),
            ];
        }
        return $businessFields;
    }
    
    /**
     * Returns the static form fields.
     * 
     * @return array
     */
    public function getCustomActions() {
        $this->beforeUpdateCustomActions(function (array &$actions) {
            $actions += [
                FormAction::create('submit', Page::singleton()->fieldLabel('Submit'))
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
     * @since 10.11.2017
     */
    public function doSubmit($data, CustomForm $form) {
        // Aggregate Data and set defaults
        $currentUserID = 0;
        $currentUser   = Security::getCurrentUser();
        if ($currentUser instanceof Member &&
            $currentUser->exists()) {
            
            $currentUserID = $currentUser->ID;
        }
        $data['MemberID'] = $currentUserID;
        $data['Locale']   = Tools::current_locale();
        if ($this->demandBirthdayDate()) {
            if (!empty($data['BirthdayDay']) &&
                !empty($data['BirthdayMonth']) &&
                !empty($data['BirthdayYear'])) {
                $data['Birthday'] = $data['BirthdayYear'] . '-' . $data['BirthdayMonth'] . '-' . $data['BirthdayDay'];
            }
        }

        // Create new regular customer and perform a log in
        $customer = $this->handleAnonymousCustomer();
        $customer->castedUpdate($data);
        $customer->write();
        $customer->changePassword($data['Password']);

        $customerGroup = $this->getTargetCustomerGroup($data);
        if ($customerGroup) {
            $customer->Groups()->add($customerGroup);
        }

        // Create ShippingAddress for customer and populate it with registration data
        $address = new Address();
        $address->castedUpdate($data);

        $country = Country::get()->byID((int) $data['Country']);
        if ($country) {
            $address->CountryID = $country->ID;
        }
        $address->write();
        $this->extend('updateRegisteredAddress', $address, $data, $form);

        //connect the ShippingAddress and the InvoiceAddress to the customer
        $customer->Addresses()->add($address);
        $customer->write();
        $customer->ShippingAddressID = $address->ID;
        $customer->InvoiceAddressID  = $address->ID;
        $customer->write();
        $this->handleNewsletterRecipient($customer);
        $this->extend('updateRegisteredCustomer', $customer, $data, $form, $data);

        $redirectTo = $this->getController()->Link('welcome');
        if (array_key_exists('redirect', $data) &&
            !empty($data['redirect'])) {
            $redirectTo = $data['redirect'];
        } elseif (array_key_exists('backlink', $data) &&
            !empty($data['backlink'])) {
            $redirectTo = $data['backlink'];
        }
        $authenticator = new MemberAuthenticator();
        $authenticator->getLoginHandler($redirectTo)->performLogin($customer, ['Remember' => false], $this->getRequest());
        $this->getController()->redirect($redirectTo);
    }
    
    /**
     * Handles the anonymous customer object if exists.
     * Anonymous customer will be transformed into a newly registered customer.
     * 
     * @return Member
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.07.2018
     */
    protected function handleAnonymousCustomer() {
        $customer = Customer::currentUser();
        if ($customer instanceof Member &&
            $customer->exists()) {
            $customer->Groups()->removeAll();
        } else {
            $customer = Member::create();
        }
        return $customer;
    }
    
    /**
     * Handles the newsletter recipient data.
     * 
     * @param Member $customer New customer
     * 
     * @return void
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 13.11.2017
     */
    protected function handleNewsletterRecipient(Member $customer) {
        // Remove from the anonymous newsletter recipients list
        if (AnonymousNewsletterRecipient::doesExist($customer->Email)) {
            $recipient = AnonymousNewsletterRecipient::getByEmailAddress($customer->Email);
            if ($recipient->NewsletterOptInStatus) {
                $customer->NewsletterOptInStatus      = 1;
                $customer->NewsletterConfirmationHash = $recipient->NewsletterOptInConfirmationHash;
                $customer->write();
            }
            AnonymousNewsletterRecipient::removeByEmailAddress($customer->Email);
        }
        
        if ( $customer->SubscribedToNewsletter &&
            !$customer->NewsletterOptInStatus) {
            
            Newsletter::subscribeRegisteredCustomer($customer);
        }
    }
    
    /**
     * Indicates wether the registration fields specific to business customers
     * should be shown.
     *
     * @return boolean
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.12.2011
     */
    public function EnableBusinessCustomers() {
        return Config::enableBusinessCustomers();
    }
    
    /**
     * Indicates wether the birthday date has to be entered.
     *
     * @return boolean
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 12.10.2011
     */
    public function demandBirthdayDate() {
        return Config::demandBirthdayDateOnRegistration();
    }
    
    /**
     * Returns whether there is a minimum age to order.
     *
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 20.01.2014
     */
    public function UseMinimumAgeToOrder() {
        return Config::UseMinimumAgeToOrder();
    }
    
    /**
     * Returns the minimum age to order.
     *
     * @return boolean
     *
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 20.01.2014
     */
    public function MinimumAgeToOrder() {
        return Config::MinimumAgeToOrder();
    }
    
    /**
     * Returns the target customer group.
     * 
     * @param array $data Submitted form data.
     * 
     * @return Group
     */
    public function getTargetCustomerGroup($data) {
        if (array_key_exists('IsBusinessAccount', $data) &&
            $data['IsBusinessAccount'] == '1') {
            $customerGroup = Customer::default_customer_group_b2b();
        } else {
            $customerGroup = Customer::default_customer_group();
        }
        return $customerGroup;
    }
    
    /**
     * Returns the backlink.
     * 
     * @return string
     */
    public function getBackLink() {
        return $this->backLink;
    }

    /**
     * Sets the backlink.
     * 
     * @param string $backLink Backlink
     * 
     * @return void
     */
    public function setBackLink($backLink) {
        $this->backLink = $backLink;
    }
    
}