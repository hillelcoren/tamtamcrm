<?php

namespace App\Repositories;

use App\Account;
use App\Models\Company;
use App\Repositories\Base\BaseRepository;
use Illuminate\Http\Request;

/**
 * AccountRepository
 */
class AccountRepository extends BaseRepository
{
    /**
     * AccountRepository constructor.
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        parent::__construct($account);
        $this->model = $account;
    }

    /**
     * Gets the class name.
     *
     * @return     string The class name.
     */
    public function getClassName()
    {
        return Account::class;
    }

    /**
     * @param int $id
     * @return Account
     */
    public function findAccountById(int $id): Account
    {
        return $this->findOneOrFail($id);
    }

    /**
     * Saves the client and its contacts
     *
     * @param array $data The data
     * @param Account
     * $client  The Account
     *
     * @return     Client|Company|null  Company Object
     */
    public function save(array $data, Account $account): ?Account
    {
        if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
            $data['custom_fields'] = $this->parseCustomFields($data['custom_fields']);
        }

        $account->fill($data);
        $account->save();
        return $account;

    }

    private function parseCustomFields($fields): array
    {
        foreach ($fields as &$value) {
            $value = (string)$value;
        }
        return $fields;
    }
}
