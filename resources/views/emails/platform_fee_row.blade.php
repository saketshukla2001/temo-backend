@php
    $platformFeeFor = $platformFeeFor ?? 'user';
    $platformFeeField = $platformFeeFor === 'driver' ? 'platform_fee_for_driver' : 'platform_fee_for_user';
    $platformFeeAmount = $data->requestBill->{$platformFeeField} ?? 0;
@endphp

@if($platformFeeAmount > 0)
<tr>
<td bgcolor="#ffffff">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tbody>
<tr>
<td border="0" cellpadding="0" cellspacing="0" valign="top" style="padding-left: 7px; padding-right: 13px; padding-bottom: 2px">
<table cellpadding="0" cellspacing="0" border="0" width="72%" style="width: 72%" align="left">
<tbody>
<tr>
<td style="padding: 0 0 0 0">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tbody>
<tr>
<td align="left">
<table>
<tbody>
<tr>
<td align="left" style="font-family: Helvetica, 'Helvetica Neue', Arial, sans-serif; color: #707070; font-size: 14px; font-weight: normal; line-height: 18px;">
Platform Fee
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="27%" style="width: 27%" align="right">
<tbody>
<tr>
<td style="padding: 0 0 0 0">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tbody>
<tr>
<td align="left">
<table align="right">
<tbody>
<tr>
<td style="font-family: Helvetica, 'Helvetica Neue', Arial, sans-serif; color: #707070; font-size: 14px; font-weight: normal; line-height: 18px;">
{{ $data->currency }}
{{ number_format($platformFeeAmount, 2) }}
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
<tr>
<td style="color: #ffffff; line-height: 8px !important; padding: 0 0 0 0; font-size: 13px">
<img style="width: 100%" src="{{ asset('invoice/Invoice_White_line.png') }}" class="CToWUd" />
</td>
</tr>
</tbody>
</table>
</td>
</tr>
@endif
