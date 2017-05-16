<label class="col-md-2 control-label">
  {!! (!empty($filter_settings['name'])) ? $filter_settings['name'] : '{PLACEHOLDER_ATTRIBUTE_NAME}' !!}
</label>
<div class="col-md-2">
  {!! Html::select()->name(($template) ? '{PLACEHOLDER_SEARCH_NAME}_operator[]' : $filter_name.'_operator[]')->addClass('search-operator form-control')->style('width:100%;')->addOptionsArray($operator_options, 'value', 'name', (array_has($filter, 0)) ? (string)$filter[0] : '') !!}
</div>
<div class="col-md-7">&nbsp;</div>
<div class="col-md-1" style="text-align:right;padding-right:16px;">
  {!! Html::button()->addClass('action-remove-row btn btn-danger')->text('X') !!}
</div>