<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Forms;

use Ken_Cir\EconomyCore\Forms\Base\BaseForm;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use Ken_Cir\LibFormAPI\Forms\SimpleForm;
use Ken_Cir\Mail\Mail;
use pocketmine\player\Player;

class MailForm implements BaseForm
{
    public function execute(Player $player): void
    {
        $form = new SimpleForm($player,
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