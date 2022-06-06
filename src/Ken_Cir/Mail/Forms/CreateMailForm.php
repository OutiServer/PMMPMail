<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Form;

use DateTime;
use jojoe77777\FormAPI\CustomForm;
use Ken_Cir\EconomyCore\Database\Player\PlayerData;
use Ken_Cir\EconomyCore\Database\Player\PlayerDataManager;
use Ken_Cir\EconomyCore\Forms\Base\BaseForm;
use Ken_Cir\EconomyCore\Utils\FormUtil;
use Ken_Cir\Mail\Database\Mail\MailDataManager;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CreateMailForm implements BaseForm
{
    public function execute(Player $player): void
    {
        $form = new CustomForm(function (Player $player, $data) {
            if (!$data[0] and !$data[1] and !$data[2]) {
                $player->sendMessage(TextFormat::RED . "メールタイトル・メール内容・送信相手は入力必須項目です");
                FormUtil::backForm([$this, "execute"], [$player]);
                return;
            }

            $time = new DateTime('now');
            // 全員に送信
            if ($data[4]) {
                $success = 0;
                foreach (PlayerDataManager::getInstance()->getAll(false) as $playerData) {
                    MailDataManager::getInstance()->create($data[0],
                        $data[1],
                        $playerData->getXuid(),
                        $data[3] ? "運営" : $player->getXuid(),
                        $time->getTimestamp());
                    $success++;
                }

                $player->sendMessage(TextFormat::GREEN . "{$success}人にメールを一括送信しました");
            }
            // 通常送信
            else {
                $sendToPlayerData = PlayerDataManager::getInstance()->getName($data[2]);
                if (!$sendToPlayerData) {
                    $player->sendMessage(TextFormat::RED . "プレイヤー名: $data[2]のデータは存在しません");
                    return;
                }

                MailDataManager::getInstance()->create($data[0],
                $data[1],
                $sendToPlayerData->getXuid(),
                $data[3] ? "運営" : $player->getXuid(),
                    $time->getTimestamp());
            }
        });
        $form->setTitle("[Economy Mail] メールの新規作成");
        $form->addInput("メールタイトル", "mailTitle");
        $form->addInput("メール内容", "mailContent");
        $form->addInput("送信相手", "sendToPlayerName");
        if (Server::getInstance()->isOp($player->getName())) {
            $form->addToggle(TextFormat::RED . "[運営専用] " . TextFormat::WHITE . "運営名義でメールを作成する");
            $form->addToggle(TextFormat::RED . "[運営専用] " . TextFormat::WHITE . "メールをプレイヤー全員に送信する");
        }
        $player->sendForm($form);
    }
}