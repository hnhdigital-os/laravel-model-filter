<div id="{!! array_get($modal, 'id', '') !!}" class="modal {!! array_get($modal, 'animation', 'fade') !!}" aria-hidden="true">
  <div id="modal-form">
    <div class="modal-dialog">
      <div class="modal-content">
        @if(array_has($modal, 'header_left'))
        <div class="modal-header">
          <h3 class="m-t-none" style="margin-bottom:0;">{!! array_get($modal, 'header_left', '') !!}</h3>
        </div>
        @endif
        <div class="modal-body">
          <div class="row">
            {!! array_get($modal, 'content', '') !!}
          </div>
        </div>
        @if(array_has($modal, 'footer_left') || array_has($modal, 'footer_right'))
        <div class="modal-footer">
          @if(array_get($modal, 'footer_left', ''))
          <div class="pull-left">
            {!! array_get($modal, 'footer_left', '') !!}
          </div>
          @endif
          {!! array_get($modal, 'footer_right', '') !!}
        </div>
        @endif
      </div>
    </div>
  </div>
</div>