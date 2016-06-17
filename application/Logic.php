<?php

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
            $json = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_X_LINE_CHANNELSIGNATURE'];
            
            Log::logger(LOG_DEBUG, __METHOD__, __LINE__, json_encode(['_sever' => $_SERVER, '_post' => $_POST, '_get' => $_GET, '_json' => json_decode($json, true)], true));

            // authorization check
            $auth = new Auth($json, $signature);
            if (! $auth->isVerified()) {
                throw new Exception('failed authorization!');
            }

            // send 
            
            http_response_code(200);
            echo json_encode(['response' => 'success']);

        } catch (Exception $e) {
            http_response_code(470);
            Log::logger(LOG_ERR, __METHOD__, __LINE__, $e->getMessage() . ' : ' . $e->getTraceAsString());
        }
    }
}
