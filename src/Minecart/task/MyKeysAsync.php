<?php

namespace Minecart\task;

use pocketmine\scheduler\AsyncTask;
use Minecart\utils\Form;
use Minecart\utils\Errors;
use Minecart\Minecart;
use Minecart\MinecartAPI;
use Minecart\MinecartAuthorizationAPI;

class MyKeysAsync extends AsyncTask
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
        $result = $this->minecartAPI->myKeys($this->username);

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
