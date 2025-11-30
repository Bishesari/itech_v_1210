<div class="p-4 border rounded shadow w-96 mx-auto mt-10">
    <h2 class="text-lg font-bold mb-4">{{ $product->name }}</h2>
    <p class="mb-4">{{ $product->description }}</p>
    <p class="mb-4 font-semibold">قیمت: {{ number_format($product->price) }} ریال</p>

    <input type="text" wire:model="cellNumber" placeholder="شماره همراه" class="w-full p-2 border rounded mb-4">

    <button wire:click="initiatePayment" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
        پرداخت
    </button>

    @if(session()->has('error'))
        <p class="text-red-500 mt-2">{{ session('error') }}</p>
    @endif
    @if(session()->has('success'))
        <p class="text-green-500 mt-2">{{ session('success') }}</p>
    @endif
</div>
