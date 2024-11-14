<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExampleJob implements ShouldQueue
{
   use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   protected $message;

   /**
    * Create a new job instance.
    *
    * @param string $message
    */
   public function __construct($message = 'Hello, Background Job!')
   {
      $this->message = $message;
   }

   /**
    * Execute the job.
    */
   public function handle()
   {
      Log::info("Executing ExampleJob with message: {$this->message}");
      echo "Executing ExampleJob with message: {$this->message}\n";
   }
}
