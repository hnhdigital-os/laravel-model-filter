@if(config('dynamic_filter.no_search_js', true))
<script src="{{ config('dynamic_filter.search_js_url', Resource::elixir('vendor/hnhdigital-os/laravel-model-filter/search.js')) }}" defer></script>
@endif
<link rel="stylesheet" type="text/css" href="{{ config('dynamic_filter.search_js_url', Resource::elixir('vendor/hnhdigital-os/laravel-model-filter/search.css')) }}" defer>

    @capturestart
    <p class="form-group">
      <label class="control-label" style="width: 100%">Name</label>
      {!! Html::createElement('input')->name('filter_name')->type('text')->placeholder('Enter a name to save this filter')
        ->value(array_get($search_filters, 'saved_filter.name', ''))->addClass('input form-control search-filter-field')->required() !!}
    </p>
    <p class="form-group">
      <label class="control-label" style="width: 100%">Description</label>      
      {!! Html::createElement('textarea')->name('filter_description')->text(array_get($search_filters, 'saved_filter.description', ''))
        ->addClass('input form-control') !!}
    </p>
    <div style="padding:5px;font-size:90%;color:#666;">
      <p>Once saved, you can reload these filter fields again. This filter will also become available within areas that use {!! strtolower($app_model_title) !!} and will be available for any automated processes.</p>
      <p>By default, any saved filter is limited to your user profile, unless you check the public checkbox below.</p>
      <p>Visit the `Filters` section to manage access to this filter.</p>
    </div>
    @capturestop('modal_content')

    @capturestart
    {!! Html::a('Save Changes')->scriptLink('Save changes')->addClass('action-save-filter1 btn btn-m btn-primary')
      ->data('model-uuid', $app_model_uuid)->data('saved-filter-uuid', array_get($search_filters, 'saved_filter.uuid', ''))->data('info_message', 'Saving filter...')->data('app-model-title', $app_model_title)
      ->hidden() !!}

    {!! Html::a('Save')->scriptLink('Save')->addClass('action-save-filter btn btn-m btn-info')
      ->data('model-uuid', $app_model_uuid)->data('info_message', 'Saving filter...') !!}
    @capturestop('modal_footer_right')

    @capturestart
    {!! Html::a('Close')->href('Close')->addClass('action-close-save-filter btn btn-default')->data('dismiss', 'modal') !!}
    @capturestop('modal_footer_left')

@include('inspinia::module.content.modal', ['modal' => ['id' => 'action-save-filter', 'header_left' => 'Save '.$app_model_title.' Filter', 'content' => $modal_content, 'footer_left' => $modal_footer_left, 'footer_right' => $modal_footer_right]])

