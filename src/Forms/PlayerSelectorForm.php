<?php

declare(strict_types=1);

namespace outiserver\mail\Forms;

use outiserver\economycore\Database\Player\PlayerDataManager;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use Ken_Cir\LibFormAPI\Forms\SimpleForm;
use Ken_Cir\LibFormAPI\Utils\FormUtil;
use outiserver\mail\Language\LanguageManager;
use outiserver\mail\Mail;
use pocketmine\player\Player;

/**
 * 複数のプレイヤーから選択するFormだよ
 */
class PlayerSelectorForm
{
    public function execute(Player $player, string $name, callable $callback): void
    {
        $result = PlayerDataManager::getInstance()->getNamePrefix($name);
        // Q.E.D
        if (count($result) < 1) {
            $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.playerselector.playernotfound"));
            $player->sendMessage(LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.back"));
            FormUtil::backForm(Mail::getInstance(),
                [Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid()), "reSend"],
                [],
                3);
        } else {
            $formContent = [];
            foreach ($result as $playerData) {
                $formContent[] = new SimpleFormButton($playerData->getName());
            }

            new SimpleForm(Mail::getInstance(),
                $player,
                LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.playerselector.title"),
                LanguageManager::getInstance()->getLanguage($player->getLocale())->translateString("form.playerselector.content"),
                $formContent,
                function (Player $player, int $data) use ($callback, $result): void {
                    $playerData = $result[$data];
                    $callback($player, $playerData);
                },
                // CLOSED
                function (Player $player): void {
                    Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())->reSend();
                });
        }
    }
}