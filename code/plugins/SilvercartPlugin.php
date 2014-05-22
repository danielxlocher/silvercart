<?php
/**
 * Copyright 2011 pixeltricks GmbH
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
 * @subpackage Plugins
 */

/**
 * Base object providing general methods for all extending plugin-provider
 * objects.
 *
 * @package Silvercart
 * @subpackage Plugins
 * @author Sascha Koehler <skoehler@pixeltricks.de>
 * @since 22.09.2011
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @copyright 2011 pixeltricks GmbH
 */
class SilvercartPlugin extends Object {
    
    /**
     * The object that called this plugin
     *
     * @var mixed
     */
    protected $callingObject = null;

    /**
     * Contains informations about calling objects for caching purposes.
     *
     * @var array
     */
    protected static $pluginProvidersForCallingObject = array();

    /**
     * Contains all registered plugin providers.
     *
     * @var array
     */
    public static $registeredPluginProviders = array();
    
    /**
     * Takes the calling object as argument and stores it in a class variable
     *
     * @param mixed $callingObject The calling object
     *
     * @return void
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public function __construct($callingObject) {
        parent::__construct();
        
        $this->callingObject = $callingObject;
    }
    
    /**
     * Registers a plugin provider for the given class.
     *
     * @param string $forObject               The class name of the object you want to provide with the plugin
     * @param string $pluginProviderClassName The class name of the plugin provider
     *
     * @return void
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public static function registerPluginProvider($forObject, $pluginProviderClassName) {
        if (!array_key_exists($forObject, self::$registeredPluginProviders)) {
            self::$registeredPluginProviders[$forObject] = array();
        }
        
        self::$registeredPluginProviders[$forObject][] = array(
            'className' => $pluginProviderClassName,
            'object'    => null
        );
    }
    
    /**
     * Returns all extensions for the given class.
     *
     * @param string $className The name of the class for which you want the extensions
     *
     * @return array
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public static function getExtensionsFor($className) {
        return self::get_static($className, 'extensions');
    }
    
    /**
     * Returns all extensions for the current class.
     *
     * @return array
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public function getExtensions() {
        return self::getExtensionsFor($this->class);
    }
    
    /**
     * Returns the calling object.
     *
     * @return mixed
     */
    public function getCallingObject() {
        return $this->callingObject;
    }
    
