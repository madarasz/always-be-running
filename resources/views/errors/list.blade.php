@if (isset($errors) && count($errors))
    <div class="alert alert-danger" id="error-list">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif