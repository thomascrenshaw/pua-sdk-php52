<?php
/**
 * Pop Up Archive API SDK written in PHP (>=5.2)
 *
 * @category  File
 * @package   Popuparchive_Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */
require_once 'lib/Exception.php';
require_once 'lib/Version.php';

/**
 * Pop Up Archive API SDK
 *
 * @category  Services
 * @package   Popuparchive_Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 *
 * PSR1 - https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
 * PSR2 - https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
 */
class Popuparchive_Services
{
    /**
     * Custom cURL option
     *
     * @var integer
     *
     * @access public
     */
    const CURLOPT_OAUTH_TOKEN = 1337;
    /**
     * Default cURL options
     *
     * @var array
     *
     * @access private
     * @static
     */
     private static $_curlDefaultOptions = array(
         CURLOPT_HEADER => true,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_USERAGENT => ''
     );

    /**
     * cURL options
     *
     * @var array
     *
     * @access private
     */
    private $_curlOptions;

    /**
     * API domain
     *
     * @var string
     *
     * @access private
     * @static
     */
    private $_domain = 'www.popuparchive.org';

    /**
     * HTTP response body from the last request
     *
     * @var string
     *
     * @access private
     */
    private $_lastHttpResponseBody;

    /**
     * HTTP response code from the last request
     *
     * @var integer
     *
     * @access private
     */
    private $_lastHttpResponseCode;

    /**
     * HTTP response headers from last request
     *
     * @var array
     *
     * @access private
     */
    private $_lastHttpResponseHeaders;

