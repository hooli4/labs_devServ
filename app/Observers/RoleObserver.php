<?php

namespace App\Observers;

use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Role $role): void
    {
        DB::transaction(function () use ($role) {
            try {
                foreach ($role->only([
                    'name', 'description', 'code'
                ]) as $field => $value) {
                    $role->logs()->create([
                        'field' => $field,
                        'old_value' => 'created',
                        'new_value' => $value,
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
     * Handle the Permission "updated" event.
     */
    public function updated(Role $role): void
    {
        DB::transaction(function () use ($role) {
            try {
                foreach ($role->getChanges() as $field => $newValue) {
                    $role->logs()->create([
                        'field' => $field,
                        'old_value' => $role->getOriginal($field),
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
     * Handle the Permission "deleted" event.
     */
    public function deleted(Role $role): void
    {
        if (count($role->getChanges()) == 1) {
            DB::transaction(function () use ($role) {
                try {
                    $role->logs()->create([
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
    }
    /**
     * Handle the Permission "restored" event.
     */
    public function restored(Role $role): void
    {
        
    }

    /**
     * Handle the Permission "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        DB::transaction(function () use ($role) {
            try {
                foreach ($role->only([
                    'name', 'code', 'description'
                ]) as $field => $old_value) {
                    $role->logs()->create([
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
