<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sliders\CreateRequest;
use App\Http\Requests\Admin\Sliders\UpdateRequest;
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

        return view('admin.sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateRequest $request)
    {
        $this->service->createSlider($request->validated());

        return redirect()->route('admin.sliders.index')->with('success', __('Slider created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $slider = $this->service->getSliderById($id);

        return view('admin.sliders.show', compact('slider'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $slider = $this->service->getSliderById($id);

        return view('admin.sliders.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $slider = $this->service->getSliderById($id);
        if (! $slider) {
            return redirect()->route('admin.sliders.index')->with('error', __('Slider not found.'));
        }

        $this->service->updateSlider($id, $request->validated());

        return redirect()->route('admin.sliders.index')->with('success', __('Slider updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->service->deleteSlider($id);

        return redirect()->route('admin.sliders.index')->with('success', __('Slider deleted successfully.'));
    }
}
