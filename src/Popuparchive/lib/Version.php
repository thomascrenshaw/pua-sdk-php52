<?php
/**
 * Version.php contains the Popuparchive Services Version class
 *
 * @category  File
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */

/**
 * Popuparchive package version
 *
 * @category  Services
 * @package   Popuparchive_Services\Version
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */

class Popuparchive_Services_Version
{
    const MAJOR = 0;
    const MINOR = 0;
    const PATCH = 1;

    /**
     * Magic to string method
     *
     * @return string
     *
     * @access public
     */
    public function __toString()
    {
        return implode('.', array(self::MAJOR, self::MINOR, self::PATCH));
    }
}
