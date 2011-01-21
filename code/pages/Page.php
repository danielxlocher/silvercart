<?php

/**
 * Standard Page
 *
 * @author Roland Lehmann <rlehmann@pixeltricks.de>, Jiri Ripa <jripa@pixeltricks.de>
 * @since 20.09.2010
 * @copyright 2010 pixeltricks GmbH
 * @license BSD
 */
class Page extends SiteTree {

    public static $db = array(
        'LayoutType' => "int(3)",
    );
    public static $has_one = array(
        'headerPicture' => 'Image'
    );
    public static $defaults = array(
        'LayoutType' => 2
    );

    /**
     * is the centerpiece of every data administration interface in Silverstripe
     *
     * @return FieldSet all related CMS fields
     * @author Jiri Ripa <jripa@pixeltricks.de>
     * @since 15.10.2010
     */
    public function getCMSFields() {

        // add Page Layout Array
        $layoutTypes = array(
            1 => 'Einspalter',
            11 => 'Einspalter mit Teaser',
            2 => 'Zweispalter Content Links',
            22 => 'Zweispalter Content Links mit Teaser',
            3 => 'Zweispalter Content Rechts',
            33 => 'Zweispalter Content Rechts mit Teaser',
            4 => 'Dreispalter',
            44 => 'Dreispalter mit Teaser',
        );

        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Content.Main', new FileIFrameField('headerPicture', 'Headergrafik'));
        $fields->addFieldToTab("Root.Behaviour", new DropdownField('LayoutType', 'Layouttyp', $layoutTypes));
        return $fields;
    }

}

/**
 * Standard Controller
 *
 * @author Roland Lehmann <rlehmann@pixeltricks.de>, Jiri Ripa <jripa@pixeltricks.de>
 * @since 20.09.2010
 * @copyright 2010 pixeltricks GmbH
 * @license BSD
 */
class Page_Controller extends ContentController implements PermissionProvider {

    public static $allowed_actions = array(
    );

    /**
     * standard init method
     * 
     * @return void
     * @author Jiri Ripa <jripa@pixeltricks.de>
     * @since 20.09.2010
     */
    public function init() {
        $this->registerCustomHtmlForm('QuickSearch', new QuickSearchForm($this));
        $this->registerCustomHtmlForm('QuickLogin', new QuickLoginForm($this));
        $this->extend('init');

        parent::init();
        
        $cssForLayoutType = $this->getCssForLayoutType($this->LayoutType);
        Requirements::themedCSS($cssForLayoutType);
        Requirements::javascript("pixeltricks_module/script/jquery.js");
        Requirements::javascript("silvercart/js/startupScripts.js");
        Requirements::javascript("silvercart/js/jquery.pixeltricks.tools.js");
    }

    /**
     * set Yaml CSS framework Layoutcolumns to Page Template
     *
     * @param integer $LayoutType CSS Layout type
     *
     * @return string String as with name of used Layout
     * @author Jiri Ripa <jripa@pixeltricks.de>
     * @since 25.09.2010
     */
    public function getCssForLayoutType($LayoutType) {
        switch ($LayoutType) {
            case 1:
                return 'layout1column';
                break;
            case 11:
                return 'layout1columnTeaser';
                break;
            case 2:
                return 'layout2columnLeft';
                break;
            case 22:
                return 'layout2columnTeaserLeft';
                break;
            case 3:
                return 'layout2columnRight';
                break;
            case 33:
                return 'layout2columnTeaserRight';
                break;
            case 4:
                return 'layout3column';
                break;
            case 44:
                return 'layout3columnTeaser';
                break;
            default:
                return 'layout2columnLeft';
        }
    }

