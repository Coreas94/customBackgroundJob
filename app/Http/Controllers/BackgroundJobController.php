<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BackgroundJob;

class BackgroundJobController extends Controller
{
   public function index(Request $request)
   {
      $query = BackgroundJob::query();

      // Apply filters if present
      if ($request->filled('status')) {
         $query->where('status', $request->status);
      }

      if ($request->filled('priority')) {
         $query->where('priority', $request->priority);
      }

      // Retrieve paginated jobs with sorting by priority and creation date
      $jobs = $query->orderBy('priority', 'desc')
         ->orderBy('created_at', 'desc')
         ->paginate(10);

      return view('background_jobs.index', compact('jobs'));
   }

   public function cancel($id)
   {
      $job = BackgroundJob::find($id);
      if ($job && $job->status === 'running') {
         // Update the job status to 'failed' to indicate it was manually canceled
         $job->update(['status' => 'failed', 'error_message' => 'Job was manually canceled.']);
         return back()->with('status', 'Job canceled successfully');
      }
      return back()->with('error', 'Job could not be canceled');
   }

   public function retry($id)
   {
      $job = BackgroundJob::find($id);

      if ($job && $job->status === 'failed') {
         // Reset job status and attempt count for retry
         $job->update(['status' => 'running', 'attempts' => 0, 'error_message' => null]);

         // Dynamically dispatch job with class and parameters
         $className = $job->class;
         if (class_exists($className)) {
            $instance = new $className(...$job->params);
            dispatch($instance);
            return back()->with('status', 'Job reattempted successfully');
         }

         return back()->with('status', 'Job reattempted successfully');
      }

      return back()->with('error', 'Job could not be retried');
   }
}
