<x-filament-panels::page>
    <p>Buat pembayaran untuk beberapa tagihan dalam sekali input</p>
    <small style="color: red;">*) hati-hati dalam pemilihan tagihan, jangan sampai tagihan dipilih berulang. Periksa kembali sebelum menyimpan</small>
    <form wire:submit.prevent="create"> 
        {{ $this->form }}

        <div style="width: 100%; height:20px;"></div>
        <x-filament::actions 
            :actions="$this->getFormActions()" 
            alignment="left" 
            class="mt-6"
        />
    </form>
</x-filament-panels::page>
