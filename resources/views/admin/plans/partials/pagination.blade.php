@if($plans->hasPages())
    <div class="mt-3">
        {{ $plans->links() }}
    </div>
@endif
