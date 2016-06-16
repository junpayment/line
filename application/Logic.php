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
        Log::logger(LOG_DEBUG, __METHOD__, __LINE__, json_encode(['_sever' => $_SERVER, '_post' => $_POST, '_get' => $_GET, '_json' => json_decode(file_get_contents('php://input'), true)], true));
        echo json_encode(['response' => 'success']);
    }
}
