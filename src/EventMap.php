<?php

namespace Laravel\Horizon;

use App\Packages\horizon\src\Listeners\ForgetJobInPendingTags;
use App\Packages\horizon\src\Listeners\StoreTagsForCompletedJob;
use App\Packages\horizon\src\Listeners\StoreTagsForPendingJob;
use App\Packages\horizon\src\Listeners\TrimCompletedJobs;

trait EventMap
{
    /**
     * All of the Horizon event / listener mappings.
     *
     * @var array
     */
    protected $events = [
        Events\JobPushed::class => [
            Listeners\StoreJob::class,
            Listeners\StoreMonitoredTags::class,
            StoreTagsForPendingJob::class,
        ],

        Events\JobReserved::class => [
            Listeners\MarkJobAsReserved::class,
            Listeners\StartTimingJob::class,
        ],

        Events\JobReleased::class => [
            Listeners\MarkJobAsReleased::class,
        ],

        Events\JobDeleted::class => [
            Listeners\MarkJobAsComplete::class,
            StoreTagsForCompletedJob::class,
            ForgetJobInPendingTags::class,
            Listeners\UpdateJobMetrics::class,
        ],

        Events\JobsMigrated::class => [
            Listeners\MarkJobsAsMigrated::class,
        ],

        \Illuminate\Queue\Events\JobExceptionOccurred::class => [
            Listeners\ForgetJobTimer::class,
        ],

        \Illuminate\Queue\Events\JobFailed::class => [
            Listeners\ForgetJobTimer::class,
            Listeners\MarshalFailedEvent::class,
        ],

        Events\JobFailed::class => [
            Listeners\MarkJobAsFailed::class,
            Listeners\StoreTagsForFailedJob::class,
        ],

        Events\MasterSupervisorLooped::class => [
            Listeners\TrimRecentJobs::class,
            Listeners\TrimFailedJobs::class,
            //Listeners\TrimCompletedJob::class,
            Listeners\TrimMonitoredJobs::class,
            Listeners\ExpireSupervisors::class,
            Listeners\MonitorMasterSupervisorMemory::class,
        ],

        Events\SupervisorLooped::class => [
            Listeners\PruneTerminatingProcesses::class,
            Listeners\MonitorSupervisorMemory::class,
            Listeners\MonitorWaitTimes::class,
        ],

        Events\WorkerProcessRestarting::class => [
            //
        ],

        Events\SupervisorProcessRestarting::class => [
            //
        ],

        Events\LongWaitDetected::class => [
            Listeners\SendNotification::class,
        ],
    ];
}
