
const api = new Api({ endpoint: config.url, hash: config.hash });
let apidata = []
let pz = null

function getNested(arr, parent) {
  let out = []
  for (var i in arr) {
    if (arr[i].bound_with == parent) {
      const related = getNested(arr, arr[i].id)
      const children = related.filter(r => r.bound_is === 'child')
      const partners = related.filter(r => r.bound_is === 'partner')
      const unsure = related.filter(r => r.bound_is !== 'partner' && r.bound_is !== 'child')
      if(children.length) {
        arr[i].children = children
      }
      if(partners.length) {
        arr[i].partner = partners
      }
      if(unsure.length) {
        out = out.concat(unsure)
      }
      out.push(arr[i])
    }
  }
  return out
}

function getFormData() {
  const row = $('#rowEdit').find('form').serializeObject()
  return formatRow(row)
}

function formatRow(row) {
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
  })
  return row
}

function initSearch() {
  if (config.search) {
    $('[autocomplete]').each((i, item) => {
      const target = $(item).data('target')
      if (target in config.search) {
        const ac = TableModal.autoComplete($(item), {
          lookup: config.search[target],
          onSelect: suggestion => {
            zoomTo(suggestion.data)
          }
        })
      }
    })
  }
}

function render(root) {
  return `<li>
  ${root.children ? `
    <i class="icon ion-ios-arrow-dropup-circle toggle"></i>` : ''}
  <span>
    ${labelContent(root)}
  </span>
  ${root.children ? `
    <ul>${root.children.map(child => render(child)).join('')}</ul>` : ''}
  </li>`
}

function labelContent(row) {
  const rows = [row].concat(row.partner || [])
  return rows.map((item, i) => {
    const name = (i === 0 && row.id !== 0) ? `<b>${TableModal.nameFormat(item)}</b>` :
    `${TableModal.nameFormat(item)}`
    return `<a href="#${item.id}"
    draggable="false"
    ${item.id ? `data-toggle="modal"
    data-target="#rowEdit"` : ''}
    data-row="${item.id}">${name}</a>`
  }).join('&hearts;')
}

function zoomTo (id) {
  const ppos = $('.tree > ul > li').offset()
  const el = $(`[data-row="${id}"]`)
  // open closed trees
  el.parentsUntil('#map', 'ul').removeClass('hidden')
  if (ppos && el.length) {
    const center = {
      left: ppos.left - el.offset().left + window.innerWidth / 2 - el.width() / 2,
      top: ppos.top - el.offset().top + window.innerHeight / 2 - el.height() / 2
    }
    //pz.zoomTo(pz.getTransform().x, pz.getTransform().y, 1.5)
    //pz.smoothZoom(window.innerWidth / 2, window.innerHeight / 2, 1.5 - pz.getTransform().scale)
    pz.moveTo(center.left, center.top)
    setTimeout(() => {
      pz.smoothZoom(window.innerWidth / 2, window.innerHeight / 2, 10)
    }, 150)
    el.closest('span').addClass('selected')
    setTimeout(() => {
      el.closest('span').removeClass('selected')
    }, 5000)
  }
}

function initTree(data) {
  const treeData = getNested(data)
  let html
  let count = 0
  if (treeData.length > 1) {
    html = render({
      id: 0,
      firstname: 'Oviiride',
      lastname: 'Sugupuu',
      children: treeData
    })
  } else if (treeData.length > 0) {
    html = render(treeData[0])
  }
  $('#map .tree').html(`<ul>${html}</ul>`)

  // And pass it to panzoom
  pz = panzoom(document.querySelector('.tree'), {
    zoomSpeed: 0.02,
    zoomDoubleClickSpeed: 2,
    maxZoom: 1.5,
    minZoom: 0.1,
    onTouch: e => {
      // `e` - is current touch event.
      return false; // tells the library to not preventDefault.
    }
  })
  pz.on('panend', e => {})
  $('#map .tree i.toggle').on('click', e => {
    $(e.target).toggleClass('ion-ios-arrow-dropup-circle ion-ios-arrow-dropdown-circle')
    $(e.target).closest('li').find('>ul').toggleClass('hidden')
  })
}

function reload(id) {
  api.load({ params: config.params })
    .then(response => {
      apidata = response
      config.search.bound_with = apidata.map(row => {
        return {
          data: row[config.primary],
          value: `${TableModal.nameFormat(row)}`
        }
      })
      initTree(apidata)
      initSearch()
      if (typeof id !== 'undefined') {
        setTimeout(() => {
          zoomTo(id)
        }, 250)
      }
    })
    .catch(e => {
      console.error(e)
    })
}

(function($) {

  setTimeout(() => {
    if (!config.url) {
      return false
    }
    const hash = window.location.hash.slice(1)
    reload(hash || 0)
  })

  window.onhashchange = () => {
    const hash = window.location.hash.slice(1)
    if (!isNaN(hash)) {
      zoomTo(hash)
    }
  }

  // modal
  $('#rowEdit').on('show.bs.modal', e => {
    if ($(e.relatedTarget).attr('href')) {
      window.location.hash = $(e.relatedTarget).attr('href')
    }
    const primary = Number($(e.relatedTarget).data('row'))
    let row = Object.keys(config.col).reduce((a,b)=> (a[b]='',a),{})
    if (primary) {
      let rows = apidata.filter(item => {
        return item[config.primary] === primary
      })
      if (rows.length) {
        row = formatRow(rows[0])
      }
    }
    TableModal.onShow($(e.target), config, row)
  }).on('hide.bs.modal', e => {
    TableModal.onHide()
  })

  // save row
  $('#rowEdit').on('click', '.save', e => {
    if (!TableModal.validate()) {
      return false
    }
    const row = getFormData()
    const primary = row[config.primary]
    delete row[config.primary]
    if (primary) {
      api.update(primary, row)
      .then(response => {
        TableModal.onSave()
        reload(primary)
      })
      .catch(error => console.error(error))
    } else {
      api.insert(row)
      .then(response => {
        TableModal.onSave()
        reload(response)
      })
      .catch(error => console.error(error))
    }
  })

  // delete row
  $('#rowEdit').on('click', '.delete', e => {
    TableModal.deleteConfirm()
      .then(() => {
        let row = getFormData()
        const primary = row[config.primary]
        const parent = row.bound_with || null
        row = {}
        row[config.delete] = true
        api.update(primary, row)
          .then(response => {
            console.log(parent)
            reload(parent || 0)
            TableModal.onDelete()
          })
          .catch(error => console.error(error))
      })
      .catch(e => console.error(e))
  });

})(jQuery);
