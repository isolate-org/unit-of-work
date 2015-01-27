<?php

namespace Isolate\UnitOfWork\Command;

abstract class BatchNewCommandHandler implements NewCommandHandler
{
    use BatchCommandHandler;

    /**
     * @param NewCommand $command
     * @return mixed
     */
    final public function handle(NewCommand $command)
    {
        if ($this->getBatchSize() >= $this->getMaximumBatchSize()) {
            $result = $this->handleBatch($this->getBatch());
            $this->increaseHandledCommands();

            if ($result === false) {
                $this->clearBatch();
                $this->resetHandledCommands();
                return $result;
            }

            $this->clearBatch();
        }

        $this->addCommandToBatch($command);

        if ($command->getTotalNewEntities() === $this->getHandledCommandsCount() + $this->getBatchSize()) {
            $result = $this->handleBatch($this->getBatch());
            $this->increaseHandledCommands();

            if ($result === false) {
                $this->clearBatch();
                $this->resetHandledCommands();
                return $result;
            }

            $this->clearBatch();
        }
    }

    /**
     * @param array $commands
     * @return mixed
     */
    abstract protected function handleBatch($commands = []);
}
