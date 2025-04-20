<?php

namespace App\Observers;

use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        DB::transaction(function () use ($permission) {
            try {
                foreach ($permission->only([
                    'name', 'description', 'code'
                ]) as $field => $value) {
                    $permission->logs()->create([
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
    public function updated(Permission $permission): void
    {
        DB::transaction(function () use ($permission) {
            try {
                foreach ($permission->getChanges() as $field => $newValue) {
                    $permission->logs()->create([
                        'field' => $field,
                        'old_value' => $permission->getOriginal($field),
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
    public function deleted(Permission $permission): void
    {
        if (count($permission->getChanges()) == 1) {
            DB::transaction(function () use ($permission) {
                try {
                    $permission->logs()->create([
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
    public function restored(Permission $permission): void
    {
        
    }

    /**
     * Handle the Permission "force deleted" event.
     */
    public function forceDeleted(Permission $permission): void
    {
        DB::transaction(function () use ($permission) {
            try {
                foreach ($permission->only([
                    'name', 'code', 'description'
                ]) as $field => $old_value) {
                    $permission->logs()->create([
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
