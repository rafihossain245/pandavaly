@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any() && ! $errors->has('email') && ! $errors->has('password'))
    <div class="alert alert-danger">
        Please review the highlighted fields and try again.
    </div>
@endif
