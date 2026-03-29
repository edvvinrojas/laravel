<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        AuditService::log('CREATE', $this->module($model), $model->id, 'Registro creado');
    }

    public function updated(Model $model): void
    {
        $changed = array_keys($model->getChanges());
        if (empty($changed) || $changed === ['updated_at']) return;
        AuditService::log('UPDATE', $this->module($model), $model->id, 'Campos: ' . implode(', ', $changed));
    }

    public function deleted(Model $model): void
    {
        AuditService::log('DELETE', $this->module($model), $model->id, 'Registro eliminado');
    }

    private function module(Model $model): string
    {
        return class_basename($model);
    }
}
