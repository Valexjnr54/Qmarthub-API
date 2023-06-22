@if(count($errors) > 0)
    @foreach($errors->all() as $error)
        <script>
          iziToast.error({
            title: 'Oops Something Went Wrong!'+'<br>',
            message: {!! json_encode($error) !!},
            position: 'topRight'
          });
        </script>
    @endforeach
@endif

@if(session('success'))
    <script>
   "use strict";
      iziToast.success({
        title: {!! json_encode(session('title')) !!},
        message: {!! json_encode(session('success')) !!},
        position: 'topRight'
      });
    </script>
@endif

@if(session('successMessage'))
    <script>
   "use strict";
      iziToast.success({
        title: {!! json_encode(session('title')) !!},
        message: {!! json_encode(session('successMessage')) !!},
        position: 'topRight'
      });
    </script>
@endif

@if(session('warning'))
    <script>
   "use strict";
      iziToast.warning({
        title: {!! json_encode(session('title')) !!},
        message: {!! json_encode(session('warning')) !!},
        position: 'topRight'
      });
    </script>
@endif

@if(session('error'))
    <script>
   "use strict";
      iziToast.error({
        title: 'Oops Something Went Wrong!'+'<br>',
        message: {!! json_encode(session('error')) !!},
        position: 'topRight'
      });
    </script>
@endif