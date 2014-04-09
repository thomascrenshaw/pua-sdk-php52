<?php
/**
 * Pop Up Archive API SDK written in PHP (>=5.2)
 *
 * @category  File
 * @package   Popuparchive_Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */
require_once 'Popuparchive/Exception.php';
require_once 'Popuparchive/Version.php';

/**
 * Pop Up Archive API SDK
 *
 * @category  Services
 * @package   Popuparchive_Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 
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
     * Access token returned by the service provider after a successful authentication
     *
     * @var string
     *
     * @access private
     */
    private $_accessToken;

    /**
     * Version of the API to use
     *
     * @var integer
     *
     * @access private
     * @static
     */
    private static $_apiVersion = 1;

    /**
     * Supported audio MIME types
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_audioMimeTypes = array(
        'aac' => 'video/mp4',
        'aiff' => 'audio/x-aiff',
        'flac' => 'audio/flac',
        'mp3' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'wav' => 'audio/x-wav'
    );

    /**
     * OAuth client id
     *
     * @var string
     *
     * @access private
     */
    private $_clientId;

    /**
     * OAuth client secret
     *
     * @var string
     *
     * @access private
     */
    private $_clientSecret;

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
     * @todo add development domain when it is launched
     *
     * @var string
     *
     * @access private
     * @static
     */
    private $_domain = 'www.popuparchive.com';

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
     * OAuth paths
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_paths = array(
        'authorize' => 'oauth/authorize',
        'access_token' => 'oauth/token',
    );

    /**
     * OAuth redirect URI
     *
     * @var string
     *
     * @access private
     */
    private $_redirectUri;

     /**
     * API response format MIME type
     *
     * @var string
     *
     * @access private
     */
    private $_requestFormat;

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
     * @param string $clientId     OAuth client id
     * @param string $clientSecret OAuth client secret
     * @param string $redirectUri  OAuth redirect URI
     *
     * @return void
     * @throws Popuparchive_Services_Missing_Client_Id_Exception
     *
     * @access public
     */
    public function __construct($clientId, $clientSecret, $redirectUri = null)
    {
        if (empty($clientId)) {
            throw new Popuparchive_Services_Missing_Client_Id_Exception();
        }
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
        $this->_responseFormat = self::$_responseFormats['json'];
        $this->_curlOptions = self::$_curlDefaultOptions;
        $this->_curlOptions[CURLOPT_USERAGENT] .= $this->getUserAgent();
    }

    /**
     * Get authorization URL
     *
     * @param array $params Optional query string parameters
     *
     * @return string
     *
     * @access public
     * @see Popuparchive::buildUrl()
     */
    public function getAuthorizeUrl($params = array())
    {
        $defaultParams = array(
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUri,
            'response_type' => 'code'
        );
        $params = array_merge($defaultParams, $params);

        return $this->buildUrl(self::$_paths['authorize'], $params, false);
    }

    /**
     * Get access token URL
     *
     * @param array $params Optional query string parameters
     *
     * @return string
     *
     * @access public
     * @see Popuparchive::buildUrl()
     */
    public function getAccessTokenUrl($params = array())
    {
        return $this->buildUrl(self::$_paths['access_token'], $params, false);
    }

    /**
     * Retrieve access token through credentials flow
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return mixed
     *
     * @access public
     */
    public function credentialsFlow($username, $password)
    {
        $postData = array(
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password'
        );

        $options = array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData);
        $response = json_decode(
            $this->request($this->getAccessTokenUrl(), $options),
            true
        );

        if (array_key_exists('access_token', $response)) {
            $this->_accessToken = $response['access_token'];

            return $response;
        } else {
            return false;
        }
    }

    /**
     * Retrieve access token
     *
     * @param string $code        Optional OAuth code returned from the service provider
     * @param array  $postData    Optional post data
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::getAccessToken()
     */
    public function accessToken($code = null, $postData = array(), $curlOptions = array())
    {
        $defaultPostData = array(
            'code' => $code,
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'redirect_uri' => $this->_redirectUri,
            'grant_type' => 'authorization_code'
        );
        $postData = array_filter(array_merge($defaultPostData, $postData));

        return $this->getAccessToken($postData, $curlOptions);
    }

    /**
     * Refresh access token
     *
     * @param string $refreshToken The token to refresh
     * @param array  $postData     Optional post data
     * @param array  $curlOptions  Optional cURL options
     *
     * @return mixed
     * @see Popuparchive::getAccessToken()
     *
     * @access public
     */
    public function accessTokenRefresh($refreshToken, $postData = array(), $curlOptions = array())
    {
        $defaultPostData = array(
            'refresh_token' => $refreshToken,
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'redirect_uri' => $this->_redirectUri,
            'grant_type' => 'refresh_token'
        );
        $postData = array_merge($defaultPostData, $postData);

        return $this->getAccessToken($postData, $curlOptions);
    }

    /**
     * Get access token
     *
     * @return mixed
     *
     * @access public
     */
    public function getAccessTokenPlease()
    {
        return $this->_authCode;
    }

   /**
     * Get API version
     *
     * @return integer
     *
     * @access public
     */
    public function getApiVersion()
    {
        return self::$_apiVersion;
    }

    /**
     * Get the corresponding MIME type for a given file extension
     *
     * @param string $extension Given extension
     *
     * @return string
     * @throws Popuparchive_Services_Unsupported_Audio_Format_Exception
     *
     * @access public
     */
    public function getAudioMimeType($extension)
    {
        if (array_key_exists($extension, self::$_audioMimeTypes)) {
            return self::$_audioMimeTypes[$extension];
        } else {
            throw new Popuparchive_Services_Unsupported_Audio_Format_Exception();
        }
    }

   /**
     * Get cURL options
     *
     * @param string $key Optional options key
     *
     * @return mixed
     *
     * @access public
     */
    public function getCurlOptions($key = null)
    {
         if ($key) {
            return (array_key_exists($key, $this->_curlOptions))
                ? $this->_curlOptions[$key]
                : false;
        } else {
            return $this->_curlOptions;
        }
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
        if (is_array($this->_lastHttpResponseHeaders)
            && array_key_exists($header, $this->_lastHttpResponseHeaders)
        ) {
            return $this->_lastHttpResponseHeaders[$header];
        } else {
            return false;
        }
    }

    /**
     * Get redirect URI for the authentication flow
     *
     * @return string
     *
     * @access public
     */
    public function getRedirectUri()
    {
        return $this->_redirectUri;
    }

    /**
     * Get response format from the request
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
     * Set the access token
     *
     * @param string $accessToken Access token
     *
     * @return object
     *
     * @access public
     */
    public function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;

        return $this;
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
     * Set redirect URI
     *
     * @param string $redirectUri Redirect URI
     *
     * @return object
     *
     * @access public
     */
    public function setRedirectUri($redirectUri)
    {
        $this->_redirectUri = $redirectUri;

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
     * Send a POST HTTP request
     *
     * @param string $path        Request path
     * @param array  $postData    Optional post data
     * @param array  $curlOptions Optional cURL options
     * 
     * @todo the post method has not been tested with live data
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::_request()
     */
    public function post($path, $postData = array(), $curlOptions = array())
    {
        $url = $this->buildUrl($path);
        $options = array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData);
        $options += $curlOptions;

        return $this->request($url, $options);
    }

    /**
     * Send a PUT HTTP request
     *
     * @param string $path        Request path
     * @param array  $postData    Optional post data
     * @param array  $curlOptions Optional cURL options
     *
     * 
     * @todo the put method has not been tested with live data
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::_request()
     */
    public function put($path, $postData, $curlOptions = array())
    {
        $url = $this->_buildUrl($path);
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $postData
        );
        $options += $curlOptions;

        return $this->_request($url, $options);
    }

    /**
     * Send a DELETE HTTP request
     *
     * @param string $path        Request path
     * @param array  $params      Optional query string parameters
     * @param array  $curlOptions Optional cURL options
     *
     * 
     * @todo the delete function has not been tested with live data
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::_request()
     */
    public function delete($path, $params = array(), $curlOptions = array())
    {
        $url = $this->_buildUrl($path, $params);
        $options = array(CURLOPT_CUSTOMREQUEST => 'DELETE');
        $options += $curlOptions;

        return $this->_request($url, $options);
    }

    /**
     * Get publically available collections
     * 
     * This method returns all of the public collections currently in the Pop Up Archive system.
     * Pagination is on the API roadmap and can be added to this function once that goes live on the
     * API. See http://developer.popuparchive.com for more details on the response from this API endpoint. 
     *
     * @param array $params      OPTIONAL query string parameters
     * @param array $curlOptions OPTIONAL cURL options
     * 
     * @todo ensure that pagination works on this endpoint once it is implemented
     *
     * @return JSON
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function getPublicCollections($params = array(), $curlOptions = array())
    {
        $url = $this->buildUrl(
            'collections/public',
            $params
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Get the public and private collections of the authenticated user
     *
     * If the OAuth2 dance has successfully been completed, this method will return
     * the authenticated user's collections, both public and private. 
     *
     * @param array $params      Optional query string parameters
     * @param array $curlOptions Optional cURL options
     *
     * @return JSON
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function getUserCollections($params = array(), $curlOptions = array())
    {
        $url = $this->buildURL(
            'collections',
            $params
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Get a single Pop Up Archive collection by its unique ID
     *
     * This method returns the associated metadata for a collection including title
     * and whether it is publically available or not
     *
     *
     * @param string $collectionId Request path
     * @param array  $params       Optional query string parameters
     * @param array  $curlOptions  Optional cURL options
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
     * Get a group of Pop Up Archive collections by their unique IDs
     * 
     * This method is still under development. The goal is to be able to pass
     * in an arbitrary set of collection IDs and display the associated metadata
     *
     * @param array $collectionIds array of collection ids to get
     * @param array $params        Optional query string parameters
     * @param array $curlOptions   Optional cURL options
     *
     * @todo this function is still in progress and will NOT work
     * 
     * @return JSON
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function getCollectionsByIds($collectionIds, $params = array(), $curlOptions = array())
    {
        $collections = explode(',', $collectionIds);

        for ($i=0;$i<count($collections);$i++) {
            /*
             *  @todo pull out the collection ids and make individual calls
             */
        }
        $url = $this->buildURL(
            'collections/'.$collectionId,
            $params
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Get a Pop Up Archive audio item and its metadata
     * using its unique ID and the associated Collection ID
     * 
     * This method provides a complete set of metadata for a given audio item.
     * This method is called after the list of items associated with a collection have
     * been retreived using getItemsByCollectionId()
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
     * Retreive all the audio items associated with a Pop Up Archive collection
     * This is currently the preferred method of retreiving a collections audio items
     *
     * @param string $collectionId Request path
     * @param array  $params       Optional query string parameters
     * @param array  $curlOptions  Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function getItemsByCollectionId($collectionId, $params = array(), $curlOptions = array())
    {
        $url = $this->buildURL(
            'search?query=collection_id:'.$collectionId,
            $params
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Search Pop Up Archive, with search set narrowed down by a filter
     *
     * @param string $filterKey   filter type - only filter currently available is 'collection_id'
     * @param string $filterValue filter value
     * @param array  $params      Optional query string parameters; format -- array('query'=>'chicago OR american','page'=>"1");
     * @param array  $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access public
     * @see Popuparchive::request()
     */
    public function searchByFilter($filterKey, $filterValue, $params = array(), $curlOptions = array())
    {
        $filterArray = array('filters['.$filterKey.']' => $filterValue);
        /** @params array|null should contain all query parameters needed for request */
        $query = array_merge($filterArray, $params);
        $url = $this->buildURL(
            'search',
            $query
        );

        return $this->request($url, $curlOptions);
    }

    /**
     * Construct the default HTTP request headers for requests
     *
     * @param boolean $includeAccessToken Include access token
     *
     * @return array $headers
     *
     * @access protected
     */
    protected function buildDefaultHeaders($includeAccessToken = true)
    {
        $headers = array();

        if ($this->_responseFormat) {
            array_push($headers, 'Accept: ' . $this->_responseFormat);
        }

        if ($includeAccessToken && $this->_accessToken) {
            array_push($headers, 'Authorization: Bearer ' . $this->_accessToken);
        }

        return $headers;
    }

    /**
     * Construct the URLs necessary to make the correct requests
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
        if (!$this->_accessToken) {
            $params['consumer_key'] = $this->_clientId;
        }
        if (preg_match('/^https?\:\/\//', $path)) {
            $url = $path;
        } else {
            $url = 'https://';
            $url .= $this->_domain;
            $url .='/';
            $url .= (!preg_match('/authorize/', $path)) ? 'api/' : '';
//            $url .= '/';
            $url .= $path;
        }

        $url .= (count($params)) ? '?' . http_build_query($params) : '';

        return $url;
    }

    /**
     * Retrieve the access token for OAuth2 authentication
     *
     * @param array $postData    Post data
     * @param array $curlOptions Optional cURL options
     *
     * @return mixed
     *
     * @access protected
     */
    protected function getAccessToken($postData, $curlOptions = array())
    {
        $options = array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData);
        $options += $curlOptions;
        $response = json_decode(
            $this->request($this->getAccessTokenUrl(), $options),
            true
        );

        if (array_key_exists('access_token', $response)) {
            $this->_accessToken = $response['access_token'];

            return $response;
        } else {
            return false;
        }
    }

    /**
     * Get the static user agent that is to be passed with the request
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
     * Parse the HTTP headers
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
     * Returns the returned HTTP response code
     *
     * @param integer $code HTTP code
     * 
     * @todo currenly only 404 and 200 are returned by the API
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

        if (array_key_exists(self::CURLOPT_OAUTH_TOKEN, $options)) {
            $includeAccessToken = $options[self::CURLOPT_OAUTH_TOKEN];
            unset($options[self::CURLOPT_OAUTH_TOKEN]);
        } else {
            $includeAccessToken = true;
        }

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
