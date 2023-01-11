<?php

namespace Minecart\helpers;

class PlayerHelper
{
    public static function playerOnline(string $username): bool
    {
        return true;
    }

    public static function playerTimeOnline($username): int
    {
        return 0;
    }
}
