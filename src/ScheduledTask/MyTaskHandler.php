<?php declare(strict_types=1);

namespace Swag\ScheduledTaskPlugin\ScheduledTask;

use Shopware\Core\Framework\ScheduledTask\ScheduledTaskHandler;

class MyTaskHandler extends ScheduledTaskHandler
{
    public static function getHandledMessages(): iterable
    {
        return [ MyTask::class ];
    }

    public function run(): void
    {
        echo 'Do stuff!';
    }
}
