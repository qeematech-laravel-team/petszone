<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Services\VendorService;

class VendorController extends Controller
{
    protected VendorService $service;

    public function __construct(VendorService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $perPage = request()->get('per_page', 15);
        $filters = [
            'search' => request()->get('search', ''),
            'featured' => request()->get('featured', ''),
            'status' => 'active',
        ];
        $vendors = $this->service->getPaginatedVendors($perPage, $filters);

        return VendorResource::collection($vendors);
    }

    public function show($id)
    {
        $vendor = $this->service->getVendorById((int) $id, activeProductsOnly: true);

        return new VendorResource($vendor);
    }
}
