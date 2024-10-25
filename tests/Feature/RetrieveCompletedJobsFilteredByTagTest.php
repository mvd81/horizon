<?php

namespace Laravel\Horizon\Tests\Feature;

use Illuminate\Http\Request;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Http\Controllers\CompletedJobsController;
use Laravel\Horizon\Tests\IntegrationTest;

class RetrieveCompletedJobsFilteredByTagTest extends IntegrationTest
{
    protected $jobRepository;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->jobRepository = $this->createMock(JobRepository::class);
    }

    public function test_can_retrieve_completed_jobs_without_tag_search()
    {
        $jobs = collect([
            (object)['id' => 1, 'payload' => json_encode(['tags' => []])],
            (object)['id' => 2, 'payload' => json_encode(['tags' => []])],
            (object)['id' => 3, 'payload' => json_encode(['tags' => []])],
            (object)['id' => 4, 'payload' => json_encode(['tags' => []])]
        ]);

        $this->jobRepository->method('getCompleted')->willReturn($jobs);

        $controller = new CompletedJobsController($this->jobRepository);
        $request = Request::create('/api/jobs', 'GET');

        $response = $controller->index($request);

        $this->assertCount(4, $response['jobs']);
        $this->assertEquals(4, $response['total']);
    }

    public function test_can_retrieve_completed_jobs_with_tag_search()
    {
        $tag = 'developer';
        $jobs = collect([
            (object)['id' => 1, 'payload' => json_encode(['tags' => [$tag]])],
            (object)['id' => 2, 'payload' => json_encode(['tags' => [$tag]])],
            (object)['id' => 3, 'payload' => json_encode(['tags' => ['other']])],
            (object)['id' => 4, 'payload' => json_encode(['tags' => []])]
        ]);

        $this->jobRepository->method('getCompleted')->willReturn($jobs);

        $controller = new CompletedJobsController($this->jobRepository);
        $request = Request::create('/api/jobs', 'GET', ['tag' => $tag]);

        $response = $controller->index($request);

        $this->assertCount(2, $response['jobs']);
        $this->assertEquals(2, $response['total']);
    }
}