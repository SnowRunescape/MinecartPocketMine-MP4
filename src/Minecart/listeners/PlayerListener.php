<?php

namespace Minecart\listeners;

use Minecart\Minecart;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class PlayerListener implements Listener
{
    public function PlayerJoinEvent(PlayerJoinEvent $event)
    {
        $username = strtolower($event->getPlayer()->getName());

        Minecart::getInstance()->playerTimeOnline[$username] = time();
    }

    public function onPlayerQuit(PlayerQuitEvent $event)
    {
        $username = strtolower($event->getPlayer()->getName());

        unset(Minecart::getInstance()->playerTimeOnline[$username]);
    }
}
