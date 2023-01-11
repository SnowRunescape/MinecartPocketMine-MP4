<?php

namespace Minecart\task;

use pocketmine\scheduler\AsyncTask;
use Minecart\utils\Form;
use Minecart\utils\Errors;
use Minecart\Minecart;
use Minecart\MinecartAPI;

class MyKeysAsync extends AsyncTask
{
    private $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function onRun(): void
    {
        $result = MinecartAPI::myKeys($this->username);

        $this->setResult($result);
    }

    public function onCompletion(): void
    {
        $player = Minecart::getInstance()->getServer()->getPlayerExact($this->username);
        $response = $this->getResult();

        if (!empty($response)) {
            $statusCode = $response["statusCode"];

            if ($statusCode == 200) {
                $keys = $response["response"]["products"];

                $form = new Form();
                $form->setProducts($keys);
                $form->showMyKeys($player);
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
}
