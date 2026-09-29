<?php

namespace App\Services;

use App\Models\Slider;
use App\Repositories\SliderRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class SliderService
{
    protected SliderRepository $sliderRepository;

    public function __construct(SliderRepository $sliderRepository)
    {
        $this->sliderRepository = $sliderRepository;
    }

    /**
     * Get all sliders
     */
    public function getAllSliders(): Collection
    {
        return $this->sliderRepository->getAllSliders();
    }

    /**
     * Get a slider by ID
     */
    public function getSliderById(int $id): ?Slider
    {
        return $this->sliderRepository->getSliderById($id);
    }

    /**
     * Create a new slider
     */
    public function createSlider(array $sliderData): Slider
    {
        if (isset($sliderData['image'])) {
            $sliderData['image'] = $sliderData['image']->store('sliders', 'public');
        }

        return $this->sliderRepository->createSlider($sliderData);
    }

    /**
     * Update a slider
     */
    public function updateSlider(int $id, array $sliderData): ?Slider
    {
        $slider = $this->sliderRepository->getSliderById($id);
        if (! $slider) {
            return null;
        }
        if (isset($sliderData['image'])) {
            if ($slider->getRawOriginal('image') && Storage::disk('public')->exists($slider->getRawOriginal('image'))) {
                Storage::disk('public')->delete($slider->getRawOriginal('image'));
            }
            $sliderData['image'] = $sliderData['image']->store('sliders', 'public');
        }

        return $this->sliderRepository->updateSlider($id, $sliderData);
    }

    /**
     * Delete a slider
     */
    public function deleteSlider(int $id): ?bool
    {
        $slider = $this->sliderRepository->getSliderById($id);
        if ($slider) {
            if ($slider->image && Storage::disk('public')->exists($slider->image)) {
                Storage::disk('public')->delete($slider->image);
            }

            return $this->sliderRepository->deleteSlider($id);
        }

        return false;
    }

    /**
     * Restore a soft deleted slider
     */
    public function restoreSlider(int $id): bool
    {
        return $this->sliderRepository->restoreSlider($id);
    }
}