    /**
     * determin weather a cart is filled or empty; usefull for template conditional
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 1.11.2010
     * @return boolean is cart filled?
     */
    public function isFilledCart() {
        $customer = Member::currentUser();
        
        if ($customer && $customer->hasMethod('shoppingCart') && $customer->shoppingCart()->positions()->Count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * form for incrementing the amount of a shopping cart position
     * 
     * @return Form to increment a shopping cart position
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 20.09.2010
     */
    public function incrementAmountForm() {
        $fields = new FieldSet();
        $fields->push(new HiddenField('ShoppingCartPositionID', 'ShoppingCartPositionID'));
        $actions = new FieldSet();
        $actions->push(new FormAction('doIncrementAmount', '+'));
        $form = new Form($this, 'doIncrementAmount', $fields, $actions);
        return $form;
    }

    /**
     * action method for IncrementAmountForm
     *
     * @param array $data Array with Cartpositions
     * @param Form  $form Cart Form Object
     * 
     * @return object Object CartPage_Controller
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 20.09.2010
     */
    public function doIncrementAmount($data, $form) {
        $shoppingCartPosition = DataObject::get_by_id('ShoppingCartPosition', $data['ShoppingCartPositionID']);
        if ($shoppingCartPosition) {
            if ($shoppingCartPosition->shoppingCartID == Member::currentUser()->shoppingCartID) {//make shure that a customer can delete only his own shoppingCartpositions, in your face, damn hackers!
                $shoppingCartPosition->Quantity++;
                $shoppingCartPosition->write();
            }
        }
        return $this;
    }

    /**
     * form for decrementing the amount of a shopping cart position
     *
     * @return Form
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 30.09.2010
     */
    public function decrementAmountForm() {
        $fields = new FieldSet();
        $fields->push(new HiddenField('ShoppingCartPositionID', 'ShoppingCartPositionID'));
        $actions = new FieldSet();
        $actions->push(new FormAction('doDecrementAmount', '-'));
        $form = new Form($this, 'decrementAmountForm', $fields, $actions);
        return $form;
    }

    /**
     * action method for DecrementAmountForm
     *
     * @param array $data Array with Cartpositions
     * @param Form  $form Cart Form Object
     * 
     * @return bool
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 30.09.2010
     */
    public function doDecrementAmount($data, $form) {
        $shoppingCartPosition = DataObject::get_by_id('ShoppingCartPosition', $data['ShoppingCartPositionID']);
        // Zuweisung gewollt
        if ($shoppingCartPosition) {
            if ($shoppingCartPosition->Quantity > 1
                    && $shoppingCartPosition->shoppingCartID == Member::currentUser()->shoppingCartID) {
                $shoppingCartPosition->Quantity--;
                $shoppingCartPosition->write();
            } elseif ($shoppingCartPosition->Quantity == 1
                    && $shoppingCartPosition->shoppingCartID == Member::currentUser()->shoppingCartID) {
                $shoppingCartPosition->delete();
            }

            return $this;
        }
    }

    /**
     * form for deleting article from shopping cart
     *
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @return Form 
     * @since 30.09.2010
     */
    public function removeFromCartForm() {
        $fields = new FieldSet();
        $fields->push(new HiddenField('ShoppingCartPositionID', 'ShoppingCartPositionID'));
        $actions = new FieldSet();
        $actions->push(new FormAction('doRemoveFromCart', 'entfernen'));
        $form = new Form($this, 'removeFromCartForm', $fields, $actions);
        return $form;
    }

    /**
     * Action to remove article from shopping cart
     *
     * @param array $data Array with Cartpositions
     * @param Form  $form Cart Form Object
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @return Bool
     * @since 30.09.2010
     */
    public function doRemoveFromCart($data, $form) {
        $shoppingCartPosition = DataObject::get_by_id('ShoppingCartPosition', $data['ShoppingCartPositionID']);
        if ($shoppingCartPosition) {
            if ($shoppingCartPosition->shoppingCartID == Member::currentUser()->shoppingCartID) {
                $shoppingCartPosition->delete();
            }
            return $this;
        }
    }

    /**
     * create form for flushing article from shopping cart
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @return Form
     * @since 30.09.2010
     */
    public function flushCartForm() {
        $fields = new FieldSet();
        $fields->push(new HiddenField('cartID', 'cartID', Member::currentUser()->shoppingCartID));
        $actions = new FieldSet();
        $actions->push(new FormAction('doFlushCart', 'leeren'));
        $form = new Form($this, 'flushCartForm', $fields, $actions);
        return $form;
    }

    /**
     * action to flush article from shopping cart
     *
     * @param array $data Array with Cartpositions
     * @param Form  $form Cart Form Object
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @return Bool
     * @since 30.09.2010
     */
    public function doFlushCart($data, $form) {
        if ($data['cartID'] == Member::currentUser()->shoppingCartID) {
            $cartID = Member::currentUser()->shoppingCartID;
            $filter = sprintf("\"shoppingCartID\" = '%s'", $cartID);
            $shoppingCartPositions = DataObject::get('ShoppingCartPosition', $filter);
            foreach ($shoppingCartPositions as $shoppingCartPosition) {
                $shoppingCartPosition->delete();
            }
            return $this;
        }
    }

    /**
     * Eigene Zugriffsberechtigungen definieren.
     *
     * @author Sascha koehler <skoehler@pixeltricks.de>
     * @return array configuration of API permissions
     * @since 12.10.2010
     */
    public function providePermissions() {
        return array(
            'API_VIEW' => 'Darf über die API Objekte auslesen',
            'API_CREATE' => 'Darf über die API Objekte erstellen',
            'API_EDIT' => 'Darf über die API Objekte ändern',
            'API_DELETE' => 'Darf über die API Objekte löschen'
        );
    }

    /**
     * template method for breadcrumbs
     * show breadcrumbs for pages which show a DataObject determined via URL parameter ID
     * see _config.php
     *
     * @return string html for breadcrumbs
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 3.11.2010
     */
    public function getBreadcrumbs() {
        $page = DataObject::get_one(
                        'Page',
                        sprintf(
                                '"URLSegment" LIKE \'%s\'',
                                $this->urlParams['URLSegment']
                        )
        );

        return $this->ContextBreadcrumbs($page);
    }

    /**
     * pages with own url rewriting need their breadcrumbs created in a different way
     *
     * @param Controller $context        the current controller
     * @param int        $maxDepth       maximum levels
     * @param bool       $unlinked       link breadcrumbs elements
     * @param bool       $stopAtPageType ???
     * @param bool       $showHidden     show pages that will not show in menus
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 3.11.2010
     * @return string html for breadcrumbs
     */
    public function ContextBreadcrumbs($context, $maxDepth = 20, $unlinked = false, $stopAtPageType = false, $showHidden = false) {
        $page = $context;
        $parts = array();

        // Get address type
        $address = DataObject::get_by_id($context->getSection(), $this->urlParams['ID']);
        $parts[] = $address->singular_name();

        $i = 0;
        while (
        $page
        && (!$maxDepth || sizeof($parts) < $maxDepth)
        && (!$stopAtPageType || $page->ClassName != $stopAtPageType)
        ) {
            if ($showHidden || $page->ShowInMenus || ($page->ID == $this->ID)) {
                if ($page->URLSegment == 'home') {
                    $hasHome = true;
                }
                if (($page->ID == $this->ID) || $unlinked) {
                    $parts[] = Convert::raw2xml($page->Title);
                } else {
                    $parts[] = ("<a href=\"" . $page->Link() . "\">" . Convert::raw2xml($page->Title) . "</a>");
                }
            }
            $page = $page->Parent;
        }

        return implode(SiteTree::$breadcrumbs_delimiter, array_reverse($parts));
    }

    /**
     * replace Page contentwith Array values
     *
     * @return bool
     * @author Sascha koehler <skoehler@pixeltricks.de>
     * @since  01.10.2010
     */
    protected function replaceContent() {
        $member = Member::currentUser();
        if ($member) {
            $email = $member->Email;

            $this->Content = str_replace(
                            array(
                                '__EMAIL__'
                            ),
                            array(
                                $email
                            ),
                            $this->Content
            );
        }
    }

    /**
     * a template method
     * Function similar to Member::currentUser(); Determins if we deal with a registered customer
     *
     * @return Member|false Costomer-Object or false
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 9.11.10
     */
    public function CurrentRegisteredCustomer() {
        $member = Member::currentUser();
        if ($member) {
            if ($member->ClassName != "AnonymousCustomer") {
                return $member;
            }
        } else {
            return false;
        }
    }

    /**
     * Liefert true oder false, abhängig vom eingeloggten Benutzer
     *
     * @return Boolean true/false Je nach aktuellem Benutzer
     *
     * @author Oliver Scheer <oscheer@pixeltricks.de>
     * @since 01.12.2010
     * @copyright 2010 pixeltricks GmbH
     * @license BSD
     */
    public function MemberInformation() {

        if (Member::currentUser() && Member::currentUser()->ClassName != 'AnonymousCustomer') {
            return true;
        } else {
            return false;
        }

    }

    /**
     * This function is replacing the default SilverStripe Logout Form. This form is used to logout the customer and direct
     * the user to the startpage
     *
     * @return null
     *
     * @author Oliver Scheer <oscheer@pixeltricks.de>
     * @since 11.11.2010
     */
    public function logOut() {
        Security::logout(false);
        Director::redirect("home/");
    }

    /**
     * This function is used to return the Latest Blogentries
     *
     * @return DataObjectSet blog entries
     * @author Oliver <info@pixeltricks.de>, Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 18.10.2010
     */
    public function LatestBlogEntry() {

        $blogEntry = DataObject::get("BlogEntry", "", "Created DESC", "", 1);

        return $blogEntry;
    }

    /**
     * This function is used to return the current count of shopping Cart positions
     *
     * @return Integer $shoppingCartPositions Anzahl der Positionen im Warenkorb
     *
     * @author Oliver Scheer <oscheer@pixeltricks.de>
     * @since 02.12.2010
     */
    public function getCount() {

        $memberID = Member::currentUserID();
        $member = DataObject::get_by_id("Member", $memberID);
        if ($member) {
            $shoppingCartPositions = DataObject::get("ShoppingCartPosition", "\"shoppingCartID\" = '$member->shoppingCartID'");
            return Count($shoppingCartPositions);
        }
    }
}