
$(document).ready(function() {

    /* User clicks the availablle filters */
    var track_filters = 0;
    $('.common-module-content-search .action-add-filter').on('select2:select', function(e) {
        var search_table = $(this).parents('.common-module-content-search');
        var select_settings = $(this).children('option:selected').val().split('|');
        while ($('#' + select_settings[0] + '_' + track_filters).length > 0) {
            track_filters++;
        }
        var filter_id = select_settings[0]+'_'+track_filters;
        var filter_name = select_settings[0];
        track_filters++;

        var filter_template = search_table.find('.filter-'+select_settings[1]+'-template').html();
        filter_template = filter_template.replace(/{PLACEHOLDER_ATTRIBUTE_NAME}/g, $(this).children('option:selected').text());
        filter_template = filter_template.replace(/{PLACEHOLDER_SEARCH_NAME}/g, filter_name);
        filter_template = filter_template.replace(/{PLACEHOLDER_ID}/g, filter_id);
        search_table.find('.applied-filters').append(filter_template);
        $('.applied-filters .select2-on-add').each(function() {
          $(this).removeClass('select2-on-add').addClass('select2');
          $(this).find('option:not(.filter-' + filter_name + ')').remove();
          $(this).trigger('extensions::init');
        });
        this.selectedIndex = 0;
        $(this).trigger('change.select2');

        toastr.info('Added...', '', {timeOut: 500});

        $('#'+filter_id+' select.search-value1').each(function() {
            for (i = 0; i < list_filter_options[filter_name].length; i++) {
                $(this).append($('<option></option>').attr('value', list_filter_options[filter_name][i][0]).text(list_filter_options[filter_name][i][1]));
            }
            $(this).select2({
              tags: true,
              multiple: true,
              placeholder: {
                id: '-1',
                text: 'Select one or many...'
              },
              allowClear: true
            });
        });
    });

    /* User clicks the remove button against a single applied filter */
    $('.common-module-content-search').on('click', '.action-remove-row', function(e) {
      $(this).parents('.form-group').remove()
    });

    /* User clicks the remove button against a single applied filter */
    $('.common-module-content-search').on('keypress', '.form-control:not(.enable-on-enter)', function(e) {
      var search_table = $(this).parents('.common-module-content-search');
      var keycode = (event.keyCode ? event.keyCode : event.which);
      if (keycode === 13) {
        e.preventDefault();
        search_table.find('button[type=submit]').trigger('click');
      }
    });

    /* User clicks the mode drop down */
    $('.common-module-content-search').on('click', '.search-filter-mode ul.dropdown-menu li a', function(e) {
        var search_table = $(this).parents('.common-module-content-search');
        var mode_name = $(this).html()+'<span class="caret"></span>';
        var mode_value = $(this).data('mode');
        $(this).parents('.search-filter-mode').children('a.dropdown-toggle').html(mode_name);
        $(this).parents('.search-filter-mode').children('input.search-field').val(mode_value);
        search_table.find(('.dropdown-menu li').show();
        search_table.find(('.dropdown-menu li a[data-mode='+mode_value+']').parent('li').hide();
        search_table.find('button[type=submit]').trigger('click');
    });

    /* Hide the drop down option that is current */
    $('.common-module-content-search input.search-field[name=mode]').each(function() {
        $('.dropdown-menu li a[data-mode='+$(this).val()+']').parent('li').hide();
    });

    submit_search_form = function(e) {
      $(this).off('click');
      var search_table_id = $(this).parents('form').attr('id').replace('-form', '');
      var submit_button = $(this);
      submit_button.attr('disabled', 'disabled');
      if ($('#'+search_table_id+' .search-result-rows tr:first-child').data('processing') == undefined) {
        $('#'+search_table_id+' .search-result-rows tr:first-child').data('processing', true);

        if ($('#'+search_table_id+' input.search-field[name=change_page]').val() > 0) {
          var page = $('#'+search_table_id+' input.search-field[name=change_page]').val();
        } else {
          var page = 1;
        }
        $.ajax($('#'+search_table_id).data('search-request') + '?page=' + page, {
          data: common_module_content_search.getFilters(search_table_id),
          beforeSend: function() {
            toastr.info('Processing search request...', '', {timeOut: 0});
            if ($('.action-apply-filter').length) {
                $('#'+search_table_id+' .action-apply-filter').ladda().ladda('start').prop('disabled', true);
            }
          },
          success: function(response, textStatus, jqXHR) {
            submit_button.removeAttr('disabled');
            submit_button.on('click', submit_search_form);
            common_module_content_search.update(search_table_id, response);
          }
        });
      }
      e.stopImmediatePropagation();
      return false;
    };

    /* User submits the search form */
    $('.common-module-content-search-form').each(function(count, el) {
      var search_table = $(el);
      search_table.find('button[type=submit]').on('click', submit_search_form);
    });

    /* User clicks the apply button */
    $('.common-module-content-search').on('click', '.action-apply-filter', function(e) {
      var search_table_id = $(this).parents('.common-module-content-search').attr('id');
      $('#'+search_table_id+'-form button[type=submit]').trigger('click');
    });

    /* User clicks the change button */
    $('.common-module-content-search').on('click', '.action-change-filter', function(e) {
      var search_table_id = $(this).parents('.common-module-content-search').attr('id');
      $('#'+search_table_id+'-tab2').tab('show');
    });

    /* User clicks the cancel button */
    $('.common-module-content-search').on('click', '.action-cancel-filter', function(e) {
      var search_table_id = $(this).parents('.common-module-content-search').attr('id');
      $('#'+search_table_id+' .applied-filters .action-remove-row').trigger('click');
      $('#'+search_table_id+' .search-filter-field').val('');
      $('#'+search_table_id+'-form button[type=submit]').trigger('click');
    });

    /* User clicks the save button */
    $('.common-module-content-search').on('click', 'a.action-save-filter', function(e) {
      var search_table_id = $(this).parents('.common-module-content-search').attr('id');
      $('#action-save-filter').data('search-table-id', search_table_id);
    });

    /* User clicks the save button */
    $('#action-save-filter').on('click', '.action-save-filter,.action-save-filter1', function(e) {
      toastr.remove();
      $('#action-save-filter').find('input[name=filter_name]').parents('.form-group').removeClass('has-error');
      var search_table_id = $('#action-save-filter').data('search-table-id');

      var filter_data = {};
      filter_data['name'] = $('#action-save-filter').find('input[name=filter_name]').val();
      filter_data['description'] = $('#action-save-filter').find('textarea[name=filter_description]').val();
      filter_data['search'] = common_module_content_search.getFilters(search_table_id);
      filter_data['is_public'] = $('#action-save-filter').find('input[name=filter_is_public]').val();

      if ($(this).hasClass('action-save-filter1') && $(this).data('saved-filter-uuid') != '') {
        var update_cmd = $(this).data('saved-filter-uuid') + '/saveFilter';
        var method = 'PATCH';
      } else {
        var update_cmd = 'saveNewFilter';
        var method = 'POST';
      }

      if (filter_data.name.length > 2) {
        $.ajax('/search-filter/' + $(this).data('model-uuid') + '/' + update_cmd + '?_method='+method, {
          data: filter_data,
          beforeSend: function() {
              toastr.info('Saving filter...', '', {timeOut: 0});
          },
          success: function(response, textStatus, jqXHR) {
              if (typeof response.is_error != 'undefined') {
                toastr.remove();
                toastr.error(response.error_message, 'Error in saving filter', {timeOut: 5000});
              } else {
                common_module_content_search.update(search_table_id, response);
                $('#action-save-filter .action-save-filter1').data('saved-filter-uuid', response.saved_filter_uuid);
                $('#action-save-filter').modal('hide');
                $('#' + search_table_id + ' .saved-filter-name').html(filter_data['name']);
                toastr.remove();
                toastr.success('Saved filter!', '', {timeOut: 3000});
              }
          }
        });
      } else {
        toastr.error('Filter name is required.', '', {timeOut: 3000});
        $('#action-save-filter').find('input[name=filter_name]').parents('.form-group').addClass('has-error');
        $('#action-save-filter').find('input[name=filter_name]').focus();
      }
    });

    /* When the modal displays to the user, focus on the name field */
    $('#action-save-filter').on('shown.bs.modal', function () {
      toastr.remove();
      if ($('#action-save-filter .action-save-filter1').data('saved-filter-uuid') != '') {
        $('#action-save-filter h3').html($('#action-save-filter').find('input[name=filter_name]').val() + ' - Saved ' + $('#action-save-filter .action-save-filter1').data('app-model-title') + ' Filter');
        $('#action-save-filter .action-save-filter').html('Save as New');
        $('#action-save-filter .action-save-filter1').show();
      } else {
        $('#action-save-filter').find('input[name=filter_name]').focus();
        $('#action-save-filter .action-save-filter').html('Save');
        $('#action-save-filter .action-save-filter1').hide();
      }
    });

    /* When the modal hides from the user, reset */
    $('#action-save-filter').on('hidden.bs.modal', function () {
        $('#action-save-filter h3').html('Search ' + $('#action-save-filter .action-save-filter1').data('app-model-title') + 'Filter');
        $('#action-save-filter .action-save-filter').html('Save');
        $('#action-save-filter .action-save-filter1').hide();
    });

    /* User clicks an option in the available saved filters  */
    $('.common-module-content-search .action-load-filter').on('select2:select', function(e) {
      var search_table_id = $(this).parents('.common-module-content-search').attr('id');
      var selected_uuid = $(this).children('option:selected').val();

      var search_data = {
        'model': $(this).parents('.common-module-content-search').data('search-model'),
        'controller': $(this).parents('.common-module-content-search').data('search-controller'),
        'method': $(this).parents('.common-module-content-search').data('search-method'),
        'route' : $('#'+search_table_id+' [name=route]').val(),
        'search-tab': $('#'+search_table_id+' [name=search-tab]').val()
      };

      $.ajax($(this).parents('.common-module-content-search').data('search-base') + '/' + selected_uuid + '/loadFilter?_method=POST', {
        data: search_data,
        beforeSend: function() {
          toastr.info('Loading filter...', '', {timeOut: 0});
        },
        success: function(response, textStatus, jqXHR) {
            if (typeof response.is_error != 'undefined') {          
              toastr.remove();      
              toastr.error(response.error_message, 'Error in saving filter', {timeOut: 3000});
            } else {
              common_module_content_search.update(search_table_id, response);
              $('#action-save-filter').modal('hide');

              if (typeof response.search != 'undefined' && typeof response.search.saved_filter != 'undefined') {
                $('#action-save-filter .action-save-filter').val('Save as New');
                $('#action-save-filter .action-save-filter1').show();
                $('#action-save-filter .action-save-filter1').data('saved-filter-uuid', response.search.saved_filter.uuid);
                $('#action-save-filter').find('input[name=filter_name]').val(response.search.saved_filter.name);
                $('#action-save-filter').find('textarea[name=filter_description]').val(response.search.saved_filter.description);
                $('#action-save-filter').find('input[name=filter_is_public]').val(response.search.saved_filter.is_public);
              }

              toastr.remove();
              toastr.success('Filter has been applied!', '', {timeOut: 3000});
            }
        }
      });

      this.selectedIndex = 0;
      $(this).trigger('change.select2');
    });

    /* User clicks the left or right button */
    $('.common-module-content-search').on('click', '.search-result-up,.search-result-first,.search-result-down,.search-result-last', function(e) {
        if ($(this).hasClass('btn-primary btn-outline')) {
            search_table_id = $(this).parents('.common-module-content-search').attr('id');
            $('#'+search_table_id+' input.search-field[name=change_page]').val($(this).data('change-page'));
            $('#'+search_table_id+'-form button[type=submit]').trigger('click');
        }
    });

    $('.common-module-content-search').find('input,textarea,select,button').each(function() {
        if ($(this).attr('form') == undefined) {
          search_table_id = $(this).parents('.common-module-content-search').attr('id');
          $(this).attr('form', search_table_id+'-form');
        }
        $(this).addClass('ignore');
    });

    $('.common-module-content-search .search').on('keydown', 'input', function (e) {
      if (e.keyCode == 13) {
        search_table_id = $(this).parents('.common-module-content-search').attr('id');
        $('#'+search_table_id+'-form button[type=submit]').trigger('click');
        return false;
      }
    });

    $('.common-module-content-search a.tab-search-results[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      var search_table = $(this).parents('.common-module-content-search');
      if (search_table.find('.search-items-per-page-pulldown').data('has-search')) {
        search_table.find('.search-buttons,.search-items-per-page-pulldown').show();
      }
    });

    $('.common-module-content-search a.tab-search-results[data-toggle="tab"]').on('hidden.bs.tab', function (e) {
      var search_table_id = $(this).parents('.common-module-content-search').attr('id');
      if (!$('#'+search_table_id+' .search-items-per-page-pulldown').data('has-search')) {
        search_table.find('.search-buttons,.search-items-per-page-pulldown').hide();
      }
    });

    $('.common-module-content-search').each(function(e) {
      var search_table = $(this);
      search_table.find('#'+search_table_id).find('li:first-child a[data-toggle=tab]').tab('show');
      search_table.find('.search-buttons,.search-items-per-page-pulldown').hide();
      if (search_table.find('.search-buttons a').hasClass('btn-primary')) {
        search_table.find('.search-buttons,.search-items-per-page-pulldown').show();
      }
    });
    
});

var common_module_content_search = {

    /**
     * Update search results.
     *
     * @param  string search_table_id
     * @param  object response
     * @return void
     */
    update: function(search_table_id, response)
    {

        if (typeof response.rows != 'undefined') {
            toastr.remove();
            toastr.success('Search results updated.', '', {timeOut: 1000});
            $('#'+search_table_id+' .search-header').html($H.build(response.rows.thead));
            $('#'+search_table_id+' .search-result-rows').html($H.build(response.rows.tbody));
            $('#'+search_table_id+' .search-result-rows').show().animateCss('fadeIn');
            $('#'+search_table_id).trigger('common_module_content_search::update', [response]);
            window.scrollTo(0, 0);
            $('#'+search_table_id+'-tab1').tab('show');
        }

        if (typeof response.count != 'undefined') {
            $('#'+search_table_id+' .search-count a').html(response.count);
        }

        if (typeof response.saved_filters != 'undefined') {
            $('#'+search_table_id+' .action-load-filter').html($H.build(response.saved_filters));
            $('#'+search_table_id+' .action-load-filter').trigger('change');
        }

        if (typeof response.advanced_filters != 'undefined') {
            $('#'+search_table_id+' .applied-filters').html(response.advanced_filters);
        }

        if (typeof response.lookup != 'undefined') {
            $('#'+search_table_id+' input[name*=lookup_value1]').val(response.lookup);
        }

        if (typeof response.items_per_page != 'undefined') {
            $('#'+search_table_id+' .search-items-per-page').html(response.items_per_page);
        }

        var has_search = false;

        if (typeof response.left_arrow != 'undefined') {
            $('#'+search_table_id+' .search-result-up').data('change-page', response.left_arrow_page);
            if (response.left_arrow) {
                $('#'+search_table_id+' .search-result-up').addClass('btn-primary btn-outline');
                $('#'+search_table_id+' .search-result-first').addClass('btn-primary btn-outline');
                has_search = true;
            } else {
                $('#'+search_table_id+' .search-result-up').removeClass('btn-primary btn-outline');
                $('#'+search_table_id+' .search-result-first').removeClass('btn-primary btn-outline');
            }
        }
        if (typeof response.right_arrow != 'undefined') {
            $('#'+search_table_id+' .search-result-down').data('change-page', response.right_arrow_page);
            if (response.right_arrow) {
                $('#'+search_table_id+' .search-result-down').addClass('btn-primary btn-outline');
                $('#'+search_table_id+' .search-result-last').addClass('btn-primary btn-outline');
                $('#'+search_table_id+' .search-result-last').data('change-page', response.search.paginate_last_page);
                has_search = true;
            } else {
                $('#'+search_table_id+' .search-result-down').removeClass('btn-primary btn-outline');
                $('#'+search_table_id+' .search-result-last').removeClass('btn-primary btn-outline');
            }
        }

        if (has_search == false) {
          $('#'+search_table_id+' .search-buttons,#'+search_table_id+' .search-items-per-page-pulldown').hide();
        } else {
          $('#'+search_table_id+' .search-buttons,#'+search_table_id+' .search-items-per-page-pulldown').show();
        }

        $('#'+search_table_id+' .search-items-per-page-pulldown').data('has-search', has_search);

        if (typeof $.ladda != 'undefined') {
            $.ladda('stopAll');
        }

        $('body').trigger('extensions::init', [$('#'+search_table_id)]);
    },

    /**
     * Capture all the filter input
     * @param  string search_table_id
     * @return array
     */
    getFilters: function(search_table_id)
    {
        var current_filters = {
          'filters': {}
        };

        $('#'+search_table_id+' .search-field').each(function() {
            current_filters[this.name] = $(this).val();
        });

        $('#'+search_table_id+' .applied-filters .search-operator').each(function() {
            search_name = this.name.replace('_operator[]', '');
            search_operator = $(this).children('option:selected').val();
            search_value1 = $(this).parents('.form-group').find('.search-value1').val();
            search_value2 = $(this).parents('.form-group').find('.search-value2').val();
            if (typeof current_filters['filters'][search_name] == 'undefined') {
                current_filters['filters'][search_name] = [];
            }
            current_filters['filters'][search_name].push([search_operator, search_value1, search_value2]);
        });

        $('#'+search_table_id+' .search-filter-field').each(function() {
            search_name = this.name.replace('_value1[]', '');
            search_value1 = $(this).val();
            if (typeof current_filters[search_name] == 'undefined') {
                current_filters['filters'][search_name] = [];
            }
            if (search_value1.trim().length > 0) {
                current_filters['filters'][search_name].push(['*=*', search_value1.trim(), '']);
            }
        });

        return current_filters;
    }
}
