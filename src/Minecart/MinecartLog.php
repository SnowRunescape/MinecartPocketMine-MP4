<?php

namespace Minecart;

class MinecartLog
{
    public static function executeCommand(string $command)
    {
        $file = Minecart::getInstance()->getDataFolder() . "error.log";
        $date = date("d/m/Y");
        $message = "[{$date}] [ERROR] Ocorreu um erro ao executar o comando ( {$command} )\n";

        file_put_contents($file, $message, FILE_APPEND);
    }
}
