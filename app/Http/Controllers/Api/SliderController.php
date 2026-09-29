<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Services\SliderService;

class SliderController extends Controller
{
    protected SliderService $service;

    public function __construct(SliderService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = $this->service->getAllSliders();

        return SliderResource::collection($sliders);
    }
}
