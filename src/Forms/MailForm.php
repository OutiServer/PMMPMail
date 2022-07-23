<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use outiserver\economycore\Forms\Base\BaseForm;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use Ken_Cir\LibFormAPI\Forms\SimpleForm;
use outiserver\mail\Mail;
use pocketmine\player\Player;

class MailForm implements BaseForm
{
    public function execute(Player $player): void
    {
        $form = new SimpleForm(Mail::getInstance(),
            $player,
        "[Mail] メール",
        "",
        [
            new SimpleFormButton("メールを作成"),
            new SimpleFormButton("メールを閲覧")
        ],
        function (Player $player, int $data): void {
            switch ($data) {
                case 0:
                    (new CreateMailForm())->execute($player);
                    break;
                case 1:
                    (new ViewMailForm())->execute($player);
                    break;
                default:
                    break;
            }
        },
        function (Player $player): void {
            Mail::getInstance()->getStackFormManager()->deleteStack($player->getXuid());
        });

        Mail::getInstance()->getStackFormManager()->addStackForm($player->getXuid(), "mail", $form);
    }
}