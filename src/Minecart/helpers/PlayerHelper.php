<?php

namespace Minecart\helpers;

use Minecart\Minecart;
use pocketmine\player\Player;

class PlayerHelper
{
    public static function playerOnline(string $username): bool
    {
        $username = strtolower($username);

        $player = Minecart::getInstance()->getServer()->getPlayerExact($username);

        return (
            !is_null($player) &&
            $player instanceof Player &&
            $player->isOnline()
        );
    }

    public static function playerTimeOnline(string $username): int
    {
        $time = 0;

        $username = strtolower($username);

        if (isset(Minecart::getInstance()->playerTimeOnline[$username])) {
            $time = time() - Minecart::getInstance()->playerTimeOnline[$username];
        }

        return $time;
    }
}
