<?php

namespace Coduo\UnitOfWork\Command;

abstract class BatchRemoveCommandHandler implements RemoveCommandHandler
{
    use BatchCommandHandler;

    /**
     * @param RemoveCommand $command
     * @return mixed
     */
    final public function handle(RemoveCommand $command)
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

        if ($command->getTotalRemovedObjects() === ($this->getHandledCommandsCount() + $this->getBatchSize())) {
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
