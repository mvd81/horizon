<?php

namespace Laravel\Horizon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\TagRepository;

class CompletedJobsController extends Controller
{
    /**
     * The job repository implementation.
     *
     * @var \Laravel\Horizon\Contracts\JobRepository
     */
    public $jobs;

    /**
     * The tag repository implementation.
     *
     * @var \Laravel\Horizon\Contracts\TagRepository
     */
    public $tags;

    /**
     * Create a new controller instance.
     *
     * @param \Laravel\Horizon\Contracts\JobRepository  $jobs
     * @param \Laravel\Horizon\Contracts\TagRepository $tags
     * @return void
     */
    public function __construct(JobRepository $jobs, TagRepository $tags)
    {
        parent::__construct();

        $this->jobs = $jobs;
        $this->tags = $tags;
    }

    /**
     * Get all of the completed jobs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $jobs = ! $request->query('tag')
            ? $this->paginate($request)
            : $this->paginateByTag($request, $request->query('tag'));

        $total = $request->query('tag')
            ? $this->tags->count('completed:'.$request->query('tag'))
            : $this->jobs->countCompleted();

        return [
            'jobs' => $jobs,
            'total' => $total,
        ];
    }

    /**
     * Paginate the failed jobs for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    protected function paginate(Request $request)
    {
        return $this->jobs->getCompleted($request->query('starting_at') ?: -1)->map(function ($job) {
            return $this->decode($job);
        });
    }

    /**
     * Paginate the failed jobs for the request and tag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    protected function paginateByTag(Request $request, $tag)
    {

        return $this->jobs->getCompleted($request->query('starting_at') ?: -1)->filter(function ($job) use ($tag) {
            return in_array($tag, json_decode($job->payload)->tags);
        })->map(function ($job) {
            return $this->decode($job);
        })->values();
    }


    /**
     * Decode the given job.
     *
     * @param  object  $job
     * @return object
     */
    protected function decode($job)
    {
        $job->payload = json_decode($job->payload);

        return $job;
    }
}
