<?php

namespace LineN;

/**
 * Class Logic
 */
class Logic
{
    /**
     * Logic constructor.
     */
    public function __construct()
    {
        echo __METHOD__.":".__LINE__."\n";
    }

    /**
     * 実行
     */
    public function run()
    {
        try {
            $request = new Request(file_get_contents('php://input')); 
            
            $signature = $_SERVER['HTTP_X_LINE_CHANNELSIGNATURE'];
            
            Log::logger(LOG_DEBUG, __METHOD__, __LINE__, json_encode(['_sever' => $_SERVER, '_post' => $_POST, '_get' => $_GET, '_json' => json_decode($request->getJson(), true)], true));
            
            // authorization check
            $auth = new Auth($request, $signature);
            if (! $auth->isVerified()) {
                throw new \Exception('failed authorization!');
            }
            
            // send
            $text = ['でゅっふっふ', 'それがプロマネです(ｷﾘ', 'はいすいろ..はいすいろ...', '俺のゲームの邪魔をする奴は殺す！'];
            
            $line = new \LINE\LINEBot($auth->getLineConfigAsArray(), new \LINE\LINEBot\HTTPClient\GuzzleHTTPClient($auth->getLineConfigAsArray()));
            $line->sendText([$request->getFromMid()], $text[array_rand($text)]);
            
            http_response_code(200);
            echo json_encode(['response' => 'success']);

        } catch (\Exception $e) {
            http_response_code(470);
            Log::logger(LOG_ERR, __METHOD__, __LINE__, $e->getMessage() . ' : ' . $e->getTraceAsString());
        }
    }
}
