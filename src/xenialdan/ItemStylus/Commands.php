<?php

declare(strict_types=1);

namespace xenialdan\ItemStylus;

use InvalidStateException;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xenialdan\ItemStylus\subcommand\CancelSubCommand;
use xenialdan\ItemStylus\subcommand\LoreSubCommand;
use xenialdan\ItemStylus\subcommand\RenameSubCommand;
use xenialdan\ItemStylus\subcommand\SubCommand;

class Commands extends PluginCommand
{
    private $subCommands = [];

    /* @var SubCommand[] */
    private $commandObjects = [];

    public function __construct(Plugin $plugin)
    {
        parent::__construct('itemstylus', $plugin);
        $this->setPermission('itemStylus.command');
        $this->setDescription('The main commands for ItemStylus');

        $this->loadSubCommand(new CancelSubCommand($plugin));
        $this->loadSubCommand(new RenameSubCommand($plugin));
        $this->loadSubCommand(new LoreSubCommand($plugin));
    }

    private function loadSubCommand(SubCommand $command): void
    {
        $this->commandObjects[] = $command;
        $commandId = count($this->commandObjects) - 1;
        $this->subCommands[$command->getName()] = $commandId;
        foreach ($command->getAliases() as $alias) {
            $this->subCommands[$alias] = $commandId;
        }
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool|mixed
     * @throws InvalidStateException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            return $this->sendHelp($sender);
        }
        $subCommand = strtolower(array_shift($args));
        if (!isset($this->subCommands[$subCommand])) {
            return $this->sendHelp($sender);
        }
        $command = $this->commandObjects[$this->subCommands[$subCommand]];
        $canUse = $command->canUse($sender);
        if ($canUse) {
            if (!$command->execute($sender, $args)) {
                $sender->sendMessage(TextFormat::YELLOW . 'Usage: /itemStylus ' . $command->getName() . TextFormat::BOLD . TextFormat::DARK_AQUA . ' > ' . TextFormat::RESET . TextFormat::YELLOW . $command->getUsage());
            }
        } else if (!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED . 'Please run this command in-game.');
        } else {
            $sender->sendMessage(TextFormat::RED . 'You do not have permissions to run this command');
        }
        return true;
    }

    /**
     * @param CommandSender $sender
     * @return bool
     * @throws InvalidStateException
     */
    private function sendHelp(CommandSender $sender): bool
    {
        $sender->sendMessage('===========[ItemStylus commands]===========');
        foreach ($this->commandObjects as $command) {
            if ($command->canUse($sender)) {
                $sender->sendMessage(TextFormat::DARK_GREEN . '/itemStylus ' . $command->getName() . TextFormat::BOLD . TextFormat::DARK_AQUA . ' > ' . TextFormat::RESET . TextFormat::DARK_GREEN . $command->getUsage() . ': ' .
                    TextFormat::WHITE . $command->getDescription()
                );
            }
        }
        return true;
    }
}
