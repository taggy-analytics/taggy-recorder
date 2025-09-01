<?php

namespace App\Actions;

use App\Enums\TransactionAction;
use App\Models\Transaction;
use Illuminate\Support\Arr;

class CleanTransactions
{
    public function execute($endpoint, $entityId, $lastTransactionsResetAt = null)
    {
        $modalTransactions = $this->query($endpoint, $entityId, $lastTransactionsResetAt)
            ->orderBy('created_at')
            ->get();

        $cleanedTransactions = [];

        $modalTransactions
            ->where('action', TransactionAction::DELETE)
            ->each(function (Transaction $deleteTransaction) use (&$cleanedTransactions) {
                $cleanedTransactions[$deleteTransaction->model_type][$deleteTransaction->model_id]['delete'] = $deleteTransaction;
            });

        $modalTransactions
            ->where('action', '<>', TransactionAction::DELETE)
            ->each(function (Transaction $transaction) use (&$cleanedTransactions) {
                if (Arr::has($cleanedTransactions, $transaction->model_type.'.'.$transaction->model_id.'.delete')) {
                    return;
                } elseif ($transaction->action == TransactionAction::CREATE) {
                    $cleanedTransactions[$transaction->model_type][$transaction->model_id]['create'] = $transaction;
                } elseif ($transaction->action == TransactionAction::UPDATE) {
                    $cleanedTransactions[$transaction->model_type][$transaction->model_id]['update'][$transaction->property] = $transaction;
                } else {
                    $cleanedTransactions[$transaction->model_type][$transaction->model_id]['relation'][$transaction->property][serialize($transaction->value)] = $transaction;
                }
            });

        $cleanedTransactions = Arr::flatten($cleanedTransactions);

        $this->query($endpoint, $entityId, $lastTransactionsResetAt)
            ->whereNotIn('id', Arr::pluck($cleanedTransactions, 'id'))
            ->delete();

        return $cleanedTransactions;
    }

    private function query($endpoint, $entityId, $lastTransactionsResetAt)
    {
        return Transaction::query()
            ->where('endpoint', $endpoint)
            ->where('entity_id', $entityId)
            ->when(filled($lastTransactionsResetAt), fn ($query) => $query->where('created_at', '>', $lastTransactionsResetAt));
    }
}
