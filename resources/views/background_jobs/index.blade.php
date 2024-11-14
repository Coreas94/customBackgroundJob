@extends('layouts.app')

@section('content')
<div class="container">
   <h1>Background Job Dashboard</h1>

   <form method="GET" action="{{ route('background_jobs.index') }}" class="mb-4">
      <label for="status">Status:</label>
      <select name="status" id="status" onchange="this.form.submit()">
         <option value="">All Status</option>
         <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
         <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
         <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
      </select>

      <label for="priority">Priority:</label>
      <select name="priority" id="priority" onchange="this.form.submit()">
         <option value="">All Priorities</option>
         <option value="1" {{ request('priority') == 1 ? 'selected' : '' }}>Low</option>
         <option value="2" {{ request('priority') == 2 ? 'selected' : '' }}>Medium</option>
         <option value="3" {{ request('priority') == 3 ? 'selected' : '' }}>High</option>
      </select>
   </form>

   <table class="table table-bordered">
      <thead>
         <tr>
            <th>ID</th>
            <th>Class</th>
            <th>Method</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Attempts</th>
            <th>Error Message</th>
            <th>Created At</th>
            <th>Last Updated</th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
         @foreach($jobs as $job)
         <tr class="{{ $job->status === 'completed' ? 'table-success' : ($job->status === 'failed' ? 'table-danger' : 'table-warning') }}">
            <td>{{ $job->id }}</td>
            <td>{{ $job->class }}</td>
            <td>{{ $job->method }}</td>
            <td>{{ $job->priority }}</td>
            <td>{{ ucfirst($job->status) }}</td>
            <td>{{ $job->attempts }}</td>
            <td>{{ $job->error_message }}</td>
            <td>{{ $job->created_at }}</td>
            <td>{{ $job->updated_at }}</td>
            <td>
               @if($job->status === 'running')
               <form action="{{ route('background_jobs.cancel', $job->id) }}" method="POST" style="display:inline;">
                  @csrf
                  <button type="submit" class="btn btn-danger">Cancel</button>
               </form>
               @elseif($job->status === 'failed' && ($job->error_message === 'Job was manually canceled.' || !$job->error_message))
               <form action="{{ route('background_jobs.retry', $job->id) }}" method="POST" style="display:inline;">
                  @csrf
                  <button type="submit" class="btn btn-warning">Retry</button>
               </form>
               @endif
            </td>
         </tr>
         @endforeach
      </tbody>
   </table>

   {{ $jobs->links('pagination::bootstrap-4') }}

</div>
@endsection