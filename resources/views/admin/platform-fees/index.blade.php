@extends('admin.layouts.app')

@section('title', 'Platform Fees')

@section('content')
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <div class="row text-right">
                        <div class="col-2">
                            <div class="form-group">
                                <input type="text" id="search_keyword" name="search" class="form-control" placeholder="@lang('view_pages.enter_keyword')">
                            </div>
                        </div>
                        <div class="col-1">
                            <button id="search" class="btn btn-success btn-outline btn-sm mt-5" type="button">
                                @lang('view_pages.search')
                            </button>
                        </div>
                        <div class="col-9 text-right">
                            <a href="{{ url('platform-fees/create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus-circle mr-2"></i>Add Platform Fee
                            </a>
                        </div>
                    </div>
                </div>

                <div id="js-platform-fees-partial-target">
                    <include-fragment src="platform-fees/fetch">
                        <span style="text-align: center;font-weight: bold;">@lang('view_pages.loading')</span>
                    </include-fragment>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/fetchdata.min.js') }}"></script>
    <script>
        $(function() {
            $('body').on('click', '.pagination a', function(e) {
                e.preventDefault();
                $.get($(this).attr('href'), function(data) {
                    $('#js-platform-fees-partial-target').html(data);
                });
            });

            $('#search').on('click', function(e) {
                e.preventDefault();
                fetch('platform-fees/fetch?search=' + encodeURIComponent($('#search_keyword').val()))
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('#js-platform-fees-partial-target').innerHTML = html;
                    });
            });
        });
    </script>
</section>
@endsection
