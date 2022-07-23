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
use outiserver\mail\Mail;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class ViewMailForm implements BaseForm
{
    public function execute(Player $player): void
    {
        $formContents = [];
        foreach (MailDataManager::getInstance()->getPlayerXuid($player->getXuid(), true) as $mailData) {
            if ($mailData->getRead() === 1) {
                $formContents[] = new SimpleFormButton("[" . date("Y年m月d日 H時i分s秒", $mailData->getSendTime()). "] {$mailData->getTitle()}");
            }
            else {
                $formContents[] = new SimpleFormButton(TextFormat::LIGHT_PURPLE . "NEW " . TextFormat::WHITE  . "[" . date("Y年m月d日 H時i分s秒", $mailData->getSendTime()). "] {$mailData->getTitle()}");
            }
        }

        $form = new SimpleForm(Mail::getInstance(),
            $player,
            "[Mail] メールを閲覧",
            "閲覧するメールを選択",
            $formContents,
        function (Player $player, int $data): void {
            $this->view($player, MailDataManager::getInstance()->getPlayerXuid($player->getXuid(), true)[$data]);
        },
        function (Player $player): void {
            Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), "view_mail");
            Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())?->reSend();
        });

        Mail::getInstance()->getStackFormManager()->addStackForm($player->getXuid(), "view_mail", $form);
    }

    public function view(Player $player, MailData $mailData): void
    {
        new ModalForm(Mail::getInstance(),
            $player,
        "[Mail] メールを閲覧 #{$mailData->getId()}",
        "タイトル: {$mailData->getTitle()}\n送信者: " . ($mailData->getAuthorXuid() === "システム" or $mailData->getAuthorXuid() === "運営" ? $mailData->getAuthorXuid() : PlayerDataManager::getInstance()->getXuid($mailData->getAuthorXuid())->getName()) . "\nメール送信時刻: " . date("Y年m月d日 H時i分s秒", $mailData->getSendTime()) . "\n\n{$mailData->getContent()}",
        "削除",
        "閉じる",
            function (Player $player, bool $data) use ($mailData): void {
            if ($data) {
                MailDataManager::getInstance()->delete($mailData->getId());
                $player->sendMessage(TextFormat::GREEN . "メールを削除しました");
                $player->sendMessage("3秒後前のフォームに戻ります");
                // キャッシュ消す
                Mail::getInstance()->getStackFormManager()->deleteStackForm($player->getXuid(), "view_mail");
                FormUtil::backForm(Mail::getInstance(), [$this, "execute"], [$player], 3);
            }
            else {
                $mailData->setRead(1);
                Mail::getInstance()->getStackFormManager()->getStackFormEnd($player->getXuid())->reSend();
            }
        });
    }
}