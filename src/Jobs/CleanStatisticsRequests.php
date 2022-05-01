<?php

declare(strict_types=1);

namespace Rovereto\Metrika\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Rovereto\Metrika\Models\Request;

class CleanStatisticsRequests implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        !config('metrika.lifetime') || Request::where('created_at', '<=', Carbon::now()->subDays(config('metrika.lifetime')))->delete();
    }
}
