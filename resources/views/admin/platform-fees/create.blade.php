@extends('admin.layouts.app')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-header with-border">
                        <a href="{{ url('platform-fees') }}">
                            <button class="btn btn-danger btn-sm pull-right" type="button">
                                <i class="mdi mdi-keyboard-backspace mr-2"></i>
                                @lang('view_pages.back')
                            </button>
                        </a>
                    </div>

                    <div class="col-12">
                        <form method="post" action="{{ url('platform-fees/store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="user_type">User Type<span class="text-danger">*</span></label>
                                        <select name="user_type" id="user_type" class="form-control" required>
                                            <option value="user" {{ old('user_type') == 'user' ? 'selected' : '' }}>User</option>
                                            <option value="driver" {{ old('user_type') == 'driver' ? 'selected' : '' }}>Driver</option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('user_type') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="fee_type">Fee Type<span class="text-danger">*</span></label>
                                        <select name="fee_type" id="fee_type" class="form-control" required>
                                            <option value="fixed" {{ old('fee_type') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                            <option value="percentage" {{ old('fee_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                        <span class="text-danger">{{ $errors->first('fee_type') }}</span>
                                    </div>
                                </div>

                                <div class="col-sm-4 float-left mb-md-3">
                                    <div class="form-group">
                                        <label for="amount">Amount<span class="text-danger">*</span></label>
                                        <input id="amount" name="amount" type="number" step="0.01" min="0" class="form-control" value="{{ old('amount') }}" required>
                                        <span class="text-danger">{{ $errors->first('amount') }}</span>
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
@endsection
