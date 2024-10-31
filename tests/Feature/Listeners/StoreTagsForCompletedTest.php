<?php

namespace Laravel\Horizon\Tests\Feature\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Jobs\Job;
use Laravel\Horizon\Contracts\TagRepository;
use Laravel\Horizon\Events\JobDeleted;
use Laravel\Horizon\Tests\IntegrationTest;
use Mockery as m;

class StoreTagsForCompletedTest extends IntegrationTest
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function test_temporary_completed_job_should_be_deleted_when_the_main_job_is_deleted(): void
    {
        config()->set('horizon.trim.completed', 120);

        $tagRepository = m::mock(TagRepository::class);

        $tagRepository->shouldReceive('addTemporary')->once()->with(120, '1', ['completed:foobar'])->andReturn([]);

        $this->instance(TagRepository::class, $tagRepository);

        $this->app->make(Dispatcher::class)->dispatch(new JobDeleted(
            new CompletedJob(), '{"id":"1","displayName":"displayName","tags":["foobar"]}'
        ));
    }
}

class CompletedJob extends Job
{
    public function getJobId()
    {
        return '1';
    }

    public function getRawBody()
    {
        return '';
    }
}
