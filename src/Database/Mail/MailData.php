<?php

declare(strict_types=1);

namespace outiserver\mail\Database\Mail;

use outiserver\economycore\Database\Base\BaseData;
use outiserver\mail\Mail;
use poggit\libasynql\DataConnector;
use poggit\libasynql\SqlError;

class MailData extends BaseData
{
    private int $id;

    private string $title;

    private string $content;

    private string $sendXuid;

    private string $authorXuid;

    private int $sendTime;

    private int $read;

    public function __construct(DataConnector $dataConnector, int $id, string $title, string $content, string $sendXuid, string $authorXuid, int $sendTime, int $read)
    {
        parent::__construct($dataConnector);

        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->sendXuid = $sendXuid;
        $this->authorXuid = $authorXuid;
        $this->sendTime = $sendTime;
        $this->read = $read;
    }

    protected function update(): void
    {
        $this->dataConnector->executeChange("economy.mail.mails.update",
            [
                "read" => $this->read,
                "id" => $this->id
            ],
            null,
            function (SqlError $error) {
                Mail::getInstance()->getLogger()->error("[SqlError] {$error->getErrorMessage()}");
            });
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getSendXuid(): string
    {
        return $this->sendXuid;
    }

    /**
     * @return string
     */
    public function getAuthorXuid(): string
    {
        return $this->authorXuid;
    }

    /**
     * @return int
     */
    public function getSendTime(): int
    {
        return $this->sendTime;
    }

    /**
     * @return int
     */
    public function getRead(): int
    {
        return $this->read;
    }

    /**
     * @param int $read
     */
    public function setRead(int $read): void
    {
        $this->read = $read;
        $this->update();
    }
}