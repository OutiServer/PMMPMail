<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use Ken_Cir\LibFormAPI\FormContents\CustomForm\ContentInput;
use Ken_Cir\LibFormAPI\FormContents\CustomForm\ContentToggle;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use Ken_Cir\LibFormAPI\Forms\CustomForm;
use Ken_Cir\LibFormAPI\Forms\SimpleForm;
use Ken_Cir\LibFormAPI\Utils\FormUtil;
use outiserver\economycore\Database\Player\PlayerData;
use outiserver\economycore\Database\Player\PlayerDataManager;
use outiserver\economycore\Forms\Base\BaseForm;
use outiserver\mail\Database\Mail\MailData;
use outiserver\mail\Database\Mail\MailDataManager;
use outiserver\mail\Language\LanguageManager;
use outiserver\mail\Mail;
use pocketmine\player\Player;

class MailManagerForm implements BaseForm
{
    public const FORM_KEY = "manager_mail";

    // プレイヤーを選択する部分
    public function execute(Player $player): void
    {
        $contents = [];
        $playerDatas = [];

        foreach (PlayerDataManager::getInstance()->getAll(true) as $playerData) {
            $playerMails = MailDataManager::getInstance()->getPlayerXuid($playerData->getXuid(), true);
            if (count($playerMails) > 0) {
                $playerDatas[] = $playerData;
                $contents[] = new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.button1", [$playerData->getName(), count($playerMails), MailDataManager::getInstance()->unReadCount($playerData->getXuid())]));
            }
        }

        $form = new SimpleForm(Mail::getInstance(),
            $player,
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.title"),
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.content"),
            $contents,
            function (Player $player, int $data) use ($playerDatas) {
                $this->viewPlayer($player, $playerDatas[$data]);
            },
            function (Player $player) {
                Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), self::FORM_KEY);
                Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())?->reSend();
            });

        Mail::getInstance()->getStackFormManager()->addStackForm($player->getXuid(), self::FORM_KEY, $form);
    }

    /**
     * 選択したプレイヤーが受信したメールを確認 or 選択する
     *
     * @param Player $player
     * @param PlayerData $selectPlayerData
     * @return void
     */
    public function viewPlayer(Player $player, PlayerData $selectPlayerData): void
    {
        $contents = [];

        foreach (MailDataManager::getInstance()->getPlayerXuid($selectPlayerData->getXuid(), true) as $mailData) {
            if ($mailData->getRead() === 1) {
                $contents[] = new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.button1", [date("Y年m月d日 H時i分s秒", $mailData->getSendTime()), $mailData->getTitle()]));
            } else {
                $contents[] = new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.button2", [date("Y年m月d日 H時i分s秒", $mailData->getSendTime()), $mailData->getTitle()]));
            }
        }

        (new SimpleForm(Mail::getInstance(),
            $player,
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.view_player.title"),
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.view_player.content"),
            $contents,
            function (Player $player, int $data) use ($selectPlayerData) {
                $this->editMail($player, $selectPlayerData, MailDataManager::getInstance()->getPlayerXuid($selectPlayerData->getXuid(), true)[$data]);
            },
            function (Player $player) {
                $this->execute($player);
            }));
    }

    private function editMail(Player $player, PlayerData $selectPlayerData, MailData $mailData): void
    {
        (new CustomForm(Mail::getInstance(),
            $player,
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.edit_mail.title", [$mailData->getId()]),
            [
                new ContentInput(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.button1"), "mailTitle", $mailData->getTitle()),
                new ContentInput(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.create_mail.button2"), "mailContent", $mailData->getContent()),
                new ContentToggle(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.edit_mail.button3"), (bool)$mailData->getRead()),
            ],
            function (Player $player, array $data) use ($selectPlayerData, $mailData) {
                $mailData->setTitle($data[0]);
                $mailData->setContent($data[1]);
                $mailData->setRead((int)$data[2]);

                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail_manager.edit_mail.success"));
                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.back"));
                FormUtil::backForm(Mail::getInstance(), [$this, "viewPlayer"], [$player, $selectPlayerData], 3);
            },
            function (Player $player) use ($selectPlayerData) {
                $this->viewPlayer($player, $selectPlayerData);
            }));
    }
}