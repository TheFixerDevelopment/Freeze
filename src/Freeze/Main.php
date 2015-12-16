<?php

namespace Freeze;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener{
    public $frozen = [];

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::GREEN . "Freeze by Fycarman enabled!");
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
    }

    public function onLoad(){
        $this->getLogger()->info(TextFormat::YELLOW . "Loading Freeze by Fycarman...");
    }

    public function onDisable(){
        $this->getLogger()->info(TextFormat::RED . "Disabling Freeze by Fycarman");
        $this->getConfig()->save();
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch ($command->getName()) {
            case "freeze":
                if (!$sender->hasPermission("freeze.freeze")) {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command!");
                }
                foreach ($this->getServer()->getLevelByName($this->getConfig()->get("worlds"))->getPlayers($args[0]) as $player) {
                    if (trim($player) === "") {
                        $sender->sendMessage(TextFormat::RED . "Please specify a valid player name");
                    } else {
                        $this->freeze($player, $sender);
                    }
                }
            case "unfreeze":
                if (!$sender->hasPermission("freeze.unfreeze")) {
                    $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command");
                }
                foreach ($this->getServer()->getLevelByName($this->getConfig()->get("worlds"))->getPlayers($args[0]) as $player) {
                    if (trim($player) === "") {
                        $sender->sendMessage(TextFormat::RED . "Please specify  a valid player name");
                    } else {
                        $this->unfreeze($player, $sender);
                    }
                }
        }
    }

    public function freeze(Player $player, CommandSender $sender){
        $id = $player->getUniqueId();
        $name = $player->getName();
        $this->frozen[$name] = $id;
        $sender->sendMessage(TextFormat::GREEN . ".$name. is now frozen");
    }

    public function unfreeze(Player $player, CommandSender $sender){
        $id = $player->getUniqueId();
        $name = $player->getName();
        if(in_array($id, $this->frozen)){
            $index = array_search($id, $this->frozen);
            unset($this->frozen[$index]);
            $sender->sendMessage(TextFormat::GREEN . ".$name. can now walk");
        }
    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        foreach($this->frozen as $name => $id){
            if($player->getName() === $name and $player->getUniqueId() === $id){
                $event->setCancelled();
            }
        }
    }

    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        foreach ($this->frozen as $name => $id){
            if($player->getName() === $name and $player->getUniqueId() === $id){
                $event->setCancelled();
            }
        }

    }
}
