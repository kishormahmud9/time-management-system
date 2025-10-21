<?php

use App\Models\User;
use Illuminate\Support\Str;

if (!function_exists('generateUniqueUsername')) {
    function generateUniqueUsername($name)
    {
        $base = Str::of($name)
            ->lower()
            ->trim()
            ->replace(' ', '')
            ->replaceMatches('/[^a-z0-9_]/', '');
        $base = (string) $base;

        if (empty($base)) {
            $base = 'user';
        }

        $username = $base;
        $counter = 0;

        while (User::where('username', $username)->exists()) {
            $counter++;
            $username = $base . $counter;
        }

        return $username;
    }
}
