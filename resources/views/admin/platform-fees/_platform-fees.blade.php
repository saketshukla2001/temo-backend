<div class="row p-0 m-0">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="col-sm-12 p-0">
                    <table class="table table-hover" style="border-collapse: collapse; border-spacing: 0px; width: 100%;">
                        <thead>
                            <tr>
                                <th>{{ trans('view_pages.s_no') }}</th>
                                <th>User Type</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>{{ trans('view_pages.status') }}</th>
                                <th>{{ trans('view_pages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($results) == 0)
                                <tr>
                                    <td class="no-result" colspan="6">{{ trans('view_pages.no_data_found') }}</td>
                                </tr>
                            @endif

                            @php
                                $i = $results->firstItem();
                            @endphp

                            @foreach ($results as $result)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ ucfirst($result->user_type) }}</td>
                                    <td>{{ ucfirst($result->fee_type) }}</td>
                                    <td>{{ number_format($result->amount, 2) }}{{ $result->fee_type == 'percentage' ? '%' : '' }}</td>
                                    <td>
                                        @if($result->active)
                                            <span class="badge badge-success">@lang('view_pages.active')</span>
                                        @else
                                            <span class="badge badge-danger">@lang('view_pages.blocked')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-info btn-sm" href="{{ url('platform-fees/toggle_status', $result->id) }}">
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
