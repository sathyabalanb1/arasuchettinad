
<div class="row dinein-qr-output">

    @foreach ($formModel->locations as $location)
    <div class="col-3">
        @php $qr_url = config('app.url').'/'.$location->permalink_slug.'/table/'.$formModel->getKey(); @endphp
        <div class="py-2">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($qr_url); !!}</div>
        <p class="help-block">{{ $location->location_name }}<br /><button class="btn btn-secondary mt-2" type="button">@lang('thoughtco.dinein::default.text_qr_download')</button></p>
    </div>
    @endforeach

    <script>
    window.addEventListener('load', function(){
        jQuery(function(){
            $('.dinein-qr-output button').on('click', function(event){
                var win = window.open();
                win.document.write('<iframe src="data:image/svg+xml;base64,' + window.btoa($(event.target).parents('.col-3').find('svg')[0].outerHTML) + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen></iframe>');
            });
        });
    });
    </script>

</div>
