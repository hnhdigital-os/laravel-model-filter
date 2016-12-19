@if($template)
<div class="{!! ($template) ? 'filter-'.snake_case($type).'-template' : '' !!}" {!! ($template) ? 'style="display:none;"' : '' !!}>
@endif
<div class="form-group" id="{!! ($template) ? '{PLACEHOLDER_ID}' : $placeholder_id !!} {!! snake_case($type) !!}">
  <div>
  @include('common.module.content.search.filter_'.snake_case($type), ['type' => $type, 'template' => $template, 'filter_name' => $filter_name, 'filter_settings' => $filter_settings, 'filter' => $filter, 'operator_options' => $operator_options])
  </div>
</div>
@if($template)
</div>
@endif