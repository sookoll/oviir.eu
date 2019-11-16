function ImageTagger(selector, data) {
  this.el = $(selector)
  this.active = false
  this.canvas = null
  this.tags = data
  this.ii = this.tags.length
  this.paint = false
  this.events = {
    'activate': [],
    'deactivate': [],
    'save': [],
    'remove': [],
    'select': [],
    'deselect': []
  }
  this.el.find('.photomap').on('click', e => {
    $(e.target).find('div').removeClass('active')
    this.fire('deselect')
  })

  this.getImage = () => {
    return this.el.find('img')
  }

  this.on = (event, cb) => {
    if (event in this.events) {
      this.events[event].push(cb)
    }
  }

  this.fire = (event, data) => {
    if (event in this.events) {
      this.events[event].forEach(cb => {
        cb(data)
      })
    }
  }

  this.activate = () => {
    this.active = true
    const w = this.getImage().width()
    const h = this.getImage().height()
    this.canvas = $(`<canvas class="tagCanvas" width="${w}" height="${h}"></canvas>`)
    this.el.append(this.canvas)
    this.canvas.css('cursor', 'crosshair')
    this.el.find('.photomap').html('')
    this.redraw()
    this.canvas.on('mousedown', e => {
      if (!this.paint && this.active) {
        this.paint = true
        this.addClick(e.pageX - this.el.offset().left, e.pageY - this.el.offset().top)
      }
    })
    this.canvas.on('mousemove', e => {
      if (this.paint && this.active) {
        this.addClick(e.pageX - this.el.offset().left, e.pageY - this.el.offset().top, true)
        this.redraw()
      }
    })
    this.canvas.on('mouseup', e => {
      if (this.paint && this.active) {
        this.addTagger()
      }
    })
    this.fire('activate')
  }

  this.deactivate = () => {
    this.active = false
    if (this.canvas) {
      this.canvas.remove()
    }
    let map = []
    for (let i in this.tags) {
      const x = this.tags[i].xi < this.tags[i].xii ? this.tags[i].xi : this.tags[i].xii
      const y = this.tags[i].yi < this.tags[i].yii ? this.tags[i].yi : this.tags[i].yii
      map.push(`<div id="${i}" data-toggle="tooltip"
        style="top: ${y}%; left:${x}%; width: ${Math.abs(this.tags[i].xii - this.tags[i].xi)}%; height: ${Math.abs(this.tags[i].yii - this.tags[i].yi)}%;"
        title="${this.tags[i].username}"><i class="icon ion-md-close" data-toggle="tooltip" data-placement="bottom" title="Kustuta"></i></div>`)
    }
    this.fire('deactivate', this.tags)
    this.el.find('.photomap').html(map.join(''))
    this.el.find('[data-toggle="tooltip"]').tooltip()
    this.el.find('.photomap').find('div').on('click', e => {
      e.stopPropagation()
      this.el.find('.photomap').find('div').not(e.target).removeClass('active')
      $(e.target).toggleClass('active')
      const event = $(e.target).hasClass('active') ? 'select' : 'deselect'
      this.fire(event, $(e.target).attr('id'))
    })
    this.el.find('.photomap').find('div i').on('click', e => {
      e.stopPropagation()
      $(e.target).tooltip('hide')
      $(e.target).parent().tooltip('hide')
      const id = $(e.target).parent().attr('id')
      this.removeTag(id)
      $(e.target).parent().remove()
    })
  }

  this.redraw = () => {
    if (this.active) {
      const context = this.canvas[0].getContext("2d")
      this.canvas[0].width = this.canvas[0].width // Clears the canvas
      context.strokeStyle = "#fff"
      context.lineJoin = "round"
      context.lineWidth = 3
      context.globalAlpha = 0.7
      for (let i in this.tags) {
        context.strokeRect(
          this.px(this.tags[i].xi, this.canvas[0].width),
          this.px(this.tags[i].yi, this.canvas[0].height),
          this.px(this.tags[i].xii - this.tags[i].xi, this.canvas[0].width),
          this.px(this.tags[i].yii - this.tags[i].yi, this.canvas[0].height)
        )
      }
    }
  }

  this.addClick = (x, y, dragging) => {
    if (dragging) {
      this.tags[this.ii].xii = this.pc(x, this.canvas[0].width)
      this.tags[this.ii].yii = this.pc(y, this.canvas[0].height)
    } else {
      this.tags[this.ii] = {
        id: null,
        user: null,
        username: null,
        xi: this.pc(x, this.canvas[0].width),
        yi: this.pc(y, this.canvas[0].height),
        xii: this.pc(x, this.canvas[0].width),
        yii: this.pc(y, this.canvas[0].height)
      }
    }
  }

  this.addTagger = () => {
    this.paint = false
    // check size
    if (
      this.px(Math.abs(this.tags[this.ii].xii - this.tags[this.ii].xi), this.canvas[0].width) < 10 ||
      this.px(Math.abs(this.tags[this.ii].yii - this.tags[this.ii].yi), this.canvas[0].height) < 10
    ) {
      this.removeTag(this.ii)
      return false
    }
    const x = this.tags[this.ii].xi + (this.tags[this.ii].xii - this.tags[this.ii].xi) / 2
    const y = this.tags[this.ii].yi + (this.tags[this.ii].yii - this.tags[this.ii].yi) / 2
    const pop = $('<div class="tagger"></div>')
    pop.css({ top: y + '%', left: x + '%', width: '2px', height: '2px', position: 'absolute' })
    this.el.append(pop)
    pop.popover({
      html : true,
      title: `&nbsp;<button type="button" class="close" data-dismiss="popover" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>`,
      content: `<div class="input-group p-2">
        <input type="text" class="form-control" placeholder="Sisesta nimi">
        <div class="input-group-append">
          <button class="btn btn-primary save" type="button">
            <i class="icon ion-ios-save"></i>
          </button>
        </div>
      </div>`,
      placement: 'auto',
      trigger: 'manual',
      offset: 50
    }).on('shown.bs.popover', e => {
      $('.popover').find('input').focus()
      $('.popover').find('.close').on('click', e => {
        pop.popover('hide')
        this.removeTag(this.ii)
        this.deactivate()
      })
      $('.popover').find('.save').on('click', e => {
        pop.popover('hide')
        this.saveTag(this.ii, $('.popover').find('input').val())
      })
      this.active = false
    }).on('hidden.bs.popover', e => {
      pop.remove()
      this.active = true
    }).popover('show')
  }

  this.saveTag = (i, value) => {
    // save to api
    this.tags[this.ii].username = value
    this.tags[this.ii].user = 1
    this.fire('save', this.tags[this.ii])
    this.ii++
    this.deactivate()
  }

  this.removeTag = (i) => {
    this.fire('remove', [i, this.tags[i]])
    this.tags.splice(i, 1)
    this.redraw()
  }

  this.getTagEl = (i) => {
    return this.el.find('#' + i)
  }

  this.px = (percent, size) => {
    return percent * size / 100
  }

  this.pc = (px, size) => {
    return px * 100 / size
  }

  this.deactivate()
}
