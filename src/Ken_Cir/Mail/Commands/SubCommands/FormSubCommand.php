<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Commands\SubCommands;

use CortexPE\Commando\BaseSubCommand;
use Ken_Cir\Mail\Forms\MailForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FormSubCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission("mail.command.form");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "このコマンドはサーバー内で実行してください");
            return;
        }

        (new MailForm())->execute($sender);
    }
}