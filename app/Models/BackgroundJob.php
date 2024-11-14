<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackgroundJob extends Model
{
   use HasFactory;

   protected $table = 'background_jobs';
   protected $fillable = [
      'class', 'method', 'params', 'priority', 'attempts', 'status', 'error_message'
   ];

   protected $casts = [
      'params' => 'array',
   ];
}
