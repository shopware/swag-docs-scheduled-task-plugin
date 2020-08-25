<?php declare(strict_types=1);

namespace Swag\ScheduledTaskPluginTests;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\QueueTestBehaviour;
use Shopware\Core\PlatformRequest;
use Swag\ScheduledTaskPlugin\ScheduledTask\MyTask;
use Swag\ScheduledTaskPlugin\ScheduledTask\MyTaskHandler;

class ScheduledTasksTest extends TestCase
{
    use IntegrationTestBehaviour;
    use QueueTestBehaviour;
    use AdminApiTestBehaviour;

    public function test_handle(): void
    {
        /** @var EntityRepositoryInterface $scheduledTaskRepo */
        $scheduledTaskRepo = $this->getContainer()->get('scheduled_task.repository');

        /** @var ScheduledTaskEntity $task */
        $task = $scheduledTaskRepo->search((new Criteria())->addFilter(new EqualsFilter('name', 'vendor_prefix.my_task')), Context::createDefaultContext())->first();
        $task->setStatus(ScheduledTaskDefinition::STATUS_QUEUED);
        $scheduledTaskRepo->upsert([$task->jsonSerialize()], Context::createDefaultContext());

        $scheduled = new MyTask();
        $scheduled->setTaskId($task->getId());

        $handler = new MyTaskHandler($scheduledTaskRepo);

        ob_start();
        $handler->handle($scheduled);
        $result = ob_get_clean();

        static::assertSame('Do stuff!', $result);
    }

    public function test_isTaskQueued(): void
    {
        $yesterday = new \DateTime('now -1 day');

        /** @var EntityRepositoryInterface $scheduledTaskRepo */
        $scheduledTaskRepo = $this->getContainer()->get('scheduled_task.repository');

        /** @var ScheduledTaskEntity $task */
        $task = $scheduledTaskRepo->search((new Criteria())->addFilter(new EqualsFilter('name', 'vendor_prefix.my_task')), Context::createDefaultContext())->first();
        $task->setNextExecutionTime($yesterday);

        $scheduledTaskRepo->upsert([$task->jsonSerialize()], Context::createDefaultContext());

        $url = sprintf('/api/v%s/_action/scheduled-task/run', PlatformRequest::API_VERSION);
        $client = $this->getBrowser();
        $client->request('POST', $url);

        /** @var ScheduledTaskEntity $result */
        $result = $scheduledTaskRepo->search((new Criteria())->addFilter(new EqualsFilter('name', 'vendor_prefix.my_task')), Context::createDefaultContext())->first();

        static::assertSame(ScheduledTaskDefinition::STATUS_QUEUED, $result->getStatus());
    }
}
