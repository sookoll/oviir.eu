const Draft = {
  url: null,
  path: null,
  id: null,
  changed: false,
  editor: null,
  init (textarea, path, id, url) {
    this.url = url
    this.path = path
    this.id = id
    this.editor = CodeMirror.fromTextArea(textarea, {
      mode : 'markdown',
      lineNumbers : true,
      theme : "default",
      extraKeys : {
        "Enter" : "newlineAndIndentContinueMarkdownList"
      },
      viewportMargin: Infinity,
      cutter: true
    });
    if (url && id) {
      this.load(`${this.url}?path=${ path ? path + '/' : '' }${ id }`)
        .then(content => {
          this.editor.setValue(content)
          setTimeout(() => {
            this.editor.refresh()
          }, 150)
        })
        .catch(e => {
          console.error(e)
          //$('#draft').modal('hide')
        })
    }

  },
  load(url) {
    return new Promise((resolve, reject) => {
      fetch(url)
        .then(response => response.text())
        .then(response => resolve(response))
        .catch(reject)
    })
  },
  dispose() {
    this.editor.toTextArea()
    this.editor = null
    this.url = null
    this.path = null
    this.id = null
    this.changed = false
  },
  validate() {
    return this.editor.getValue().length > 100 &&
      $('#draft').find('.modal-title span').text().length > 0
  },
  save() {
    return new Promise((resolve, reject) => {
      fetch(this.url, {
        method: this.path ? 'POST' : 'PUT',
        body: JSON.stringify({
          path: `${this.path}${this.id}`,
          content: this.editor.getValue()
        })
      }).then(response => response.text())
        .then(response => {
          if (response === 'ok') {
            this.changed = true
            resolve(response)
          } else {
            reject(new Error(response))
          }
        })
        .catch(reject)
    })
  },
  deleteConfirm() {
    const btn = $('#draft').find('.delete')
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
  delete() {
    return new Promise((resolve, reject) => {
      fetch(this.url, {
        method: 'DELETE',
        body: JSON.stringify({
          path: `${this.path}${this.id}`
        })
      }).then(response => response.text())
        .then(response => {
          if (response === 'ok') {
            this.changed = true
            resolve(response)
          } else {
            reject(new Error(response))
          }
        })
        .catch(reject)
    })
  }
};

(function($) {

  // modal
  $('#draft').on('show.bs.modal', e => {
    const mode = $(e.relatedTarget).data('draft-type')
    const url = $(e.relatedTarget).data('draft-url')
    const path = $(e.relatedTarget).data('draft-path')
    const id = $(e.relatedTarget).data('draft-id')
    $(e.target).find('.modal-title b').text(mode === 'edit' ? `Muuda:` : `Lisa: ${path}`)
    $(e.target).find('.modal-title span').text(id)
    if (mode === 'create') {
      $(e.target).find('.modal-title span')
      .prop('contenteditable', mode === 'create')
      .selectText()
    }
    if (mode === 'edit') {
      $(e.target).find('.delete').prop('disabled', false)
    }
    Draft.init($(e.target).find('textarea')[0], path, id, url)
    Draft.editor.on('change', (cm, change) => {
      $(e.target).find('.save').removeClass('btn-success').addClass('btn-primary')
    });
  }).on('hide.bs.modal', e => {
    const path = Draft.path
    const file = Draft.id
    const changed = Draft.changed
    $(e.target).find('.modal-title b').text('')
    $(e.target).find('.modal-title span').text('').prop('contenteditable', false)
    $(e.target).find('.save').removeClass('btn-success').addClass('btn-primary')
    $(e.target).find('.delete')
      .removeClass('btn-danger').addClass('btn-light')
      .data('action', 'confirm')
      .prop('disabled', true)
    Draft.dispose()
    if (changed && path.length === 0) {
      window.location.reload()
    }
    if (changed && path && file) {
      window.location.href = BASE_URL + '/' + path + file
    }
  })

  // save row
  $('#draft').on('click', '.save', e => {
    if (!Draft.validate()) {
      return false
    }
    Draft.save()
      .then(response => {
        $('#draft').find('.save').toggleClass('btn-primary btn-success')
        if (Draft.path) {
          setTimeout(() => {
            $('#draft').modal('hide')
          }, 900)
        }
      })
      .catch(error => {
        const info = $('#draft').find('.info').text()
        $('#draft').find('.save').removeClass('btn-success btn-primary').addClass('btn-danger')
        $('#draft').find('.info').text(error.toString())
        setTimeout(() => {
          $('#draft').find('.save').removeClass('btn-danger').addClass('btn-primary')
          $('#draft').find('.info').text(info)
        }, 5000)
      })
  })

  $(window).on('keydown', e => {
    if (e.ctrlKey || e.metaKey) {
      switch (String.fromCharCode(e.which).toLowerCase()) {
        case 's':
          e.preventDefault();
          $('#draft').find('.save').trigger('click')
          break;
      }
    }
  });

  // delete row
  $('#draft').on('click', '.delete', e => {
    Draft.deleteConfirm()
      .then(() => {
        Draft.delete()
          .then(response => {
            setTimeout(() => {
              $('#draft').modal('hide')
            }, 900)
          })
          .catch(error => {
            const info = $('#draft').find('.info').text()
            $('#draft').find('.delete')
              .removeClass('btn-danger').addClass('btn-light')
              .data('action', 'confirm')
            $('#draft').find('.info').text(error.toString())
            setTimeout(() => {
              $('#draft').find('.save').removeClass('btn-danger').addClass('btn-primary')
              $('#draft').find('.info').text(info)
            }, 5000)
          })
      })
      .catch(console.error)
  });

})(jQuery);
