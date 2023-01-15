<?php

namespace Minecart;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Minecart\commands\Redeem;
use Minecart\commands\MyKeys;
use Minecart\listeners\PlayerListener;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Language;

class Minecart extends PluginBase
{
    const VERSION = "2.3.0";

    const TIME_PREVENT_LOGIN_DELIVERY = 120;

    private static Minecart $instance;

    private MinecartAuthorizationAPI $minecartAuthorizationAPI;
    private Config $messages;
    private Config $config;

    public array $playerTimeOnline = [];
    public array $cooldown = [];

    public function onEnable(): void
    {
        $this->registerInstance();
        $this->registerConfig();
        $this->registerMessages();
        $this->registerMinecartAuthorizationAPI();
        $this->registerEvents();
        $this->registerCommands();
        $this->registerSchedulers();

        $this->getServer()->getLogger()->info("§7Plugin §aMinecart§7 ativado com sucesso!");
    }

    private function registerMinecartAuthorizationAPI(): void
    {
        $this->minecartAuthorizationAPI = new MinecartAuthorizationAPI(
            $this->getCfg("Minecart.ShopKey"),
            $this->getCfg("Minecart.ShopServer")
        );
    }

    private function registerCommands(): void
    {
        $this->getServer()->getCommandMap()->register("mykeys", new MyKeys());
        $this->getServer()->getCommandMap()->register("redeem", new Redeem());
    }

    private function registerEvents()
    {
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);
    }

    private function registerSchedulers()
    {
        $this->getScheduler()->scheduleRepeatingTask(new Scheduler($this), MinecartAPI::DELAY);
    }

    private function registerInstance(): void
    {
        self::$instance = $this;
    }

    public static function getInstance(): Minecart
    {
        return self::$instance;
    }

    public function getMinecartAuthorizationAPI(): MinecartAuthorizationAPI
    {
        return $this->minecartAuthorizationAPI;
    }

    private function registerMessages(): void
    {
        $this->saveResource("messages.yml");
        $this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
    }

    private function registerConfig(): void
    {
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    public function getMessage(string $key): string
    {
        $message = $this->messages->getNested($key);

        if (is_null($message)) {
            $message = "§7Mensagem §c{$key} §7não encontrada";
        }

        return $message;
    }

    public function getCfg(string $key, $default = null)
    {
        return $this->config->getNested($key, $default);
    }

    public function dispatchCommand(string $command): bool
    {
        $consoleCommandSender = new ConsoleCommandSender($this->getServer(), new Language("eng"));

        return $this->getServer()->dispatchCommand($consoleCommandSender, $command);
    }
}
