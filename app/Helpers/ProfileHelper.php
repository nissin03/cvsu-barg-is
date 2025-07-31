<?php

namespace App\Helpers;

class ProfileHelper
{
      public static function isProfileIncomplete($user): bool
      {
            if ($user->role === 'student') {
                  return empty($user->name) ||
                        empty($user->email) ||
                        empty($user->phone_number) ||
                        empty($user->year_level) ||
                        empty($user->department) ||
                        empty($user->course);
            }

            return empty($user->name) ||
                  empty($user->email) ||
                  empty($user->phone_number);
      }
}