    /**
     * Available response formats
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_responseFormats = array(
        '*' => '*/*',
        'json' => 'application/json'
    );

    /**
     * HTTP user agent
     *
     * @var string
     *
     * @access private
     * @static
     */
    private static $_userAgent = 'SDK-PHP52-Popuparchive';

    /**
     * Class constructor
     *
     * @return void
     *
     * @access public
     */
    public function __construct()
    {
        $this->_responseFormat = self::$_responseFormats['json'];
        $this->_curlOptions = self::$_curlDefaultOptions;
        $this->_curlOptions[CURLOPT_USERAGENT] .= $this->getUserAgent();
    }

    /**
     * Get cURL options
     *
     * @return mixed
     *
     * @access public
     */
    public function getCurlOptions()
    {
        return $this->_curlOptions;
    }

    /**
     * Get HTTP response header
     *
     * @param string $header Name of the header
     *
     * @return mixed
     *
     * @access public
     */
    public function getHttpHeader($header)
    {
        if (is_array($this->_lastHttpResponseHeaders)) {
            return $this->_lastHttpResponseHeaders[$header];
        } else {
            return false;
        }
    }

    /**
     * Get response format
     *
     * @return string
     *
     * @access public
     */
    public function getResponseFormat()
    {
        return $this->_responseFormat;
    }

    /**
     * Set cURL options
     *
     * The method accepts arguments in two ways.
     *
     * You could pass two arguments when adding a single option.
     * <code>
     * $popuparchive->setCurlOptions(CURLOPT_SSL_VERIFYHOST, 0);
     * </code>
     *
     * You could also pass an associative array when adding multiple options.
     * <code>
     * $popuparchive->setCurlOptions(array(
     *     CURLOPT_SSL_VERIFYHOST => 0,
     *    CURLOPT_SSL_VERIFYPEER => 0
     * ));
     * </code>
     *
     * @return object
     *
     * @access public
     */
    public function setCurlOptions()
    {
        $args = func_get_args();
        $options = (is_array($args[0]))
            ? $args[0]
            : array($args[0] => $args[1]);

        foreach ($options as $key => $val) {
            $this->_curlOptions[$key] = $val;
        }

        return $this;
    }

    /**
     * Set response format
     *
     * @param string $format Response format
     *
     * @return object
     * @throws Popuparchive_Services_Unsupported_Response_Format_Exception
     *
     * @access public
     */
    public function setResponseFormat($format)
    {
        if (array_key_exists($format, self::$_responseFormats)) {
            $this->_responseFormat = self::$_responseFormats[$format];
        } else {
            throw new Popuparchive_Services_Unsupported_Response_Format_Exception();
        }

        return $this;
    }

    /**
     * Send a GET HTTP request
     *
     * @param string $path        Request path
     * @param array  $params      Optional query string parameters
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function get($path, $params = array(), $curlOptions = array())
    {
        $url = $this->buildUrl($path, $params);

        return $this->request($url, $curlOptions);
    }

    /**
     * Get a Pop Up Archive collectoin by its ID
     *
     * @param string $collectionId Request path
     * @param array  $params       Optional query string parameters
     * @param array  $curlOptions  Optional cURL options
     *
     * @example popuparchive_examples.php 21 2 example call for getCollectionById
     *
     * @return JSON
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function getCollectionById($collectionId, $params = array(), $curlOptions = array())
    {
        $url = $this->buildURL(
            'collections/'.$collectionId,
            $params
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Get a Pop Up Archive item by its ID and associated Collection ID
     *
     * @param string $collectionId Request path
     * @param string $itemId       Request path
     * @param array  $params       Optional query string parameters
     * @param array  $curlOptions  Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function getItemById($collectionId, $itemId, $params = array(), $curlOptions = array())
    {
        $url = $this->buildURL(
            'collections/'.$collectionId.'/items/'.$itemId,
            $params
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Search Pop Up Archive by a single facet
     *
     * @param string $filterKey   filter type (collection_id, interviewer, interviewee)
     * @param string $filterValue filter value
     * @param array  $params      Optional query string parameters
     * @param array  $curlOptions Optional cURL options
     *
     * @return JSON
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function searchByFilter($filterKey, $filterValue, $params = array(), $curlOptions = array())
    {
        $url = $this->buildURL(
            'search/?filters['.$filterKey.']='.$filterValue,
            $params
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Construct a URL
     *
     * @param string $path   Relative or absolute URI
     * @param array  $params Optional query string parameters
     *
     * @return string $url
     *
     * @access protected
     */
    protected function buildUrl($path, $params = array())
    {
        if (preg_match('/^https?\:\/\//', $path)) {
            $url = $path;
        } else {
            $url = 'https://';
            $url .= $this->_domain;
            $url .= '/api/';
            $url .= $path;
        }

        $url .= (count($params)) ? '?' . http_build_query($params) : '';
        //echo('url is '.$url.'<br/>');
        return $url;
    }

    /**
     * Construct default HTTP request headers
     *
     * @return array $headers
     *
     * @access protected
     */
    protected function buildDefaultHeaders()
    {
        $headers = array();

        if ($this->_responseFormat) {
            array_push($headers, 'Accept: ' . $this->_responseFormat);
        }

        return $headers;
    }

    /**
     * Get HTTP user agent
     *
     * @return string
     *
     * @access protected
     */
    protected function getUserAgent()
    {
        return self::$_userAgent . '/' . new Popuparchive_Services_Version;
    }

    /**
     * Parse HTTP headers
     *
     * @param string $headers HTTP headers
     *
     * @return array $parsedHeaders
     *
     * @access protected
     */
    protected function parseHttpHeaders($headers)
    {
        $headers = explode("\n", trim($headers));
        $parsedHeaders = array();

        foreach ($headers as $header) {
            if (!preg_match('/\:\s/', $header)) {
                continue;
            }

            list($key, $val) = explode(': ', $header, 2);
            $key = str_replace('-', '_', strtolower($key));
            $val = trim($val);

            $parsedHeaders[$key] = $val;
        }

        return $parsedHeaders;
    }

    /**
     * Validate HTTP response code
     *
     * @param integer $code HTTP code
     *
     * @return boolean
     *
     * @access protected
     */
    protected function validResponseCode($code)
    {
        return (bool) preg_match('/^20[0-9]{1}$/', $code);
    }

    /**
     * Performs the actual HTTP request using cURL
     *
     * @param string $url         Absolute URL to request
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     * @throws Popuparchive_Services_Invalid_Http_Response_Code_Exception
     *
     * @access protected
     */
    protected function request($url, $curlOptions = array())
    {
        $curlinit = curl_init($url);
        $options = $this->_curlOptions;
        $options += $curlOptions;

        $includeAccessToken = false;

        if (array_key_exists(CURLOPT_HTTPHEADER, $options)) {
            $options[CURLOPT_HTTPHEADER] = array_merge(
                $this->buildDefaultHeaders(),
                $curlOptions[CURLOPT_HTTPHEADER]
            );
        } else {
            $options[CURLOPT_HTTPHEADER] = $this->buildDefaultHeaders(
                $includeAccessToken
            );
        }

        curl_setopt_array($curlinit, $options);

        $data = curl_exec($curlinit);
        $info = curl_getinfo($curlinit);

        curl_close($curlinit);

        if (array_key_exists(CURLOPT_HEADER, $options) && $options[CURLOPT_HEADER]) {
            $this->_lastHttpResponseHeaders = $this->parseHttpHeaders(
                substr($data, 0, $info['header_size'])
            );
            $this->_lastHttpResponseBody = substr($data, $info['header_size']);
        } else {
            $this->_lastHttpResponseHeaders = array();
            $this->_lastHttpResponseBody = $data;
        }

        $this->_lastHttpResponseCode = $info['http_code'];

        if ($this->validResponseCode($this->_lastHttpResponseCode)) {
            return $this->_lastHttpResponseBody;
        } else {
            throw new Popuparchive_Services_Invalid_Http_Response_Code_Exception(
                null,
                0,
                $this->_lastHttpResponseBody,
                $this->_lastHttpResponseCode
            );
        }
    }
}