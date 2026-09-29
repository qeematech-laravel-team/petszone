@php
    $categoryName = is_array($category->name)
        ? ($category->name[app()->getLocale()] ?? reset($category->name))
        : $category->name;
    $padding = ($level + 1) * 24 ;
    $prefix = $level > 0 ? '— ' : '';
    $checked = $selected == $category->id ? 'checked' : '';
@endphp

<div class="form-check mb-2" style="padding-inline-start: {{ $padding }}px;">
    <input class="form-check-input" type="radio" name="parent_id"
        id="parent_{{ $category->id }}" value="{{ $category->id }}" {{ $checked }}>
    <label class="form-check-label" for="parent_{{ $category->id }}">
        {!! $prefix !!}{!! $categoryName !!}
    </label>
</div>

@if($category->children && $category->children->count() > 0)
    @foreach($category->children as $child)
        @include('admin.categories.partials.category-tree-item', [
            'category' => $child,
            'selected' => $selected,
            'level' => $level + 1
        ])
    @endforeach
@endif
