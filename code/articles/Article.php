<?php
/**
 * Article class.
 *
 * @author Sascha Koehler <skoehler@pixeltricks.de>, Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright 2010 pixeltricks GmbH
 * @since 22.11.2010
 * @license none
 */
class Article extends DataObject {

    /**
     * Singular-Beschreibung zur Darstellung im Backend.
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 22.11.2010
     */
    public static $singular_name = "Artikel";

    /**
     * Plural-Beschreibung zur Darstellung im Backend.
     *
     * @var string
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 22.11.2010
     */
    public static $plural_name = "Artikel";

    /**
     * Attributes
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 22.11.2010
     */
    public static $db = array(
        'Title'                     => 'VarChar(255)',
        'PurchasePrice'             => 'Money',
        'Price'                     => 'Money', //Wenn kein Price-Object gepflegt ist, gilt dieser Preis
        'ShortDescription'          => 'VarChar(255)',
        'LongDescription'           => 'Text',
        'MSRPrice'                  => 'Money',
        'MetaDescription'           => 'VarChar(255)',
        'Weight'                    => 'Int', //unit is gramm
        'Quantity'                  => 'Int', //Quantity Pieces (Pack)
        'MetaTitle'                 => 'VarChar(64)', //search engines use only 64 chars
        'MetaKeywords'              => 'VarChar',
        'isFreeOfCharge'            => 'Boolean', //evades the mechanism of preventing articles without price to go into the frontend
        'ArticleNumberShop'         => 'VarChar(50)',
        'ArticleNumberManufacturer' => 'VarChar(50)',
        'EANCode'                   => 'VarChar(13)'
    );

    public static $summary_fields = array(
        'Title'                     => 'Artikel',
        'manufacturer.Title'        => 'Hersteller'
    );
    public static $searchable_fields = array(
        'Title',
        'ShortDescription',
        'LongDescription',
        'manufacturer.Title',
        'isFreeOfCharge'
    );
    /**
     * defines names on scaffolded forms
     * @var <type> array
     */
    public static $field_labels = array(
        'Title'                     => 'Bezeichnung',
        'LongDescription'           => 'Artikelbeschreibung',
        'manufacturer.Title'        => 'Hersteller',
        'isFreeOfCharge'            => 'kostenlos',
        'PurchasePrice'             => 'Einkaufspreis',
        'MSRPrice'                  => 'UVP'
    );
    /**
     * Array of all attributes that must be set to show an article in the frontend and enter it via backend
     * @var <type> array
     * @author Roland Lehmann
     */
    protected static $requiredAttributes = array();

    /**
     * 1:n Beziehungen
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 24.11.2010
     */
    public static $has_one = array(
        'tax'                   => 'Tax',
        'manufacturer'          => 'Manufacturer',
        'articleGroup'          => 'ArticleGroupPage',
        'masterArticle'         => 'Article',
        'image'                 => 'Image'
    );

    /**
     * n:m Beziehungen
     *
     * @var array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 22.11.2010
     */
    public static $has_many = array(
        'shoppingCartPositions' => 'ShoppingCartPosition'
    );

    public static $belongs_many_many = array(
        'shoppingCarts'         => 'ShoppingCart',
        'orders'                => 'Order',
        'categories'            => 'ArticleCategoryPage'
    );

    // -----------------------------------------------------------------------
    // Methoden
    // -----------------------------------------------------------------------

    /**
     * Getter similar to DataObject::get(); returns a DataObectSet of articles filtered by the requirements in self::getRequiredAttributes();
     * If an article is free of charge, it can have no price. This is for giveaways and gifts.
     *
     * @param string  $whereClause to be inserted into the sql where clause
     * @param string  $sort        string with sort clause
     * @param string  $join        string for a join
     * @param integer $limit       DataObject limit
     *
     * @return DataObjectSet DataObjectSet of articles or false
     * @author Roland Lehmann
     * @since 23.10.2010
     */
    public static function get($whereClause = "", $sort = null, $join = null, $limit = null) {
        $requiredAttributes = self::getRequiredAttributes();
        if (!empty($requiredAttributes)) {
            $filter = "";
            foreach ($requiredAttributes as $requiredAttribute) {
                if ($requiredAttribute == "Price") {
                    $filter .= sprintf("(`PriceAmount` !='' OR `isFreeOfCharge` = '1') AND ");
                } else {
                    $filter .= sprintf("`%s` !='' AND ", $requiredAttribute);
                }
            }
            //The where clause must not end with "AND"
            $filter = substr($filter, 0, -5);
            if ($whereClause != "") {
                $filter = $filter . " AND " . $whereClause;
            }
            //
        } else {
            $filter = $whereClause;
        }
        $articles = DataObject::get('Article', $filter, $sort, $join, $limit);
        if ($articles) {
            return $articles;
        } else {
            return false;
        }
    }

    /**
     * Customizes the backend popup for Articles.
     *
     * @return FieldSet the editible fields
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 23.10.2010
     */
    public function getCMSFields_forPopup() {
        $fields = $this->getCMSFields();
        $fields->removeByName('masterArticleID'); //remove the dropdown for the relation masterArticle
        //Get all articles that have no master
        $var = sprintf("\"masterArticleID\" = '%s'", "0");
        $masterArticles = DataObject::get("Article", $var);
        $dropdownField = new DropdownField('masterArticleID', 'Master Artikel', $masterArticles->toDropDownMap(), null, null, "-- Master wählen --");
        $fields->push($dropdownField);
        return $fields;
    }

