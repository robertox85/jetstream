<!-- <iframe
        src="{{ url('/laravel-filemanager/' . $getRecord()->id) }}"
        style="width: 100%; height: 600px; border: none;"
        id="lfm-iframe">
</iframe> -->

<iframe src="{{url("/filemanager/". $getRecord()->id)}}" style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>

<!-- <form action="{{ url('/laravel-filemanager/' . $getRecord()->id . '/upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">Carica</button>
</form>-->

