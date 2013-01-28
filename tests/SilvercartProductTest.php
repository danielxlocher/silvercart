<?php
/**
 * Copyright 2010, 2011 pixeltricks GmbH
 *
 * This file is part of SilverCart.
 *
 * SilverCart is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SilverCart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with SilverCart.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package Silvercart
 * @subpackage Tests
 */

/**
 * tests for methods of the class SilvercartProduct
 *
 * @package Silvercart
 * @subpackage Tests
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright Pixeltricks GmbH
 * @since 18.04.2011
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartProductTest extends SapphireTest {
    
    /**
     * Fixture file
     *
     * @var string
     */
    public static $fixture_file = 'silvercart/tests/SilvercartProductTest.yml';
    
    /**
     * test for the wrapper function SilvercartProduct::get()
     * -filtering via SilvercartProduct::setRequiredAttributes
     * -filtering via where clause
     * -isActive
     * -up to three required attributes
     * 
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <diel@pixeltricks.de>, Carolin Wörner <cwoerner@pixeltricks.de>
     * @since 25.01.2013
     */
    public function testGet() {
        //Only active products with a price or free of charge must be loaded. 
        SilvercartProduct::setRequiredAttributes("Price");
        $productsWithPrice = SilvercartProduct::get();
        $this->assertEquals(5, (int) $productsWithPrice->Count(), "The quantity of products with a price is not correct.");
        
        //Only active products with short description and price defined as required attributes must be loaded
        SilvercartProduct::setRequiredAttributes("Price, ShortDescription");
        $productsWithPriceAndShortDescription = SilvercartProduct::get();
        $this->assertEquals(4, (int) $productsWithPriceAndShortDescription->Count(), "The quantity of products with price and short description is not correct.");
        
        //Only one specific product with Title = 'Product with price'
        $productsWithPriceTitle = SilvercartProduct::get()->filter(array("Title" => 'Product with price'));
        $this->assertTrue($productsWithPriceTitle->Count() == 1, "Quantity of products with Title 'product with price' not correct");
        
        //inactive products must not be loaded
        $productsWithInactiveTitle = SilvercartProduct::get()->filter(array("Title" => 'inactive product'));
        $this->assertTrue($productsWithInactiveTitle->Count() == 0, "An inactive product can be loaded via SilvercartProduct::get()");
        
        //load products with three required attributes defined
        SilvercartProduct::setRequiredAttributes("Price, ShortDescription, LongDescription");
        $productsWithPriceAndShortDescriptionAndLongDescription = SilvercartProduct::get();
        $this->assertEquals(3, $productsWithPriceAndShortDescriptionAndLongDescription->Count(), "The quantity of products with price, short description and long description set is not correct.");
        
    }
    
    /**
     * tests the function getPrice which should return prices dependent on pricetypes
     * 
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <diel@pixeltricks.de>, Carolin Wörner <cwoerner@pixeltricks.de>
     * @since 28.01.2013
     */
    public function testGetPrice() {
        $productWithPrice = $this->objFromFixture("SilvercartProduct", "ProductWithPrice");
        
        //check price for admins
        $this->assertEquals(99.99, $productWithPrice->getPrice()->getAmount(), 'Error: A admin user without address gets net prices shown.');
        
        //check for anonymous users, test runner makes an auto login, so we have to log out first
        $member = Member::currentUser();
        if ($member) {
            $member->logOut();
        }
        
        $this->assertEquals(99.99, $productWithPrice->getPrice()->getAmount());
        
        //check price for business customers
        $businessCustomer = $this->objFromFixture("Member", "BusinessCustomer");
        $businessCustomer->logIn();
        $productWithPriceWithoutShortDescription = $this->objFromFixture("SilvercartProduct", "ProductWithPriceWithoutShortDescription");
        $this->assertEquals(9.00, $productWithPriceWithoutShortDescription->getPrice()->getAmount(), "business customers price is not correct.");
        $businessCustomer->logOut();
        
        //check price for regular customers
        $regularCustomer = $this->objFromFixture("Member", "RegularCustomer");
        $regularCustomer->logIn();
        $this->assertEquals(99.99, $productWithPrice->getPrice()->getAmount());
        $regularCustomer->logOut();
    }
    
    /**
     * add a new product to a cart
     * increase existing shopping cart positions amount
     * 
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <diel@pixeltricks.de>, Carolin Wörner <cwoerner@pixeltricks.de>
     * @since 25.01.2013
     */
    public function testAddToCart() {
        $cart = $this->objFromFixture("SilvercartShoppingCart", "ShoppingCart");
        $cartPosition = $this->objFromFixture("SilvercartShoppingCartPosition", "ShoppingCartPosition");
        $productWithPrice = $this->objFromFixture("SilvercartProduct", "ProductWithPrice");
        
        //existing position
        $productWithPrice->addToCart($cart->ID, 2);
        $position = DataObject::get_by_id("SilvercartShoppingCartPosition", $cartPosition->ID);
        $this->assertEquals(3, (int) $position->Quantity, "The quantity of the overwritten shopping cart position is incorrect.");
        
        //new position
        $productWithPriceWithoutLongDescription = $this->objFromFixture("SilvercartProduct", "ProductWithPriceWithoutLongDescription");
        $productWithPriceWithoutLongDescription->addToCart($cart->ID);
        $refreshedPosition = DataObject::get_one("SilvercartShoppingCartPosition", "SilvercartProductID = $productWithPriceWithoutLongDescription->ID");
        $this->assertEquals(1, $refreshedPosition->Quantity, "The quantity of the newly created shopping cart position is incorrect.");
        
    }
    
    /**
     * tests the reqired attributes system for products
     * 
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <diel@pixeltricks.de>, Carolin Wörner <cwoerner@pixeltricks.de>
     * @since 25.01.2013
     */
    public function testRequiredAttributes() {
        
        //two attributes
        SilvercartProduct::resetRequiredAttributes();
        SilvercartProduct::setRequiredAttributes("Price, Weight");
        $twoAttributes = SilvercartProduct::getRequiredAttributes();
        $this->assertEquals(array("Price", "Weight"), $twoAttributes, "Something went wrong setting two required attributes.");
        
        //four attributes
        SilvercartProduct::resetRequiredAttributes();
        SilvercartProduct::setRequiredAttributes("Price, Weight, ShortDescription, LongDescription");
        $fourAttributes = SilvercartProduct::getRequiredAttributes();
        $this->assertEquals(array("Price", "Weight", "ShortDescription", "LongDescription"), $fourAttributes, "Something went wrong setting four required attributes.");
    }
    
    /**
     * Is tax rate returned correctly?
     * 
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 24.4.2011
     */
    public function testGetTaxRate() {
        $productWithTax = $this->objFromFixture("SilvercartProduct", "ProductWithPrice");
        $taxRate = $productWithTax->getTaxRate();
        $this->assertEquals(19, $taxRate, "The tax rate is not correct.");
    }
    
    /**
     * Does the method return the correct boolean answer?
     * 
     * @return void
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 24.4.2011
     */
    public function testShowPricesGross() {
        $product = $this->objFromFixture("SilvercartProduct", "ProductWithPrice");
        
        //admin is logged in
        $admin = Member::currentUser();
        
        //admin logged out
        $admin->logOut();
        $this->assertTrue($product->showPricesGross(), "Inspite nobody is logged in prices are shown net.");
    }
}

