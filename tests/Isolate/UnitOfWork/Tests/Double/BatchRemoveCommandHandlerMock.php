<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\BatchRemoveCommandHandler;

class BatchRemoveCommandHandlerMock extends BatchRemoveCommandHandler
{
    protected $handledBatches = 0;

    protected $handledCommands = 0;

    /**
     * @param array $commands
     * @return mixed
     */
    protected function handleBatch($commands = [])
    {
        $this->handledBatches++;
        $this->handledCommands += count($commands);
    }

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
