<?php

namespace App\Actions;

use App\Enums\ModelTransactionAction;
use App\Models\Transaction;
use Illuminate\Support\Arr;

class CleanTransactions
{
    public function execute($entityId)
    {
        $modalTransactions = Transaction::where('entity_id', $entityId)->get();

        $cleanedTransactions = [];

        $modalTransactions
            ->where('action', ModelTransactionAction::DELETE)
            ->each(function (Transaction $deleteTransaction) use (&$cleanedTransactions) {
                $cleanedTransactions[$deleteTransaction->model_type][$deleteTransaction->model_id]['delete'] = $deleteTransaction;
            });

        $modalTransactions
            ->where('action', '<>', ModelTransactionAction::DELETE)
            ->each(function (Transaction $transaction) use (&$cleanedTransactions) {
                if(Arr::has($cleanedTransactions, $transaction->model_type . '.' . $transaction->model_id . '.delete')) {
                    return;
                }
                elseif($transaction->action == ModelTransactionAction::CREATE) {
                    $cleanedTransactions[$transaction->model_type][$transaction->model_id]['create'] = $transaction;
                }
                elseif($transaction->action == ModelTransactionAction::UPDATE) {
                    $cleanedTransactions[$transaction->model_type][$transaction->model_id]['update'][$transaction->property] = $transaction;
                }
                else {
                    $cleanedTransactions[$transaction->model_type][$transaction->model_id]['relation'][$transaction->property][serialize($transaction->value)] = $transaction;
                }
            });

        $cleanedTransactions = Arr::flatten($cleanedTransactions);

        Transaction::whereNotIn('id', Arr::pluck($cleanedTransactions, 'id'))
            ->delete();

        return $cleanedTransactions;
    }
}
