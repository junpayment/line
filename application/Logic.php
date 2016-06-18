<?php

namespace LineN;
use LineN\Shiritori\ResponseShiritori;

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
            $shiritori = new \LineN\Shiritori\Shiritori($request->getFromMid());
            if (true === $shiritori->checkShiritori($request->getText())) {
                $res = $shiritori->post($request->getText());
                if (ResponseShiritori::RESULT_CONTINUE == $res->getResult()) {
                    $out = $res->getNext(); 
                } else {
                    $out = $res->getStatusText($res->getResult());
                }
            } else {
                $text = ['しりとりしよ？', 'ねえしりとりしようよ！', '"しりとり"って打ったらスタートだよ！'];
                $out = $text[array_rand($text)];
            }
            
            $line = new \LINE\LINEBot($auth->getLineConfigAsArray(), new \LINE\LINEBot\HTTPClient\GuzzleHTTPClient($auth->getLineConfigAsArray()));
            $line->sendText([$request->getFromMid()], $out);
            
            http_response_code(200);
            echo json_encode(['response' => 'success']);

        } catch (\Exception $e) {
            http_response_code(470);
            Log::logger(LOG_ERR, __METHOD__, __LINE__, $e->getMessage() . ' : ' . $e->getTraceAsString());
        }
    }
}
