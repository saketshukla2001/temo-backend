@extends('admin.layouts.app')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-header with-border">
                        <a href="{{ url('driver-subscriptions') }}">
                            <button class="btn btn-danger btn-sm pull-right" type="button">
                                <i class="mdi mdi-keyboard-backspace mr-2"></i>
                                @lang('view_pages.back')
                            </button>
                        </a>
                    </div>

                    <div class="col-12">
                        <form method="post" action="{{ url('driver-subscriptions/store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="name">Plan Name<span class="text-danger">*</span></label>
                                        <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" required>
                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="subscription_basis">Subscription Type<span class="text-danger">*</span></label>
                                        <select name="subscription_basis" id="subscription_basis" class="form-control" required>
                                            <option value="day_wise" {{ old('subscription_basis') == 'day_wise' ? 'selected' : '' }}>Day Wise</option>
                                            <option value="ride_wise" {{ old('subscription_basis') == 'ride_wise' ? 'selected' : '' }}>Ride Wise</option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('subscription_basis') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="price">Price<span class="text-danger">*</span></label>
                                        <input id="price" name="price" type="number" step="0.01" min="0" class="form-control" value="{{ old('price') }}" required>
                                        <span class="text-danger">{{ $errors->first('price') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3 day-wise-field">
                                    <div class="form-group">
                                        <label for="duration_days">Days<span class="text-danger">*</span></label>
                                        <input id="duration_days" name="duration_days" type="number" min="1" class="form-control" value="{{ old('duration_days') }}">
                                        <span class="text-danger">{{ $errors->first('duration_days') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3 ride-wise-field">
                                    <div class="form-group">
                                        <label for="total_rides">Ride Count<span class="text-danger">*</span></label>
                                        <input id="total_rides" name="total_rides" type="number" min="1" class="form-control" value="{{ old('total_rides') }}">
                                        <span class="text-danger">{{ $errors->first('total_rides') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-12">
                                    <button class="btn btn-primary btn-sm m-5 pull-right" type="submit">
                                        @lang('view_pages.save')
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('styles/js/jquery.js') }}"></script>
<script>
    $(function() {
        function toggleFields() {
            var basis = $('#subscription_basis').val();
            $('.day-wise-field').toggle(basis === 'day_wise');
            $('.ride-wise-field').toggle(basis === 'ride_wise');
        }

        $('#subscription_basis').on('change', toggleFields);
        toggleFields();
    });
</script>
@endsection
