<?php

declare(strict_types=1);

namespace Ken_Cir\Mail;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Mail extends PluginBase
{
    use SingletonTrait;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }
}