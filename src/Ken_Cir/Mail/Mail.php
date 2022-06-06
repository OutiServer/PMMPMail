<?php

declare(strict_types=1);

namespace Ken_Cir\Mail;

use Ken_Cir\EconomyCore\EconomyCore;
use Ken_Cir\Mail\Database\Mail\MailDataManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Mail extends PluginBase
{
    use SingletonTrait;

    const CORE_VERSION = "1.0.0";

    const VERSION = "1.0.0";

    private DataConnector $dataConnector;

    private MailDataManager $mailDataManager;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        if (EconomyCore::VERSION !== self::CORE_VERSION) {
            $this->getLogger()->emergency("EconomyCoreのバージョンが一致しません、このプラグインが動作に必要なバージョンは" . self::CORE_VERSION . "です");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        if (@file_exists("{$this->getDataFolder()}database.yml")) {
            $config = new Config("{$this->getDataFolder()}database.yml", Config::YAML);
            // データベース設定のバージョンが違う場合は
            if ($config->get("version") !== self::VERSION) {
                rename("{$this->getDataFolder()}database.yml", "{$this->getDataFolder()}database.yml.{$config->get("version")}");
                $this->getLogger()->warning("database.yml バージョンが違うため、上書きしました");
                $this->getLogger()->warning("前バージョンのdatabase.ymlは{$this->getDataFolder()}database.yml.{$config->get("version")}にあります");
            }
        }
        $this->saveResource("database.yml");

        $this->dataConnector = libasynql::create($this, (new Config("{$this->getDataFolder()}database.yml", Config::YAML))->get("database"), [
            "sqlite" => "sqlite.sql"
        ]);
        $this->dataConnector->executeGeneric("economy.mail.mails.init");
        $this->dataConnector->waitAll();

        $this->mailDataManager = new MailDataManager($this->dataConnector);
    }

    protected function onDisable(): void
    {
        if (isset($this->dataConnector)) {
            $this->dataConnector->waitAll();
            $this->dataConnector->close();
        }
    }

    /**
     * @return DataConnector
     */
    public function getDataConnector(): DataConnector
    {
        return $this->dataConnector;
    }

    /**
     * @return MailDataManager
     */
    public function getMailDataManager(): MailDataManager
    {
        return $this->mailDataManager;
    }
}