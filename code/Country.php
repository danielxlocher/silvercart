<?php

/**
 * Abstract for a country
 *
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright Pixeltricks GmbH
 * @license BSD
 * @since 20.10.2010
 */
class Country extends DataObject {

    /**
     * Singular name
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $singular_name = "Land";

    /**
     * Plural name
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $plural_name = "Länder";

    /**
     * Attributes.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $db = array(
        'Title' => 'VarChar',
        'ISO2'  => 'VarChar',
        'ISO3'  => 'VarChar'
    );

    /**
     * Many-many relationships.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $many_many = array(
        'paymentMethods' => 'PaymentMethod'
    );

    /**
     * Belongs-many-many relationships.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $belongs_many_many = array(
        'zones' => 'Zone'
    );

    /**
     * Summaryfields for display in tables.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $summary_fields = array(
        'Title',
        'ISO2',
        'ISO3',
        'AttributedZones',
        'AttributedPaymentMethods'
    );

    /**
     * Column labels for display in tables.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $field_labels = array(
        'Title'                     => 'Land',
        'ISO2'                      => 'ISO2 Code',
        'ISO3'                      => 'ISO3 Code',
        'AttributedZones'           => 'Zugeordnete Zonen',
        'AttributedPaymentMethods'  => 'Zugeordnete Bezahlarten'
    );

    /**
     * List of searchable fields for the model admin
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $searchable_fields = array(
        'Title',
        'ISO2',
        'ISO3',
        'zones.ID' => array(
            'title' => 'Zugeordnete Zonen'
        ),
        'paymentMethods.ID' => array(
            'title' => 'Zugeordnete Bezahlarten'
        )
    );

    /**
     * Virtual database columns.
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public static $casting = array(
        'AttributedZones'           => 'Varchar(255)',
        'AttributedPaymentMethods'  => 'Varchar(255)'
    );

    /**
     * Default database records
     *
     * @return void
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 20.10.2010
     */
    public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        $standardCountry = DataObject::get_one(
            'Country'
        );

        if (!$standardCountry) {
            $obj        = new Country();
            $obj->Title = 'Deutschland';
            $obj->ISO2  = 'de';
            $obj->ISO3  = 'deu';
            $obj->write();
        }
    }

    /**
     * customizes the backends fields, mainly for ModelAdmin
     * 
     * @return FieldSet the fields for the backend
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 28.10.10
     */
    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeByName('paymentMethods');
        $fields->removeByName('zones');

        $paymentMethodsTable = new ManyManyComplexTableField(
            $this,
            'paymentMethods',
            'PaymentMethod',
            null,
            'getCmsFields_forPopup'
        );
        $paymentMethodsTable->setAddTitle('Zahlarten');
        $fields->addFieldToTab('Root.Zahlarten', $paymentMethodsTable);
        
        return $fields;
    }

    /**
     * Returns the attributed zones as string (limited to 150 chars).
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function AttributedZones() {
        $attributedZonesStr = '';
        $attributedZones    = array();
        $maxLength          = 150;

        foreach ($this->zones() as $zone) {
            $attributedZones[] = $zone->Title;
        }

        if (!empty($attributedZones)) {
            $attributedZonesStr = implode(', ', $attributedZones);

            if (strlen($attributedZonesStr) > $maxLength) {
                $attributedZonesStr = substr($attributedZonesStr, 0, $maxLength).'...';
            }
        }

        return $attributedZonesStr;
    }

    /**
     * Returns the attributed payment methods as string (limited to 150 chars).
     *
     * @return string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2011 pixeltricks GmbH
     * @since 31.01.2011
     */
    public function AttributedPaymentMethods() {
        $attributedPaymentMethodsStr = '';
        $attributedPaymentMethods    = array();
        $maxLength                   = 150;

        foreach ($this->paymentMethods() as $paymentMethod) {
            $attributedPaymentMethods[] = $paymentMethod->Name;
        }

        if (!empty($attributedPaymentMethods)) {
            $attributedPaymentMethodsStr = implode(', ', $attributedPaymentMethods);

            if (strlen($attributedPaymentMethodsStr) > $maxLength) {
                $attributedPaymentMethodsStr = substr($attributedPaymentMethodsStr, 0, $maxLength).'...';
            }
        }

        return $attributedPaymentMethodsStr;
    }
}
