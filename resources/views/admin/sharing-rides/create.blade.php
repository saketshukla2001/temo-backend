@extends('admin.layouts.app')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-header with-border">
                        <a href="{{ url('sharing-rides') }}">
                            <button class="btn btn-danger btn-sm pull-right" type="button">
                                <i class="mdi mdi-keyboard-backspace mr-2"></i>
                                @lang('view_pages.back')
                            </button>
                        </a>
                    </div>

                    <div class="col-12">
                        <form method="post" action="{{ url('sharing-rides/store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="user_id">Select User<span class="text-danger">*</span></label>
                                        <select name="user_id" id="user_id" class="form-control" required>
                                            <option value="" selected disabled>Select User</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name ?: $user->mobile }} - {{ $user->mobile }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('user_id') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="fleet_id">Select Vehicle<span class="text-danger">*</span></label>
                                        <select name="vehicle_type_id" id="vehicle_type_id" class="form-control" required>
                                            <option value="" selected disabled>Select Vehicle</option>
                                            @foreach ($vehicleTypes as $vehicleType)
                                                <option value="{{ $vehicleType->id }}" data-capacity="{{ $vehicleType->capacity ?: 1 }}" {{ old('vehicle_type_id') == $vehicleType->id ? 'selected' : '' }}>
                                                    {{ $vehicleType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger">{{ $errors->first('vehicle_type_id') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-2 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="capacity_preview">Capacity</label>
                                        <input id="capacity_preview" type="text" class="form-control" value="-" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-2 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="price_per_km">Price / KM<span class="text-danger">*</span></label>
                                        <input id="price_per_km" name="price_per_km" type="number" step="0.01" min="0" class="form-control" value="{{ old('price_per_km') }}" required>
                                        <span class="text-danger">{{ $errors->first('price_per_km') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="trip_start_time">Ride Date & Time<span class="text-danger">*</span></label>
                                        <input id="trip_start_time" name="trip_start_time" type="datetime-local" class="form-control" value="{{ old('trip_start_time') }}" required>
                                        <span class="text-danger">{{ $errors->first('trip_start_time') }}</span>
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
        function setCapacity() {
            var capacity = $('#vehicle_type_id option:selected').data('capacity') || '-';
            $('#capacity_preview').val(capacity);
        }

        $('#vehicle_type_id').on('change', setCapacity);
        setCapacity();
    });
</script>
@endsection
