<?php

namespace Minecart\task;

use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use Minecart\utils\Form;
use Minecart\Minecart;
use Minecart\MinecartAPI;
use Minecart\MinecartAuthorizationAPI;
use Minecart\MinecartLog;
use Minecart\utils\Errors;
use Minecart\utils\Messages;

class RedeemCashAsync extends AsyncTask
{
    private MinecartAPI $minecartAPI;
    private string $username;

    public function __construct(MinecartAuthorizationAPI $minecartAuthorizationAPI, string $username)
    {
        $this->minecartAPI = new MinecartAPI($minecartAuthorizationAPI);
        $this->username = $username;
    }

    public function onRun(): void
    {
        $result = $this->minecartAPI->redeemCash($this->username);

        $this->setResult($result);
    }

    public function onCompletion(): void
    {
        $player = Minecart::getInstance()->getServer()->getPlayerExact($this->username);
        $response = $this->getResult();

        if (!empty($response)) {
            $statusCode = $response["statusCode"];

            if ($statusCode == 200) {
                $response = $response["response"];

                if (Minecart::getInstance()->dispatchCommand($response["command"])) {
                    $messages = new Messages();
                    $messages->sendGlobalInfo($player, "cash", $response["cash"]);
                } else {
                    MinecartLog::executeCommand($response["command"]);
                    $error = $this->parseText(Minecart::getInstance()->getMessage("error.redeem-cash"), $player, $response);

                    $player->sendMessage($error);
                }
            } else {
                $form = new Form();
                $form->setTitle("Erro!");

                $errors = new Errors();
                $error = $errors->getError($player, $response["response"]["code"] ?? $statusCode, true);

                $form->setMessage($error);
                $form->showFormError($player);
            }
        } else {
            $player->sendMessage(Minecart::getInstance()->getMessage("error.internal-error"));
        }
    }

    private function parseText(string $text, Player $player, array $response): string
    {
        return str_replace(["{player.name}", "{cash.quantity}"], [$player->getName(), $response["cash"]], $text);
    }
}
