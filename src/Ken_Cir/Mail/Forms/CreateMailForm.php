<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Form;

use jojoe77777\FormAPI\CustomForm;
use Ken_Cir\EconomyCore\Forms\Base\BaseForm;
use pocketmine\player\Player;

class CreateMailForm implements BaseForm
{
    public function execute(Player $player): void
    {
        $form = new CustomForm(function (Player $player, $data) {

        });
        $player->sendForm($form);
    }
}