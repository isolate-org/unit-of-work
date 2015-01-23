<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\BatchEditCommandHandler;

class FailingBatchEditCommandHandlerStub extends BatchEditCommandHandler
{
    protected $handledBatches = 0;

    protected $handledCommands = 0;

    /**
     * @param array $commands
     * @return mixed
     */
    protected function handleBatch($commands = [])
    {
        if ($this->handledBatches > 0) {
            return false;
        }

        $this->handledBatches++;
        $this->handledCommands += count($commands);
    }

    /**
     * @return int
     */
    public function getHandledBatchesCount()
    {
        return $this->handledBatches;
    }

    /**
     * @return int
     */
    public function getTotalHandledCommandsCount()
    {
        return $this->handledCommands;
    }
}
