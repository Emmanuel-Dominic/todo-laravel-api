<?php
namespace App\Http\Traits;

use DateTimeInterface;
use Carbon\Carbon;

trait LocalizedDiffForHumansTrait
  {
      /**
       * Prepare a date for array / JSON serialization.
       *
       * @param  \DateTimeInterface  $date
       * @return string
       */
      protected function serializeDate(DateTimeInterface $date)
      {
          return Carbon::instance($date)->diffForHumans();
      }

  }
