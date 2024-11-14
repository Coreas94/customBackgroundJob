<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\BackgroundJob;
use Exception;

class RunBackgroundJob extends Command
{
   /**
    * Command signature defining the required arguments and options.
    */
   protected $signature = 'app:background-job {class} {method} {--params=} {--delay=0} {--priority=1}';

   /**
    * Description of the command.
    */
   protected $description = 'Run a background job by specifying a class, method, and optional parameters';

   private const MAX_RETRIES = 3;
   private const ALLOWED_CLASSES = ['App\\Jobs\\ExampleJob'];

   /**
    * Main method to handle the execution of the background job command.
    */
   public function handle()
   {
      $className = $this->argument('class');
      $method = $this->argument('method');
      $params = $this->getParams();
      $delay = $this->getOptionValue('delay', 0);
      $priority = $this->getOptionValue('priority', 1);

      if (!$this->isClassAllowed($className)) {
         return $this->logUnauthorizedClassError($className, $method, $params, $delay, $priority);
      }

      $job = $this->createJobRecord($className, $method, $params, $priority);

      if ($delay > 0) {
         $this->applyDelay($delay);
      }

      $this->executeJobWithRetries($className, $method, $params, $priority, $job);
   }

   /**
    * Get the parameters from the command option and decode them as an array.
    */
   private function getParams(): array
   {
      return $this->option('params') ? json_decode($this->option('params'), true) : [];
   }

   /**
    * Retrieve the value of an option or use the default value if not specified.
    */
   private function getOptionValue(string $option, int $default): int
   {
      return $this->option($option) !== null ? (int)$this->option($option) : $default;
   }

   /**
    * Check if the given class is allowed to run as a background job.
    */
   private function isClassAllowed(string $className): bool
   {
      return in_array($className, self::ALLOWED_CLASSES);
   }

   /**
    * Log an error for an unauthorized class and create a failed job record.
    */
   private function logUnauthorizedClassError($className, $method, $params, $delay, $priority): void
   {
      $errorMessage = "Unauthorized class: {$className}";

      BackgroundJob::create([
         'class' => $className,
         'method' => $method,
         'params' => $params,
         'priority' => $priority,
         'status' => 'failed',
         'attempts' => 1,
         'error_message' => $errorMessage,
      ]);
      Log::channel('background_jobs_errors')->error($errorMessage);
      $this->error($errorMessage);
   }

   /**
    * Create an initial job record in the database with status 'running'.
    */
   private function createJobRecord(string $className, string $method, array $params, int $priority): BackgroundJob
   {
      return BackgroundJob::create([
         'class' => $className,
         'method' => $method,
         'params' => $params,
         'priority' => $priority,
         'status' => 'running',
         'attempts' => 0,
      ]);
   }

   /**
    * Apply a delay before starting the job execution.
    */
   private function applyDelay(int $delay): void
   {
      $this->info("Waiting {$delay} seconds before executing the job...");
      sleep($delay);
   }

   /**
    * Execute the job with a retry mechanism in case of failure.
    */
   private function executeJobWithRetries(string $className, string $method, array $params, int $priority, BackgroundJob $job): void
   {
      $attempt = 0;
      $success = false;

      while ($attempt < self::MAX_RETRIES && !$success) {
         try {
            $attempt++;
            $job->update(['attempts' => $attempt]);

            $this->validateClassAndMethod($className, $method);
            $this->runMethod($className, $method, $params);

            Log::channel('background_jobs')->info("Job executed successfully with priority {$priority} on attempt {$attempt}: {$className}@{$method}");
            $this->info("Job executed successfully with priority {$priority} on attempt {$attempt}: {$className}@{$method}");

            $job->update(['status' => 'completed']);
            $success = true;
         } catch (Exception $e) {
            $this->handleExecutionError($e, $attempt, $className, $method, $priority, $job);
         }
      }

      if (!$success) {
         $job->update(['status' => 'failed']);
         $this->error("Job permanently failed after " . self::MAX_RETRIES . " attempts: {$className}@{$method}");
      }
   }

   /**
    * Validate if the specified class and method exist.
    */
   private function validateClassAndMethod(string $className, string $method): void
   {
      if (!class_exists($className)) {
         throw new Exception("Class {$className} not found");
      }

      if (!method_exists($className, $method)) {
         throw new Exception("Method {$method} not found in {$className}");
      }
   }

   /**
    * Run the specified method with the provided parameters.
    */
   private function runMethod(string $className, string $method, array $params): void
   {
      $instance = new $className();
      call_user_func_array([$instance, $method], $params);
   }

   /**
    * Handle execution errors by logging and updating the job status.
    */
   private function handleExecutionError(Exception $e, int $attempt, string $className, string $method, int $priority, BackgroundJob $job): void
   {
      Log::channel('background_jobs_errors')->error("Error on attempt {$attempt} for {$className}@{$method} with priority {$priority}: {$e->getMessage()}");
      $this->error("Error on attempt {$attempt} for {$className}@{$method} with priority {$priority}: {$e->getMessage()}");

      if ($attempt >= self::MAX_RETRIES) {
         $job->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
         Log::channel('background_jobs_errors')->error("Job failed after " . self::MAX_RETRIES . " attempts with priority {$priority}: {$className}@{$method}");
      }
   }
}
