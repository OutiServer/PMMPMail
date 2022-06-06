<?php

declare(strict_types=1);

namespace Ken_Cir\Mail\Database\Mail;

use Ken_Cir\EconomyCore\Database\Base\BaseData;
use poggit\libasynql\DataConnector;

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
            ]);
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
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
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