<label class="col-md-2 control-label">
  {!! $column1 !!}
</label>
<div class="col-md-3">
  {!! $column2 !!}
</div>
<div class="col-md-7">
  <div class="input-group">
    {!! $column3 !!}
    <span class="input-group-btn">
      {!! Html::button()->addClass('action-remove-row btn btn-danger')->text('X') !!}
    </span>
  </div>
</div>