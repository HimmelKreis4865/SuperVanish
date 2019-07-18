<?php

namespace HimmelKreis4865\Supervanish;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class vanish extends PluginBase implements Listener{
    public function onEnable(){
        $this->getLogger()->info("Plugin Supervanish wurde geladen");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
    }
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args):bool{
        $cfg = new Config($this->getDataFolder(). $sender->getName() . ".yml", Config::YAML);
        $config = $this->getConfig();
        $online = $this->getServer()->getOnlinePlayers();
        $tcfg = new Config($this->getDataFolder() . $sender->getName() . ".yml", Config::YAML);
        if ($cmd->getName() == "sv"){
            if (!isset($args[0])){
                if ($sender->hasPermission("sv.use") or $sender->hasPermission("sv.self") or $sender->hasPermission("sv.admin")){
                    if (file_exists($this->getDataFolder() . $sender->getName() . ".yml")){
                        if ($cfg->get("Vanished") == true){
                            foreach ($online as $p){
                                $p->showPlayer($sender);
                            }
                            $sender->sendMessage($config->getNested("deactivate.self"));
                            $cfg->set("Vanished", false);
                            $cfg->save();
                        }else{
                            foreach ($online as $p){
                                if (!$p->hasPermission("sv.see") or !$p->hasPermission("sv.admin")){
                                    $p->hidePlayer($sender);
                                }else{
                                    $p->showPlayer($sender);
                                }
                            }
                            $cfg->set("Vanished", true);
                            $cfg->save();
                            $sender->sendMessage($config->getNested("activate.self"));
                        }
                    }else{
                        $cfg->set("Vanished", true);
                        $cfg->save();
                        foreach ($online as $p){
                            if (!$p->hasPermission("sv.see") or !$p->hasPermission("sv.admin")){
                                $p->hidePlayer($sender);
                            }else{
                                $p->showPlayer($sender);
                            }
                        }
                        $sender->sendMessage($config->getNested("activate.self"));
                    }
                }else{
                    $sender->sendMessage($config->get("NoPermission"));
                }
            }else{
                $target = $this->getServer()->getPlayer($args[0]);
                if (!$target == null){
                    if (file_exists($this->getDataFolder() . $target->getName() . ".yml")){
                        if ($tcfg->get("Vanished") == true){
                            foreach ($online as $p){
                                $p->showPlayer($target);
                            }
                            $msg = $config->getNested("deactivate.other");
                            $msg = str_replace("{player}", $target->getName(), $msg);
                            $sender->sendMessage($msg);
                            $msg = $config->getNested("deactivate.byother");
                            $msg = str_replace("{player}", $target->getName(), $msg);
                            $target->sendMessage($msg);
                            $tcfg->set("Vanished", false);
                            $tcfg->save();
                        }else{
                            foreach ($online as $p){
                                if (!$p->hasPermission("sv.see") or !$p->hasPermission("sv.admin")){
                                    $p->hidePlayer($target);
                                }else{
                                    $p->showPlayer($target);
                                }
                            }
                            $tcfg->set("Vanished", true);
                            $tcfg->save();
                            $msg = $config->getNested("activate.other");
                            $msg = str_replace("{player}", $target->getName(), $msg);
                            $sender->sendMessage($msg);
                            $msg = $config->getNested("activate.byother");
                            $msg = str_replace("{player}", $target->getName(), $msg);
                            $target->sendMessage($msg);
                        }
                    }
                }else{
                    $sender->sendMessage($config->get("InvalidPlayer"));
                }
            }
        }
        return true;
    }
    public function onJoin(PlayerJoinEvent $event){
        $online = $this->getServer()->getOnlinePlayers();
        $player = $event->getPlayer();
        foreach($online as $p){
            $cfg = new Config($this->getDataFolder(). $p->getName() . ".yml", Config::YAML);
            if (file_exists($this->getDataFolder(). $p->getName() . ".yml")){
                if ($cfg->get("Vanished") == "true"){
                    if (!$player->isOp() or !$player->hasPermission("sv.admin")){
                        $player->hidePlayer($p);
                    }else{
                        $player->showPlayer($p);
                    }
                }
            }
        }
    }
    public function onQuit (PlayerQuitEvent $e){
        $p = $e->getPlayer();
        $config = new Config($this->getDataFolder() . $p->getName() . ".yml", Config::YAML);
        if (file_exists($this->getDataFolder() . $p->getName() . ".yml")){
            if ($config->get("Vanished") == true){
                $config->set("Vanished", false);
                $this->getLogger()->info("Vanish mode von " . $p->getName() . " wurde deaktivert!");
            }
        }
    }
}
