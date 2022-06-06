<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Commands;

use CortexPE\Commando\BaseCommand;
use Ken_Cir\Mail\Commands\SubCommands\CreateSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class MailCommand extends BaseCommand
{
    protected function prepare(): void
    {
        $this->setPermission("mail.command");
        $this->registerSubCommand(new CreateSubCommand("create", "メールを新規作成する", []));
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