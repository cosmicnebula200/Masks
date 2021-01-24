<?php


namespace cosmicnebula200\Masks;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemBlock;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class Masks extends PluginBase implements Listener
{

	public const CONFIG_VERSION = 1;

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this , $this);
		$this->saveDefaultConfig();
		if($this->getConfig()->get("config_version" !== self::CONFIG_VERSION)){
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{
		switch ($command->getName()){
			case "mask":
				$item = Item::get(Item::MOB_HEAD);
				$nbt = $item->getNamedTag();
				if(count($args) < 1){
					$sender->sendMessage($command->getUsage());
				}else{
					switch ($args[0]){
						case "husk":
							$nbt->setInt("Type" , "1");
							$name = $this->getConfig()->get("HuskMaskName");
							$item->setCustomName(str_replace("&" , "§" , $name));
							$meta = $this->getConfig()->get("husk");
							$item->setDamage($meta);
							break;
						case "wither":
							$nbt->setInt("Type" , "2");
							$name = $this->getConfig()->get("WitherMaskName");
							$item->setCustomName(str_replace("&" , "§" , $name));
							$meta = $this->getConfig()->get("wither");
							$item->setDamage($meta);
							break;
						case "rabbit":
							$nbt->setInt("Type" , "3");
							$name = $this->getConfig()->get("RabbitMaskName");
							$item->setCustomName(str_replace("&" , "§" , $name));
							$meta = $this->getConfig()->get("rabbit");
							$item->setDamage($meta);
							break;
						case "miner":
							$nbt->setInt("Type" , "4");
							$name = $this->getConfig()->get("MinerMaskName");
							$item->setCustomName(str_replace("&" , "§" , $name));
							$meta = $this->getConfig()->get("miner");
							$item->setDamage($meta);
							break;
						case "grinder":
							$nbt->setInt("Type" , "5");
							$name = $this->getConfig()->get("GrinderMaskName");
							$item->setCustomName(str_replace("&" , "§" , $name));
							$meta = $this->getConfig()->get("grinder");
							$item->setDamage($meta);
							break;
						case "list":
							$sender->sendMessage("these are the available masks ");
							$sender->sendMessage("Husk , Rabbit , Wither , Miner , Grinder");
							break;
						default:
							$sender->sendMessage("not valid type");
							break;
					}
					if(isset($args[1])){
						if($args[1] instanceof Player){
							$player = $this->getServer()->getPlayer($args[1]);
							$player->getInventory()->addItem($item);
						}else{
							$sender->sendMessage("Player Not found :C");
						}
					}else{
						if($sender instanceof Player){
							$sender->getInventory()->addItem($item);
						}else{
							$sender->sendMessage($command->getUsage());
						}
					}
				}
		}
		return true;
	}

	public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event){
		$hit = $event->getEntity();
		$attacker = $event->getDamager();
		if($attacker instanceof Player){
			$mask = $attacker->getArmorInventory()->getHelmet();
			$nbt = $mask->getNamedTag();
			$chance = mt_rand(0,100);
			if($nbt->hasTag("Type" , IntTag::class)){
				switch ($nbt->getInt("Type")) {
					case 1:
						$ActivateChance = $this->getConfig()->get("HuskChance");
						if ($chance <= $ActivateChance) {
							if ($hit instanceof Living) {
								$duration = $this->getConfig()->get("HuskEffectDuration");
								$amplifier = $this->getConfig()->get("HuskEffectAmplifier");
								$hit->addEffect(new EffectInstance(Effect::getEffect(Effect::HUNGER), $duration*20 , $amplifier, false));
							}
						}
						break;
					case 2:
						$ActivateChance = $this->getConfig()->get("WitherChance");
						if ($chance <= $ActivateChance) {
							if ($hit instanceof Living) {
								$duration = $this->getConfig()->get("WitherEffectDuration");
								$amplifier = $this->getConfig()->get("WitherEffectAmplifier");
								$hit->addEffect(new EffectInstance(Effect::getEffect(Effect::WITHER), $duration*20 , $amplifier, false));
							}
						}
						break;
						case 5:
							$chance = mt_rand(0 , 100);
							$ActivateChance = $this->getConfig()->get("GrinderChance");
							if($chance <= $ActivateChance){
								$minmoney = $this->getConfig()->get("MinMoney");
								$maxmoney = $this->getConfig()->get("MaxMoney");
								$money = mt_rand($minmoney , $maxmoney);
								EconomyAPI::getInstance()->addMoney($attacker , $money);
								$message = $this->getConfig()->get("MoneyAddMessage");
								$attacker->sendMessage(str_replace("&" , "§" , str_replace( "{MONEY}" , $money ,$message)));
							}	
				}
			}
		}
	}

	public function onJumpEvent(PlayerJumpEvent $event){
		$player = $event->getPlayer();
		if($player instanceof Player){
			$nbt = $player->getArmorInventory()->getHelmet()->getNamedTag();
			if($nbt->hasTag("Type" , IntTag::class)){
				$chance = mt_rand(0 , 100);
				switch ($nbt->getInt("Type")) {

					case 3:
						$ActivateChance = $this->getConfig()->get("RabbitChance");
						if($chance <= $ActivateChance){
							$duration = $this->getConfig()->get("RabbitEffectDuration");
							$amplifier = $this->getConfig()->get("RabbitEffectAmplifier");
							$player->addEffect(new EffectInstance(Effect::getEffect(Effect::JUMP_BOOST), $duration * 20, $amplifier, false));
						}
						break;
				}
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$nbt = $player->getArmorInventory()->getHelmet()->getNamedTag();
		if($nbt->hasTag("Type" , IntTag::class)){
			switch ($nbt->getInt("Type")){
				case 4:
					$chance = mt_rand(0,100);
					$ActivateChance = $this->getConfig()->get("MinerChance");
					if($chance <= $ActivateChance){
						$duration = $this->getConfig()->get("MinerEffectDuration");
						$amplifier = $this->getConfig()->get("MinerEffectAmplifier");
						$player->addEffect(new EffectInstance(Effect::getEffect(Effect::HASTE),$duration*20 ,$amplifier ,false ));
					}
					break;
				case 5:
					$chance = mt_rand(0 , 100);
					$ActivateChance = $this->getConfig()->get("GrinderChance");
					if($chance <= $ActivateChance){
						$minmoney = $this->getConfig()->get("MinMoney");
						$maxmoney = $this->getConfig()->get("MaxMoney");
						$money = mt_rand($minmoney , $maxmoney);
						EconomyAPI::getInstance()->addMoney($player , $money);
						$message = $this->getConfig()->get("MoneyAddMessage");
						$player->sendMessage(str_replace("&" , "§" , str_replace( "{MONEY}" , $money ,$message)));
					}
			}
		}
	}

	public function onBlockPlaceEvent(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$nbt = $player->getArmorInventory()->getHelmet()->getNamedTag();
		if($nbt->hasTag("Type" , IntTag::class)){
			switch ($nbt->getInt("Type")){
				case 5:
					$chance = mt_rand(0 , 100);
					$ActivateChance = $this->getConfig()->get("GrinderChance");
					if($chance <= $ActivateChance){
						$minmoney = $this->getConfig()->get("MinMoney");
						$maxmoney = $this->getConfig()->get("MaxMoney");
						$money = mt_rand($minmoney , $maxmoney);
						EconomyAPI::getInstance()->addMoney($player , $money);
						$message = $this->getConfig()->get("MoneyAddMessage");
						$player->sendMessage(str_replace("&" , "§" , str_replace( "{MONEY}" , $money ,$message)));
					}
			}

		}

	}

	public function onInteractEvent(PlayerInteractEvent $e){
		$item = $e->getItem();
		if($item->getNamedTag()->hasTag("Type" , IntTag::class)){
			$e->setCancelled();
		}
	}

}
