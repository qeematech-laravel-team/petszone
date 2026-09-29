@if($subscriptions->hasPages())
    <div class="mt-4">
        {{ $subscriptions->links() }}
    </div>
@endif
