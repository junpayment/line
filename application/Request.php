<?php

/**
 * Class Request
 */
class Request 
{
    /** @var string  */
    private $_json = ""; 
    
    /** @var string  */
    private $_fromMid = "";
    
    /** @var string  */
    private $_text = "";

    /**
     * Request constructor.
     * @param $json
     */
    public function __construct($json)
    {
        $this->_json = $json;
        
        $res = json_decode($this->_json, true)['result'][0]['content'];
        $this->_fromMid = $res['from'];
        $this->_text    = $res['text'];
    }

    /**
     * @return string
     */
    public function getFromMid()
    {
        syslog(LOG_DEBUG, __METHOD__.":".__LINE__.":".$this->_fromMid);
        return $this->_fromMid;
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return $this->_json;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }
}
