@props(['value'])
{{ rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') }}
