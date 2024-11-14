<?php

function runBackgroundJob($class, $method, $params = [])
{
   $params = json_encode($params);
   $command = "php artisan app:background-job {$class} {$method} --params='{$params}'";

   if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // Windows
      pclose(popen("start /B {$command}", "r"));
   } else {
      // Unix
      exec("{$command} > /dev/null &");
   }
}
