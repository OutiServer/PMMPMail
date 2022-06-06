<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Database\Mail;

use Ken_Cir\EconomyCore\Database\Base\BaseAutoincrement;
use Ken_Cir\EconomyCore\Database\Base\BaseDataManager;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;

class MailDataManager extends BaseDataManager
{
    use SingletonTrait;
    use BaseAutoincrement;

    public function __construct(DataConnector $dataConnector)
    {
        parent::__construct($dataConnector);
        self::setInstance($this);

        $this->dataConnector->executeSelect("economy.mail.mails.load",
            [],
            function (array $row) {
                foreach ($row as $data) {
                    $this->data[$data["id"]] = new MailData($this->dataConnector, $data["id"], $data["title"], $data["content"], $data["send_xuid"], $data["author_xuid"], $data["send_time"], $data["read"]);
                }
            });
        $this->dataConnector->executeSelect("economy.mail.mails.seq",
            [],
            function (array $row) {
                if (count($row) < 1) {
                    $this->seq = 0;
                    return;
                }
                foreach ($row as $data) {
                    $this->seq = $data["seq"];
                }
            });
    }

    public function get(int $id): ?MailData
    {
        if (!isset($this->data[$id])) return null;
        return $this->data[$id];
    }

    public function create(string $title, string $content, string $sendXuid, string $authorXuid, int $sendTime): MailData
    {
        $this->dataConnector->executeInsert("economy.mail.mails.create",
        [
            "title" => $title,
            "content" => $content,
            "send_xuid" => $sendXuid,
            "author_xuid" => $authorXuid,
            "send_time" => $sendTime
        ]);

        return ($this->data[++$this->seq] = new MailData($this->dataConnector, $this->seq, $title, $content, $sendXuid, $authorXuid, $sendTime, 0));
    }

    public function delete(int $id): void
    {
        $this->dataConnector->executeGeneric("economy.mail.mails.delete",
        [
            "id" => $id
        ]);
    }
}