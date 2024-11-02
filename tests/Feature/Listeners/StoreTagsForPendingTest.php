<?php

namespace Laravel\Horizon\Tests\Feature\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Jobs\Job;
use Laravel\Horizon\Contracts\TagRepository;
use Laravel\Horizon\Events\JobPushed;
use Laravel\Horizon\Tests\IntegrationTest;
use Mockery as m;

class StoreTagsForPendingTest extends IntegrationTest
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    public function test_a_pending_tag_is_added_when_the_job_is_pushed(): void
    {
        config()->set('horizon.trim.pending', 120);

        $tagRepository = m::mock(TagRepository::class);

        $tagRepository->shouldReceive('addTemporary')->once()->with(120, '1', ['pending:bar'])->andReturn([]);
        $tagRepository->shouldReceive('monitored')->once()->andReturn([]);

        $this->instance(TagRepository::class, $tagRepository);

        $this->app->make(Dispatcher::class)->dispatch(new JobPushed(
            '{"id":"1","displayName":"displayName","tags":["bar"]}'
        ));

    }
}
