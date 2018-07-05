<?php

namespace SilverCart\Extensions\ORM\FieldType;

use SilverStripe\Core\Extension;

/**
 * Extension for SilverStripe\ORM\FieldType\DBString.
 *
 * @package SilverCart
 * @subpackage Extensions_ORM_FieldType
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 05.07.2018
 * @copyright 2018 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class DBStringExtension extends Extension {

    /**
     * Limit this field's content by a number of words AND line breaks.
     *
     * @param int    $numWords      Number of words to limit by.
     * @param int    $numLineBreaks Number of line breaks to limit by.
     * @param string $add           Ellipsis to add to the end of truncated string.
     *
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.07.2018
     */
    public function LimitWordAndLineBreakCount($numWords = 26, $numLineBreaks = 5, $add = '...') {
        $value     = $this->owner->LimitWordCount($numWords, '');
        $lines     = explode(PHP_EOL, $value);
        $result    = '';
        $lineCount = 0;
        foreach ($lines as $line) {
            if ($numLineBreaks < $lineCount) {
                break;
            }
            if (!empty($result)) {
                $result .= PHP_EOL;
            }
            $result .= $line;
            if (!empty($line)) {
                $lineCount++;
            }
        }
        $result = trim($result);
        if (!empty($result)) {
            $result .= $add;
        }
        return $result;
    }

}