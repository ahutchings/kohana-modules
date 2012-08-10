<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * Abstract base class for the SenderScore reputation checker module.
 * @package Kohana
 * @category SenderScore
 * @author William Betts (http://www.github.com/epicheals [] www.0xeb.info)
 * @copyright (c) 2012 William Betts
 * @license https://github.com/epicheals/senderscore/blob/master/LICENSE.md
 * @date 8-10-2012
 */
abstract class Kohana_Senderscore
{
    /**
     * Holds the variables for the setter method
     * @var array
     * @access protected
     */
    protected $mp_Variables;

    /**
     * The HTML from a curl request
     * @var string
     * @see pRequest($url, $type = 'GET', $param = array())
     * @access protected
     */
    protected $mp_Html;

    /**
     * The ip to score map
     * @var array
     * @see pGetScore()
     * @access protected
     */
    protected $mp_Scores;

    /**
     * The curl resource
     * @var resource
     * @access private
     */
    private $m_CurlObj;

    /**
     * Generic constructor. Setups stuff for the object
     * @params $ip string The ip address to check
     * @access public
     */
    public function __construct($ip = null)
    {
        if (!is_null($ip))
        {
            $this->mp_Variables['ip']  = $ip;
        }
        else
        {
            $this->mp_Variables['ip']  = null;
        }
    }

    /**
     * Generic setter.
     * @params $key string The name of the variable being set
     * @params $value string The value for the variable being set
     * @access public
     * @return void
     */
    public function __set($key, $value)
    {
        $this->mp_Variables[$key] = $value;
    }

    /**
     * Check the senderscore for an ip address
     * @param $ip string The ip address to check
     * @throws DomainException
     * @access public
     * @return int The score for the ip address
     */
    public function Check($ip = null)
    {
        if (!is_null($ip))
        {
            $this->mp_Variables['ip'] = $ip;
        }

        if (empty($this->mp_Variables['ip']))
        {
            throw new DomainException('No ip address is set.');
        }

        if (!strstr($this->mp_Variables['ip'], '/'))
        {
            $this->mp_Variables['ip'] .= '/32';
        }
        
        $this->pRequest('https://senderscore.org/lookup.php?lookup=' . urlencode($this->mp_Variables['ip']) . '&ipLookup=Go');
        $this->pGetScore();

        return $this->mp_Scores;
    }

    /**
     * Logs into the SenderScore website
     * @param $username string The username to login with
     * @param $password string The password for the user
     * @chainable
     * @access public
     * @return SenderScore $this
     */
    public function Login($username, $password)
    {

        $this->pRequest('https://senderscore.org', 'POST', array(
            'email'     => $username,
            'password'  => $password,
            'Submit'    => 'Sign in',
            'action'    => 'localLogin',
        ));

        return $this;
    }

    /**
     * Issues a curl request
     * @param $url string The url to connec to
     * @param $type string The type of request (GET or POST). Defaults to GET
     * @param $query array An array of query parameters
     * @throws DomainException
     * @access protected
     * @return void
     */
    protected function pRequest($url, $type = 'GET', $params = array())
    {
        if (empty($this->mp_Variables['cookieFile']))
        {
            throw new DomainException('No cookie file is set.');
        }

        if (is_null($this->m_CurlObj))
        {
            $this->m_CurlObj    = curl_init();
            curl_setopt($this->m_CurlObj, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
            curl_setopt($this->m_CurlObj, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt($this->m_CurlObj, CURLOPT_RETURNTRANSFER, true );
            curl_setopt($this->m_CurlObj, CURLOPT_AUTOREFERER, true );
            curl_setopt($this->m_CurlObj, CURLOPT_MAXREDIRS, 10 );
            curl_setopt($this->m_CurlObj, CURLOPT_COOKIEJAR, $this->mp_Variables['cookieFile']);
            curl_setopt($this->m_CurlObj, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
        }


        if (!empty($params))
        {
            $query  = http_build_query($params); 
            
            if ($type == 'GET')
            {
                $url .= '?' . $query;
            }
        }

        switch ($type)
        {
            case 'GET':
                curl_setopt($this->m_CurlObj, CURLOPT_URL, $url);
                break;

            case 'POST':
                curl_setopt($this->m_CurlObj, CURLOPT_URL, $url);
                curl_setopt($this->m_CurlObj, CURLOPT_POST, true);
                curl_setopt($this->m_CurlObj, CURLOPT_POSTFIELDS, $query);
                break;
        }

        $this->mp_Html   = curl_exec($this->m_CurlObj);
    }

    /**
     * Parses the HTML document containing the ips and scores
     * @throws LogicException
     * @access protected
     * @return void
     */
    protected function pGetScore()
    {
        if (empty($this->mp_Html))
        {
            throw new LogicException('No request has been made or the html document variable somehow got over written');
        }

        $scores = array();
        $ips    = array();

        $domDoc = new DOMDocument();
        @$domDoc->loadHTML($this->mp_Html);

        $xpathObj       = new DOMXpath($domDoc);
        $ipElements     = $xpathObj->query("//*[@id=\"luCidrTbl\"]/tbody/tr/td[1]/a");
        $scoreElements  = $xpathObj->query("//*[@id=\"luCidrTbl\"]/tbody/tr/td[4]");
 
        foreach ($ipElements as $element)
        {
            $ips[]   = $element->nodeValue;
        } 

        foreach ($scoreElements as $element)
        {
            $scores[]   = $element->nodeValue;
        }

        foreach ($ips as $key => $ip)
        {
            if (!isset($scores[$key]))
            {
                throw new LogicException('Something went wrong and the we have more ip addresses than scores. This shouldn\'t happen!');
            }

            $this->mp_Scores[$ip]   = $scores[$key];
        }
    }
}
