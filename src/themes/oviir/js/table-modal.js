let tableModal = null

const TableModal = {
  el: null,
  onShow (modal, config, row) {
    tableModal = modal
    modal.find('form').append(Object.keys(row)
      .filter(key => key in config.col)
      .map((key, i) => {
        if (key === config.primary) {
          return `<input type="hidden" name="${key}" value="${row[key] || ''}">`
        }
        if (
          'dataType' in config.col[key] &&
          config.col[key].dataType === 'boolean'
        ) {
          return `<div class="form-group col-sm-6">
            <div>
              <label class="col-form-label">
                ${config.col[key].title ? config.col[key].title : key}
              </label>
            </div>
            <label class="switch">
              <input type="checkbox" name="${key}" id="${key}" ${row[key] ? 'checked' : ''} value="true">
              <span class="slider round"></span>
            </label>
          </div>`
        }
        if (
          'dataType' in config.col[key] &&
          config.col[key].dataType === 'select'
        ) {
          return `<div class="form-group col-sm-6">
            <label for="${key}" class="col-form-label">
              ${(key in config.col && config.col[key].title) ? config.col[key].title : key}:
            </label>
            <select class="form-control" name="${key}" id="${key}">
              ${Object.keys(config.col[key].select).map(opt => {
                return `<option value="${opt}" ${row[key] === opt ? 'selected' : ''}>${config.col[key].select[opt]}</option>`
              }).join('')}
            </select>
          </div>`
        }
        if (
          'dataType' in config.col[key] &&
          config.col[key].dataType === 'search' &&
          'search' in config &&
          key in config.search
        ) {
          const selected = config.search[key].filter(r => r['data'] === row[key])
          return `<div class="form-group col-sm-6">
            <label for="${key}" class="col-form-label">
              ${(key in config.col && config.col[key].title) ? config.col[key].title : key}:
            </label>
            <input type="text"
              class="form-control"
              id="${key}"
              data-target="${key}"
              value="${selected.length ? selected[0].value : ''}"
              autocomplete
              ${config.col[key].required ? 'required' : ''}>
              <input type="hidden" name="${key}" value="${row[key] || ''}">
          </div>`
        }
        return `<div class="form-group col-sm-6">
          <label for="${key}" class="col-form-label">
            ${(key in config.col && config.col[key].title) ? config.col[key].title : key}:
          </label>
          <input type="text"
            class="form-control"
            id="${key}"
            name="${key}"
            value="${row[key] || ''}"
            ${config.col[key].required ? 'required' : ''}>
            ${config.col[key].required ? `<div class="invalid-feedback">
              Eesnimi peab olema täidetud
            </div>` : ''}
        </div>`
      }).join(''))
    if (row[config.primary]) {
      modal.find('.delete').prop('disabled', false)
    }
    if (config.search) {
      modal.find('[autocomplete]').each((i, item) => {
        const target = $(item).data('target')
        if (target in config.search) {
          const ac = this.autoComplete($(item), {
            lookup: config.search[target],
            onSelect: suggestion => {
              modal.find(`[name=${target}]`).val(suggestion.data)
            },
            onInvalidateSelection: () => {
              modal.find(`[name=${target}]`).val('')
            }
          })
          if (modal.find(`[name=${target}]`).val().length) {
            // Set selection property on the instance:
            ac.autocomplete().selection = $(`[name=${target}]`).val()
          }
        }
      })
    }
  },
  autoComplete (el, options) {
    return el.autocomplete(Object.assign({
      lookup: [],
      minChars: 2,
      orientation: 'auto',
      lookupLimit: 10,
      triggerSelectOnValidInput: false,
      onSelect: suggestion => {},
      onInvalidateSelection: () => {}
    }, options))
  },
  onHide () {
    tableModal.find('[autocomplete]').each((i, item) => {
      $(item).autocomplete('dispose')
    })
    tableModal.find('form').removeClass('was-validated')
    tableModal.find('form').html('')
    tableModal.find('.save').removeClass('btn-success').addClass('btn-primary')
    tableModal.find('.delete')
      .removeClass('btn-danger').addClass('btn-light')
      .data('action', 'confirm')
      .prop('disabled', true)
    tableModal = null
  },
  validate () {
    tableModal.find('form').addClass('was-validated')
    return tableModal.find('form')[0].checkValidity()
  },
  onSave () {
    tableModal.find('.save').toggleClass('btn-primary btn-success')
    setTimeout(() => {
      tableModal.modal('hide')
    }, 900)
  },
  deleteConfirm () {
    const btn = tableModal.find('.delete')
    return new Promise((resolve, reject) => {
      switch (btn.data('action')) {
        case 'confirm':
          btn
            .toggleClass('btn-light btn-danger')
            .data('action', 'confirmed')
            .find('span').text('Kinnita kustutamine')
          break;
        case 'confirmed':
          btn
            .toggleClass('btn-light btn-danger')
            .data('action', 'confirm')
            .prop('disabled', true)
            .find('span').text('Kustuta')
          resolve()
          break
      }
    })
  },
  onDelete () {
    setTimeout(() => {
      $('#rowEdit').modal('hide')
    }, 900)
  },
  nameFormat (row) {
    const nameArr = [row.firstname]
    if (row.lastname) {
      nameArr.push(row.lastname)
    }
    if (row.birth && row.death) {
      nameArr.push(`(${row.birth} - ${row.death})`)
    } else if (row.birth) {
      nameArr.push(`(${row.birth})`)
    } else if (row.death) {
      nameArr.push(`( - ${row.death})`)
    }
    return nameArr.join(' ')
  }
}
