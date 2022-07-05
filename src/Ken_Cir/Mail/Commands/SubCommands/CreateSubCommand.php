<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Commands\SubCommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use Ken_Cir\EconomyCore\Database\Player\PlayerDataManager;
use Ken_Cir\Mail\Database\Mail\MailDataManager;
use Ken_Cir\Mail\Forms\CreateMailForm;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class CreateSubCommand extends BaseSubCommand
{
    protected function prepare(): void
    {
        $this->setPermission("mail.command.create");
        $this->registerArgument(0, new RawStringArgument("sendPlayerName", true));
        $this->registerArgument(1, new RawStringArgument("title", true));
        $this->registerArgument(2, new RawStringArgument("content", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        // 引数が全て指定されている場合
        if (isset($args["sendPlayerName"]) and isset($args["title"]) and isset($args["content"])) {
            $sendPlayerData = PlayerDataManager::getInstance()->getName($args["sendPlayerName"]);
            if ($sendPlayerData !== null) {
                MailDataManager::getInstance()->create($args["title"], $args["content"], $sendPlayerData->getXuid(), $sender instanceof Player ? $sender->getXuid() : "コンソール", time());
                $sender->sendMessage(TextFormat::GREEN . "メールを{$sendPlayerData->getName()}に送信しました");
            }
            else {
                $sender->sendMessage(TextFormat::RED . "メールの送信に失敗しました、プレイヤー名 {$args["sendPlayerName"]} のデータが見つかりません");
            }
        }
        // 引数が全て指定されてなくて、コマンド実行者がプレイヤーなら
        elseif ($sender instanceof Player) {
            (new CreateMailForm())->execute($sender);
        }
        else {
            $this->sendUsage();
        }
    }
}