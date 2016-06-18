<?php
namespace LineN;
/**
 * Class Auth
 */
class Auth
{
    /** @var string  */
    private $_channelId="";
    
    /** @var string  */
    private $_channelSecret="";
    
    /** @var string  */
    private $_mid="";
    
    /** @var bool  */
    private $_isVerified = false;
    
    /**
     * Auth constructor.
     * @param Request $request
     * @param string $signature
     */
    public function __construct($request, $signature)
    {
        $ini = parse_ini_file(APP_PATH . '/settings.ini', true)['line'];
        $this->_channelId     = $ini['channel_id'];
        $this->_channelSecret = $ini['channel_secret'];
        $this->_mid           = $ini['mid'];

        // 署名比較
        if ($signature === base64_encode(hash_hmac('sha256', $request->getJson(), $this->_channelSecret, true))) {
            $this->_isVerified = true;
        }
        
        // LineBot用のconfigフォーマット
        $this->_lineConfig = ['channelId'     => $this->_channelId,
                              'channelSecret' => $this->_channelSecret,
                              'channelMid'    => $this->_mid];
    }

    /**
     * @return bool
     */
    public function isVerified() 
    {
        return $this->_isVerified;     
    }

    /**
     * @return string
     */
    public function getChannelId()
    {
        return $this->_channelId;
    }

    /**
     * @return string
     */
    public function getChannelSecret()
    {
        return $this->_channelSecret;
    }

    /**
     * @return string
     */
    public function getMid()
    {
        return $this->_mid;
    }

    /**
     * @return array
     */
    public function getLineConfigAsArray()
    {
        return $this->_lineConfig;
    }
}
