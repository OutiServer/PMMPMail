<?php

declare(strict_types=1);

namespace outiserver\mail\Commands;

use outiserver\mail\Forms\MailForm;
use outiserver\mail\Mail;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class MailCommand extends Command implements PluginOwned
{
    private Plugin $plugin;

    public function __construct(Plugin $plugin, string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(Mail::getInstance()->getLanguageManager()->getLanguage($sender->getLanguage()->getLang())->translateString("command.error.please_used_server"));
            return;
        }

        (new MailForm())->execute($sender);
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }
}