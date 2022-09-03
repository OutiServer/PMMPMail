<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use outiserver\economycore\Forms\Base\BaseForm;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use Ken_Cir\LibFormAPI\Forms\SimpleForm;
use outiserver\mail\Language\LanguageManager;
use outiserver\mail\Mail;
use pocketmine\player\Player;
use pocketmine\Server;

class MailForm implements BaseForm
{
    public const FORM_KEY = "mail";

    public function execute(Player $player): void
    {
        $contents = [
            new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail.button1")),
            new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail.button2"))
        ];

        if (Server::getInstance()->isOp($player->getName())) {
            $contents[] = new SimpleFormButton(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.mail.button3"));
        }
        $form = new SimpleForm(Mail::getInstance(),
            $player,
        "[Mail] メール",
        "",
        $contents,
        function (Player $player, int $data): void {
            switch ($data) {
                case 0:
                    (new CreateMailForm())->execute($player);
                    break;
                case 1:
                    (new ViewMailForm())->execute($player);
                    break;
                case 2:
                    (new MailManagerForm())->execute($player);
                    break;
                default:
                    break;
            }
        },
        function (Player $player): void {
            Mail::getInstance()->getStackFormManager()->deleteStack($player->getXuid());
        });

        Mail::getInstance()->getStackFormManager()->addStackForm($player->getXuid(), self::FORM_KEY, $form);
    }
}