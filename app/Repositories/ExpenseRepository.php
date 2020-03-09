<?php

namespace App\Repositories;

use App\Company;
use App\Factory\ExpenseFactory;
use App\Expense;
use App\Repositories\Base\BaseRepository;
use App\Repositories\VSendorContactRepository;
use App\Traits\GeneratesCounter;
use Illuminate\Http\Request;

/**
 * ExpenseRepository
 */
class ExpenseRepository extends BaseRepository
{
    use GeneratesCounter;

    /**
     * ExpenseRepository constructor.
     * @param Expense $expense
     */
    public function __construct(Expense $expense)
    {
        parent::__construct($expense);
        $this->model = $expense;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param int $id
     * @return Expense
     */
    public function findExpenseById(int $id): Expense
    {
        return $this->findOneOrFail($id);
    }

    /**
     * Saves the expense and its contacts
     *
     * @param array $data The data
     * @param \App\Models\expense $expense The expense
     *
     * @return     expense|\App\Models\expense|null  expense Object
     */
    public function save(array $data, Expense $expense): ?Expense
    {
        $expense->fill($data);

        $expense->save();

// if ($expense->id_number == "" || !$expense->id_number) {
//     $expense->id_number = $this->getNextExpenseNumber($expense);
// } //todo write tests for this and make sure that custom expense numbers also works as expected from here

        return $expense;
    }

    /**
     * Store expenses in bulk.
     *
     * @param array $expense
     * @return expense|null
     */
    public function create($expense): ?Expense
    {
        return $this->save($expense, ExpenseFactory::create(auth()->user()->company()->id, auth()->user()->id));
    }
}
