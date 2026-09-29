<?php

namespace App\Repositories;

use App\Models\Address;

class AddressRepository
{
    protected $model;

    public function __construct(Address $address)
    {
        $this->model = $address;
    }

    public function allByUser(int $userId)
    {
        return $this->model->where('user_id', $userId)->active()->get();
    }

    public function findById(int $id, int $userId)
    {
        return $this->model->where('user_id', $userId)->findOrFail($id);
    }

    public function create(array $data): Address
    {
        return $this->model->create($data);
    }

    public function update(Address $address, array $data): Address
    {
        $address->update($data);

        return $address;
    }

    public function delete(Address $address): bool
    {
        return $address->update(['is_active' => false]);
    }
}
