<div class="p-4 border rounded shadow w-96 mx-auto mt-10">
    <h2 class="text-lg font-bold mb-4">{{ $product->name }}</h2>
    <p class="mb-4">{{ $product->description }}</p>
    <p class="mb-4 font-semibold">قیمت: {{ number_format($product->price) }} ریال</p>

    @auth
    <input type="text" wire:model="cellNumber" placeholder="شماره همراه" class="w-full p-2 border rounded mb-4">
    <button wire:click="initiatePayment" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
        پرداخت
    </button>
    @endauth

    @guest
        <flux:text>برای خرید محصول وارد شوید.</flux:text>
        <flux:modal.trigger name="login">
            <flux:button variant="subtle" size="sm" class="cursor-pointer">
                {{__('ورود')}}
            </flux:button>
        </flux:modal.trigger>
    @endguest


    @if(session()->has('error'))
        <p class="text-red-500 mt-2">{{ session('error') }}</p>
    @endif
    @if(session()->has('success'))
        <p class="text-green-500 mt-2">{{ session('success') }}</p>
    @endif
</div>

<script>
    document.addEventListener('reloadPage', () => {
        location.reload();
    });
</script>
