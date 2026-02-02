@php

    $state = $getState();

    if( is_string($state ))
        $qrcode =  \Rotaz\FilamentAccounts\Utils\QrCodeUtil::generateQrCodeImage($state);


@endphp

<div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }">
    @if( !empty($qrcode))
        <img src="data:image/png;base64,{{$qrcode}}" alt="QR Code" class="h-auto" >
    @endif
</div>



