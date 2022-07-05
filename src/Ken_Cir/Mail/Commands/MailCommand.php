<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Commands;

use CortexPE\Commando\BaseCommand;
use Ken_Cir\Mail\Commands\SubCommands\CreateSubCommand;
use Ken_Cir\Mail\Commands\SubCommands\FormSubCommand;
use Ken_Cir\Mail\Commands\SubCommands\ViewSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class MailCommand extends BaseCommand
{
    protected function prepare(): void
    {
        $this->setPermission("mail.command");
        $this->registerSubCommand(new CreateSubCommand("create", "メールを新規作成する", []));
        $this->registerSubCommand(new FormSubCommand("form", "メール用のFormを開く", []));
        $this->registerSubCommand(new ViewSubCommand("view", "メール閲覧Formを開く", []));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $sender->sendMessage(TextFormat::GREEN . "[Mail] 使用可能なコマンド一覧");
        foreach ($this->getSubCommands() as $subCommand) {
            if ($subCommand->testPermissionSilent($sender)) {
                $sender->sendMessage($subCommand->getUsageMessage());
            }
        }
    }
}