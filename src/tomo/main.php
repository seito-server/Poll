<?php

namespace tomo;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class main extends PluginBase
{

    public function onEnable()
    {
        $this->getLogger()->notice("これはtomoによる自作プラグインです。");
        //$this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!file_exists($this->getDataFolder())) {
            mkdir($this->getDataFolder(), 0744, true);
            //$this->config = new Config($this->getDataFolder() . "main.json", Config::JSON, array());
            //$this->config->save();
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "poll":
                if (!isset($args[0]) && !isset($args[1])) {
                    $sender->sendMessage("====Pollコマンドの使用方法======");
                    $sender->sendMessage("/poll {投票名} list -- 立候補者一覧を表示する");
                    $sender->sendMessage("/poll {投票名} {選択肢}");
                }
                if (isset($args[0]) && !isset($args[1])) {
                    $poll_name = $args[0];
                    if (!file_exists($this->getDataFolder() . $poll_name . ".json")) {
                        $sender->sendMessage("そのような名前の投票はありません");
                    } else {
                        $this->config = new Config($this->getDataFolder() . $poll_name . ".json");
                        $sender->sendMessage("==" . $this->config->get("name") . "==");
                        $sender->sendMessage("description : " . $this->config->get("description") . "\n");
                        $pollers = $this->config->get("pollers");
                        foreach ($pollers as $key => $value) {
                            $sender->sendMessage($key . ":" . count($value));
                        }
                    }
                } else {
                    $poll_name = $args[0];
                    if (!file_exists($this->getDataFolder() . $poll_name . ".json")) {
                        $sender->sendMessage("そのような名前の投票はありません");
                    } else if ($args[1] == "list") {
                        $sender->sendMessage("==立候補者一覧==");
                        $this->config = new Config($this->getDataFolder() . $poll_name . ".json");
                        foreach ($this->config->get("pollers") as $key => $value) {
                            $sender->sendMessage($key);
                        }
                    } else {
                        $this->config = new Config($this->getDataFolder() . $poll_name . ".json");
                        $type = $this->config->get("type");
                        if ($type == "name") {
                            if (array_key_exists($args[1], $this->config->get("pollers"))) {
                                $pollers = $this->config->get("pollers");
                                $contains = array();
                                //var_dump($this->config->get("pollers"));
                                foreach ($this->config->get("pollers") as $key => $value) {
                                    foreach ($value as $key1) {
                                        if ($key1 == $sender->getName()) {
                                            $sender->sendMessage("既に" . $key . "に投票済みです");
                                            array_push($contains, $key);
                                        }
                                    }
                                }
                                //var_dump($contains);
                                if (sizeof($contains) <= 2) {
                                    if (!in_array($args[1], $contains)) {
                                        array_push($pollers[$args[1]], $sender->getName());
                                        $this->config->set("pollers", $pollers);
                                        $this->config->save();
                                        $sender->sendMessage("投票完了！");
                                    } else {
                                        $sender->sendMessage("同じ人に投票することはできません");
                                    }
                                } else {
                                    $sender->sendMessage("一人3票です。");
                                }
                            } else {
                                $sender->sendMessage("選択肢にありません");
                            }
                        }
                    }
                }
        }
        return true;
    }
}
