<?php

namespace Isolate\UnitOfWork\Command;

abstract class BatchEditCommandHandler implements EditCommandHandler
{
    use BatchCommandHandler;

    /**
     * @param EditCommand $command
     * @return mixed
     */
    final public function handle(EditCommand $command)
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

        if ($command->getTotalEditedEntities() === ($this->getHandledCommandsCount() + $this->getBatchSize())) {
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
