<label class="col-md-2 control-label">
  {!! ($template) ? '{PLACEHOLDER_ATTRIBUTE_NAME}' : $filter_settings['name'] !!}
</label>
<div class="col-md-3">
  {!! Html::select()->name(($template) ? '{PLACEHOLDER_SEARCH_NAME}_operator[]' : $filter_name.'_operator[]')->addClass('search-operator form-control')->style('width:100%;')->addOptionsArray($operator_options, 'value', 'name', (isset($filter[0])) ? $filter[0] : '') !!}
</div>
<div class="col-md-7">
  <div class="input-group">
    {!! Html::input()->name(($template) ? '{PLACEHOLDER_SEARCH_NAME}_value1[]' : $filter_name.'_value1[]')->addClass('search-value1 form-control')->style('width:100%;')->placeholder('Enter a value')->value((isset($filter[1])) ? $filter[1] : '')->required() !!}
    <span class="input-group-btn">
      {!! Html::button()->addClass('action-remove-row btn btn-danger')->text('X') !!}
    </span>
  </div>
</div>