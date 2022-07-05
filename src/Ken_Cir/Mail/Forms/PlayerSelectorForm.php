<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Forms;

use Ken_Cir\EconomyCore\Database\Player\PlayerDataManager;
use Ken_Cir\LibFormAPI\FormContents\SimpleForm\SimpleFormButton;
use Ken_Cir\LibFormAPI\Forms\SimpleForm;
use Ken_Cir\LibFormAPI\Utils\FormUtil;
use Ken_Cir\Mail\Mail;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

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
            $player->sendMessage(TextFormat::RED . "プレイヤーが見つかりませんでした");
            $player->sendMessage(TextFormat::GREEN . "3秒後前のフォームに戻ります");
            FormUtil::backForm(Mail::getInstance(),
                [Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid()), "reSend"],
                [],
                3);
        }
        else {
            $formContent = [];
            foreach ($result as $playerData) {
                $formContent[] = new SimpleFormButton($playerData->getName());
            }

            new SimpleForm($player,
                "[PlayerSelector] プレイヤーを選択",
                "該当するプレイヤーが見つかりました、以下から選択してください",
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