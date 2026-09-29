@if($categories->hasPages())
    <div class="mt-3">
        {{ $categories->links() }}
    </div>
@endif
