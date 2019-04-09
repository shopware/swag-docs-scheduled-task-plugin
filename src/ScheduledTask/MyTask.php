<?php declare(strict_types=1);

namespace Swag\ScheduledTaskPlugin\ScheduledTask;

use Shopware\Core\Framework\ScheduledTask\ScheduledTask;

class MyTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'vendor_prefix.my_task';
    }

    public static function getDefaultInterval(): int
    {
        return 300; // 5 minutes
    }
}