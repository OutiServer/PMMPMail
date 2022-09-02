<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use outiserver\economycore\Database\Player\PlayerDataManager;
use outiserver\economycore\Forms\Base\BaseForm;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use Ken_Cir\LibFormAPI\Forms\ModalForm;
use Ken_Cir\LibFormAPI\Forms\SimpleForm;
use Ken_Cir\LibFormAPI\Utils\FormUtil;
use outiserver\mail\Database\Mail\MailData;
use outiserver\mail\Database\Mail\MailDataManager;
use outiserver\mail\Language\LanguageManager;
use outiserver\mail\Mail;
use pocketmine\player\Player;

class ViewMailForm implements BaseForm
{
    public const FORM_KEY = "view_mail";

    public function execute(Player $player): void
    {
        $formContents = [];
        foreach (MailDataManager::getInstance()->getPlayerXuid($player->getXuid(), true) as $mailData) {
            if ($mailData->getRead() === 1) {
                $formContents[] = new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.button1", [date("Y年m月d日 H時i分s秒", $mailData->getSendTime()), $mailData->getTitle()]));
            }
            else {
                $formContents[] = new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.button2", [date("Y年m月d日 H時i分s秒", $mailData->getSendTime()), $mailData->getTitle()]));
            }
        }

        $form = new SimpleForm(Mail::getInstance(),
            $player,
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.title"),
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.content"),
            $formContents,
        function (Player $player, int $data): void {
            $this->view($player, MailDataManager::getInstance()->getPlayerXuid($player->getXuid(), true)[$data]);
        },
        function (Player $player): void {
            Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), self::FORM_KEY);
            Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())?->reSend();
        });

        Mail::getInstance()->getStackFormManager()->addStackForm($player->getXuid(), self::FORM_KEY, $form);
    }

    public function view(Player $player, MailData $mailData): void
    {
        new ModalForm(Mail::getInstance(),
            $player,
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.view.title", [$mailData->getId()]),
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.view.content", [$mailData->getTitle(), (($mailData->getAuthorXuid() === "システム" or $mailData->getAuthorXuid() === "運営") ? $mailData->getAuthorXuid() : PlayerDataManager::getInstance()->getXuid($mailData->getAuthorXuid())->getName()), date("Y年m月d日 H時i分s秒", $mailData->getSendTime()), $mailData->getContent()]),
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.view.button1"),
            LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.view.button2"),
            function (Player $player, bool $data) use ($mailData): void {
            if ($data) {
                MailDataManager::getInstance()->delete($mailData->getId());
                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.view_mail.view.delete_success"));
                $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.back"));

                Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), self::FORM_KEY);
                FormUtil::backForm(Mail::getInstance(), [$this, "execute"], [$player], 3);
            }
            else {
                $mailData->setRead(1);
                Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())->reSend();
            }
        });
    }
}