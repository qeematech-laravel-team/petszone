@if($products->hasPages())
    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endif
