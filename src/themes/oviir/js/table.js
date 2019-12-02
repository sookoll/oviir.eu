let table = null
let empty = {}

const api = new Api({ endpoint: config.url, hash: config.hash })

function booleanCellRenderer (data, type, row) {
  return data ? 'Jah' : 'Ei'
}

function createTable (data) {
  const columns = []
  Object.keys(config.col).forEach(key => {
    empty[key] = ''
    if (config.col[key].visible) {
      const cell = Object.assign({ title: key, data: key }, config.col[key])
      if (config.col[key].dataType === 'boolean') {
        cell.render = booleanCellRenderer
      }
      columns.push(cell)
    }
  })

  if (config.editable) {
    columns.push({
      title: '',
      data: '__editable',
      searchable: false,
      orderable: false
    })
  }
  const settings = {
    data: data.map(item => {
      return formatRow(Object.assign({}, item))
    }),
    rowId: config.primary,
    columns: columns,
    lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Kõik"]],
    sPaginationType : 'full_numbers',
    iDisplayLength : 50,
    language: {
      processing: "Laeb ...",
      sLengthMenu: "N&auml;ita kirjeid _MENU_ kaupa",
      sZeroRecords: '',
      sInfo: "_START_-_END_ / _TOTAL_",
      sInfoEmpty: "Otsinguvasteid ei leitud",
      sInfoFiltered: '',
      sInfoPostFix: '',
      sSearch: '',
      thousands: '',
      paginate: {
        first: '<i class="icon ion-ios-skip-backward"></i>',
        previous: '<i class="icon ion-ios-arrow-back"></i>',
        next: '<i class="icon ion-ios-arrow-forward"></i>',
        last: '<i class="icon ion-ios-skip-forward"></i>'
      }
    },
    initComplete : function (settings, json) {
      $('#datatable_filter input')
        .attr('placeholder', 'Otsi...')
        .removeClass('form-control-sm')
    }
  }
  if (config.dom) {
    settings.sDom = config.dom
  }

  table = $('#datatable').DataTable(settings)

  table.on('draw', () => {
    const body = $(table.table().body())
    body.unhighlight()
    if (table.rows({ filter: 'applied' }).data().length) {
      body.highlight(table.search())
    }
  })
  // add button
  $('#toolbar .add').prop('disabled', false)
}

function getEditButton (row) {
  return `<button
      class="btn btn-sm btn-primary edit"
      role="button"
      data-toggle="modal"
      data-target="#rowEdit"
      data-row="${row[config.primary]}">
      <i class="icon ion-md-create"></i>
    </button>`
}

function getFormData() {
  const row = $('#rowEdit').find('form').serializeObject()
  return deFormatRow(row)
}

function formatRow (row) {
  if (config.editable) {
    row.__editable = getEditButton(row)
  }
  return row
}

function deFormatRow (row) {
  if (config.editable) {
    delete row.__editable
  }
  Object.keys(row).forEach(key => {
    if (row[key] === '') {
      row[key] = null
    }
    if (key === config.primary) {
      row[key] = Number(row[key])
    }
    if (key === 'bound_with' && row[key]) {
      row[key] = Number(row[key])
    }
    if (key === '__editable') {
      delete row[key]
    }
  })
  return row
}

function updateRow (primary, data) {
  return new Promise((resolve, reject) => {
    function afterFetch (response) {
      if (config.primary) {
        data[config.primary] = primary || response
      }
      if (config.delete in data) {
        table.row('#' + primary).remove().draw(false)
      } else if (primary) {
        table.row('#' + primary).data(formatRow(data)).draw(false)
      } else {
        table.row.add(formatRow(data)).draw(false)
      }
      resolve(response)
    }
    if (primary) {
      api.update(primary, data)
        .then(afterFetch)
        .catch(reject)
    } else {
      api.insert(data)
        .then(afterFetch)
        .catch(reject)
    }
  })
}

(function($) {

  setTimeout(() => {
    if (!config.url) {
      return false;
    }
    api.load({ params: config.params })
      .then(response => {
        config.search.bound_with = response.map(row => {
          return {
            data: row[config.primary],
            value: `${TableModal.nameFormat(row)}`
          }
        })
        createTable(response)
      })
      .catch(e => {
        console.error(e)
      })
  })

  // modal
  $('#rowEdit').on('show.bs.modal', e => {
    const primary = Number($(e.relatedTarget).data('row'))
    const row = primary ? deFormatRow(Object.assign({}, table.row('#' + primary).data())) : empty
    TableModal.onShow($(e.target), config, row)
  }).on('hide.bs.modal', e => {
    TableModal.onHide()
  })
  if (config.copy) {
    $('#copy').on('show.bs.modal', e => {
      const data = table.rows({ search: 'applied' }).data()
        .filter(row => {
          return row[config.copy] &&
            row[config.copy].length > 5 &&
            !row.death &&
            row.active
        })
        .map(row => row[config.copy])
      $(e.target).find('label span').text(`(${data.length})`)
      $(e.target).find('textarea').text(data.join(', '))
    }).on('hide.bs.modal', e => {
      $(e.target).find('textarea').text('')
    })
  }

  // save row
  $('#rowEdit').on('click', '.save', e => {
    if (!TableModal.validate()) {
      return false
    }
    const row = getFormData()
    const primary = row[config.primary]
    delete row[config.primary]
    updateRow(primary, row)
      .then(response => {
        TableModal.onSave()
      })
      .catch(error => console.error(error))
  })

  // delete row
  $('#rowEdit').on('click', '.delete', e => {
    TableModal.deleteConfirm()
      .then(() => {
        let row = getFormData()
        const primary = row[config.primary]
        row = {}
        row[config.delete] = true
        updateRow(primary, row)
          .then(response => {
            TableModal.onDelete()
          })
          .then()
          .catch(error => console.error(error))
      })
      .catch(e => console.error(e))
  });

  $('#copy').on('focus', 'textarea', function () {
    this.select()
  }).on('mouseup', 'textarea', function () {
    return false
  })

})(jQuery);
