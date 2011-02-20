<?php

/**
 * a contact form of the CustomHTMLForms modul
 *
 * @copyright pixeltricks GmbH
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @since 21.10.2010
 * @license BSD
 */
class SilvercartContactForm extends CustomHtmlForm {

    /**
     * definition of the form fields
     *
     * @var array
     */
    protected $formFields = array(
        'Salutation' => array(
            'type' => 'DropdownField',
            'title' => 'Anrede',
            'value' => array('' => 'Bitte wählen', 'Frau' => 'Frau', 'Herr' => 'Herr'),
            'checkRequirements' => array(
                'isFilledIn' => true
            )
        ),
        'FirstName' => array(
            'type' => 'TextField',
            'title' => 'Vorname',
            'checkRequirements' => array(
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
            'value' => '',
            'checkRequirements' => array(
                'isFilledIn' => true,
                'isEmailAddress' => true
            )
        ),
        'Message' => array(
            'type' => 'TextareaField',
            'title' => 'Nachricht',
            'checkRequirements' => array
                (
                'isFilledIn' => true,
                'hasMinLength' => 3
            )
        )
    );
    /**
     * form settings, mainly submit button´s name
     *
     * @var array
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 02.02.2011
     * @return void
     */
    protected $preferences = array(
        'submitButtonTitle' => 'Nachricht senden'
    );

    /**
     * logged in users get there fields filled
     *
     * @return void
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 21.10.2010
     */
    protected function fillInFieldValues() {
        $this->formFields['Salutation']['title'] = _t('SilvercartAddress.SALUTATION');
        $this->formFields['Salutation']['value'] = array('' => _t('SilvercartEditAddressForm.EMPTYSTRING_PLEASECHOOSE'), "Frau" => _t('SilvercartAddress.MISSIS'), "Herr" => _t('SilvercartAddress.MISTER'));
        $this->formFields['FirstName']['title'] = _t('SilvercartAddress.FIRSTNAME', 'firstname');
        $this->formFields['Surname']['title'] = _t('SilvercartAddress.SURNAME');
        $this->formFields['Email']['title'] = _t('SilvercartAddress.EMAIL', 'email address');
        $this->formFields['Message']['title'] = _t('SilvercartPage.MESSAGE', 'message');
        $this->preferences['submitButtonTitle'] = _t('SilvercartPage.SUBMIT_MESSAGE', 'submit message');

        $member = Member::currentUser();
        if ($member) {
            $this->formFields['Salutation']['selectedValue'] = $member->Salutation;
            $this->formFields['FirstName']['value'] = $member->FirstName;
            $this->formFields['Surname']['value'] = $member->Surname;
            $this->formFields['Email']['value'] = $member->Email;
        }
    }

    /**
     * executed if there are no valdation errors on submit
     * Form data is saved in session
     *
     * @param SS_HTTPRequest $data     contains the frameworks form data
     * @param Form           $form     not used
     * @param array          $formData contains the modules form data
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 21.10.2010
     * @return void
     */
    protected function submitSuccess($data, $form, $formData) {

        $email = new Email(
                        Email::getAdminEmail(),
                        $formData['Email'],
                        _t('SilvercartContactFormPage.REQUEST', 'request via contact form'),
                        ''
        );

        $email->setTemplate('SilvercartMailContact');
        $email->populateTemplate(
                array(
                    'FirstName' => $formData['FirstName'],
                    'Surname' => $formData['Surname'],
                    'Email' => $formData['Email'],
                    'Message' => str_replace('\r\n', '<br>', nl2br($formData['Message']))
                )
        );

        $email->send();
        /*
         * redirect a user to the page type for the response or to the root
         */
        $contactFormResponsePage = SilvercartPage_Controller::PageByIdentifierCode("SilvercartContactFormResponsePage");
        Director::redirect($contactFormResponsePage->RelativeLink());
    }

}