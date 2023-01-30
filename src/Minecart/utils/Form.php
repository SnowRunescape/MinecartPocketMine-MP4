<?php

namespace Minecart\utils;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use Minecart\task\RedeemCashAsync;
use Minecart\task\RedeemKeyAsync;
use Minecart\Minecart;

class Form
{
    private string $title;
    private $placeholder;
    private string $key = "";
    private string $redeemType;
    private array $products = [];
    private string $message;
    private Cooldown $cooldown;

    const REDEEM_KEY = 1;
    const REDEEM_CASH = 2;

    public function __construct()
    {
        $this->cooldown = new Cooldown();
    }

    public function setPlaceholder($placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setRedeemType(string $redeemType): void
    {
        $this->redeemType = $redeemType;
    }

    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function showFormError(Player $player): void
    {
        $form = new SimpleForm(function(Player $player, int $data = null) {
            if (empty($data)) {
                return;
            }
        });

        $form->setTitle($this->title);
        $form->setContent($this->message);
        $form->addButton("Fechar");
        $player->sendForm($form);
    }

    public function showChoose(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            if (is_null($data)) {
                return;
            }

            switch ($data) {
                case 0: // KEY
                    $title = Minecart::getInstance()->getMessage("form.title");
                    $placeholder = Minecart::getInstance()->getMessage("form.placeholder");

                    $this->setTitle($title);
                    $this->setPlaceholder($placeholder);
                    $this->setRedeemType(self::REDEEM_KEY);
                    $this->showRedeem($player);
                    break;
                case 1: // CASH
                    $form = new ModalForm(function(Player $player, bool $data = null) {
                        if (is_null($data)) {
                            return;
                        }

                        $minecartAuthorizationAPI = Minecart::getInstance()->getMinecartAuthorizationAPI();
                        $username = $player->getName();

                        if ($data) {
                            if ($this->cooldown->isInCooldown($player)) {
                                $error = Minecart::getInstance()->getMessage("error.cooldown");
                                $error = str_replace("{cooldown}", $this->cooldown->getCooldownTime($player), $error);

                                $this->setTitle("Erro!");
                                $this->setMessage($error);
                                $this->showFormError($player);
                                return;
                            }

                            $messages = new Messages();
                            $messages->sendWaitingResponseInfo($player);

                            $this->cooldown->setPlayerInCooldown($player);
                            Minecart::getInstance()->getServer()->getAsyncPool()->submitTask(new RedeemCashAsync($minecartAuthorizationAPI, $username));
                        }
                    });

                    $modal_title = Minecart::getInstance()->getMessage("modal.title");
                    $message = Minecart::getInstance()->getMessage("modal.confirm-cash-message");

                    $form->setTitle($modal_title);
                    $form->setContent($message);
                    $form->setButton1("§aSim");
                    $form->setButton2("§cNão");

                    $player->sendForm($form);
                    break;
            }
        });

        $form->setTitle("Ativar");
        $form->addButton("§eKEY");
        $form->addButton("§aCash");

        $player->sendForm($form);
    }

    public function showRedeem(Player $player, string $error = ""): void
    {
        $form = new CustomForm(function(Player $player, array $data = null) {
            if (empty($data)) {
                return;
            }

            $error = "";

            if (empty($data[0])) {
                $error = Minecart::getInstance()->getMessage("error.inform-key");
            }

            if ($this->cooldown->isInCooldown($player)) {
                $error = Minecart::getInstance()->getMessage("error.cooldown");
                $error = str_replace("{cooldown}", $this->cooldown->getCooldownTime($player), $error);
            }

            $key = $data[0];

            if (!empty($error)) {
                $this->setTitle($this->title);
                $this->setKey($key);
                $this->setPlaceholder($this->placeholder);
                $this->setRedeemType(self::REDEEM_KEY);
                $this->showRedeem($player, $error);
                return;
            }

            $minecartAuthorizationAPI = Minecart::getInstance()->getMinecartAuthorizationAPI();
            $username = $player->getName();

            switch ($this->redeemType) {
                case self::REDEEM_KEY:
                    $form = new ModalForm(function(Player $player, bool $data = null) use ($minecartAuthorizationAPI, $username, $key) {
                        if (empty($data)) {
                            return;
                        }

                        switch ($data) {
                            case true:
                                $messages = new Messages();
                                $messages->sendWaitingResponseInfo($player);

                                $this->cooldown->setPlayerInCooldown($player);
                                Minecart::getInstance()->getServer()->getAsyncPool()->submitTask(new RedeemKeyAsync($minecartAuthorizationAPI, $username, $key));
                                break;
                        }
                    });

                    $modal_title = Minecart::getInstance()->getMessage("modal.title");
                    $message = Minecart::getInstance()->getMessage("modal.confirm-key-message");

                    $form->setTitle($modal_title);
                    $form->setContent($message);
                    $form->setButton1("§aSim");
                    $form->setButton2("§cNão");

                    $player->sendForm($form);
                    break;
                case self::REDEEM_CASH:
                    break;
            }
        });

        $form->setTitle($this->title);
        $form->addInput("Key", $this->placeholder, $this->key);

        if (!empty($error)) {
            $form->addLabel($error);
        }

        $player->sendForm($form);
    }


    public function showMyKeys(Player $player): void
    {
        $form = new SimpleForm(function(Player $player, int $data = null) {
            if (is_null($data)) {
                return;
            }

            $key = $this->products[$data]["key"];

            $title = Minecart::getInstance()->getMessage("form.title");
            $placeholder = Minecart::getInstance()->getMessage("form.placeholder");

            $this->setTitle($title);
            $this->setPlaceholder($placeholder);
            $this->setKey($key);
            $this->setRedeemType(self::REDEEM_KEY);
            $this->showRedeem($player);
            $this->cooldown->removePlayerCooldown($player);
        });

        if (!empty($this->products)) {
            foreach ($this->products as $product) {
                $info = Minecart::getInstance()->getMessage("success.player-list-keys-key");

                $key = $product["key"];
                $productName = $product["product_name"];
                $info = str_replace(["{key.code}", "{key.product_name}"], [$key, $productName], $info);

                $form->addButton($info);
            }
        } else {
            $this->setTitle("Erro!");
            $this->setMessage(Minecart::getInstance()->getMessage("error.player-dont-have-key"));
            $this->showFormError($player);
            return;
        }

        $form->setTitle(Minecart::getInstance()->getMessage("success.player-list-keys-title"));

        $this->cooldown->setPlayerInCooldown($player);
        $player->sendForm($form);
    }
}
