<?php

declare(strict_types=1);

namespace outiserver\mail\Database\Mail;

use outiserver\economycore\Database\Base\BaseAutoincrement;
use outiserver\economycore\Database\Base\BaseDataManager;
use outiserver\mail\Mail;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\SqlError;

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
                    $this->data[$data["id"]] = new MailData($this->dataConnector, $data["id"], $data["title"], $data["content"], $data["send_xuid"], $data["author_xuid"], $data["send_time"], $data["readed"]);
                }
            },
            function (SqlError $error) {
                Mail::getInstance()->getLogger()->error("[SqlError] {$error->getErrorMessage()}");
            });
        $this->dataConnector->executeSelect("economy.mail.mails.seq",
            [],
            function (array $row) {
                if (count($row) < 1) {
                    $this->seq = 0;
                    return;
                }
                foreach ($row as $data) {
                    if (Mail::getInstance()->getDatabaseConfig()["type"] === "sqlite" or Mail::getInstance()->getDatabaseConfig()["type"] === "sqlite3" or Mail::getInstance()->getDatabaseConfig()["type"] === "sq3") {
                        $this->seq = $data["seq"];
                    } elseif (Mail::getInstance()->getDatabaseConfig()["type"] === "mysql" or Mail::getInstance()->getDatabaseConfig()["type"] === "mysqli") {
                        $this->seq = $data["Auto_increment"] ?? 0;
                    }
                }
            },
            function (SqlError $error) {
                Mail::getInstance()->getLogger()->error("[SqlError] {$error->getErrorMessage()}");
            });
    }

    public function get(int $id): ?MailData
    {
        if (!isset($this->data[$id])) return null;
        return $this->data[$id];
    }

    /**
     * @param string $xuid
     * @param bool $keyValue
     * @return MailData[]
     */
    public function getPlayerXuid(string $xuid, bool $keyValue): array
    {
        $mail = array_filter($this->data, function (MailData $mailData) use ($xuid) {
            return $mailData->getSendXuid() === $xuid;
        });

        if ($keyValue) return array_values($mail);
        return array_reverse($mail, true);
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
            ],
            null,
            function (SqlError $error) {
                Mail::getInstance()->getLogger()->error("[SqlError] {$error->getErrorMessage()}");
            });

        return ($this->data[++$this->seq] = new MailData($this->dataConnector, $this->seq, $title, $content, $sendXuid, $authorXuid, $sendTime, 0));
    }

    public function delete(int $id): void
    {
        if (!$this->get($id)) return;

        $this->dataConnector->executeGeneric("economy.mail.mails.delete",
            [
                "id" => $id
            ],
            null,
            function (SqlError $error) {
                Mail::getInstance()->getLogger()->error("[SqlError] {$error->getErrorMessage()}");
            });
        unset($this->data[$id]);
    }

    /**
     * @param string $xuid
     * @return int
     */
    public function unReadCount(string $xuid): int
    {
        $unReadCount = 0;
        foreach ($this->getPlayerXuid($xuid, false) as $data) {
            if (!$data->getRead()) $unReadCount++;
        }

        return $unReadCount;
    }
}