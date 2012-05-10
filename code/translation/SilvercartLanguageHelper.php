<?php
/**
 * Copyright 2012 pixeltricks GmbH
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
 * @subpackage Translation
 */

/**
 * Helper class to combine language object specific methods.
 * 
 * @package Silvercart
 * @subpackage Translation
 * @author Roland Lehmann <rlehmann@pixeltricks.de>
 * @copyright Pixeltricks GmbH
 * @since 04.01.2012
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class SilvercartLanguageHelper {

    /**
     * Getter for the language object for an object that has translations
     * I impemented it a a static method because it would be redundantly declared
     * in any multilanguage DataObject
     *
     * @param ComponentSet $componentset ComponentSet to search the translation for
     * @param string       $locale       locale eg. de_DE, en_NZ, ...
     *
     * @return DataObject
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 03.01.2012
     */
    public static function getLanguage($componentset, $locale = false) {
        $lang = false;
        if ($locale == false) {
            $locale = Translatable::get_current_locale();
        }
        if ($componentset->find('Locale', $locale)) {
            $lang = $componentset->find('Locale', $locale);
        } elseif (SilvercartConfig::useDefaultLanguageAsFallback()) {
            if ($componentset->find('Locale', SilvercartConfig::DefaultLanguage())) {
                $lang = $componentset->find('Locale', SilvercartConfig::DefaultLanguage());
            }
        }
        return $lang;
    }

    /**
     * Default scaffolding for language objects.
     *
     * @param DataObject $dataobject DataObject to scaffold the form fields for
     * 
     * @return FieldSet
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.01.2012
     */
    public static function prepareCMSFields($dataobject) {
        $languageFields = $dataobject->scaffoldFormFields(
                array(
                    'includeRelations' => false,
                    'tabbed' => false,
                    'ajaxSafe' => true,
                )
        );
        $languageFields->removeByName('Locale');
        foreach ($dataobject->has_one() as $has_oneName => $has_oneObject) {
            $languageFields->removeByName($has_oneName . 'ID');
        }
        return $languageFields;
    }

    /**
     * Default scaffolding for language objects in a popup.
     *
     * @param DataObject $dataobject DataObject to scaffold the form fields for
     * 
     * @return FieldSet
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>, Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.01.2012
     */
    public static function prepareCMSFields_forPopup($dataobject) {
        $fields = $dataobject->getCMSFields();
        $fields->removeByName('Locale');
        foreach ($dataobject->has_one() as $has_oneName => $has_oneObject) {
            $fields->removeByName($has_oneName . 'ID');
        }
        $localeDropdown = self::prepareLanguageDropdownField($dataobject);
        $fields->insertFirst($localeDropdown);
        return $fields;
    }
    
    /**
     * Creates and returns the language dropdown field
     *
     * @param DataObject $dataObj          DataObject to get dropdown for
     * @param string     $translatingClass Context class of the LanguageDropdownField
     * 
     * @return LanguageDropdownField 
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 04.05.2012
     */
    public static function prepareLanguageDropdownField($dataObj, $translatingClass = null) {
        $instance                   = null;
        $alreadyTranslatedLocales   = array();
        if (is_null($translatingClass)) {
            $translatingClass   = $dataObj->ClassName;
            $instance           = $dataObj;
        }
        if ($instance) {
            $alreadyTranslatedLocales   = $instance->getTranslatedLocales();
            unset($alreadyTranslatedLocales[$instance->Locale]);
        }
        $localeDropdown = new LanguageDropdownField(
            'Locale', 
            _t('SilvercartConfig.TRANSLATION'), 
            $alreadyTranslatedLocales,
            $translatingClass,
            'Locale-Native',
            $instance
        );
        $currentLocale          = Translatable::get_current_locale();
        $localesWithTitle       = $localeDropdown->getSource();
        $usedLocalesWithTitle   = Translatable::get_existing_content_languages('SiteTree');
        $languageList           = array();
        $usedLanguageList       = array();
        foreach ($localesWithTitle as $locale => $title) {
            if (is_array($title)) {
                foreach ($title as $locale2 => $title2) {
                    $title2 = self::getLanguageDisplayTitle($locale2, $currentLocale);
                    if (array_key_exists($locale2, $usedLocalesWithTitle)) {
                        $usedLanguageList[$locale2] = $title2;
                        unset($languageList[$locale2]);
                    } else {
                        $languageList[$locale2] = $title2;
                    }
                }
            } else {
                $title = self::getLanguageDisplayTitle($locale, $currentLocale);
                if (array_key_exists($locale, $usedLocalesWithTitle)) {
                    $usedLanguageList[$locale] = $title;
                    unset($languageList[$locale]);
                } else {
                    $languageList[$locale] = $title;
                }
            }
        }
        asort($languageList);
        
        if (count($usedLanguageList)) {
            asort($usedLanguageList);
            $languageList = array(
                _t('Form.LANGAVAIL', "Available languages") => $usedLanguageList,
                _t('Form.LANGAOTHER', "Other languages")    => $languageList
            );
        }
        
        $localeDropdown->setSource($languageList);
        $localeDropdown->setValue($dataObj->Locale);
        return $localeDropdown;
    }
    
    /**
     * Returns the display title of a LanguageDropdownFields option
     *
     * @param string $locale        Locale to get title for
     * @param string $currentLocale Locale to get translated title for
     * 
     * @return string
     */
    public static function getLanguageDisplayTitle($locale, $currentLocale) {
        $title = sprintf(
                "%s  [ %s ]",
                Locale::getDisplayName($locale, $currentLocale),
                Locale::getDisplayName($locale, $locale)
        );
        return $title;
    }
    
    /**
     * Writes the given language object
     *
     * @param DataObject $languageObj Language object to write
     * @param array      $mainRecord  Main record data
     *
     * @return void 
     * 
     * @author Roland Lehmann <rlehmann@pixeltricks.de>
     * @since 04.01.2012
     */
    public static function writeLanguageObject($languageObj, $mainRecord) {
        $record = array();
        foreach ($languageObj->db() as $dbFieldName => $dbFieldType) {
            if (array_key_exists($dbFieldName, $mainRecord)) {
                $record[$dbFieldName] = $mainRecord[$dbFieldName];
            }
        }
        $languageObj->update($record);
        $languageObj->write();
    }
    
    /**
     * Returns all translatable DataObjects as an array
     * 
     * @return array
     * 
     * @global array $_ALL_CLASSES A map of all classes, their type and ancestry
     */
    public static function getTranslatableDataObjects() {
        global $_ALL_CLASSES;
        $translatableDataObjects = array();
        foreach ($_ALL_CLASSES['parents'] as $className => $ancestry) {
            $extensions = Object::get_static($className, 'extensions');
            if (!is_null($extensions) &&
                is_array($extensions) &&
                in_array('SilvercartDataObjectMultilingualDecorator', $extensions)) {
                $translatableDataObjects[] = $className;
            }
        }
        return $translatableDataObjects;
    }
}
