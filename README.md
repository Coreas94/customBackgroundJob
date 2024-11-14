
# Background Job Runner System for Laravel

This project is a custom background job runner system implemented in a Laravel environment. It allows you to run PHP classes as background jobs independently of Laravel's built-in queue system. The system includes retry logic, delay, priority settings, and a web dashboard for monitoring jobs.

## Table of Contents
- [Setup Instructions](#setup-instructions)
- [Usage](#usage)
  - [Using the `runBackgroundJob` Function](#using-the-runbackgroundjob-function)
  - [Running Jobs with Delay, Priority, and Retry](#running-jobs-with-delay-priority-and-retry)
  - [Security Settings](#security-settings)
- [Advanced Features](#advanced-features)
  - [Job Dashboard](#job-dashboard)
- [Testing and Logs](#testing-and-logs)
  - [Sample Usage](#sample-usage)
  - [Log Files](#log-files)

## Setup Instructions

1. **Clone the Repository**: 
   ```bash
   git clone https://github.com/Coreas94/customBackgroundJob.git
   cd <repository-folder>
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment**:
   - Set up your `.env` file for database connection, logging, and other Laravel settings.
   - Make sure you have configured the necessary log channels in `config/logging.php`.

4. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

5. **Set Up Logging Channels**:
   Add the following log channels in `config/logging.php` to capture job logs and error logs:
   ```php
   'background_jobs' => [
       'driver' => 'single',
       'path' => storage_path('logs/background_jobs.log'),
       'level' => 'debug',
    ],
    'background_jobs_errors' => [
       'driver' => 'single',
       'path' => storage_path('logs/background_jobs_errors.log'),
       'level' => 'error',
    ],
   ```

## Usage

### Using the `runBackgroundJob` Function

The `runBackgroundJob` helper function initiates a background job by specifying the class, method, and optional parameters.

#### Syntax:
```php
runBackgroundJob($class, $method, $params = []);
```

#### Example:
```php
runBackgroundJob(App\Jobs\ExampleJob::class, 'handle', ['message' => 'Hello, Background Job!']);
```

- **Class**: The fully qualified class name of the job (e.g., `App\Jobs\ExampleJob`).
- **Method**: The method to execute (default is `handle` for Laravel jobs).
- **Params**: Parameters to pass to the job (optional).

### Running Jobs with Delay, Priority, and Retry

You can configure delay, priority, and retry options through the `RunBackgroundJob` command.

#### Command Signature:
```bash
php artisan app:background-job {class} {method} {--params=} {--delay=0} {--priority=1}
```

#### Parameters:
- **class**: The class name of the job to run.
- **method**: The method within the class to execute.
- **params**: JSON string of parameters for the method.
- **delay**: Delay in seconds before executing the job.
- **priority**: Priority level of the job (1 = low, 3 = high).

#### Example of a Successful Job:
```bash
php artisan app:background-job "App\Jobs\ExampleJob" "handle" --params='{"message": "Hello, World!"}' --delay=5 --priority=2
```

#### Example of a Failed Job (Unauthorized Class):
```bash
php artisan app:background-job "App\Jobs\UnauthorizedJob" "handle"
```

This will fail if `App\Jobs\UnauthorizedJob` is not in the allowed classes list. The job status will be set to `failed` with the error message "Unauthorized class."

### Security Settings

Only specified classes are allowed to run as background jobs. Modify the list of allowed classes in `RunBackgroundJob` by updating the `ALLOWED_CLASSES` array:
```php
private const ALLOWED_CLASSES = ['App\Jobs\ExampleJob'];
```

## Advanced Features

### Job Dashboard

A web dashboard provides an interface to monitor active, completed, and failed jobs, along with retry and cancel options.

#### Access the Dashboard:
Visit the `/` route in your application to view the dashboard.

#### Features:
- **Filter by Status**: Filter jobs by `running`, `completed`, or `failed` status.
- **Filter by Priority**: Filter jobs based on priority (1 = low, 3 = high).
- **Retry Failed Jobs**: Retry jobs that have failed or been manually canceled.
- **Cancel Running Jobs**: Cancel jobs that are currently running.

### Example of the Dashboard Layout:
<img width="1021" alt="image" src="https://github.com/user-attachments/assets/8e3422aa-758a-437f-a9a7-681409c0d710">

## Testing and Logs

### Sample Usage

#### Successful Execution
```bash
php artisan app:background-job "App\Jobs\ExampleJob" "handle" --params='{"message": "Success Test"}'
```
This command will successfully log the execution in `background_jobs.log`.

#### Failed Execution (Unauthorized Class)
```bash
php artisan app:background-job "App\Jobs\UnauthorizedJob" "handle"
```
This command will log the failure in `background_jobs_errors.log` with the error message "Unauthorized class."

### Log Files

Logs for background jobs are stored in the following locations:
- **Successful Executions**: `storage/logs/background_jobs.log`
- **Failed Executions and Errors**: `storage/logs/background_jobs_errors.log`

Example log entry for a successful job:
```
[2024-11-14 12:34:56] background_jobs.INFO: Job executed successfully with priority 1 on attempt 1: App\Jobs\ExampleJob@handle
```

Example log entry for a failed job:
```
[2024-11-14 12:35:00] background_jobs_errors.ERROR: Error on attempt 1 for App\Jobs\ExampleJob@handle with priority 1: Unauthorized class: App\Jobs\UnauthorizedJob
```
---

This documentation covers the setup, usage, and testing of the background job runner system. With this setup, you can efficiently manage and monitor background jobs, prioritize tasks, and handle failures gracefully.
