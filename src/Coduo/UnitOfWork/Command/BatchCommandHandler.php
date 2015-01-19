<?php

namespace Coduo\UnitOfWork\Command;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;

trait BatchCommandHandler
{
    /**
     * @var int
     */
    private $maximumBatchSize = 10;

    /**
     * @var int
     */
    private $handledCommands = 0;

    /**
     * @var array
     */
    private $batch = [];


    /**
     * @param int $newSize
     * @throws InvalidArgumentException
     */
    final public function changeMaximumBatchSize($newSize)
    {
        if (!is_integer($newSize) || $newSize <= 0) {
            throw new InvalidArgumentException("New batch size need to be valid integer value that is greater than 0.");
        }

        $this->maximumBatchSize = $newSize;
    }

    /**
     * @return int
     */
    final public function getMaximumBatchSize()
    {
        return $this->maximumBatchSize;
    }

    /**
     * @return int
     */
    final protected function getBatchSize()
    {
        return count($this->batch);
    }

    /**
     * @return array
     */
    final protected function getBatch()
    {
        return $this->batch;
    }

    /**
     * @param $command
     * @throws InvalidArgumentException
     */
    final protected function addCommandToBatch($command)
    {
        if (!$command instanceof EditCommand && !$command instanceof NewCommand && !$command instanceof RemoveCommand) {
            throw new InvalidArgumentException("Only EditCommand, NewCommand and RemoveCommand are supported by BatchCommandHandler.");
        }

        $this->batch[] = $command;
    }

    final protected function clearBatch()
    {
        $this->batch = [];
    }

    final protected function increaseHandledCommands()
    {
        $this->handledCommands += $this->getBatchSize();
    }

    final protected function resetHandledCommands()
    {
        $this->handledCommands = 0;
    }

    final protected function getHandledCommandsCount()
    {
        return $this->handledCommands;
    }
}
