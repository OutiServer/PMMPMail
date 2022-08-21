<?php

declare(strict_types=1);

namespace outiserver\mail\Handlers;

use outiserver\economycore\Language\LanguageManager;
use outiserver\mail\Database\Mail\MailDataManager;
use outiserver\mail\Mail;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\utils\TextFormat;

class EventHandler implements Listener
{
    private Mail $plugin;

    public function __construct(Mail $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();

        if ($this->plugin->getConfig()->get("player_join_notify", true)) {
            if (($unReadCount = MailDataManager::getInstance()->unReadCount($player->getXuid())) > 0) {
                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("event.player_join.unread", [(string)$unReadCount]));
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();

        $this->plugin->getStackFormManager()->deleteStack($player->getXuid());
    }
}