<?php
namespace LineN\Shiritori;

/**
 * Class Shiritori
 * @package LineN\Shiritori
 */
class Shiritori
{
    const PREFIX_STORE = "SHIRITORI_PREFIX_";
    
    /** @var \Predis\Client  */
    private $_redis = null;

    /** @var string  */
    private $_mid = ""; 
    
    /** @var bool  */
    private $_isShiritori = false;

    /** @var StoreShiritori  */
    private $_store = null;
    
    /**
     * Shiritori constructor.
     * @param string $mid
     */
    public function __construct($mid)
    {
        $this->_mid = $mid;
        $this->_redis = new \Predis\Client();
        $temp = $this->_redis->get(self::PREFIX_STORE . $this->_mid); 
        if (! empty($temp)) {
            $this->_store = unserialize($temp);
            $this->_isShiritori = true;
        } else {
            $this->_store = new StoreShiritori();
        }
    }

    /**
     * しりとり状態のチェック
     * @param string $word
     * @return bool
     */
    public function checkShiritori($word)
    {
        if (('しりとり' == $word && false === $this->_isShiritori) || true === $this->_isShiritori) {
            return true; 
        }
        
        return false;
    }

    /**
     * しりとりのポスト
     * @param string $word
     * @return ResponseShiritori
     */
    public function post($word)
    {
        $temp = $this->_store->getAnswer($word);
        if (ResponseShiritori::RESULT_CONTINUE != $temp->getResult()) {
            $this->_reset();
        } else {
            $this->_set();
        }
        
        return $temp;
    }

    /**
     * リセット 
     */
    public function reset()
    {
        $this->_reset();
    }
    
// private  
    /**
     * リセット
     */
    private function _reset()
    {
        $this->_redis->del(self::PREFIX_STORE . $this->_mid);
    }

    /**
     * ストア
     */
    private function _set()
    {
        $this->_redis->set(self::PREFIX_STORE . $this->_mid, serialize($this->_store));
    }
}

/**
 * Class StoreShiritori
 * @package LineN\Shiritori
 */
class StoreShiritori
{
    const WORD_API_URL = 'http://wikipedia.simpleapi.net/api?keyword=%s&output=json';
    
    /** @var string  */
    private $_tailBefore = ""; 
    
    /** @var array api から取得した言葉 */
    private $_store = [];
    
    /** @var array 既に発言した言葉 */
    private $_already = [];

    /**
     * @param string $word
     * @return ResponseShiritori
     */
    public function getAnswer($word)
    {
        /// チェック
        // 既出チェック 
        // 先頭文字が前回の末尾と同じかチェック
        // 最後が"ん"になっていないかチェック
        // word が日本語に存在するかチェック
        $temp = preg_split("//u", $word, -1, PREG_SPLIT_NO_EMPTY);
        $head = $temp[0];
        $tail = end($temp);
        if (true === in_array($word, $this->_already)) {
            return (new ResponseShiritori(ResponseShiritori::RESULT_DUPLICATED));
        }
        if (! empty($this->_tailBefore) && $this->_tailBefore != $head) {
            return (new ResponseShiritori(ResponseShiritori::RESULT_NOT_MATCH));
        }
        if (true === in_array($tail, ['ん', 'ン'])) {
            return (new ResponseShiritori(ResponseShiritori::RESULT_TAIL_N));
        }
        if (empty(json_decode(file_get_contents(sprintf(self::WORD_API_URL, $word)), true))) {
            return (new ResponseShiritori(ResponseShiritori::RESULT_NOT_HIT));
        }
        $this->_already[] = $word;

        /// 次の答えを出す
        // api 叩く 既にあるなら叩かずストアデータを参照する
        // 候補0ならしりとりの勝ち負けが決したとして終了とする
        if (empty($this->_store[$tail])) {
            $this->_store[$tail] = array_map(function($val) use ($tail) {
                return $val['title'];
            }, json_decode(file_get_contents(sprintf(self::WORD_API_URL, $tail)), true));
        }
        
        $list = array_filter($this->_store[$tail], function($val) {
            $preg = preg_split("//u", $val, -1, PREG_SPLIT_NO_EMPTY);
            if (! in_array($val, $this->_already) && ! in_array(end($preg), ['ん', 'ン']) && preg_match('/^[ぁ-んー]+$/u', $val)) {
                return true;
            }
            return false;
        });
        if (empty($list)) {
            return (new ResponseShiritori(ResponseShiritori::RESULT_PLAYER_WIN));
        }
        $next = $list[array_rand($list)];
        $this->_already[] = $next;
        $preg = preg_split("//u", $next, -1, PREG_SPLIT_NO_EMPTY);
        $this->_tailBefore = end($preg);

        return (new ResponseShiritori(ResponseShiritori::RESULT_CONTINUE, $next));
    }
}

/**
 * Class ResponseShiritori
 * @package LineN\Shiritori
 */
class ResponseShiritori
{
    const RESULT_CONTINUE   = 1; 
    const RESULT_NOT_HIT    = 2;
    const RESULT_NOT_MATCH  = 3;
    const RESULT_TAIL_N     = 4;
    const RESULT_DUPLICATED = 5;
    const RESULT_PLAYER_WIN = 6;
    
    /** @var int  */
    private $_result = null;
    
    /** @var string  */
    private $_next = null;

    /** @var array  */
    private $_statusText = [
        self::RESULT_CONTINUE   => 'さあ次の言葉を言って',
        self::RESULT_NOT_HIT    => 'それ日本語じゃないよ！ぼくの勝ち！',
        self::RESULT_NOT_MATCH  => 'しりとりになってないよ！！ぼくの勝ち！',
        self::RESULT_TAIL_N     => 'しりとりになってないよ！ぼくの勝ち！',
        self::RESULT_DUPLICATED => 'それ前にも言ったよ！ぼくの勝ち！',
        self::RESULT_PLAYER_WIN => 'もう無い...負けたよ...'
    ]; 
    
    /**
     * ResponseShiritori constructor.
     * @param int $result
     * @param string $nextWord
     */
    public function __construct($result, $nextWord = null)
    {
        $this->_result = $result;
        $this->_next   = $nextWord;
    }

    /**
     * @return int
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @return string
     */
    public function getNext()
    {
        return $this->_next;
    }

    /**
     * @param $status
     * @return mixed
     */
    public function getStatusText($status)
    {
        return $this->_statusText[$status];
    }
}
