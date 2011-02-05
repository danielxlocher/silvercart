<?php

/**
 * A form to manipulate a customers profile
 *
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @license BSD
 * @copyright Pixeltricks GmbH
 * @since 23.10.2010
 */
class EditProfileForm extends CustomHtmlForm {

    protected $formFields = array
        (
        'Salutation' => array(
            'type' => 'DropdownField',
            'title' => 'Anrede',
            'value' => array('' => 'Bitte wählen', 'Frau' => 'Frau', 'Herr' => 'Herr'),
            'checkRequirements' => array(
                'isFilledIn' => true
            )
        ),
        'FirstName' => array
        (
                'type' => 'TextField',
                'title' => 'Vorname',
                'checkRequirements' => array
                (
                        'isFilledIn' => true,
                        'hasMinLength' => 3
                )
        ),
        'Surname' => array
        (
                'type' => 'TextField',
                'title' => 'Nachname',
                'checkRequirements' => array
                (
                        'isFilledIn' => true,
                        'hasMinLength' => 3
                )
        ),
        'Email' => array(
                'type' => 'TextField',
                'title' => 'Email Adresse',
                'checkRequirements' => array(
                        'isEmailAddress'    => true,
                        'isFilledIn'        => true
                )
        ),
        'PhoneAreaCode' => array
        (
                'type' => 'TextField',
                'title' => 'Telefon Vorwahl',
                'checkRequirements' => array
                (
                        'isNumbersOnly' => true
                )
        ),
        'Phone' => array
        (
                'type' => 'TextField',
                'title' => 'Telefon Nummer.',
                'checkRequirements' => array
                (
                        'isNumbersOnly' => true
                )
        ),
        'BirthdayDay' => array(
            'type' => 'DropdownField',
            'title' => 'Tag',
            'value' => array('' => 'Bitte wählen', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20', '20' => '21', '22' => 22, '23' => 23, '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31'),
            'checkRequirements' => array(
                'isFilledIn' => true
            )
        ),
        'BirthdayMonth' => array(
            'type' => 'DropdownField',
            'title' => 'Monat',
            'value' => array('' => 'Bitte wählen', '1' => 'Januar', '2' => 'Februar', '3' => 'März', '4' => 'April', '5' => 'Mai', '6' => 'Juni', '7' => 'Juli', '8' => 'August', '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Dezember'),
            'checkRequirements' => array(
                'isFilledIn' => true
            )
        ),
        'BirthdayYear' => array(
            'type' => 'TextField',
            'title' => 'Jahr',
            'maxLength' => 4,
            'checkRequirements' => array(
                'isFilledIn' => true,
                'isNumbersOnly' => true,
                'hasLength' => 4
            )
        ),
        'Password' => array
        (
                'type' => 'PasswordField',
                'title' => 'Passwort',
                'checkRequirements' => array
                (
                        'hasMinLength' => 6
                )
        ),
        'PasswordCheck' => array
        (
                'type' => 'PasswordField',
                'title' => 'Passwort Gegenprüfung',
                'checkRequirements' => array
                (
                        'mustEqual' => 'Password'
                )
        ),
        'SubscribedToNewsletter' => array
        (       'title' => "Ich m&ouml;chte &uuml;ber neue Aktionen oder Veranstaltungen von Pour LA Table informiert werden",
                'type' => 'CheckboxField'
        )
    );

    /**
     * form preferences
     *
     * @var array
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sascha Koehler <skoehler@pixeltricks.de>
     * @since 27.10.2010
     * @return void
     */
    protected $preferences = array(
        'submitButtonTitle' => 'Speichern'
    );

    /**
     * Set initial form values
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sascha Koehler <skoehler@pixeltricks.de>
     * @since 23.10.2010
     * @return void
     */
    protected function fillInFieldValues() {
        $this->formFields['Salutation']['title'] = _t('Address.SALUTATION', 'salutation');
        $this->formFields['Salutation']['value'] = array('' => _t('EditAddressForm.EMPTYSTRING_PLEASECHOOSE'), "Frau" => _t('Address.MISSIS'), "Herr" => _t('Address.MISTER'));
        $this->formFields['FirstName']['title'] = _t('Address.FIRSTNAME', 'firstname');
        $this->formFields['Surname']['title'] = _t('Address.SURNAME', 'surname');
        $this->formFields['Invoice_Phone']['title'] = _t('Address.PHONE', 'phone');
        $this->formFields['Invoice_PhoneAreaCode']['title'] = _t('Address.PHONEAREACODE', 'phone area code');
        $this->formFields['Email']['title'] = _('Address.EMAIL');
        $this->formFields['Phone']['title'] = _t('Address.PHONE');
        $this->formFields['PhoneAreaCode']['title'] = _t('Address.PHONEAREACODE');
        $this->formFields['BirthdayDay']['title'] = _t('Page.DAY', 'day');
        $this->formFields['BirthdayDay']['value'] = array('' => _t('EditAddressForm.EMPTYSTRING_PLEASECHOOSE'), '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20', '20' => '21', '22' => 22, '23' => 23, '24' => '24', '25' => '25', '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31');
        $this->formFields['BirthdayMonth']['title'] = _t('Page.MONTH', 'month');
        $this->formFields['BirthdayMonth']['value'] = array('' => _t('EditAddressForm.EMPTYSTRING_PLEASECHOOSE'), '1' => _t('Page.JANUARY', 'january'), '2' => _t('Page.FEBRUARY', 'february'), '3' => _t('Page.MARCH', 'march'), '4' => _t('Page.APRIL', 'april'), '5' => _t('Page.MAY', 'may'), '6' => _t('Page.JUNE', 'june'), '7' => _t('Page.July', 'july'), '8' => _t('Page.AUGUST', 'august'), '9' => _t('Page.SEPTEMBER', 'september'), '10' => _t('Page.OCTOBER', 'october'), '11' => _t('Page.NOVEMBER', 'november'), '12' => _t('Page.DECEMBER', 'december'));
        $this->formFields['BirthdayYear']['title'] = _t('Page.YEAR', 'year');
        $this->formFields['Password']['title'] = _t('Page.PASSWORD');
        $this->formFields['PasswordCheck']['title'] = _t('Page.PASSWORD_CHECK', 'password check');
        $this->formFields['SubscribedToNewsletter']['title'] = _t('CheckoutFormStep.I_SUBSCRIBE_NEWSLETTER');
        $this->preferences['submitButtonTitle'] = _t('Page.SAVE');

        $member = Member::currentUser();
        #var_dump(date('d', strtotime($member->Birthday)));die();
        if ($member) {
            $this->formFields['Salutation']['selectedValue'] = $member->Salutation;
            $this->formFields['FirstName']['value'] = $member->FirstName;
            $this->formFields['Surname']['value'] = $member->Surname;

            if ($member->Birthday) {
                $this->formFields['BirthdayDay']['selectedValue'] = date('d', strtotime($member->Birthday));
                $this->formFields['BirthdayMonth']['selectedValue'] = date('m', strtotime($member->Birthday));
                $this->formFields['BirthdayYear']['value'] = date('Y', strtotime($member->Birthday));
            }

            if ($member->Email) {
                $this->formFields['Email']['value'] = $member->Email;
            }

            $this->formFields['SubscribedToNewsletter']['value'] = $member->SubscribedToNewsletter;

        }
    }

    /**
     * executed if there are no valdation errors on submit
     * Form data is saved in session
     *
     * @param SS_HTTPRequest $data             contains the frameworks form data
     * @param Form           $form             not used
     * @param array          $registrationData contains the modules form data
     *
     * @return void
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sascha Koehler <skoehler@pixeltricks.de>
     * @since 23.10.2010
     */
    protected function submitSuccess($data, $form, $registrationData) {
        $member = Member::currentUser();

        // -------------------------------------------------------------------
        // process data
        // -------------------------------------------------------------------
        // Password
        unset($registrationData['PasswordCheck']);
        if (empty($registrationData['Password'])) {
            unset($registrationData['Password']);
        }

        // birthday
        if (!empty($registrationData['BirthdayDay']) &&
            !empty($registrationData['BirthdayMonth']) &&
            !empty($registrationData['BirthdayYear'])) {
            $registrationData['Birthday'] = $registrationData['BirthdayYear'] . '-' .
                $registrationData['BirthdayMonth'] . '-' .
                $registrationData['BirthdayDay'];
        }

        $member->castedUpdate($registrationData);
        
        $member->write();

        Director::redirect($this->controller->Link());
    }
}