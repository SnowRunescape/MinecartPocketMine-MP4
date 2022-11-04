<?php

namespace Minecart;

use Minecart\utils\API;

class AutomaticDelivery
{
    const NONE = 0;
    const ONLY_PLAYER_ONLINE = 1;
    const ANYTIME = 2;

    public function run(): void
    {
        $api = new API();
        $api->setAuthorization($this->authorization);
        $api->setShopServer($this->shopServer);
        $api->setParams(["username" => $this->username]);
        $api->setURL(API::DELIVERY_PENDING_URI);

        $this->setResult($api->send());
    }

    public function onCompletion(): void
    {
        // TODO
    }

    private function executeCommands(array $commands): void
    {

    }
}