<div id="{!! $setup->get('search.name') !!}" data-search-request="{!! $setup->get('search.search_request') !!}" data-search-model="{!! $setup->get('search.model', '') !!}" data-search-controller="{!! $setup->get('search.controller', '') !!}" data-search-base="{!! $setup->get('search.base', '') !!}" data-search-method="{!! $setup->get('search.method', '') !!}" class="common-module-content-search {!! $layout_div_class !!}">
@html(input()->type('hidden')->name('route')->addClass('search-field')->value(Route::current()->getName()))
@if(isset($search_data['settings']))
@forelse ($search_data['settings'] as $name => $value)
@html(input()->type('hidden')->name($name)->addClass('search-field')->value($value))
@empty
@endforelse
@endif
  <div class="tabs-container">
    @if(!$setup->get('tab.hide', false))
    <ul class="nav nav-tabs">
      <li class="active">
        <a id="{!! $setup->get('search.name') !!}-tab1" class="tab-search-results" data-toggle="tab" href="#{!! $setup->get('search.name') !!}-tab-1">
          <i class="fa fa-search"></i> {{ $setup->get('tab.search.title', 'Results') }}
        </a>
      </li>
      @if($setup->get('tab.advanced.show', false) && $setup->get('search.model', false) && !empty($filter_options))
      <li>
        <a id="{!! $setup->get('search.name') !!}-tab2" class="tab-search-advanced" data-toggle="tab" href="#{!! $setup->get('search.name') !!}-tab-2"><i class="fa fa-binoculars"></i>{{ $setup->get('tab.advanced.title', 'Advanced') }}</a>
      </li>
      @endif
      @if($setup->get('tab.selections.show', false))
      <li>
        <a id="{!! $setup->get('search.name') !!}-tab3" class="tab-search-selections" data-toggle="tab" href="#{!! $setup->get('search.name') !!}-tab-3"><i class="fa fa-dot-circle-o"></i>{{ $setup->get('tab.selections.title', 'Selections (0)') }}</a>
      </li>
      @endif
      @if($setup->get('tab.export.show', false))
      <li>
        <a id="{!! $setup->get('search.name') !!}-tab4" class="tab-search-export" data-toggle="tab" href="#{!! $setup->get('search.name') !!}-tab-4"><i class="fa fa-download"></i>{{ $setup->get('tab.export.title', 'Export') }}</a>
      </li>
      @endif
      @if($setup->get('tab.search_filter.show', false))
      <li class="dropdown search-filter-mode">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{!! $mode_name !!}<span class="caret"></span></a>
        {!! Html::input()->type('hidden')->name('mode')->addClass('search-field')->value($setup->get('search.filters.mode', '0')) !!}
        <ul class="dropdown-menu">
          @foreach($setup->get('tab.search_filter.options', []) as $item_id => $item_name)
          <li>{!! Html::a()->scriptLink('Selects search mode')->data('mode', $item_id)->text($item_name) !!}</li>
          @endforeach
        </ul>
      </li>
      @endif
      <li class="search-count"><a href="#">{!! $result->get('count', 0) !!}</a></li>
      @if(!$setup->get('tab.search_pagination.hide', false))
      @capturestart
      <li style="float:right;padding-top:8px;text-align: center;">
        <span class="search-buttons" style="display: none;">
          <a class="search-result-first {!! ($result->get('left_arrow', false)) ? 'btn-primary btn-outline' : '' !!} fa fa-step-backward" aria-hidden="true" style="font-size:2em;padding: 0 5px 0 5px;" data-change-page="1"></a>
        </span>
        <span class="search-buttons" style="display: none;">
          <a class="search-result-up {!! ($result->get('left_arrow', false)) ? 'btn-primary btn-outline' : '' !!} fa fa-chevron-circle-left" aria-hidden="true" style="font-size:2em;padding: 0 5px 0 5px;" data-change-page="{!! $result->get('left_arrow_page', 0) !!}"></a>
        </span>
        <span style="display: none;">
          <span class="dropdown search-items-per-page-pulldown">
            {!! Html::input()->type('hidden')->name('change_page')->addClass('search-field')->value($setup->get('search.filters.change_page', '0')) !!}
            {!! Html::input()->type('hidden')->name('items_per_page')->addClass('search-field')->value($setup->get('search.filters.items_per_page', '0')) !!}
            {!! Html::a()->scriptLink('Opens search items per page')->addClass('dropdown-toggle')->data('toggle', 'dropdown')->aria('haspopup', 'true')->aria('expanded', 'false')->style('padding: 10px 5px 10px 5px;')->text('Show 20<span class="caret"></span>') !!}
            <ul class="dropdown-menu search-items-per-page">
              {!! $result->get('items_per_page') !!}
            </ul>
          </span>
        </span>
        <span class="search-buttons" style="display: none;">
          <a class="search-result-down {!! ($result->get('right_arrow', false)) ? 'btn-primary btn-outline' : '' !!} fa fa-chevron-circle-right" aria-hidden="true" style="font-size:2em;padding: 0 5px 0 5px;" data-change-page="{!! $result->get('right_arrow_page', 0) !!}"></a>
        </span>
        <span class="search-buttons" style="display: none;">
          <a class="search-result-last {!! ($result->get('right_arrow', false)) ? 'btn-primary btn-outline' : '' !!} fa fa-step-forward" aria-hidden="true" style="font-size:2em;padding: 0 5px 0 5px;" data-change-page="{!! $result->get('search.filters.paginate_last_page', 0) !!}"></a>
        </span>
      </li>
      @capturestop("result_navigation")
      @raw(echo $result_navigation)
      @endif
    </ul>
    @endif
    <div class="tab-content">
      <div id="{!! $setup->get('search.name') !!}-tab-1" class="tab-pane active">
        <div class="table-responsive">
          <table class="table {!! $setup->get('table.classes', 'table-striped table-hover') !!}">
            <colgroup>
              @for($c = 0; $c < $setup->get('colgroup.total', 1); $c++)
              <col {!! $setup->get('colgroup.'.$c.'.col', '') !!} {!! $setup->has('colgroup.'.$c.'.width') ? 'width="'.$setup->get('colgroup.'.$c.'.width').'"' : '' !!}>
              @endfor
            </colgroup>
            <thead class="search-header">
              {!! $result->get('rows.thead', '') !!}
            </thead>
            @if($setup->get('search.show', false))
            <tbody class="search">
              <tr>
                @for($c = 0; $c < $setup->get('colgroup.total', 1); $c++)
                  @if(!$setup->get('search.'.$c.'.hide', false))
                  <td {!! $setup->get('search.'.$c.'.td', '') !!}>{!! $setup->has('search.'.$c.'.text') ? $setup->get('search.'.$c.'.text') : '&nbsp;' !!}</td>
                @endif
                @endfor
              </tr>
            </tbody>
            @endif
            <tbody class="search-result-rows">
              {!! $result->get('rows.tbody', '') !!}
            </tbody>
          </table>
          @if(!$setup->get('tab.hide', false) && !$setup->get('tab.search_pagination.hide', false))
          <div style="min-height:40px;">
            <ul class="nav nav-tabs" style="display: table;margin: 0 auto;">
              @raw(echo $result_navigation)
            </ul>
          </div>
          @endif
        </div>
      </div>
      @if($setup->get('tab.advanced.show', false) && $setup->get('search.model', false) && !empty($filter_options))
      <div id="{!! $setup->get('search.name') !!}-tab-2" class="tab-pane">
        <div class="panel-body">
          <div class="form-horizontal" class="p-t-md">
            <div class="form-group">
              <label class="col-md-2 control-label">Saved filters:</label>
              <div class="col-md-10">
                <div class="input-group">
                  {!! Html::select()->addClass('form-control action-load-filter init-select2')->style('width:100%;')->data('select2-placeholder', 'Load a saved filter from the list and apply to results:')->data('select2-allow-clear', true)->addOptionsArray($model_filter_options, 0, 1) !!}
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-primary">Load</button>
                  </span>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-md-2 control-label">Available fields:</label>
              <div class="col-md-10">
                <div class="input-group">
                  {!! Html::select()->addClass('form-control action-add-filter init-select2')->style('width:100%;')->data('select2-placeholder', 'Add a field from the list to filter results:')->data('select2-allow-clear', true)->addOptionsArray($filter_options, 0, 1) !!}
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-primary">Add</button>
                  </span>
                </div>
              </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="applied-filters">
              @if(count($search_filters))
              <?php $placeholder_count = 0; ?>
              @foreach($search_filters as $filter_name => $applied_filters)
              <?php
              $filter_settings = [];
              if (isset($filters[$filter_name])) {
                $filter_settings = $filters[$filter_name];
              }
              ?>
              @if(!empty($filter_settings) && is_array($applied_filters))
              @foreach($applied_filters as $filter)
              @if(empty($filter_settings['search_tab_only']))
              @include('dynamic_filter::filter', ['placeholder_id' => $filter_name.'_'.$placeholder_count, 'type' => $filter_settings['filter'], 'template' => false, 'filter_settings' => $filter_settings, 'operator_options' => $operator_options[$filter_settings['filter']]])
              @endif
              @endforeach
              @endif
              @endforeach
              <?php $placeholder_count++; ?>
              @endif
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
              <div class="col-md-2">
                <button class="btn btn-white action-cancel-filter" type="submit">Clear filters</button>
              </div>
              <div class="col-md-7"></div>
              <label class="col-md-3 control-label">
                {!! Html::createElement('button')->type('submit')->addClass('btn btn-primary action-apply-filter ladda-button init-ladda')->text('Apply')->data('style', 'expand-right') !!}
                {!! Html::createElement('a')->addClass('btn btn-info action-save-filter ladda-button init-ladda')->text('Save filter')->href('#action-save-filter')->data('style', 'expand-right')->data('toggle', 'modal') !!}
              </label>
            </div>
            @foreach($filter_types as $type)
            @include('dynamic_filter::filter', ['type' => $type, 'template' => true, 'filter_name' => '', 'filter_settings' => [], 'filter' => [], 'operator_options' => $operator_options[$type]])
            @endforeach
          </div>
        </div>
      </div>
      @endif
      @if($setup->get('tab.selections.show', false))
      <div id="{!! $setup->get('search.name') !!}-tab-3" class="tab-pane">
        <div class="panel-body">
        </div>
      </div>
      @endif
      @if($setup->get('tab.export.show', false))
      <div id="{!! $setup->get('search.name') !!}-tab-4" class="tab-pane">
        <div class="panel-body">
          
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

@if(!empty($list_filter_options))
<script type="text/javascript">
var list_filter_options = <?= json_encode($list_filter_options) ?>
</script>
@endif

@section('footer')
@parent
<form id="{!! $setup->get('search.name') !!}-form" class="common-module-content-search-form" novalidate="novalidate" action="{!! $setup->get('search.search_request') !!}" method="post" onsubmit="return false;">
  {{ $csrf_field = csrf_field() }}
  <button type="submit" class="hidden-search-button"></button>
</form>
<script>
$('#{!! $setup->get('search.name') !!}-form').data('validator', {'settings': {}})
</script>
@stop
