<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VariantService;
use Illuminate\View\View;

class VariantController extends Controller
{
    protected VariantService $variantService;

    public function __construct(VariantService $variantService)
    {
        $this->variantService = $variantService;
    }

    /**
     * Display a listing of active variants for vendors.
     */
    public function index(): View
    {
        $variants = $this->variantService->getActiveVariants();
        return view('vendor.variants.index', compact('variants'));
    }
}
