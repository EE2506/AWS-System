@php
    $normalizedDescription = str_replace(["\r\n", "\r"], "\n", (string) $description);
    $descriptionLines = explode("\n", $normalizedDescription);
    $descriptionTitle = array_shift($descriptionLines);
    $descriptionBody = implode("\n", $descriptionLines);
@endphp

<div class="item-description-title">{{ $descriptionTitle }}</div>
@if($descriptionBody !== '')
    <div class="item-description-body">{!! nl2br(e($descriptionBody), false) !!}</div>
@endif
