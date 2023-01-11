<?php

namespace Minecart\utils;

use pocketmine\player\Player;
use Minecart\Minecart;
use Minecart\MinecartAPI;

class Errors
{
    public function getError(Player $player, int $code, bool $return = false): string
    {
        switch ($code) {
            case MinecartAPI::INVALID_KEY:
                $message = Minecart::getInstance()->getMessage("error.invalid-key");
                break;
            case MinecartAPI::INVALID_SHOP_SERVER:
                $message = Minecart::getInstance()->getMessage("error.invalid-shopserver");
                break;
            case MinecartAPI::DONT_HAVE_CASH:
                $message = Minecart::getInstance()->getMessage("error.nothing-products-cash");
                break;
            case MinecartAPI::COMMANDS_NOT_REGISTRED:
                $message = Minecart::getInstance()->getMessage("error.commands-product-not-registred");
                break;
            case 401:
                $message = Minecart::getInstance()->getMessage("error.invalid-shopkey");
                break;
            default:
                $message = Minecart::getInstance()->getMessage("error.internal-error");
                break;
        }

        if (!$return) {
            $player->sendMessage($message);
        }

        return $message;
    }
}
