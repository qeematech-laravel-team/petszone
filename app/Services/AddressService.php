<?php

namespace App\Services;

use App\Models\Address;
use App\Repositories\AddressRepository;

class AddressService
{
    public function __construct(
        protected AddressRepository $addressRepo
    ) {}

    public function getUserAddresses(int $userId)
    {
        return $this->addressRepo->allByUser($userId);
    }

    public function getUserAddressById(int $id, int $userId)
    {
        return $this->addressRepo->findById($id, $userId);
    }

    public function store(array $data, int $userId)
    {
        $data['user_id'] = $userId;

        // make this address default if none exists
        if (! $this->addressRepo->allByUser($userId)) {
            $data['is_default'] = true;
        }

        return $this->addressRepo->create($data);
    }

    public function update(Address $address, array $data)
    {
        return $this->addressRepo->update($address, $data);
    }

    public function delete(Address $address)
    {
        return $this->addressRepo->delete($address);
    }
}
