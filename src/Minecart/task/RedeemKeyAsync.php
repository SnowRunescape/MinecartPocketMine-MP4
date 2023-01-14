<?php

namespace Minecart\task;

use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use Minecart\utils\Form;
use Minecart\Minecart;
use Minecart\MinecartAPI;
use Minecart\MinecartAuthorizationAPI;
use Minecart\utils\Errors;
use Minecart\utils\Messages;

class RedeemKeyAsync extends AsyncTask
{
    private MinecartAPI $minecartAPI;
    private string $username;
    private string $key;

    public function __construct(MinecartAuthorizationAPI $minecartAuthorizationAPI, string $username, string $key)
    {
        $this->minecartAPI = new MinecartAPI($minecartAuthorizationAPI);
        $this->username = $username;
        $this->key = $key;
    }

    public function onRun(): void
    {
        $result = $this->minecartAPI->redeemKey($this->username, $this->key);

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

                if ($this->executeCommands($response["commands"])) {
                    $messages = new Messages();
                    $messages->sendGlobalInfo($player, "vip", $response["group"]);

                    $message = $this->parseText(Minecart::getInstance()->getMessage("success.active-key"), $player, $response);
                    $player->sendMessage($message);
                } else {
                    $error = $this->parseText(Minecart::getInstance()->getMessage("error.redeem-vip"), $player, $response);
                    $player->sendMessage($error);
                }
            } else {
                $form = new Form();
                $form->setTitle("Resgatar VIP");
                $form->setPlaceholder("Insira sua key");
                $form->setRedeemType(Form::REDEEM_KEY);
                $form->setKey($this->key);

                $errors = new Errors();
                $error = $errors->getError($player, $response["response"]["code"] ?? $statusCode, true);
                $form->showRedeem($player, $error);
            }
        } else {
            $player->sendMessage(Minecart::getInstance()->getMessage("error.internal-error"));
        }
    }

    private function executeCommands(array $commands): bool
    {
        $result = true;

        foreach ($commands as $command) {
            if (!Minecart::getInstance()->dispatchCommand($command)) {
                $result = false;
            }
        }

        return $result;
    }

    private function parseText(string $text, Player $player, array $response): string
    {
        return str_replace(["{player.name}", "{key.group}", "{key.duration}"], [$player->getName(), $response["group"], $response["duration"]], $text);
    }
}