    /**
     * define the searchable fields and search methods for the frontend
     *
     * @return SearchContext ???
     * @author Roland Lehmann
     * @since 23.10.2010
     */
    public function getCustomSearchContext() {
        $fields = $this->scaffoldSearchFields(
                        array('restrictFields' => array('Title', 'LongDescription', 'manufacturer.Title')
                        )
        );
        $filters = array(
            'Title' => new PartialMatchFilter('Title'),
            'LongDescription' => new ExactMatchFilter('LongDescription'),
            'manufacturer.Title' => new PartialMatchFilter('manufacturer.Title')
        );
        return new SearchContext($this->class, $fields, $filters);
    }

    /**
     * get some random articles to fill a controller every now and then
     *
     * @param integer $amount        How many articles should be returned?
     * @param boolean $masterArticle Should only master articles be returned?
     *
     * @return array DataObjectSet of random articles
     * @author Roland Lehmann
     * @copyright Pixeltricks GmbH
     * @since 23.10.2010
     */
    public static function getRandomArticles($amount = 4, $masterArticle = true) {
        if ($masterArticle) {
            return DataObject::get("Article", "\"masterArticleID\" = '0'", "RAND()", null, $amount);
        } else {
            return DataObject::get("Article", null, "RAND()", null, $amount);
        }
    }

    /**
     * get all required attributes as an array.
     *
     * @return array the attributes required to display an article in the frontend
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 23.10.2010
     */
    public static function getRequiredAttributes() {
        return self::$requiredAttributes;
    }

    /**
     * define all attributes that must be filled out to show articles in the frontend.
     *
     * @param string $concatinatedAttributesString a string with all attribute names, seperated by comma, with or without whitespaces
     *
     * @since 23.10.2010
     * @return void
     * @author Roland Lehmann
     */
    public static function setRequiredAttributes($concatinatedAttributesString) {
        $requiredAttributesArray = array();
        $requiredAttributesArray = explode(",", str_replace(" ", "", $concatinatedAttributesString));
        self::$requiredAttributes = $requiredAttributesArray;
    }

    /**
     * Remove chars from the title that are not appropriate for an url
     *
     * @return string sanitized article title
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 23.10.2010
     */
    private function title2urlSegment() {
        $remove = array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', ' ');
        $replace = array('ae', 'oe', 'ue', 'Ae', 'Oe', 'Ue', '');
        $string = str_replace($remove, $replace, $this->Title);
        return $string;
    }

    /**
     * Fuegt einen Artikel zum ShoppingCart hinzu oder erhoeht dessen Anzahl,
     * wenn er schon vorhanden ist.
     *
     * @param int $cartID   ID of the users shopping cart
     * @param int $quantity Amount of articles to be added
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.11.2010
     *
     * @return bool
     */
    public function addToCart($cartID, $quantity = 1) {
        if ($quantity == 0) {
            return false;
        }

        $filter           = sprintf("\"articleID\" = '%s' AND shoppingCartID = '%s'", $this->ID, $cartID);
        $existingPosition = DataObject::get_one('ShoppingCartPosition', $filter);

        if ($existingPosition) {
            $existingPosition->Quantity += $quantity;
            $existingPosition->write();
        } else {
            $shoppingCartPosition                 = new ShoppingCartPosition();
            $shoppingCartPosition->shoppingCartID = $cartID;
            $shoppingCartPosition->articleID      = $this->ID;
            $shoppingCartPosition->Quantity       = $quantity;
            $shoppingCartPosition->write();

            $cart = DataObject::get_by_id('ShoppingCart', $cartID);
        }

        return true;
    }

    /**
     * Link to the controller, that shows this article
     * An article has a unique URL
     *
     * @return string URL of $this
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 23.10.2010
     */
    public function Link() {
        $Link = Director::absoluteBaseURL() . 'artikelansicht/' . $this->ID . '/' . $this->title2urlSegment();
        return $Link;
    }

    /**
     * Form for adding an article to a cart
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 23.10.2010
     * @return Form add an article to the cart
     */
    public function addToCartForm() {
        $fields = new FieldSet();
        $fields->push(new HiddenField('articleID', 'articleID', $this->ID));
        $fields->push(new NumericField('articleAmount', 'Anzahl', $value = 1));
        $actions = new FieldSet();
        $actions->push(new FormAction('doAddToCart', 'in den Warenkorb'));
        $form = new Form(Controller::curr(), 'doAddToCart', $fields, $actions);
        return $form;
    }

    /**
     * Liefert den Betrag des Steueranteils fuer diesen Artikel.
     *
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @copyright 2010 pixeltricks GmbH
     * @since 25.11.2010
     */
    public function getTaxAmount() {
        $taxRate = $this->Price->getAmount() - ($this->Price->getAmount() / (100 + $this->Tax()->Rate) * 100);

        return $taxRate;
    }
}
