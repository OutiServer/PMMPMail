<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use DateTime;
use Ken_Cir\LibFormAPI\Utils\FormUtil;
use outiserver\economycore\Database\Player\PlayerData;
use outiserver\economycore\Database\Player\PlayerDataManager;
use outiserver\economycore\Forms\Base\BaseForm;
use Ken_Cir\LibFormAPI\FormContents\CustomForm\ContentInput;
use Ken_Cir\LibFormAPI\FormContents\CustomForm\ContentToggle;
use Ken_Cir\LibFormAPI\Forms\CustomForm;
use outiserver\mail\Database\Mail\MailDataManager;
use outiserver\mail\Language\LanguageManager;
use outiserver\mail\Mail;
use pocketmine\player\Player;
use pocketmine\Server;

class CreateMailForm implements BaseForm
{
    public const FORM_KEY = "create_mail";

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
                    $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.back"));

                    Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), self::FORM_KEY);
                    FormUtil::backForm(Mail::getInstance(), [$this, "execute"], [$player], 3);
                } // 通常送信
                else {
                    $sendToPlayerData = PlayerDataManager::getInstance()->getName($data[2]);
                    if (!$sendToPlayerData) {
                        (new PlayerSelectorForm())->execute($player,
                            $data[2],
                            function (Player $player, PlayerData $playerData) use ($data): void {
                                $time = new DateTime('now');
                                MailDataManager::getInstance()->create($data[0],
                                    $data[1],
                                    $playerData->getXuid(),
                                    $data[3] ? "運営" : $player->getXuid(),
                                    $time->getTimestamp());
                                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.success1", [$playerData->getName()]));
                                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.back"));

                                Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), self::FORM_KEY);
                                FormUtil::backForm(Mail::getInstance(), [$this, "execute"], [$player], 3);
                            });
                        return;
                    }

                    MailDataManager::getInstance()->create($data[0],
                        $data[1],
                        $sendToPlayerData->getXuid(),
                        $data[3] ? "運営" : $player->getXuid(),
                        $time->getTimestamp());
                    $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.success1", [$sendToPlayerData->getName()]));
                    $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.back"));

                    Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), self::FORM_KEY);
                    FormUtil::backForm(Mail::getInstance(), [$this, "execute"], [$player], 3);
                }
            },
            function (Player $player): void {
                Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), self::FORM_KEY);
                Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())->reSend();
            });

        Mail::getInstance()->getStackFormManager()->addStackForm($player->getXuid(), self::FORM_KEY, $form);
    }
}