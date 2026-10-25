<div class="row p-0 m-0">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="col-sm-12 p-0">
                    <table class="table table-hover" style="border-collapse: collapse; border-spacing: 0px; width: 100%;">
                        <thead>
                            <tr>
                                <th>{{ trans('view_pages.s_no') }}</th>
                                <th>User</th>
                                <th>Vehicle</th>
                                <th>License Number</th>
                                <th>Ride Date & Time</th>
                                <th>Price / KM</th>
                                <th>Total Capacity</th>
                                <th>Booked</th>
                                <th>Available</th>
                                <th>Ride Status</th>
                                <th>{{ trans('view_pages.status') }}</th>
                                <th>{{ trans('view_pages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($results) == 0)
                                <tr>
                                    <td class="no-result" colspan="12">{{ trans('view_pages.no_data_found') }}</td>
                                </tr>
                            @endif

                            @php
                                $i = $results->firstItem();
                            @endphp

                            @foreach ($results as $result)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $result->user->name ?? '-' }}</td>
                                    <td>
                                        @if($result->fleet)
                                            {{ $result->fleet->car_make_name ?? '-' }}
                                            {{ $result->fleet->car_model_name ?? '' }}
                                        @elseif($result->vehicleType)
                                            {{ $result->vehicleType->name }}
                                        @else
                                            -
                                        @endif
                                        @if($result->fleet && $result->fleet->vehicleType)
                                            ({{ $result->fleet->vehicleType->name }})
                                        @endif
                                    </td>
                                    <td>{{ $result->fleet->license_number ?? '-' }}</td>
                                    <td>{{ $result->trip_start_time ? \Carbon\Carbon::parse($result->trip_start_time)->format('d M Y h:i A') : '-' }}</td>
                                    <td>{{ number_format($result->price_per_km, 2) }}</td>
                                    <td>{{ $result->total_capacity }}</td>
                                    <td>{{ $result->booked_seats }}</td>
                                    <td>{{ $result->available_seats }}</td>
                                    <td>
                                        @if($result->status == 'upcoming')
                                            <span class="badge badge-info">Upcoming</span>
                                        @elseif($result->status == 'completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($result->status == 'cancelled')
                                            <span class="badge badge-danger">Cancelled</span>
                                        @else
                                            <span class="badge badge-warning">Ongoing</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($result->active)
                                            <span class="badge badge-success">@lang('view_pages.active')</span>
                                        @else
                                            <span class="badge badge-danger">@lang('view_pages.blocked')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($result->status == 'ongoing' || $result->status == 'upcoming')
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    @lang('view_pages.action')
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ url('sharing-rides/complete', $result->id) }}">Mark Complete</a>
                                                    <form method="post" action="{{ url('sharing-rides/cancel', $result->id) }}">
                                                        @csrf
                                                        <button class="dropdown-item" type="submit">Cancel Ride</button>
                                                    </form>
                                                    <a class="dropdown-item" href="{{ url('sharing-rides/toggle_status', $result->id) }}">
                                                        {{ $result->active ? trans('view_pages.block') : 'Unblock' }}
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="">
                <div class="col-sm-12 col-md-7 float-right">
                    <div class="dataTables_paginate paging_simple_numbers">
                        <ul class="pagination float-right">
                            {{ $results->links() }}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
