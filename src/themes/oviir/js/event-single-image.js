const api = new Api({ endpoint: config.url, hash: config.hash })

function createTag (i, name) {
  return `<button class="btn btn-link tag" data-target="${i}">${name}</button>`
}

function init(data) {
  const infoText = $('#tags').html()
  $('#tags').html(data.length ? data.map((tag, i) => createTag(i, tag.username)).join('') : infoText)
  const it = new ImageTagger('#phototag', data)

  it.on('activate', () => {
    $('#activate').prop('disabled', true)
    $('#tags').find('.tag').prop('disabled', true)
    if (!config.search.person.length) {
      const searchApi = new Api({ endpoint: config.searchUrl, hash: config.hash })
      searchApi.load({ params: config.searchParams })
        .then(response => {
          config.search.person = response.map(row => {
            return {
              data: row[config.primary],
              value: `${TableModal.nameFormat(row)}`
            }
          })
        })
        .catch(e => {
          console.error(e)
        })
    }
  })
  it.on('deactivate', (tags) => {
    $('#activate').prop('disabled', false)
    $('#tags').find('.tag').prop('disabled', false)
    $('#tags').html(tags.length ? tags.map((tag, i) => createTag(i, tag.username)).join('') : infoText)
  })
  it.on('save', (data) => {
    api.insert(formatRow(Object.assign({}, data)))
      .then(response => {
        //console.log(response)
      })
      .catch(e => {
        console.error(e)
      })
  })
  it.on('remove', (data) =>{
    $('#tags').find(`.tag[data-target='${data[0]}'] `).remove()
    if (!$('#tags').find('.tag').length) {
      $('#tags').html(infoText)
    }
    if (data[1] && data[1][config.primary]) {
      const primary = data[1][config.primary]
      data = {}
      data[config.delete] = true
      api.update(primary, data)
        .then(response => {
          //
        })
        .catch(e => {
          console.error(e)
        })
    }
  })
  it.on('draw', (data) => {
    data.pop.find('[autocomplete]').each((i, item) => {
      const ac = TableModal.autoComplete($(item), {
        lookup: config.search.person,
        onSelect: suggestion => {
          data.row.user = suggestion.data
        },
        onInvalidateSelection: () => {
          data.row[config.primary] = null
        }
      })
    })
  })
  it.on('select', (i) => {
    $('#tags').find(`.tag`).removeClass('active')
    $('#tags').find(`.tag[data-target=${i}]`).addClass('active')
  })
  it.on('deselect', (i) => {
    $('#tags').find(`.tag`).removeClass('active')
  })
  // activate
  $('#activate').on('click', e => {
    it.activate()
  })

  $('#tags').on('mouseover', '.tag', e => {
    it.getTagEl($(e.target).data('target')).mouseover()
  }).on('mouseout', '.tag', e => {
    it.getTagEl($(e.target).data('target')).mouseout()
  }).on('click', '.tag', e => {
    e.preventDefault()

  });
}

function formatRow (row) {
  delete row.id
  row.category = config.category
  row.item = config.item
  row.author = config.user
  return row
}

(function($) {

  setTimeout(() => {
    if (!config.url) {
      return false;
    }
    api.load({ params: config.params })
      .then(init)
      .catch(e => {
        console.error(e)
      })
  }, 200)

})(jQuery);
