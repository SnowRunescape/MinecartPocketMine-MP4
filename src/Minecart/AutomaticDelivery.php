<?php

namespace Minecart;

use Minecart\helpers\PlayerHelper;

class AutomaticDelivery
{
    const NONE = 0;
    const ONLY_PLAYER_ONLINE = 1;
    const ANYTIME = 2;

    public function run(): void
    {
        $minecartKeys = MinecartAPI::deliveryPending();
        $minecartKeys = $this->filterByAutomaticDelivery($minecartKeys);

        if (empty($minecartKeys)) {
            return;
        }

        $productsIds = array_column($minecartKeys, "id");

        if (!MinecartAPI::deliveryConfirm($productsIds)) {
            return;
        }

        foreach ($minecartKeys as $minecartKey) {
            $this->executeCommands($minecartKey["commands"]);
        }
    }

    private function filterByAutomaticDelivery(array $minecartKeys)
    {
        $tempMinecartKeys = [];

        foreach ($minecartKeys as $minecartKey) {
            if (
                $minecartKey["delivery_automatic"] == AutomaticDelivery::ANYTIME || (
                    !Minecart::getInstance()->getCfg("config.preventLoginDelivery") &&
                    PlayerHelper::playerOnline($minecartKey["username"])
                ) || (
                    Minecart::getInstance()->getCfg("config.preventLoginDelivery") &&
                    PlayerHelper::playerOnline($minecartKey["username"]) &&
                    PlayerHelper::playerTimeOnline($minecartKey["username"]) > Minecart::TIME_PREVENT_LOGIN_DELIVERY
                )
            ) {
                $tempMinecartKeys[] = $minecartKey;
            }
        }

        return $tempMinecartKeys;
    }

    private function executeCommands(array $commands): void
    {
        foreach ($commands as $command) {
            if (!Minecart::getInstance()->dispatchCommand($command)) {
                MinecartLog::executeCommand($command);
            }
        }
    }
}
