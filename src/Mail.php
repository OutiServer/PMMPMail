<?php

declare(strict_types=1);

namespace outiserver\mail;

use outiserver\economycore\EconomyCore;
use Ken_Cir\LibFormAPI\FormStack\StackFormManager;
use outiserver\mail\Commands\MailCommand;
use outiserver\mail\Database\Mail\MailDataManager;
use outiserver\mail\Handlers\EventHandler;
use outiserver\mail\Language\LanguageManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use poggit\libasynql\SqlError;

class Mail extends PluginBase
{
    use SingletonTrait;

    const CORE_VERSION = "1.0.0";

    const VERSION = "1.0.0";

    const CONFIG_VERSION = "1.0.0";

    private DataConnector $dataConnector;

    private MailDataManager $mailDataManager;

    private StackFormManager $stackFormManager;

    private LanguageManager $languageManager;

    private Config $config;

    private mixed $databaseConfig;

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
        if (@file_exists("{$this->getDataFolder()}config.yml")) {
            $config = new Config("{$this->getDataFolder()}config.yml", Config::YAML);
            // データベース設定のバージョンが違う場合は
            if ($config->get("version") !== self::CONFIG_VERSION) {
                rename("{$this->getDataFolder()}config.yml", "{$this->getDataFolder()}config.yml.{$config->get("version")}");
                $this->getLogger()->warning("config.yml バージョンが違うため、上書きしました");
                $this->getLogger()->warning("前バージョンのconfig.ymlは{$this->getDataFolder()}config.yml.{$config->get("version")}にあります");
            }
        }
        $this->saveResource("database.yml");
        $this->saveResource("config.yml");
        $this->config = new Config("{$this->getDataFolder()}config.yml", Config::YAML);
        $this->databaseConfig = (new Config("{$this->getDataFolder()}database.yml", Config::YAML))->get("database");

        $this->dataConnector = libasynql::create($this, $this->databaseConfig, [
            "sqlite" => "sql/sqlite.sql",
            "mysql" => "sql/mysql.sql",
        ]);
        $this->dataConnector->executeGeneric("economy.mail.mails.init",
            [],
            null,
            function (SqlError $error) {
                Mail::getInstance()->getLogger()->error("[SqlError] {$error->getErrorMessage()}");
            });
        $this->dataConnector->waitAll();

        $this->mailDataManager = new MailDataManager($this->dataConnector);
        $this->dataConnector->waitAll();

        $this->stackFormManager = new StackFormManager();
        $this->languageManager = new LanguageManager("{$this->getFile()}resources/lang");

        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
            new MailCommand($this, "mail", "メールコマンド", "/mail", [])
        ]);
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
    }

    protected function onDisable(): void
    {
        if (isset($this->dataConnector)) {
            $this->dataConnector->waitAll();
            $this->dataConnector->close();
        }
    }

    /**
     * @return mixed
     */
    public function getDatabaseConfig(): mixed
    {
        return $this->databaseConfig;
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

    /**
     * @return StackFormManager
     */
    public function getStackFormManager(): StackFormManager
    {
        return $this->stackFormManager;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @return LanguageManager
     */
    public function getLanguageManager(): LanguageManager
    {
        return $this->languageManager;
    }
}