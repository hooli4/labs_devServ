<?php

namespace App\Observers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        DB::transaction(function () use ($user) {
            try {
                foreach ($user->only([
                    'name', 'password', 'email', 'birthday'
                ]) as $field => $value) {
                    $user->logs()->create([
                        'field' => $field,
                        'old_value' => 'created',
                        'new_value' => $value,
                        'created_at' => Carbon::now(),
                        'created_by' => $user->id,
                    ]);
                }   
            } 
            catch (\Exception $e) {
                throw $e;
            }
        });
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        DB::transaction(function () use ($user) {
            try {
                foreach ($user->getChanges() as $field => $newValue) {
                    $user->logs()->create([
                        'field' => $field,
                        'old_value' => $user->getOriginal($field),
                        'new_value' => $newValue,
                        'created_at' => Carbon::now(),
                        'created_by' => Auth::user()->id,
                    ]);
                }
            } 
            catch (\Exception $e) {
                throw $e;
            }
        });
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        DB::transaction(function () use ($user) {
            try {
                $user->logs()->create([
                    'field' => 'deleted_at',
                    'old_value' => null,
                    'new_value' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'created_by' => Auth::user()->id,
                ]);
            }
            catch (\Exception $e) {
                throw $e;
            }
        });
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        DB::transaction(function () use ($user) {
            try {
                foreach ($user->only([
                    'name', 'password', 'email', 'birthday'
                ]) as $field => $old_value) {
                    $user->logs()->create([
                        'field' => $field,
                        'old_value' => $old_value,
                        'new_value' => 'deleted',
                        'created_at' => Carbon::now(),
                        'created_by' => Auth::user()->id,
                    ]);
                }
            }
            catch (\Exception $e) {
                throw $e;
            }
        });
    }
}