    /**
     * The central method. Every Silvercart object calls this method to invoke
     * a plugin action.
     *
     * @param mixed   $callingObject            The object that performs the call
     * @param string  $methodName               The name of the method to call
     * @param array   $arguments                The arguments to pass
     * @param boolean $passArgumentsByReference Indicate wether the arguments should be passed by reference
     * @param mixed   $returnContainer          The container to gather the output. This can be e.g. a string if you want concatenated strings,
     *                                          an array or a DataObjectSet
     *
     * @return mixed
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public static function call($callingObject, $methodName, $arguments = array(), $passArgumentsByReference = false, $returnContainer = '') {
        if (!is_array($arguments)) {
            if ($passArgumentsByReference) {
                $arguments = array(&$arguments);
            } else {
                $arguments = array($arguments);
            }
        }

        if (array_key_exists($callingObject->class, self::$pluginProvidersForCallingObject)) {
            $pluginProviders = self::$pluginProvidersForCallingObject[$callingObject->class];
        } else {
            $pluginProviders = self::getPluginProvidersForObject($callingObject);
            self::$pluginProvidersForCallingObject[$callingObject->class] = $pluginProviders;
        }

        if ($pluginProviders) {
            foreach ($pluginProviders as $pluginProvider) {
                if (method_exists($pluginProvider, $methodName)) {
                    if ($passArgumentsByReference) {
                        if (is_array($returnContainer)) {
                            $returnContainer[] = $pluginProvider->$methodName($arguments, $callingObject);
                        } else if ($returnContainer instanceof DataObjectSet) {
                            if ($returnContainer->TotalItems() === 0) {
                                $returnContainer = $pluginProvider->$methodName($arguments, $callingObject);
                            } else {
                                $returnContainer->merge($pluginProvider->$methodName($arguments, $callingObject));
                            }
                        } else if ($returnContainer == 'boolean') {
                            $returnContainer = $pluginProvider->$methodName($arguments,$callingObject);
                        } else if ($returnContainer == 'DataObject') {
                            $returnContainer = $pluginProvider->$methodName($arguments,$callingObject);
                        } else if ($returnContainer == 'DataObjectSet') {
                            $returnContainer = $pluginProvider->$methodName($arguments,$callingObject);
                        } else {
                            $result = $pluginProvider->$methodName($arguments, $callingObject);
                            if (is_string($result)) {
                                $returnContainer .= $result;
                            } else {
                                $returnContainer = $result;
                            }
                        }
                    } else {
                        if (is_array($returnContainer)) {
                            $returnContainer[] = $pluginProvider->$methodName($arguments, $callingObject);
                        } else if ($returnContainer instanceof DataObjectSet) {
                            if ($returnContainer->TotalItems() === 0) {
                                $returnContainer = $pluginProvider->$methodName($arguments, $callingObject);
                            } else {
                                $returnContainer->merge($pluginProvider->$methodName($arguments, $callingObject));
                            }
                        } else if ($returnContainer == 'boolean') {
                            $returnContainer = $pluginProvider->$methodName($arguments, $callingObject);
                        } else if ($returnContainer == 'DataObject') {
                            $returnContainer = $pluginProvider->$methodName($arguments, $callingObject);
                        } else if ($returnContainer == 'DataObjectSet') {
                            $returnContainer = $pluginProvider->$methodName($arguments, $callingObject);
                        } else {
                            $result = $pluginProvider->$methodName($arguments, $callingObject);
                            if (is_string($result)) {
                                $returnContainer .= $result;
                            } else {
                                $returnContainer = $result;
                            }
                        }
                    }
                } else {
                    if ($returnContainer == 'boolean') {
                        $returnContainer = false;
                    } else if ($returnContainer == 'DataObject') {
                        $returnContainer = new DataObject();
                    } else if ($returnContainer == 'DataObjectSet') {
                        $returnContainer = new DataObjectSet();
                    }
                }
            }
        } else {
            $returnContainer = false;
        }
        
        return $returnContainer;
    }
    
    /**
     * Retrieves all plugin providers that belong to the given object.
     *
     * @param mixed $callingObject The object for which the plugin providers shall be retrieved
     *
     * @return array
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public static function getPluginProvidersForObject($callingObject) {
        $pluginProviders = array();
        
        if (array_key_exists($callingObject->class, self::$registeredPluginProviders)) {
            foreach (self::$registeredPluginProviders[$callingObject->class] as $pluginProvider) {
                if (empty($pluginProvider['object'])) {
                    $pluginProviderClassName  = $pluginProvider['className'];
                    $pluginProvider['object'] = new $pluginProviderClassName($callingObject);
                }
                
                $pluginProviders[] = $pluginProvider['object'];
            }
        }
        
        return $pluginProviders;
    }
    
    // ------------------------------------------------------------------------
    // Base methods for plugin providers
    // ------------------------------------------------------------------------
    
    /**
     * Initialisation for plugin providers.
     *
     * @param array &$arguments The arguments to pass
     * 
     * @return void
     *
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public function init(&$arguments = array()) {
    }
    
    /**
     * Extension results consist of arrays. This method concatenates all array
     * entries into a string.
     *
     * @param array $extensionResultSet The result delivered by an extension
     *
     * @return string
     * 
     * @author Sascha Koehler <skoehler@pixeltricks.de>
     * @since 22.09.2011
     */
    public function returnExtensionResultAsString($extensionResultSet) {
        $result = '';
        
        if (is_array($extensionResultSet)) {
            foreach ($extensionResultSet as $extensionResult) {
                $result .= $extensionResult;
            }
        }
        
        return $result;
    }
    
    /**
     * Extension results consist of arrays. This method concatenates all array
     * entries into a string, separated by <br/>.
     *
     * @param array  $extensionResultSet The result delivered by an extension
     * @param string $prefix             A prefix string to add
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.04.2014
     */
    public function returnExtensionResultAsHtmlString($extensionResultSet, $prefix = '') {
        $result = '';
        if (is_array($extensionResultSet)) {
            foreach ($extensionResultSet as $key => $extensionResult) {
                if (!is_string($extensionResult) ||
                    strlen(trim($extensionResult)) == 0 ||
                    empty($extensionResult)) {
                    unset($extensionResultSet[$key]);
                }
            }
            $result = implode('<br/>' . $prefix, $extensionResultSet);
            if (!empty($result)) {
                $result = $prefix . $result;
            }
        }
        return $result;
    }
    
    /**
     * Extension results consist of arrays. This method concatenates all array
     * entries into a DataObjectSet.
     *
     * @param array $extensionResultSet The result delivered by an extension
     *
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 12.09.2012
     */
    public function returnExtensionResultAsDataObjectSet($extensionResultSet) {
        $result = new DataObjectSet();
        
        if (is_array($extensionResultSet)) {
            foreach ($extensionResultSet as $extensionResult) {
                if ($extensionResult instanceof DataObjectSet) {
                    $result->merge($extensionResult);
                } else {
                    $result->push($extensionResult);
                }
            }
        }
        return $result;
    }
    
    /**
     * Extension results consist of potential null values. The first not null 
     * value will be returned.
     *
     * @param array $extensionResultSet The result delivered by an extension
     *
     * @return DataObjectSet
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2012
     */
    public function returnFirstNotNull($extensionResultSet) {
        $result = null;
        
        if (is_array($extensionResultSet)) {
            foreach ($extensionResultSet as $extensionResult) {
                if (!is_null($extensionResult)) {
                    $result = $extensionResult;
                    break;
                }
            }
        }
        return $result;
    }
}
