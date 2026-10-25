<div class="row p-0 m-0">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="col-sm-12 p-0">
                    <table class="table table-hover" style="border-collapse: collapse; border-spacing: 0px; width: 100%;">
                        <thead>
                            <tr>
                                <th>{{ trans('view_pages.s_no') }}</th>
                                <th>Plan Name</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Days</th>
                                <th>Total Rides</th>
                                <th>{{ trans('view_pages.status') }}</th>
                                <th>{{ trans('view_pages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($results) == 0)
                                <tr>
                                    <td class="no-result" colspan="8">{{ trans('view_pages.no_data_found') }}</td>
                                </tr>
                            @endif

                            @php
                                $i = $results->firstItem();
                            @endphp

                            @foreach ($results as $result)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $result->name }}</td>
                                    <td>{{ $result->subscription_basis == 'ride_wise' ? 'Ride Wise' : 'Day Wise' }}</td>
                                    <td>{{ number_format($result->price, 2) }}</td>
                                    <td>{{ $result->duration_days ?? '-' }}</td>
                                    <td>{{ $result->total_rides ?? '-' }}</td>
                                    <td>
                                        @if($result->active)
                                            <span class="badge badge-success">@lang('view_pages.active')</span>
                                        @else
                                            <span class="badge badge-danger">@lang('view_pages.blocked')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if((float) $result->price > 0)
                                            <a class="btn btn-warning btn-sm" href="{{ url('driver-subscriptions/edit', $result->id) }}">
                                                @lang('view_pages.edit')
                                            </a>
                                        @endif
                                        <a class="btn btn-info btn-sm" href="{{ url('driver-subscriptions/toggle_status', $result->id) }}">
                                            {{ $result->active ? trans('view_pages.block') : 'Unblock' }}
                                        </a>
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
