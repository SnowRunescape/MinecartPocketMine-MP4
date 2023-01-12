<?php

namespace Minecart;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Minecart\commands\Redeem;
use Minecart\commands\MyKeys;
use Minecart\utils\Utils;
use PlayerListener;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Language;

class Minecart extends PluginBase
{
    const VERSION = "2.3.0";

    const TIME_PREVENT_LOGIN_DELIVERY = 120;

    public array $messages = [];
    public array $config = [];
    public array $playerTimeOnline = [];
    public array $cooldown = [];

    public static Minecart $instance;

    public function onEnable(): void
    {
        $this->registerInstance();
        $this->registerCommands();
        $this->registerSchedulers();
        $this->registerConfig();
        $this->registerMessages();

        $this->getServer()->getLogger()->info("§7Plugin §aMinecart§7 ativado com sucesso!");
    }

    public function registerCommands(): void
    {
        $this->getServer()->getCommandMap()->register("mykeys", new MyKeys());
        $this->getServer()->getCommandMap()->register("redeem", new Redeem());
    }

    public function registerEvents()
    {
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
    }

    public function registerSchedulers()
    {
        $this->getScheduler()->scheduleRepeatingTask(new Scheduler($this), MinecartAPI::DELAY);
    }

    public function registerInstance(): void
    {
        self::$instance = $this;
    }

    public static function getInstance(): Minecart
    {
        return self::$instance;
    }

    public function registerMessages(): void
    {
        $this->saveResource("messages.yml");
        $messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        $this->messages = $messages->getAll();
    }

    public function registerConfig(): void
    {
        $this->saveResource("config.yml");
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->config = $config->getAll();
    }

    public function getMessage(string $key): string
    {
        return Utils::getArrayKeyByString($this->messages, $key);
    }

    public function getCfg(string $key): string
    {
        return Utils::getArrayKeyByString($this->config, $key);
    }

    public function dispatchCommand(string $command): bool
    {
        $consoleCommandSender = new ConsoleCommandSender($this->getServer(), new Language("eng"));

        return $this->getServer()->dispatchCommand($consoleCommandSender, $command);
    }
}
