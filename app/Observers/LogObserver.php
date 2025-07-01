<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;

/**
 * LogObserver Class
 * 
 * This observer class is responsible for automatically logging all database operations
 * (create, update, delete) performed on models that are being observed. It provides
 * an audit trail for tracking changes to data in the application.
 * 
 * The observer logs:
 * - Table name where the operation occurred
 * - Type of operation (created, updated, deleted)
 * - Old data (for updates and deletes)
 * - New data (current state after operation)
 * - User ID who performed the operation
 * - Timestamp of the operation
 */
class LogObserver {
    
    /**
     * Handle the "created" event for the model.
     * 
     * This method is automatically called when a new record is created in the database.
     * It logs the creation operation with the new data.
     */
    public function created($model) {
        $this->logChange('created', $model);
    }

    /**
     * Handle the "updated" event for the model.
     * 
     * This method is automatically called when an existing record is updated in the database.
     * It logs the update operation with both old and new data for comparison.
     */
    public function updated($model) {
        $this->logChange('updated', $model, $model->getOriginal());
    }

    /**
     * Handle the "deleted" event for the model.
     * 
     * This method is automatically called when a record is deleted from the database.
     * It logs the deletion operation with the original data before deletion.
     */
    public function deleted($model) {
        $this->logChange('deleted', $model, $model->getOriginal());
    }

    /**
     * Log the database operation to the logs table.
     * 
     * This protected method handles the actual logging logic by inserting a record
     * into the 'logs' table with all relevant information about the operation.
     */
    protected function logChange($operation, $model, $oldData = null) {
        \DB::table('logs')->insert([
            'table_name' => $model->getTable(),        // Name of the table where operation occurred
            'operation' => $operation,                 // Type of operation performed
            'old_data' => $oldData ? json_encode($oldData) : null,  // Previous data (JSON encoded)
            'new_data' => json_encode($model->getAttributes()),     // Current data (JSON encoded)
            'user_id' => Auth::id(),                   // ID of the authenticated user
            'created_at' => now(),                     // Timestamp when log was created
            'updated_at' => now(),                     // Timestamp when log was last updated
        ]);
    }
}
