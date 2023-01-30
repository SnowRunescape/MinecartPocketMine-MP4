<?php

namespace Minecart;

use pocketmine\scheduler\Task;

class Scheduler extends Task
{
    private AutomaticDelivery $automaticDelivery;

    public function __construct()
    {
        $this->automaticDelivery = new AutomaticDelivery(Minecart::getInstance()->getMinecartAuthorizationAPI());
    }

    public function onRun(): void
    {
        $this->automaticDelivery->run();
    }
}
