<?php

namespace App\Observers;

use App\Events\ModelChanged;
use Illuminate\Database\Eloquent\Model;

class GeneralObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        event(new ModelChanged('created', $model, $model->toArray()));
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        // Don't log if only timestamps changed
        unset($changes['updated_at']);
        
        if (!empty($changes)) {
            event(new ModelChanged('updated', $model, $changes));
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        event(new ModelChanged('deleted', $model, ['id' => $model->getKey()]));
    }
}
