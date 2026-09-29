<?php

namespace App\Repositories;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Collection;

class SliderRepository
{
    protected Slider $slider;

    public function __construct(Slider $slider)
    {
        $this->slider = $slider;
    }

    /**
     * Get all sliders
     */
    public function getAllSliders(): Collection
    {
        return $this->slider->all();
    }

    /**
     * Get a slider by ID
     */
    public function getSliderById(int $id): ?Slider
    {
        return $this->slider->find($id);
    }

    /**
     * Create a new slider
     */
    public function createSlider(array $sliderData): Slider
    {
        return $this->slider->create($sliderData);
    }

    /**
     * Update a slider
     */
    public function updateSlider(int $id, array $sliderData): ?Slider
    {
        $slider = $this->slider->find($id);
        if ($slider) {
            $slider->update($sliderData);

            return $slider->fresh();
        }

        return null;
    }

    /**
     * Delete a slider
     */
    public function deleteSlider(int $id): ?bool
    {
        return $this->slider->find($id)->delete();
    }

    /**
     * Restore a soft deleted slider
     */
    public function restoreSlider(int $id): bool
    {
        return $this->slider->find($id)->restore();
    }
}
