<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use DateTime;
use Ken_Cir\LibFormAPI\Utils\FormUtil;
use outiserver\economycore\Database\Player\PlayerData;
use outiserver\economycore\Database\Player\PlayerDataManager;
use outiserver\economycore\Forms\Base\BaseForm;
use outiserver\economycore\Language\LanguageManager;
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
    public const FORM_KEY = "";

    public function execute(Player $player): void
    {
        $formContent = [
            new ContentInput(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.button1"), "mailTitle"),
            new ContentInput(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.button2"), "mailContent"),
            new ContentInput(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.button3"), "sendToPlayerName"),
        ];

        if (Server::getInstance()->isOp($player->getName())) {
            $formContent[] = new ContentToggle(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.button4"));
            $formContent[] = new ContentToggle(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.button5"));
        }

        $form = new CustomForm(Mail::getInstance(),
            $player,
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.title"),
        $formContent,
        function (Player $player, array $data): void {
            if (!$data[0] and !$data[1] and !$data[2]) {
                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.error1"));
                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.back"));
                FormUtil::backForm(Mail::getInstance(), [Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid()), "reSend"], [$player], 3);
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

                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.success2", [(string)$success]));
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
                        $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.success1", [$sendToPlayerData->getName()]));
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