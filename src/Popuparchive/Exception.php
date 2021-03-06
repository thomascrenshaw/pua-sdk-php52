<?php
/**
 * Exception.php contains the exception classes for the for the Pop Up Archive SDK (PHP >=5.2)
 *
 * @category  File
 * @package   Popuparchive_Services\Exception
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */

/**
 * Invalid HTTP response code exception class
 *
 * @category  Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */
class Popuparchive_Services_Exception_Invalid_Http_Response_Code extends Exception
{
    /**
     * HTTP response body.
     *
     * @access protected
     *
     * @var string
     */
    protected $httpBody;

    /**
     * HTTP response code.
     *
     * @access protected
     *
     * @var integer
     */
    protected $httpCode;

    /**
     * Default message.
     *
     * @access protected
     *
     * @var string
     */
    protected $message = 'The requested URL responded with HTTP code %d.';

    /**
     * Constructor.
     *
     * @param string  $message  Message that is displayed to the developer on Exception
     * @param string  $code     Exception code to display (default=0)
     * @param string  $httpBody HTTP Body (optional)
     * @param integer $httpCode HTTP Code (default=0)
     *
     * @return void
     */
    public function __construct($message = null, $code = 0, $httpBody = null, $httpCode = 0)
    {
        $this->httpBody = $httpBody;
        $this->httpCode = $httpCode;
        $message = sprintf($this->message, $httpCode);

        parent::__construct($message, $code);
    }

    /**
     * Get HTTP response body.
     *
     * @access public
     *
     * @return mixed
     */
    public function getHttpBody()
    {
        return $this->httpBody;
    }

    /**
     * Get HTTP response code.
     *
     * @access public
     *
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

}

/**
 * Popuparchive unsupported response format exception.
 *
 * @category  Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */
class Popuparchive_Services_Exception_Unsupported_Response_Format extends Exception
{
    /**
     * Default message.
     *
     * @access protected
     *
     * @var string
     */
    protected $message = 'The given response format is unsupported.';

}
