<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use DateTime;
use outiserver\economycore\Database\Player\PlayerData;
use outiserver\economycore\Database\Player\PlayerDataManager;
use outiserver\economycore\Forms\Base\BaseForm;
use outiserver\economycore\Utils\FormUtil;
use Ken_Cir\LibFormAPI\FormContents\CustomForm\ContentInput;
use Ken_Cir\LibFormAPI\FormContents\CustomForm\ContentToggle;
use Ken_Cir\LibFormAPI\Forms\CustomForm;
use outiserver\mail\Database\Mail\MailDataManager;
use outiserver\mail\Mail;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CreateMailForm implements BaseForm
{
    public function execute(Player $player): void
    {
        $formContent = [
            new ContentInput("メールタイトル", "mailTitle"),
            new ContentInput("メール内容", "mailContent"),
            new ContentInput("送信相手", "sendToPlayerName"),
        ];

        if (Server::getInstance()->isOp($player->getName())) {
            $formContent[] = new ContentToggle(TextFormat::RED . "[運営専用] " . TextFormat::WHITE . "運営名義でメールを作成する");
            $formContent[] = new ContentToggle(TextFormat::RED . "[運営専用] " . TextFormat::WHITE . "メールをプレイヤー全員に送信する");
        }

        $form = new CustomForm(Mail::getInstance(),
            $player,
        "[Mail] メールの新規作成",
        $formContent,
        function (Player $player, array $data): void {
            if (!$data[0] and !$data[1] and !$data[2]) {
                $player->sendMessage(TextFormat::RED . "メールタイトル・メール内容・送信相手は入力必須項目です");
                $player->sendMessage(TextFormat::GREEN . "3秒後前のフォームに戻ります");
                FormUtil::backForm([Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid()), "reSend"], [$player], 3);
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
                    (new PlayerSelectorForm())->execute($player,
                    $data[2],
                    function (Player $player, PlayerData $playerData) use ($sendToPlayerData, $data): void {
                        $time = new DateTime('now');
                        MailDataManager::getInstance()->create($data[0],
                            $data[1],
                            $playerData->getXuid(),
                            $data[3] ? "運営" : $player->getXuid(),
                            $time->getTimestamp());
                        $player->sendMessage(TextFormat::GREEN . "{$sendToPlayerData->getName()}にメールを送信しました");
                    });
                    return;
                }

                MailDataManager::getInstance()->create($data[0],
                    $data[1],
                    $sendToPlayerData->getXuid(),
                    $data[3] ? "運営" : $player->getXuid(),
                    $time->getTimestamp());
                $player->sendMessage(TextFormat::GREEN . "{$sendToPlayerData->getName()}にメールを送信しました");
            }
        },
        function (Player $player): void {
            Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), "create_form");
            Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())?->reSend();
        });

        Mail::getInstance()->getStackFormManager()->addStackForm($player->getXuid(), "create_mail", $form);
    }
}